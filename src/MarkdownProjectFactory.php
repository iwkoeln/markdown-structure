<?php

namespace Iwm\MarkdownStructure;

use InvalidArgumentException;
use Iwm\MarkdownStructure\Collection\FinisherCollection;
use Iwm\MarkdownStructure\Collection\MarkdownProjectFinisherCollection;
use Iwm\MarkdownStructure\Collection\ParserCollection;
use Iwm\MarkdownStructure\Collection\MarkdownProjectValidatorCollection;
use Iwm\MarkdownStructure\Collection\ValidatorCollection;
use Iwm\MarkdownStructure\Finisher\FallbackUrlForProjectFileLinksFinisher;
use Iwm\MarkdownStructure\Finisher\FinisherInterface;
use Iwm\MarkdownStructure\Finisher\MarkdownProject\CollectFileErrorsFinisher;
use Iwm\MarkdownStructure\Finisher\MarkdownProject\MarkdownProjectFinisherInterface;
use Iwm\MarkdownStructure\Parser\MarkdownToHtmlParser;
use Iwm\MarkdownStructure\Parser\ParserInterface;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Iwm\MarkdownStructure\Utility\FileTreeBuilder;
use Iwm\MarkdownStructure\Utility\GitFilesFinder;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Validator\MarkdownImageValidator;
use Iwm\MarkdownStructure\Validator\MarkdownLinksValidator;
use Iwm\MarkdownStructure\Validator\MediaFileValidator;
use Iwm\MarkdownStructure\Validator\MarkdownProject\OrphanFileValidator;
use Iwm\MarkdownStructure\Validator\MarkdownProject\MarkdownProjectValidatorInterface;
use Iwm\MarkdownStructure\Validator\ValidatorInterface;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class MarkdownProjectFactory
{
    // Configuration
    private string $projectRootPath;
    public readonly string $absoluteDocumentationPath;
    private ?string $fallbackBaseUrl;
    public bool $enableNestedStructure = true;

    // Files
    /** @var array|string[] All (but documentation) files in given repository */
    public array $projectFiles = [];
    /** @var array|string[] Markdown files */
    public array $documentationFiles = [];
    /** @var array|string[] Media files */
    public array $documentationMediaFiles = [];
    /** @var array|string[] External files but referenced */
    public array $referencedExternalFiles = [];

    // Nesting
    public array $nestedDocumentationFiles = [];

    // Extendable Collections
    private ?ParserCollection $parser = null;
    private ?ValidatorCollection $validators = null;
    private ?FinisherCollection $finisher = null;
    private ?MarkdownProjectValidatorCollection $markdownProjectValidators = null;
    private ?MarkdownProjectFinisherCollection $markdownProjectFinisher = null;

    public function __construct(
        string                  $projectRootPath,
        readonly private string $documentationPath = '/docs',
        readonly private string $documentationIndexFile = '/index.md',
        ?string                 $fallbackBaseUrl = null,
    )
    {
        // Set paths
        $this->projectRootPath = rtrim(realpath($projectRootPath), DIRECTORY_SEPARATOR);
        // Check if the directory exists
        if (!is_dir($this->projectRootPath)) {
            throw new InvalidArgumentException(sprintf('Given project root path "%s" does not exist.', $this->projectRootPath));
        }
        $this->absoluteDocumentationPath = $this->projectRootPath . DIRECTORY_SEPARATOR . trim($documentationPath, DIRECTORY_SEPARATOR);
        if (!is_dir($this->absoluteDocumentationPath)) {
            throw new InvalidArgumentException(sprintf('Given documentation path "%s" does not exist.', $this->absoluteDocumentationPath));
        }
        $this->fallbackBaseUrl = $fallbackBaseUrl;

        // Init Collections
        $this->parser = new ParserCollection();
        $this->validators = new ValidatorCollection();
        $this->finisher = new FinisherCollection();
        $this->markdownProjectValidators = new MarkdownProjectValidatorCollection();
        $this->markdownProjectFinisher = new MarkdownProjectFinisherCollection();

        // Set default file parser
        $this->parser->add(new MarkdownToHtmlParser());

        // Set default file validator
        $this->validators->add(new MarkdownLinksValidator());
        $this->validators->add(new MarkdownImageValidator());
        $this->validators->add(new MediaFileValidator());

        // Set default file finisher
        $this->finisher->add(new FallbackUrlForProjectFileLinksFinisher());

        // Set default markdown project validator
        $this->markdownProjectValidators->add(new OrphanFileValidator());

        // Set default markdown project finisher
        $this->markdownProjectFinisher->add(new CollectFileErrorsFinisher());
    }

    public function create(): MarkdownProject
    {
        // Process Files - Parse, validate, finish
        $files = array_merge($this->documentationFiles, $this->documentationMediaFiles);
        foreach ($files as $file) {

            $this->parseFile($file);

            $this->validateFile($file);

            $this->finishFile($file);

            // Set the file in the nested file tree as value
            if ($this->enableNestedStructure) {
                FileTreeBuilder::setValueFromNestedReferencesArray($this->nestedDocumentationFiles, $file, $file);
            }
        }

        // Setup Markdown Project
        $markdownProject = new MarkdownProject(
            $this->projectRootPath,
            $this->documentationPath,
            $this->documentationIndexFile,
            $this->documentationFiles,
            $this->documentationMediaFiles,
            $this->projectFiles,
            $this->referencedExternalFiles,
            $this->nestedDocumentationFiles
        );

        // Validate Markdown Project
        $this->validateMarkdownProject($markdownProject);

        // Finish Markdown Project
        $this->finishMarkdownProject($markdownProject);

        return $markdownProject;
    }

    /*****************************************
     * PARSER, VALIDATOR, FINISHER
     *****************************************/

    public function parseFile(MarkdownFile|MediaFile $file): void
    {
        /** @var ParserInterface $parser */
        foreach ($this->parser->getItems() as $parser) {
            $parser->parse($file, $this->documentationFiles, $this->documentationMediaFiles, $this->projectFiles);
        }
    }

    public function finishFile(MarkdownFile|MediaFile $file): void
    {
        /** @var FinisherInterface $finisher */
        foreach ($this->finisher->getItems() as $finisher) {
            $finisher->finish($file, $this->documentationFiles, $this->documentationMediaFiles, $this->projectFiles);
        }
    }

    private function validateFile(MarkdownFile|MediaFile $file): void
    {
        /** @var ValidatorInterface $validator */
        foreach ($this->validators->getItems() as $validator) {
            $validator->validate($file, $this->documentationFiles, $this->documentationMediaFiles);
        }
    }

    private function validateMarkdownProject(MarkdownProject $markdownProject): void
    {
        /** @var MarkdownProjectValidatorInterface $validator */
        foreach ($this->markdownProjectValidators->getItems() as $validator) {
            $validator->validate($markdownProject);
        }
    }

    private function finishMarkdownProject(MarkdownProject $markdownProject): void
    {
        /** @var MarkdownProjectFinisherInterface $finisher */
        foreach ($this->markdownProjectFinisher->getItems() as $finisher) {
            $finisher->finish($markdownProject);
        }
    }

    /*****************************************
     * PATH TO OBJECT CONVERSIONS
     *****************************************/

    private function covertFilePathToMarkdownFileObject(string $filePath): MarkdownFile
    {
        return new MarkdownFile(
            $this->projectRootPath,
            $filePath,
            $this->readFile($filePath),
            '',
            $this->fallbackBaseUrl,
            []
        );
    }

    private function convertFilePathToMediaFileObject(string $filePath): MediaFile
    {
        return new MediaFile(
            $filePath,
            $filePath,
        );
    }

    /*****************************************
     * ADDING AND READING FILES
     *****************************************/

    // TODO: Definieren aus welchem Scope der Dateipfad erwartet wird (/var/www/html/ oder basierend auf /var/www/html/docs/)
    public function addFile(string|SplFileInfo $file):void
    {
        if (is_string($file)) {
            $filePath = trim($file);
            // TODO: Prüfen ob ein absoluter Pfad gegeben wurde und ob die Datei existiert
            // TODO: Wenn ja, dann den absoluten Pfad verwenden
            // TODO: Wenn nein, dann den Pfad auflösen, basierend auf dem gegebenen $this->projectPath
        } elseif ($file instanceof SplFileInfo) {
            $filePath = $file->getRealPath();
        } else {
            $type = gettype($file);
            if ($type === 'object') {
                $type = get_class($file);
            }
            throw new InvalidArgumentException(sprintf('Project files of MarkdownProject allows array of strings or SplFileInfo, only. %s given.', $type));
        }

        if (!str_starts_with($filePath, $this->projectRootPath)) {
            $this->referencedExternalFiles[$filePath] = $filePath;
        } elseif (str_starts_with($filePath, $this->absoluteDocumentationPath) && PathUtility::isMarkdownFile($filePath)) {
            $this->documentationFiles[$filePath] = $this->covertFilePathToMarkdownFileObject($filePath);
        } elseif (str_starts_with($filePath, $this->absoluteDocumentationPath) && PathUtility::isMediaFile($filePath)) {
            $this->documentationMediaFiles[$filePath] = $this->convertFilePathToMediaFileObject($filePath);
        } else {
            $this->projectFiles[$filePath] = $filePath;
        }
    }

    private function readFile(string $filePath): string
    {
        // Check if the project path is a Git repository
        if (PathUtility::isGitRepository($this->documentationPath)) {

            // Extract the relative path from the full file path
            $relativePath = PathUtility::resolveRelativePath($this->documentationPath, $filePath);

            // Use the git show command to get the file content from the bare repository
            $branchOrTag = 'master';
            $command = sprintf('git --git-dir=%s show %s:%s',
                escapeshellarg($this->documentationPath),
                escapeshellarg($branchOrTag),
                escapeshellarg($relativePath));

            $output = shell_exec($command);

            if ($output === null) {
                throw new RuntimeException(sprintf('Failed to read file from Git repository: %s', $filePath));
            }
            return $output;
        } else {

            // For regular directories, read the file content normally
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new RuntimeException(sprintf('Failed to open file: %s', $filePath));
            }
            return $content;
        }
    }

    public function addFiles(array|Finder $files):void
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    /*****************************************
     * EXTENDABLE CONFIGURATION
     *****************************************/

    public function registerParser(array $parsers): void
    {
        /** @var ParserInterface $parser */
        foreach ($parsers as $parser) {
            $this->parser->add($parser);
        }
    }

    public function registerValidators(array $validators): void
    {
        /** @var ValidatorInterface $validator */
        foreach ($validators as $validator) {
            $this->validators->add($validator);
        }
    }

    public function registerFinisher(array $finishers): void
    {
        /** @var FinisherInterface $finsiher */
        foreach ($finishers as $finisher) {
            $this->finisher->add($finisher);
        }
    }

    public function registerProjectValidators(array $validators): void
    {
        /** @var MarkdownProjectValidatorInterface $validator */
        foreach ($validators as $validator) {
            $this->markdownProjectValidators->add($validator);
        }
    }

    public function registerProjectFinishers(array $finishers): void
    {
        /** @var MarkdownProjectFinisherInterface $finsiher */
        foreach ($finishers as $finisher) {
            $this->markdownProjectFinisher->add($finisher);
        }
    }

    /*****************************************
     * DROPPABLE STUFF
     *****************************************/

    public function addExternalSource(string $name, string $path)
    {
        // TODO: Specify functions function
    }

    public function loadFilesByPath(string $path): void
    {
        // TODO: Remove this method...
        // Check if the path is a Git repository (bare or non-bare)
        if (PathUtility::isGitRepository($path)) {
            $files = GitFilesFinder::listTrackedFiles($path);
        } else {
            $files = FilesFinder::findFilesByPath($path);
        }

        $this->addFiles($files);
    }
}

<?php

namespace Iwm\MarkdownStructure;

use InvalidArgumentException;
use Iwm\MarkdownStructure\Collection\FinisherCollection;
use Iwm\MarkdownStructure\Collection\ParserCollection;
use Iwm\MarkdownStructure\Collection\ValidatorCollection;
use Iwm\MarkdownStructure\Error\ErrorInterface;
use Iwm\MarkdownStructure\Finisher\FallbackUrlForProjectFileLinksFinisher;
use Iwm\MarkdownStructure\Finisher\FinisherInterface;
use Iwm\MarkdownStructure\Parser\MarkdownToHtmlParser;
use Iwm\MarkdownStructure\Parser\ParserInterface;
use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Iwm\MarkdownStructure\Utility\FileTreeBuilder;
use Iwm\MarkdownStructure\Utility\GitFilesFinder;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Validator\MarkdownImageValidator;
use Iwm\MarkdownStructure\Validator\MarkdownLinksValidator;
use Iwm\MarkdownStructure\Validator\MediaFileValidator;
use Iwm\MarkdownStructure\Validator\OrphanValidator;
use Iwm\MarkdownStructure\Validator\ValidatorInterface;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;
use SplFileInfo;
use Symfony\Component\Finder\Finder;

class MarkdownProjectFactory
{
    // Configuration
    public bool $enableNestedStructure = true;
    private string $projectRootPath;
//    public readonly string $documentationPath;
//    private string $documentationIndexFile;
    public readonly string $absoluteDocumentationPath;
    private ?string $fallbackBaseUrl;

    // Files
    /** @var array|string[] All (but documentation) files in given repository */
    public array $projectFiles = [];
    /** @var array|string[] External files but referenced */
    public array $referencedExternalFiles = [];
    /** @var array|string[] Markdown files */
    public array $documentationFiles = [];
    /** @var array|string[] Media files */
    public array $documentationMediaFiles = [];

    // Nesting
    public ?array $nestedDocumentationFiles = null;

    // Output
    public ?array $links = null;
    public ?array $errors = null;
    public ?array $orphans = null;

    // Utilities and Collections
    public ?ValidatorCollection $validators = null;
    private ?ParserCollection $parser = null;
    private ?FinisherCollection $finisher = null;

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

        $this->fallbackBaseUrl = $fallbackBaseUrl;

        // Init Collections
        $this->parser = new ParserCollection();
        $this->validators = new ValidatorCollection();
        $this->finisher = new FinisherCollection();

        // Set default parser
        $this->parser->add(new MarkdownToHtmlParser());

        // Set default validator
        $this->validators->add(new MarkdownLinksValidator());
        $this->validators->add(new MarkdownImageValidator());
        $this->validators->add(new MediaFileValidator());
        $this->validators->add(new OrphanValidator());

        // Set default finisher
        $this->finisher->add(new FallbackUrlForProjectFileLinksFinisher());
    }

    public function create(): MarkdownProject
    {
        if ($this->enableNestedStructure) {
            $this->nestedDocumentationFiles = FileTreeBuilder::buildFileTree($this->documentationFiles);
        }

        $this->processDocumentationFiles();
        $this->processDocumentationMediaFiles();
        //TODO: VALIDATE PROJECT

        return new MarkdownProject(
            $this->projectRootPath,
            $this->documentationPath,
            $this->documentationIndexFile,
            $this->documentationFiles,
            $this->documentationMediaFiles,
            $this->projectFiles,
            $this->referencedExternalFiles,
            $this->nestedDocumentationFiles,
            $this->errors,
            $this->orphans
        );
    }

    private function processDocumentationFiles(): void
    {
        foreach ($this->documentationFiles as $documentationFile) {
            // TODO: hier die parser ausführen, nicht in ->addFiles()?
            $links = $this->extractLinks($documentationFile->html, $documentationFile);
            if (count($links) > 0 && !isset($this->links[(string) $documentationFile])) {
                $this->links[(string) $documentationFile] = $links;
            }

            $errors = $this->performValidation($documentationFile->html, $documentationFile);

            if (!empty($errors)) {
                $documentationFile->errors = $errors;
            }

            $this->finishFile($documentationFile);

            if ($this->enableNestedStructure) {
                $this->setValueFromNestedReferencesArray($this->nestedDocumentationFiles, $documentationFile, $documentationFile);
            }
        }

        $orphanValidator = $this->validators->getItem(OrphanValidator::class);
        if ($orphanValidator instanceof OrphanValidator) {
            $this->orphans = $orphanValidator->getOrphanFiles($this->documentationFiles);
        }
    }

    private function processDocumentationMediaFiles(): void
    {
        foreach ($this->documentationMediaFiles as $projectMediaFilePath) {
            $errors = $this->performValidation(null, $projectMediaFilePath);

            $newMediaFile = new MediaFile(
                $projectMediaFilePath,
                $projectMediaFilePath,
                $errors
            );
            $this->documentationMediaFiles[$projectMediaFilePath] = $newMediaFile;

            if ($this->enableNestedStructure) {
                $this->setValueFromNestedReferencesArray($this->nestedDocumentationFiles, $projectMediaFilePath, $newMediaFile);
            }
        }
    }

    public function parseFile(MarkdownFile $markdownFile): void
    {
        /** @var ParserInterface $parser */
        foreach ($this->parser->getItems() as $parser) {
            $parser->parse($markdownFile, $this->documentationFiles, $this->documentationMediaFiles, $this->projectFiles);
        }
    }

    public function finishFile(MarkdownFile $markdownFile): void
    {
        /** @var FinisherInterface $finisher */
        foreach ($this->finisher->getItems() as $finisher) {
            $finisher->finish($markdownFile, $this->documentationFiles, $this->documentationMediaFiles, $this->projectFiles);
        }
    }

    private function performValidation(string|null $parsedResult, string $filePath): array
    {
        /** @var ValidatorInterface $validator */
        foreach ($this->validators->getItems() as $validator) {
            // Get errors from the current validator
            $validatorErrors = $validator->validate($parsedResult, $filePath, $this->documentationFiles, $this->documentationMediaFiles);
            if (!empty($validatorErrors)) {

                // Initialize the errors array for the current file if not already set
                if (!isset($this->errors[$filePath])) {
                    $this->errors[$filePath] = [];
                }

                /** @var ErrorInterface $error */
                foreach ($validatorErrors as $error) {
                    // Assuming getErrorMessage() returns a string that can be used as a unique key
                    $errorKey = $error->getErrorMessage();

                    // Only add if the error message is not already present
                    if (!isset($this->errors[$filePath][$errorKey])) {
                        $this->errors[$filePath][$errorKey] = $error;
                    }
                }
            }
        }

        // If there were any errors added, flatten the errors array to remove keys
        if (isset($this->errors[$filePath])) {
            $this->errors[$filePath] = array_values($this->errors[$filePath]);
        }

        // Return the list of errors for the current file, or an empty array if there were none
        return $this->errors[$filePath] ?? [];
    }

    private function setValueFromNestedReferencesArray(array &$nestedFiles, string $newFilePath, mixed $newFileObject): void
    {
        $keys = explode('/', $newFilePath);
        $lastKey = array_pop($keys);
        $current = &$nestedFiles;
        foreach ($keys as $key) {
            $current = &$current[$key];
        }
        $current[$lastKey] = $newFileObject;
    }

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
            $this->documentationFiles[$filePath] = $this->registerDocumentationFile($filePath);
        } elseif (str_starts_with($filePath, $this->absoluteDocumentationPath) && PathUtility::isMediaFile($filePath)) {
            $this->documentationMediaFiles[$filePath] = $filePath;
        } else {
            $this->projectFiles[$filePath] = $filePath;
        }
    }

    private function registerDocumentationFile(string $filePath): MarkdownFile
    {
        $markdownContent = $this->readFile($filePath);

        $newMarkdownFile = new MarkdownFile(
            $this->projectRootPath,
            $filePath,
            $markdownContent,
            '',
            $this->fallbackBaseUrl,
            []
        );

        $this->parseFile($newMarkdownFile);

        return $newMarkdownFile;
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
                throw new \RuntimeException(sprintf('Failed to read file from Git repository: %s', $filePath));
            }
            return $output;
        } else {

            // For regular directories, read the file content normally
            $content = file_get_contents($filePath);
            if ($content === false) {
                throw new \RuntimeException(sprintf('Failed to open file: %s', $filePath));
            }
            return $content;
        }
    }

    private function extractLinks(string $parsedResult, object|string $currentFile): array
    {
        $extractedLinks = DomLinkExtractor::extractLinks($parsedResult, (string) $currentFile);

        $links = array_filter($extractedLinks, function ($link) {
            // Perform any additional checks or processing here
            // Return true to keep the link, or false to skip it
            return true;
        });

        return $links;
    }

    public function addFiles(array|Finder $files):void
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    public function loadFilesByPath(string $path): void
    {
        // Check if the path is a Git repository (bare or non-bare)
        if (PathUtility::isGitRepository($path)) {
            $files = GitFilesFinder::listTrackedFiles($path);
        } else {
            $files = FilesFinder::findFilesByPath($path);
        }

        $this->addFiles($files);
    }

    public function registerValidators(array $validators): void
    {
        /** @var ValidatorInterface $validator */
        foreach ($validators as $validator) {
            $this->validators->add($validator);
        }
    }

    public function registerParser(array $parsers): void
    {
        /** @var ParserInterface $parser */
        foreach ($parsers as $parser) {
            $this->parser->add($parser);
        }
    }

    public function registerFinisher(array $finishers): void
    {
        /** @var FinisherInterface $finsiher */
        foreach ($finishers as $finsiher) {
            $this->finisher->add($finsiher);
        }
    }

    public function addExternalSource(string $name, string $path)
    {
        // TODO: Specify functions function
    }
}

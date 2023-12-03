<?php

namespace Iwm\MarkdownStructure;

use Iwm\MarkdownStructure\Collection\FinisherCollection;
use Iwm\MarkdownStructure\Collection\MarkdownProjectFinisherCollection;
use Iwm\MarkdownStructure\Collection\MarkdownProjectValidatorCollection;
use Iwm\MarkdownStructure\Collection\ParserCollection;
use Iwm\MarkdownStructure\Collection\ValidatorCollection;
use Iwm\MarkdownStructure\Finisher\FallbackUrlForProjectFileLinksFinisher;
use Iwm\MarkdownStructure\Finisher\FinisherInterface;
use Iwm\MarkdownStructure\Finisher\MarkdownProject\CollectFileErrorsFinisher;
use Iwm\MarkdownStructure\Finisher\MarkdownProject\MarkdownProjectFinisherInterface;
use Iwm\MarkdownStructure\Parser\MarkdownToHtmlParser;
use Iwm\MarkdownStructure\Parser\ParserInterface;
use Iwm\MarkdownStructure\Utility\FileTreeBuilder;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Validator\MarkdownImageValidator;
use Iwm\MarkdownStructure\Validator\MarkdownLinksValidator;
use Iwm\MarkdownStructure\Validator\MarkdownProject\MarkdownProjectValidatorInterface;
use Iwm\MarkdownStructure\Validator\MarkdownProject\OrphanFileValidator;
use Iwm\MarkdownStructure\Validator\MediaFileValidator;
use Iwm\MarkdownStructure\Validator\ValidatorInterface;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;
use Symfony\Component\Finder\Finder;

class MarkdownProjectFactory
{
    // Configuration
    private string $projectRootPath;
    public readonly string $absoluteDocumentationPath;
    private ?string $fallbackBaseUrl;
    public bool $enableNestedStructure = true;

    // Files
    /** @var array<string> All (but documentation) files in given repository */
    public array $projectFiles = [];
    /** @var array<MarkdownFile> Markdown files */
    public array $documentationFiles = [];
    /** @var array<MediaFile> Media files */
    public array $documentationMediaFiles = [];
    /** @var array<string> External files but referenced */
    public array $referencedExternalFiles = [];

    // Nesting
    /**
     * @var array<string, mixed> Nested file tree, with file or folder name as values
     */
    public array $nestedDocumentationFiles = [];

    // Extendable Collections
    private ParserCollection $parser;
    private ValidatorCollection $validators;
    private FinisherCollection $finisher;
    private MarkdownProjectValidatorCollection $markdownProjectValidators;
    private MarkdownProjectFinisherCollection $markdownProjectFinisher;

    /**
     * MarkdownProjectFactory constructor.
     * This Method will set the project root path and the documentation path.
     * It will also set the default parser, validator and finisher.
     *
     * @param string      $projectRootPath        The absolute path to the project root directory
     * @param string      $documentationPath      The relative path to the documentation directory
     * @param string      $documentationIndexFile The relative path to the documentation index file
     * @param string|null $fallbackBaseUrl        The fallback base url for links to files in the project
     */
    public function __construct(
        string $projectRootPath,
        readonly private string $documentationPath = '/docs',
        readonly private string $documentationIndexFile = '/index.md',
        string $fallbackBaseUrl = null,
    ) {
        // Set paths
        if ($realProjectRootPath = realpath($projectRootPath)) {
            $this->projectRootPath = rtrim($realProjectRootPath, DIRECTORY_SEPARATOR);
        } else {
            $this->projectRootPath = rtrim($projectRootPath, DIRECTORY_SEPARATOR);
        }

        // Check if the directory exists and build paths
        if (!is_dir($this->projectRootPath)) {
            throw new \InvalidArgumentException(sprintf('Given project root path "%s" does not exist.', $this->projectRootPath));
        }
        $this->absoluteDocumentationPath = $this->projectRootPath . DIRECTORY_SEPARATOR . trim($documentationPath, DIRECTORY_SEPARATOR);
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

    /**
     * Create a MarkdownProject from the given files.
     */
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

    // PARSER, VALIDATOR, FINISHER

    /**
     * Parse the given file.
     */
    public function parseFile(MarkdownFile|MediaFile $file): void
    {
        /** @var ParserInterface $parser */
        foreach ($this->parser as $parser) {
            $parser->parse($file, $this->documentationFiles, $this->documentationMediaFiles, $this->projectFiles);
        }
    }

    /**
     * Finish the given file.
     */
    public function finishFile(MarkdownFile|MediaFile $file): void
    {
        /** @var FinisherInterface $finisher */
        foreach ($this->finisher as $finisher) {
            $finisher->finish($file, $this->documentationFiles, $this->documentationMediaFiles, $this->projectFiles);
        }
    }

    /**
     * Validate the given file.
     */
    private function validateFile(MarkdownFile|MediaFile $file): void
    {
        /** @var ValidatorInterface $validator */
        foreach ($this->validators as $validator) {
            $validator->validate($file, $this->documentationFiles, $this->documentationMediaFiles);
        }
    }

    // Validate the given MarkdownProject
    private function validateMarkdownProject(MarkdownProject $markdownProject): void
    {
        /** @var MarkdownProjectValidatorInterface $validator */
        foreach ($this->markdownProjectValidators as $validator) {
            $validator->validate($markdownProject);
        }
    }

    // Finish the given MarkdownProject
    private function finishMarkdownProject(MarkdownProject $markdownProject): void
    {
        /** @var MarkdownProjectFinisherInterface $finisher */
        foreach ($this->markdownProjectFinisher as $finisher) {
            $finisher->finish($markdownProject);
        }
    }

    // PATH TO OBJECT CONVERSIONS

    /**
     * Convert the given file path to a MarkdownFile object.
     */
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

    /**
     * Convert the given file path to a MediaFile object.
     */
    private function convertFilePathToMediaFileObject(string $filePath): MediaFile
    {
        return new MediaFile(
            $filePath,
            $filePath,
        );
    }

    // ADDING AND READING FILES

    /**
     * Add Files manually by providing the relative path of the file from the projectRootPath.
     */
    public function addFile(string|\SplFileInfo $file): void
    {
        if (is_string($file)) {
            $filePath = trim($file);
            if (!PathUtility::isAbsolutePath($filePath)) {
                $filePath = PathUtility::resolveAbsolutePath($this->projectRootPath, $filePath);
            }
        } elseif ($file instanceof \SplFileInfo) {
            $filePath = $file->getRealPath();
        } else {
            throw new \InvalidArgumentException(sprintf('Project files of MarkdownProject allows array of strings or SplFileInfo, only. %s given.', gettype($file)));
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

    /**
     * Read the file content from the given file path.
     */
    private function readFile(string $filePath): string
    {
        // Check if the project path is a Git repository
        if (PathUtility::isGitRepository($this->absoluteDocumentationPath)) {

            // Extract the relative path from the full file path
            $relativePath = PathUtility::resolveRelativePath($this->projectRootPath, $filePath);

            // Use the git show command to get the file content from the bare repository
            $branchOrTag = 'master';
            $command = sprintf(
                'git --git-dir=%s show %s:%s',
                escapeshellarg($this->projectRootPath),
                escapeshellarg($branchOrTag),
                escapeshellarg($relativePath)
            );

            $output = shell_exec($command);

            if (null === $output || false === $output) {
                throw new \RuntimeException(sprintf('Failed to read file from Git repository: %s', $filePath));
            }

            return $output;
        }

        // For regular directories, read the file content normally
        $content = file_get_contents($filePath);
        if (false === $content) {
            throw new \RuntimeException(sprintf('Failed to open file: %s', $filePath));
        }

        return $content;

    }

    /**
     * Add Files programmatically by providing an array of file paths.
     *
     * @param array<string>|Finder $files
     */
    public function addFiles(array|Finder $files): void
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    // EXTENDABLE CONFIGURATION

    /**
     * Register each parser in the given array.
     *
     * @param array<ParserInterface> $parsers
     */
    public function registerParser(array $parsers): void
    {
        foreach ($parsers as $parser) {
            $this->parser->add($parser);
        }
    }

    /**
     * Register each validator in the given array.
     *
     * @param array<ValidatorInterface> $validators
     */
    public function registerValidators(array $validators): void
    {
        foreach ($validators as $validator) {
            $this->validators->add($validator);
        }
    }

    /**
     * Register each finisher in the given array.
     *
     * @param array<FinisherInterface> $finishers
     */
    public function registerFinisher(array $finishers): void
    {
        foreach ($finishers as $finisher) {
            $this->finisher->add($finisher);
        }
    }

    /**
     * Register each project validator in the given array.
     *
     * @param array<MarkdownProjectValidatorInterface> $validators
     */
    public function registerProjectValidators(array $validators): void
    {
        foreach ($validators as $validator) {
            $this->markdownProjectValidators->add($validator);
        }
    }

    /**
     * Register each project finisher in the given array.
     *
     * @param array<MarkdownProjectFinisherInterface> $finishers
     */
    public function registerProjectFinishers(array $finishers): void
    {
        foreach ($finishers as $finisher) {
            $this->markdownProjectFinisher->add($finisher);
        }
    }
}

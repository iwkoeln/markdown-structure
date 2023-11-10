<?php

namespace Iwm\MarkdownStructure;

use Closure;
use DOMElement;
use InvalidArgumentException;
use Iwm\MarkdownStructure\Collection\AfterRegistrationParserCollection;
use Iwm\MarkdownStructure\Collection\BeforeCreationParserCollection;
use Iwm\MarkdownStructure\Collection\ValidatorCollection;
use Iwm\MarkdownStructure\Parser\FallbackUrlForProjectFileLinksParser;
use Iwm\MarkdownStructure\Parser\MarkdownToHtmlParser;
use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Iwm\MarkdownStructure\Utility\FileTreeBuilder;
use Iwm\MarkdownStructure\Utility\GitFilesFinder;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Validator\MarkdownImageValidator;
use Iwm\MarkdownStructure\Validator\MarkdownLinksValidator;
use Iwm\MarkdownStructure\Validator\MediaFileValidator;
use Iwm\MarkdownStructure\Validator\OrphanValidator;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;
use League\CommonMark\ConverterInterface;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Output\RenderedContentInterface;
use League\Config\Exception\ConfigurationExceptionInterface;
use SplFileInfo;

class MarkdownProjectFactory
{
    // Configuration
    public bool $enableNestedStructure = true;
    private string $projectPath;
    private string $documentationPath;
    private string $documentationEntryPoint;
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
    private ?AfterRegistrationParserCollection $afterRegistrationParsers = null;
    private ?BeforeCreationParserCollection $beforeCreationParsers = null;
    public ConverterInterface $markdownParser;

    public function __construct(
        string $projectPath,
        string $documentationPath = '/docs',
        string $documentationEntryPoint = '/index.md',
        ?string $fallbackBaseUrl = null,
    )
    {
        // Set paths
        $this->projectPath = $projectPath;
        $this->documentationPath = $projectPath . $documentationPath;
        $this->documentationEntryPoint = $this->documentationPath . $documentationEntryPoint;
        $this->fallbackBaseUrl = $fallbackBaseUrl;

        // Init Collections
        $this->validators = new ValidatorCollection();
        $this->afterRegistrationParsers = new AfterRegistrationParserCollection();
        $this->beforeCreationParsers = new BeforeCreationParserCollection();

        // Set default markdownParser
        $this->afterRegistrationParsers->add(new MarkdownToHtmlParser());
        $this->beforeCreationParsers->add(new FallbackUrlForProjectFileLinksParser());

        // Set default validator
        $this->validators->add(new MarkdownLinksValidator());
        $this->validators->add(new MarkdownImageValidator());
        $this->validators->add(new MediaFileValidator());
        $this->validators->add(new OrphanValidator());

        // Load files in the documentation path
        $this->loadFilesByPath($this->documentationPath);
    }

    public function create(): MarkdownProject
    {
        if ($this->enableNestedStructure) {
            $this->nestedDocumentationFiles = FileTreeBuilder::buildFileTree($this->documentationFiles);
        }

        $this->processDocumentationFiles();
        $this->processDocumentationMediaFiles();

        return new MarkdownProject(
            $this->projectPath,
            $this->documentationPath,
            $this->documentationEntryPoint,
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

            $links = $this->extractLinks($documentationFile->html, $documentationFile);
            if (count($links) > 0 && !isset($this->links[(string) $documentationFile])) {
                $this->links[(string) $documentationFile] = $links;
            }

            $errors = $this->performValidation($documentationFile->html, $documentationFile);

            if (!empty($errors)) {
                $documentationFile->errors = $errors;
            }

            $this->parseFileBeforeCreation($documentationFile);


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

    public function parseFileAfterRegistrationFile(MarkdownFile $markdownFile): void
    {
        foreach ($this->afterRegistrationParsers->getItems() as $parser) {
            $parser->parse($markdownFile, $this->documentationFiles, $this->documentationMediaFiles, $this->projectFiles);
        }
    }

    public function parseFileBeforeCreation(MarkdownFile $markdownFile): void
    {
        foreach ($this->beforeCreationParsers->getItems() as $parser) {
            $parser->parse($markdownFile, $this->documentationFiles, $this->documentationMediaFiles, $this->projectFiles);
        }
    }

    private function performValidation(string|null $parsedResult, string $filePath): array
    {
        foreach ($this->validators->getItems() as $validator) {

            // Get errors from the current validator
            $validatorErrors = $validator->validate($parsedResult, $filePath, $this->documentationFiles, $this->documentationMediaFiles);
            if (!empty($validatorErrors)) {

                // Initialize the errors array for the current file if not already set
                if (!isset($this->errors[$filePath])) {
                    $this->errors[$filePath] = [];
                }

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

    public function addFile(string|SplFileInfo $file):void
    {
        if (is_string($file)) {
            $filePath = trim($file);
        } elseif ($file instanceof SplFileInfo) {
            $filePath = $file->getPath();
        } else {
            $type = gettype($file);
            if ($type === 'object') {
                $type = get_class($file);
            }
            throw new InvalidArgumentException(sprintf('Project files of MarkdownProject allows array of strings or SplFileInfo, only. %s given.', $type));
        }

        if (!str_starts_with($filePath, $this->projectPath)) {
            $this->referencedExternalFiles[$filePath] = $filePath;
        } elseif (str_starts_with($filePath, $this->documentationPath) && PathUtility::isMarkdownFile($filePath)) {
            $this->documentationFiles[$filePath] = $this->registerDocumentationFile($filePath);
        } elseif (str_starts_with($filePath, $this->documentationPath) && PathUtility::isMediaFile($filePath)) {
            $this->documentationMediaFiles[$filePath] = $filePath;
        } else {
            $this->projectFiles[$filePath] = $filePath;
        }
    }

    private function registerDocumentationFile(string $filePath): MarkdownFile
    {
        $markdownContent = $this->readFile($filePath);

        $newMarkdownFile = new MarkdownFile(
            $this->projectPath,
            $filePath,
            $markdownContent,
            '',
            $this->fallbackBaseUrl,
            []
        );

        $this->parseFileAfterRegistrationFile($newMarkdownFile);

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

    public function addFiles(array $files):void
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }

    public function loadFilesByPath(string $path): void
    {
        // Check if the path is a Git repository (bare or non-bare)
        if (PathUtility::isGitRepository($path)) {
            $files = GitFilesFinder::listTrackedFiles($path, );
        } else {
            $files = FilesFinder::findFilesByPath($path);
        }

        $this->addFiles($files);
    }

    public function registerValidators(array $validators): void
    {
        foreach ($validators as $validator) {
            $this->validators->add($validator);
        }
    }

    public function registerParserForAfterRegistration(array $parsers): void
    {
        foreach ($parsers as $parser) {
            $this->afterRegistrationParsers->add($parser);
        }
    }

    public function registerParserForBeforeCreation(array $parsers): void
    {
        foreach ($parsers as $parser) {
            $this->beforeCreationParsers->add($parser);
        }
    }

    public function addExternalSource(string $name, string $path)
    {
        // TODO: Specify functions function
    }
}

<?php

namespace Iwm\MarkdownStructure;

use Closure;
use DOMElement;
use InvalidArgumentException;
use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Iwm\MarkdownStructure\Utility\FileTreeBuilder;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Validator\MarkdownLinksValidator;
use Iwm\MarkdownStructure\Validator\ValidatorInterface;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownLink;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;
use League\CommonMark\ConverterInterface;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use League\Config\Exception\ConfigurationExceptionInterface;
use SplFileInfo;
use Symfony\Component\DomCrawler\Crawler;

class MarkdownProjectFactory
{
    public bool $enableNestedStructure = true;

    /** @var array|string[] All (but documentation) files in given repository */
    public array $projectFiles = [];

    /** @var array|string[] External files but referenced */
    public array $referencedExternalFiles = [];
    /** @var array|string[] Markdown files */
    public array $documentationFiles = [];
    /** @var array|string[] Media files */
    public array $documentationMediaFiles = [];
    public ?array $nestedDocumentationFiles = null;

    public ?array $links = null;
    public ?array $errors = null;
    public Closure|null $readFileFunction = null;
    public ConverterInterface $markdownParser;

    /** @var array|ValidatorInterface[] */
    public array $validators = [];
    private array $fileParsers = [];

    public function __construct(
        private readonly string $projectPath,
        string                  $documentationPath = 'docs/',
        string                  $documentationEntryPoint = 'index.md',
        private ?string         $fallbackBaseUrl = null,
    )
    {
        // Set default markdownParser
        $parser = new GithubFlavoredMarkdownConverter([]);
        $parser->getEnvironment()->addExtension(new HeadingPermalinkExtension());
        $this->markdownParser = $parser;

        // Set default validator
        $this->validators[] = new MarkdownLinksValidator();

        $this->documentationPath = $projectPath . $documentationPath;
        $this->documentationEntryPoint = $documentationPath . $documentationEntryPoint;

        $this->loadFilesByPath($this->documentationPath);
    }

    /**
     * @throws ConfigurationExceptionInterface
     * @throws CommonMarkException
     */
    public function create(): MarkdownProject
    {
        if ($this->enableNestedStructure) {
            $this->nestedDocumentationFiles = FileTreeBuilder::buildFileTree($this->documentationFiles);
        }

        // TODO: Parse Input Group
        $this->processDocumentationFiles();
        $this->processDocumentationMediaFiles();

        // TODO: Validate Output Group

        // TODO: Grep validation results like OrphanValidator

        // TODO: Parse Output Group

        return new MarkdownProject(
            $this->projectPath,
            $this->documentationPath,
            $this->documentationFiles,
            $this->documentationMediaFiles,
            $this->documentationEntryPoint,
            $this->projectFiles,
            $this->referencedExternalFiles,
            $this->nestedDocumentationFiles,
            $this->errors
        );
    }

    /**
     * @throws ConfigurationExceptionInterface
     * @throws CommonMarkException
     */
    private function processDocumentationFiles(): void
    {
        foreach ($this->documentationFiles as $projectFilePath) {
            $markdownContent = $this->readFile($projectFilePath);
            $parsedResult = $this->markdownParser->convert($markdownContent);

            $links = $this->extractLinks($parsedResult, $projectFilePath);
            if (count($links) > 0 && !isset($this->links[$projectFilePath])) {
                $this->links[$projectFilePath] = $links;
            }

            $errors = $this->performValidation($parsedResult, $projectFilePath);

            $newMarkdownFile = new MarkdownFile(
                $projectFilePath,
                $markdownContent,
                $parsedResult->getContent(),
                $this->fallbackBaseUrl,
                $errors
            );
            $this->parseFile($newMarkdownFile);

            $this->documentationFiles[$projectFilePath] = $newMarkdownFile;

            if ($this->enableNestedStructure) {
                $this->setValueFromNestedReferencesArray($this->nestedDocumentationFiles, $projectFilePath, $newMarkdownFile);
            }
        }
    }

    public function parseFile(MarkdownFile $markdownFile): void
    {
        foreach ($this->fileParsers as $parser) {
            $parser->parse($markdownFile, $this->documentationFiles, $this->documentationMediaFiles, $this->projectFiles);
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

    private function performValidation(RenderedContentInterface|null $parsedResult, string $filePath): array
    {
        $errors = [];
        if (empty($this->validators) === false) {
            foreach ($this->validators as $validator) {
                $this->errors[$filePath] = array_merge($errors, $validator->validate($parsedResult, $filePath, $this->documentationFiles));
            }
        }
        return $errors;
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
            $this->documentationFiles[$filePath] = $filePath;
        } elseif (str_starts_with($filePath, $this->documentationPath) && PathUtility::isMediaFile($filePath)) {
            $this->documentationMediaFiles[$filePath] = $filePath;
        } else {
            $this->projectFiles[$filePath] = $filePath;
        }
    }

    private function readFile(string $filePath): string
    {
        $closure = $this->readFileFunction;
        if (null === $closure) {
            $closure = static function (string $filePath) {
                return file_get_contents($filePath);
            };
        }

        return $closure($filePath);
    }

    private function extractLinks(RenderedContentInterface $parsedResult, object|string $currentFile): array
    {
        $extractedLinks = DomLinkExtractor::extractLinks($parsedResult, (string) $currentFile);

        $links = array_filter($extractedLinks, function ($link) {
            // Perform any additional checks or processing here
            // For example, you might want to skip certain links based on some criteria
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
        $this->addFiles(FilesFinder::findFilesbyPath($path));
    }

    public function addValidators(array $validators): void
    {
        $this->validators = array_merge($this->validators, $validators);
    }

    public function addFileParsers(array $parsers): void
    {
        $this->fileParsers = array_merge($this->fileParsers, $parsers);
    }

    public function addExternalSource(string $name, string $path)
    {
        // TODO: Specify functions function
    }
}

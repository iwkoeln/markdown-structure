<?php

namespace Iwm\MarkdownStructure;

use Closure;
use DOMElement;
use InvalidArgumentException;
use Iwm\MarkdownStructure\Parser\CombineTextAndImagesParser;
use Iwm\MarkdownStructure\Parser\CombineTextAndListParser;
use Iwm\MarkdownStructure\Parser\HeadlinesToSectionParser;
use Iwm\MarkdownStructure\Parser\MarkdownToHTMLParser;
use Iwm\MarkdownStructure\Parser\ParagraphToContainerParser;
use Iwm\MarkdownStructure\Parser\SectionParser;
use Iwm\MarkdownStructure\Parser\SplitByEmptyLineParser;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Iwm\MarkdownStructure\Utility\FileTreeBuilder;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Validator\MarkdownProjectValidator;
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

    /** @var array|string[] All (but project) files in given repository */
    public array $files = [];

    /** @var array|string[] External files but referenced */
    public array $referencedExternalFiles = [];
    /** @var array|string[] Markdown project files */
    public array $projectFiles = [];
    /** @var array|string[] Media project files */
    public array $projectMediaFiles = [];
    public ?array $nestedProjectFiles = null;

    public ?array $links = null;
    public ?array $errors = null;
    public Closure|null $readFileFunction = null;
    public ConverterInterface $markdownParser;

    /** @var array|ValidatorInterface[] */
    public array $validators = [];
    private array $fileParsers = [];

    public function __construct(
        private readonly string $rootPath,
        string $projectPath = 'docs/',
        string $projectEntryPointPath = 'index.md',
        private ?string $fallbackBaseUrl = null,
    )
    {
        // Set default markdownParser
        $parser = new GithubFlavoredMarkdownConverter([]);
        $parser->getEnvironment()->addExtension(new HeadingPermalinkExtension());
        $this->markdownParser = $parser;

        // Set default validator
        $this->validators[] = new MarkdownProjectValidator();

        $this->projectPath = $rootPath . $projectPath;
        $this->projectEntryPointPath = $projectPath . $projectEntryPointPath;

        $this->loadFilesByPath($this->projectPath);
    }

    /**
     * @throws ConfigurationExceptionInterface
     * @throws CommonMarkException
     */
    public function create(): MarkdownProject
    {
        if ($this->enableNestedStructure) {
            $this->nestedProjectFiles = FileTreeBuilder::buildFileTree($this->projectFiles);
        }

        // TODO: Set default fileParsers
        if ($this->fileParsers === []) {
            $this->fileParsers = [
                new SplitByEmptyLineParser(),
                new HeadlinesToSectionParser(),
                new CombineTextAndImagesParser(),
                new CombineTextAndListParser(),
                new MarkdownToHTMLParser(),
                new ParagraphToContainerParser(),
            ];
        }

        $this->processMarkdownFiles();
        $this->processMediaFiles();

        return new MarkdownProject(
            $this->rootPath,
            $this->projectPath,
            $this->projectFiles,
            $this->projectMediaFiles,
            $this->projectEntryPointPath,
            $this->files,
            $this->referencedExternalFiles,
            $this->nestedProjectFiles,
            $this->errors
        );
    }

    /**
     * @throws ConfigurationExceptionInterface
     * @throws CommonMarkException
     */
    private function processMarkdownFiles(): void
    {
        foreach ($this->projectFiles as $projectFilePath) {
            $markdownContent = $this->readFile($projectFilePath);
            $parsedResult = $this->markdownParser->convert($markdownContent);

            $links = $this->extractLinks($parsedResult, $projectFilePath);
            if (count($links) > 0 && !isset($this->links[$projectFilePath])) {
                $this->links[$projectFilePath] = $links;
            }

            $errors = $this->performValidation($parsedResult, $projectFilePath);

            $newMarkdownFile = new MarkdownFile($projectFilePath, $markdownContent, $parsedResult->getContent(), $errors);
            $this->parseFile($newMarkdownFile);

            $this->projectFiles[$projectFilePath] = $newMarkdownFile;

            if ($this->enableNestedStructure) {
                $this->setValueFromNestedReferencesArray($this->nestedProjectFiles, $projectFilePath, $newMarkdownFile);
            }
        }
    }

    public function parseFile(MarkdownFile $markdownFile): void
    {
        foreach ($this->fileParsers as $parser) {
            $parser->parse($markdownFile);
        }
    }

    private function processMediaFiles(): void
    {
        foreach ($this->projectMediaFiles as $projectMediaFilePath) {
            $errors = $this->performValidation(null, $projectMediaFilePath);

            $newMediaFile = new MediaFile($projectMediaFilePath, $projectMediaFilePath, $errors);
            $this->projectMediaFiles[$projectMediaFilePath] = $newMediaFile;

            if ($this->enableNestedStructure) {
                $this->setValueFromNestedReferencesArray($this->nestedProjectFiles, $projectMediaFilePath, $newMediaFile);
            }
        }
    }

    private function performValidation(RenderedContentInterface|null $parsedResult, string $filePath): array
    {
        $errors = [];
        if (empty($this->validators) === false) {
            foreach ($this->validators as $validator) {
                $this->errors[$filePath] = array_merge($errors, $validator->validate($parsedResult, $filePath, $this->projectFiles));
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

        if (!str_starts_with($filePath, $this->rootPath)) {
            $this->referencedExternalFiles[$filePath] = $filePath;
        } elseif (str_starts_with($filePath, $this->projectPath) && PathUtility::isMarkdownFile($filePath)) {
            $this->projectFiles[$filePath] = $filePath;
        } elseif (str_starts_with($filePath, $this->projectPath) && PathUtility::isMediaFile($filePath)) {
            $this->projectMediaFiles[$filePath] = $filePath;
        } else {
            $this->files[$filePath] = $filePath;
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
        $domCrawler = new Crawler($parsedResult->getContent());
        $linkNodes = $domCrawler->filter('a');

        $links = [];
        foreach ($linkNodes as $linkNode) {
            if ($linkNode instanceof DOMElement) {
                $href = $linkNode->getAttribute('href');
                $urlParts = parse_url($href);
                if (!isset($urlParts['path']) || str_starts_with($href, 'mailto:')) {
                    continue;
                }

                $link = new MarkdownLink($href, PathUtility::isExternalUrl($href), (string) $currentFile);

                $links[] = $link;
            }
        }

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

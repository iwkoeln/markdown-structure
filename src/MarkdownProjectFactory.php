<?php

namespace IWM\MarkdownStructure;

use IWM\MarkdownStructure\Value\MarkdownFile;
use IWM\MarkdownStructure\Value\MarkdownLink;
use IWM\MarkdownStructure\Value\MarkdownProject;
use League\CommonMark\ConverterInterface;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use Symfony\Component\DomCrawler\Crawler;

class MarkdownProjectFactory
{
    public bool $enableNestedStructure = true;
    public bool $enableValidation = true;

    /** @var array|string[] All (but project) files in given repository */
    private array $files = [];
    /** @var array|string[] Markdown project files */
    private array $projectFiles = [];
    /** @var array|string[] Media project files */
    private array $projectMediaFiles = [];
    private ?array $projectFilesNested = null;

    private ?array $errors = null;
    private \Closure|null $readFileFunction = null;
    private ConverterInterface $markdownParser;
    public ValidatorInterface $validator;


    public function __construct(
        readonly private string $rootPath,
        readonly private string $fallbackBaseUrl,
        readonly private string $indexPath = 'index.md',
    )
    {
        // Set default markdownParser
        $parser = new GithubFlavoredMarkdownConverter([]);
        $parser->getEnvironment()->addExtension(new HeadingPermalinkExtension());
        $this->setMarkdownParser($parser);

        // Set default validator
        $this->validator = new MarkdownProjectValidator();
    }

    public function addFile(string|\SplFileInfo $file):void
    {
        if (is_string($file)) {
            $filePath = trim($file);
        } elseif ($file instanceof \SplFileInfo) {
            $filePath = $file->getPath();
        } else {
            $type = gettype($file);
            if ($type === 'object') {
                $type = get_class($file);
            }
            throw new \InvalidArgumentException(sprintf('Project files of MarkdownProject allows array of strings or SplFileInfo, only. %s given.', $type));
        }

        if (str_starts_with($filePath, $this->rootPath)) {
            if (str_ends_with($filePath, '.md')) {
                $this->projectFiles[$filePath] = $filePath;
            } else {
                $this->projectMediaFiles[$filePath] = $filePath;
            }
        } else {
            $this->files[$filePath] = $filePath;
        }
    }

    public function setFiles(array $files):void
    {
        foreach ($files as $file) {
            $this->addFile($file);
        }
    }


    public function setMarkdownParser(ConverterInterface $markdownParser): void
    {
        $this->markdownParser = $markdownParser;
    }

    public function setReadFileFunction(\Closure $readFileClosure): void
    {
        $this->readFileFunction = $readFileClosure;
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

    public function create(): MarkdownProject
    {
        if ($this->enableNestedStructure) {
            $this->projectFilesNested = FileTreeBuilder::buildFileTree($this->projectFiles);
        }

        foreach ($this->projectFiles as $projectFilePath) {
            $markdownContent = $this->readFile($projectFilePath);
            $parsedResult = $this->markdownParser->convert($markdownContent);

            $links = $this->extractLinks($parsedResult);



            $errors = null;
            if ($this->enableValidation) {
                $errors = $this->validator->validate($parsedResult, $projectFilePath, $this->projectFiles);
                if (count($errors) > 0 && !isset($this->errors[$projectFilePath])) {
                    $this->errors[$projectFilePath] = $errors;
                }

            }

            $newMarkdownFile = new MarkdownFile($projectFilePath, $markdownContent, $parsedResult->getContent(), $errors);
            $this->projectFiles[$projectFilePath] = $newMarkdownFile;
            if ($this->enableNestedStructure) {
                $this->setValueFromNestedReferencesArray($this->projectFilesNested, $projectFilePath, $newMarkdownFile);
            }

        }

        foreach ($this->projectMediaFiles as $projectMediaFile) {
            // TODO: Eigene Image Klasse erzeugen. 
            $this->projectMediaFiles[$projectMediaFile] = new \stdClass();
            $this->projectMediaFiles[$projectMediaFile]->image = 'Goes here';
        }

        $project = new MarkdownProject($this->rootPath, $this->projectFiles, $this->projectMediaFiles, $this->indexPath, $this->projectFilesNested, $this->errors);

        return $project;
    }


    private function setValueFromNestedReferencesArray(array &$array, string $keyPath, mixed $newValue): void
    {
        $keys = explode('/', $keyPath);
        $lastKey = array_pop($keys);
        $current = &$array;
        foreach ($keys as $key) {
            $current = &$current[$key];
        }
        $current[$lastKey] = $newValue;
    }

    /**
     * @param RenderedContentInterface $parsedResult
     * @return void
     */
    private function extractLinks(\League\CommonMark\Output\RenderedContentInterface $parsedResult): array
    {
        $domCrawler = new Crawler($parsedResult->getContent());
        $linkNodes = $domCrawler->filter('a');

        $links = [];
        foreach ($linkNodes as $linkNode) {
            if ($linkNode instanceof \DOMElement) {
                $href = $linkNode->getAttribute('href');
                $urlParts = parse_url($href);
                if (!isset($urlParts['path']) || str_starts_with($href, 'mailto:')) {
                    continue;
                }

                $links[] = new MarkdownLink('TODO');
            }
        }

        return $links;
    }
}

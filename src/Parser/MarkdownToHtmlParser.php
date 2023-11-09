<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Parser\ParserInterface;
use Iwm\MarkdownStructure\Value\Section;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownToHtmlParser implements ParserInterface
{

    public function fileIsParsable(string $fileType): bool
    {
        return $fileType === 'Iwm\MarkdownStructure\Value\MarkdownFile';
    }

    public function parse(mixed $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): mixed
    {
        if (!$this->fileIsParsable(get_class($file))) {
            return $file;
        }

        $file->html = $this->markdownToHtml($file->markdown);

        return $file;
    }

    private function markdownToHtml($markdown): string
    {
        $parser = new GithubFlavoredMarkdownConverter([]);
        $parser->getEnvironment()->addExtension(new HeadingPermalinkExtension());

        return $parser->convert($markdown)->getContent();
    }
}

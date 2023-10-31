<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Parser\ParserInterface;
use Iwm\MarkdownStructure\Value\Section;
use League\CommonMark\Exception\CommonMarkException;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownToHTMLParser implements ParserInterface
{
    /**
     * @throws CommonMarkException
     */
    public function parse(mixed $file): mixed
    {
        if (!$this->fileIsParsable(get_class($file))) {
            return $file;
        }

        $file->sectionedResult = $this->modifyAllSections($file->sectionedResult);

        return $file;
    }

    public function fileIsParsable(string $fileType): bool
    {
        return $fileType === 'Iwm\MarkdownStructure\Value\MarkdownFile';
    }

    /**
     * @throws CommonMarkException
     */
    private function modifyAllSections($sectionedResult): array
    {
        $result = [];

        foreach ($sectionedResult as $currentSection) {
            if ($currentSection instanceof Section) {
                $currentSection->content = $this->convertParagraphsToHtml($currentSection->content);
            }
            $result[] = $currentSection;
        }

        return $result;
    }

    /**
     * @throws CommonMarkException
     */
    private function convertParagraphsToHtml(array $paragraphs): array
    {
        $parser = new GithubFlavoredMarkdownConverter([]);
        $parser->getEnvironment()->addExtension(new HeadingPermalinkExtension());

        $htmlParagraphs = [];

        foreach ($paragraphs as $paragraph) {
            $htmlParagraphs[] = $parser->convert($paragraph);
        }

        return $htmlParagraphs;
    }
}
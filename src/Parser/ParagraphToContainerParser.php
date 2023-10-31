<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Parser\ParserInterface;
use Iwm\MarkdownStructure\Value\Section;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class ParagraphToContainerParser implements ParserInterface
{
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

    private function modifyAllSections($sectionedResult): array
    {
        $result = [];

        foreach ($sectionedResult as $currentSection) {
            if ($currentSection instanceof Section) {
                $currentSection->content = $this->containerParagraphs($currentSection->content);
            }
            $result[] = $currentSection;
        }

        return $result;
    }

    private function containerParagraphs(array $paragraphs): array
    {
        $containeredParagraphs = [];

        foreach ($paragraphs as $paragraph) {
            $containeredParagraphs[] = '<div>' . $paragraph . '</div>';
        }

        return $containeredParagraphs;
    }
}
<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;

class ParagraphToContainerParser implements ParserInterface
{
    public function fileIsParsable(MarkdownFile|MediaFile $file): bool
    {
        return $file instanceof MarkdownFile;
    }

    public function parse(MarkdownFile|MediaFile $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): MarkdownFile|MediaFile
    {
        if (!$this->fileIsParsable($file)) {
            return $file;
        }

        $file->sectionedResult = $this->modifyAllSections($file->sectionedResult);

        return $file;
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

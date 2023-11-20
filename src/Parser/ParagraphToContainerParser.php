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
        if (!$file instanceof MarkdownFile) {
            return $file;
        }

        $file->sectionedResult = $this->modifyAllSections($file->sectionedResult);

        return $file;
    }

    /**
     * @param array<Section|string> $sectionedResult
     *
     * @return array<Section|string>
     */
    private function modifyAllSections(array $sectionedResult): array
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

    /**
     * @param array<string> $paragraphs
     *
     * @return array<string>
     */
    private function containerParagraphs(array $paragraphs): array
    {
        $containeredParagraphs = [];

        foreach ($paragraphs as $paragraph) {
            $containeredParagraphs[] = '<div>' . $paragraph . '</div>';
        }

        return $containeredParagraphs;
    }
}

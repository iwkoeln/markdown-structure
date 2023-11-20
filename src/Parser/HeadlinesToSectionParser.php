<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;
use Iwm\MarkdownStructure\Value\SectionType;

class HeadlinesToSectionParser implements ParserInterface
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

        $file->sectionedResult = $this->headlinesToSection($file->sectionedResult);

        return $file;
    }

    /**
     * @param array<Section|string> $sectionedResult
     *
     * @return array<Section|string>
     */
    private function headlinesToSection(array $sectionedResult): array
    {
        $result = [];
        $currentHeadline = null;

        foreach ($sectionedResult as $currentLine) {
            if (preg_match('/^#+/', $currentLine, $matches)) {
                $headlineLevel = strlen($matches[0]);
                $headlineTitle = trim($currentLine, '# ');
                $currentHeadline = new Section(SectionType::HEADLINE);
                $currentHeadline->title = $headlineTitle;
                $currentHeadline->level = $headlineLevel;
                $result[] = $currentHeadline;
            } else {
                if (null === $currentHeadline) {
                    $currentHeadline = new Section(SectionType::PARAGRAPH);
                    $result[] = $currentHeadline;
                }
                $currentHeadline->content[] = (string)$currentLine;
            }
        }

        return $result;
    }
}

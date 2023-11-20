<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;

class CombineTextAndImagesParser implements ParserInterface
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
                $currentSection->content = $this->combineTextAndImages($currentSection->content);
            }
            $result[] = $currentSection;
        }

        return $result;
    }

    private function combineTextAndImages(array $content): array
    {
        $result = [];
        $predecessor = null;

        foreach ($content as $currentLine) {
            if (preg_match('/!\[.*?\]\(.*?\)/', $currentLine) && null !== $predecessor) {

                $lines = explode("\n", $predecessor);
                $lastLineOfPredecessor = end($lines);

                if (!preg_match('/!\[.*?\]\(.*?\)/', $lastLineOfPredecessor)) {
                    $currentLine = $predecessor . "\n\n" . $currentLine;
                    array_pop($result);
                }
            }
            $result[] = $currentLine;
            $predecessor = $currentLine;
        }

        return $result;
    }
}

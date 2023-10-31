<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Value\Section;

class CombineTextAndImagesParser implements ParserInterface
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
            if (preg_match('/!\[.*?\]\(.*?\)/', $currentLine) && $predecessor !== null) {

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
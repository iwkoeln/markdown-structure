<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Parser\ParserInterface;
use Iwm\MarkdownStructure\Value\Section;

class CombineTextAndListParser implements ParserInterface
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
                $currentSection->content = $this->combineTextAndList($currentSection->content);
            }
            $result[] = $currentSection;
        }
        return $result;
    }

    private function combineTextAndList(array $content): array
    {
        $result = [];
        $predecessor = null;

        foreach ($content as $currentLine) {
            if (preg_match('/^[\t ]*[\*\-] /m', $currentLine) && $predecessor !== null) {

                $lines = explode("\n", $predecessor);
                $lastLineOfPredecessor = end($lines);

                if (!preg_match('/^[\t ]*[\*\-] /m', $lastLineOfPredecessor)) {
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
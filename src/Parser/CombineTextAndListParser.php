<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;

class CombineTextAndListParser implements ParserInterface
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
                $currentSection->content = $this->combineTextAndList($currentSection->content);
            }
            $result[] = $currentSection;
        }

        return $result;
    }

    /**
     * @param array<string> $content
     *
     * @return array<string>
     */
    private function combineTextAndList(array $content): array
    {
        $result = [];
        $predecessor = null;

        foreach ($content as $currentLine) {
            if (preg_match('/^[\t ]*[\*\-] /m', $currentLine) && null !== $predecessor) {

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

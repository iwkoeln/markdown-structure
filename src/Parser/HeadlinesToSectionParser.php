<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Parser\ParserInterface;
use Iwm\MarkdownStructure\Value\Section;
use Iwm\MarkdownStructure\Value\SectionType;

class HeadlinesToSectionParser implements ParserInterface
{
    public function parse(mixed $file): mixed
    {
        if (!$this->fileIsParsable(get_class($file))) {
            return $file;
        }

        $file->sectionedResult = $this->headlinesToSection($file->sectionedResult);

        return $file;
    }

    public function fileIsParsable(string $fileType): bool
    {
        return $fileType === 'Iwm\MarkdownStructure\Value\MarkdownFile';
    }

    private function headlinesToSection($sectionedResult): array
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
                if ($currentHeadline === null) {
                    continue;
                }
                $currentHeadline->content[] = $currentLine;
            }
        }

        return $result;
    }
}

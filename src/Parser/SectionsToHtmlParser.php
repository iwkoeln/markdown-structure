<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Parser\ParserInterface;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;

class SectionsToHtmlParser implements ParserInterface
{
    public function fileIsParsable(MarkdownFile|MediaFile $file): bool
    {
        return $file instanceof MarkdownFile && !empty($file->sectionedResult);
    }

    public function parse(MarkdownFile|MediaFile $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): MarkdownFile|MediaFile
    {
        if (!$this->fileIsParsable($file)) {
            return $file;
        }

        $file->html = $this->sectionsToHTML($file->sectionedResult);

        return $file;
    }

    private function sectionsToHTML($sectionedResult): string
    {
        $html = '';

        foreach ($sectionedResult as $currentSection) {
            if ($currentSection instanceof Section) {
                $html .= $this->sectionToHTML($currentSection);
            }
        }

        return $html;
    }

    private function sectionToHTML(Section $currentSection): string
    {
        $html = '<h' . $currentSection->level . '>' . $currentSection->title . '</h' . $currentSection->level . '>';
        $html .= $this->paragraphsToHTML($currentSection->content);

        return $html;
    }

    private function paragraphsToHTML(array $content): string
    {
        $html = '';

        foreach ($content as $paragraph) {
            $html .= $paragraph . "\n";
        }

        return $html;
    }
}

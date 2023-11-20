<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;

class SplitByEmptyLineParser implements ParserInterface
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

        $file->sectionedResult = $this->parseSections($file->markdown);

        return $file;
    }

    /**
     * @return array<string>
     */
    private function parseSections(string $markdown): array
    {
        $sections = explode("\n\n", $markdown);
        // Remove empty strings
        $sections = array_filter($sections, 'strlen');

        return array_values($sections);
    }
}

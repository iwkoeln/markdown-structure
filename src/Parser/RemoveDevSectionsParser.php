<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;

class RemoveDevSectionsParser implements ParserInterface
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

        $file->sectionedResult = $this->removeDevSections($file->sectionedResult);

        return $file;
    }

    private function removeDevSections(array $sections): array
    {
        $result = [];

        foreach ($sections as $currentSection) {
            if (!str_starts_with($currentSection->title, 'Dev:')) {
                $result[] = $currentSection;
            }
        }

        return $result;

    }
}

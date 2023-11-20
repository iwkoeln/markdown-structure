<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;

class RemoveDevSectionsParser implements ParserInterface
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

        $file->sectionedResult = $this->removeDevSections($file->sectionedResult);

        return $file;
    }

    /**
     * @param array<Section|string> $sections
     *
     * @return array<Section|string>
     */
    private function removeDevSections(array $sections): array
    {
        $result = [];

        foreach ($sections as $currentSection) {
            if ($currentSection instanceof Section && !str_starts_with($currentSection->title, 'Dev:')) {
                $result[] = $currentSection;
            }
        }

        return $result;
    }
}

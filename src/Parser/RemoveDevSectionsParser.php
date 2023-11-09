<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Parser\ParserInterface;

class RemoveDevSectionsParser implements ParserInterface
{

    public function fileIsParsable(string $fileType): bool
    {
        return $fileType === 'Iwm\MarkdownStructure\Value\MarkdownFile';
    }

    public function parse(mixed $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): mixed
    {
        if (!$this->fileIsParsable(get_class($file))) {
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

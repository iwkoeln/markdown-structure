<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;

interface ParserInterface
{
    public function fileIsParsable(MarkdownFile|MediaFile $file): bool;

    /**
     * @param array<MarkdownFile>|null $documentationFiles
     * @param array<MediaFile>|null    $documentationMediaFiles
     * @param array<string>|null       $projectFiles
     */
    public function parse(MarkdownFile|MediaFile $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): MarkdownFile|MediaFile;
}

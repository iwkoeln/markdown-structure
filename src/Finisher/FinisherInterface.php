<?php

namespace Iwm\MarkdownStructure\Finisher;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;

interface FinisherInterface
{
    public function fileCanBeFinished(MarkdownFile|MediaFile $file): bool;

    /**
     * @param array<MarkdownFile>|null $documentationFiles
     * @param array<MediaFile>|null    $documentationMediaFiles
     * @param array<string>|null       $projectFiles
     */
    public function finish(MarkdownFile|MediaFile $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): MarkdownFile|MediaFile;
}

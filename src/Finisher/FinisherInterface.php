<?php

namespace Iwm\MarkdownStructure\Finisher;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;

interface FinisherInterface
{
    public function fileCanBeFinished(MarkdownFile|MediaFile $file): bool;

    public function finish(MarkdownFile|MediaFile $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): MarkdownFile|MediaFile;
}

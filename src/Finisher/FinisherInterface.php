<?php

namespace Iwm\MarkdownStructure\Finisher;

interface FinisherInterface
{
    public function fileCanBeFinished(string $fileType): bool;
    public function finish(mixed $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): mixed;
}

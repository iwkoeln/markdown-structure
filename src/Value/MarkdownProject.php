<?php

namespace Iwm\MarkdownStructure\Value;

final class MarkdownProject
{
    public function __construct(
        public readonly string $projectPath,
        public readonly string $documentationPath,
        public readonly string $documentationEntryPoint,
        public readonly array  $documentationFiles,
        public readonly array  $documentationMediaFiles,
        public readonly array  $projectFiles = [],
        public readonly array  $referencedExternalFiles = [],
        public readonly ?array $projectFilesNested = null,
        public readonly ?array $errors = null,
        public readonly ?array $orphans = null
    ) {}

    public function getFileByPath(string $path): ?MarkdownFile
    {
        return $this->documentationFiles[$path] ?? null;
    }

}

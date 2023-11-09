<?php

namespace Iwm\MarkdownStructure\Value;

final class MarkdownProject
{
    public function __construct(
        public string $projectPath,
        public string $documentationPath,
        public array  $documentationFiles,
        public array  $documentationMediaFiles,
        public string $documentationEntryPoint,
        public array  $projectFiles = [],
        public array  $referencedExternalFiles = [],
        public ?array $projectFilesNested = null,
        public ?array $errors = null,
    ) {}

    public function getFileByPath(string $path): ?MarkdownFile
    {
        return $this->documentationFiles[$path] ?? null;
    }

}

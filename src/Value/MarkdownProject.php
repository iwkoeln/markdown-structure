<?php

namespace Iwm\MarkdownStructure\Value;

final class MarkdownProject
{
    public array $errors = [];
    public array $orphans = [];

    public function __construct(
        public readonly string $projectRootPath,
        public readonly string $documentationPath,
        public readonly string $documentationIndexFile,
        public readonly array $documentationFiles,
        public readonly array $documentationMediaFiles,
        public readonly array $projectFiles = [],
        public readonly array $referencedExternalFiles = [],
        public readonly array $nestedDocumentationFiles = []
    ) {
    }

    public function getFileByPath(string $path): ?MarkdownFile
    {
        return $this->documentationFiles[$path] ?? null;
    }
}

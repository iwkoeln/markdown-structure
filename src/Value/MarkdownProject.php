<?php

namespace IWM\MarkdownStructure\Value;

final readonly class MarkdownProject
{
    public function __construct(
        public string $rootPath,
        public array $projectFiles,
        public array $projectMediaFiles,
        public string $indexPath,
        public ?array $projectFilesNested = null,
        public ?array $errors = null,
    ) {}

    public function getFileByPath(string $path): ?MarkdownFile
    {
        return $this->projectFiles[$path] ?? null;
    }

}

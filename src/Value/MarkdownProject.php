<?php

namespace Iwm\MarkdownStructure\Value;

final class MarkdownProject
{
    public function __construct(
        public string $rootPath,
        public string $mdProjectPath,
        public array  $projectFiles,
        public array  $projectMediaFiles,
        public string $indexPath,
        public array  $files = [],
        public array  $externalFiles = [],
        public ?array $projectFilesNested = null,
        public ?array $errors = null,
    ) {}

    public function getFileByPath(string $path): ?MarkdownFile
    {
        return $this->projectFiles[$path] ?? null;
    }

}

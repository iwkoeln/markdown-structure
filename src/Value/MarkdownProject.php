<?php

namespace Iwm\MarkdownStructure\Value;

use Iwm\MarkdownStructure\Error\ErrorInterface;

final class MarkdownProject
{
    /**
     * @var array<array<ErrorInterface>|ErrorInterface>
     */
    public array $errors = [];
    /**
     * @var array<string>
     */
    public array $orphans = [];

    /**
     * @param array<MarkdownFile> $documentationFiles
     * @param array<MediaFile>    $documentationMediaFiles
     * @param array<string>       $projectFiles
     * @param array<string>       $referencedExternalFiles
     * @param array<string>       $nestedDocumentationFiles
     */
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

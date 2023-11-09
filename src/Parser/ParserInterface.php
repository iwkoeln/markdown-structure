<?php

namespace Iwm\MarkdownStructure\Parser;

interface ParserInterface
{
    public function fileIsParsable(string $fileType): bool;
    public function parse(mixed $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): mixed;
}

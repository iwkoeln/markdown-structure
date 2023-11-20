<?php

namespace Iwm\MarkdownStructure\Value;

class MarkdownFile
{
    // ENHANCEMENT: Handle references between images and Markdown files.
    public array $sectionedResult = [];

    public function __construct(
        readonly public string $basePath,
        readonly public string $path,
        public string $markdown,
        public string $html = '',
        readonly public ?string $fallbackUrl = null,
        public array $errors = []
    ) {
    }

    public function __toString(): string
    {
        return $this->path;
    }
}

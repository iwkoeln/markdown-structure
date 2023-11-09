<?php

namespace Iwm\MarkdownStructure\Value;

// TODO: Handle references between images and Markdown files.

use League\CommonMark\Output\RenderedContentInterface;

class MarkdownFile
{
    public array $sectionedResult = [];
    public function __construct(
        readonly public string $path,
        public string $markdown,
        public string $html = '',
        readonly public ?string $fallbackUrl = null,
        public ?array $errors = null
    )
    {
    }

    public function __toString(): string
    {
        return $this->path;
    }
}

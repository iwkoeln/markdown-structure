<?php

namespace Iwm\MarkdownStructure\Value;

// TODO: Handle references between images and Markdown files.

class MarkdownFile
{
    public array $sectionedResult = [];
    public function __construct(
        readonly public string $path,
        readonly public string $markdown,
        public string $html,
        readonly public ?array $errors = null
    )
    {
    }

    public function __toString(): string
    {
        return $this->path;
    }
}

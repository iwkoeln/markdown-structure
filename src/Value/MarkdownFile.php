<?php

namespace Iwm\MarkdownStructure\Value;

use Iwm\MarkdownStructure\Error\ErrorInterface;

class MarkdownFile
{
    // ENHANCEMENT: Handle references between images and Markdown files.
    /**
     * @var array<string|Section>
     */
    public array $sectionedResult = [];

    /**
     * @param array<ErrorInterface> $errors
     */
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

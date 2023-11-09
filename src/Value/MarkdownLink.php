<?php

namespace Iwm\MarkdownStructure\Value;

use Iwm\MarkdownStructure\Utility\PathUtility;

class MarkdownLink
{
    public function __construct(
        readonly public string $source,
        readonly public string $target,
        readonly public bool $isExternal,
        readonly public string $linkText = '',
    ){}
    public function absolutePath(): string
    {
        return PathUtility::resolveAbsolutePath($this->source, $this->target);
    }
}

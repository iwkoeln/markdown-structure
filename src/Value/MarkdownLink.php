<?php

namespace Iwm\MarkdownStructure\Value;

use Iwm\MarkdownStructure\Utility\PathUtility;

class MarkdownLink
{
    public function __construct(
        readonly public string $target,
        readonly public bool $isExternal,
        readonly public string $source,
    ){}
    public function absolutePath(): string
    {
        return PathUtility::resolveAbsolutPath($this->source, $this->target);
    }
}

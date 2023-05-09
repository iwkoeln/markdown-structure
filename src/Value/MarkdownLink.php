<?php

namespace IWM\MarkdownStructure\Value;

class MarkdownLink
{
    public function __construct(
        readonly public string $target
    )
    {
    }
}

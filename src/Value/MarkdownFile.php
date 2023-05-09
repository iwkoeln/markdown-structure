<?php

namespace IWM\MarkdownStructure\Value;

class MarkdownFile
{
    public function __construct(
        readonly public string $path,
        readonly public string $markdown,
        readonly public string $html,
        readonly public ?array $errors = null
    )
    {
    }


    public function __toString(): string
    {
        return $this->path;
    }
}

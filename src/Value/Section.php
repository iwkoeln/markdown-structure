<?php

namespace Iwm\MarkdownStructure\Value;

class Section
{
    public string $title = '';
    public array $content = [];
    public int $level = 0;
    public string $type = '';
}
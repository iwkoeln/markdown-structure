<?php

namespace Iwm\MarkdownStructure\Value;

class Section
{
    public string $title = '';
    public array $content = [];
    public int $level = 0;
    public SectionType $type;

    public function __construct(SectionType $type) {
        $this->type = $type;
    }
}

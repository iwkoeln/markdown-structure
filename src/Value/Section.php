<?php

namespace Iwm\MarkdownStructure\Value;

class Section
{
    public string $title = '';
    /** @var array<string> */
    public array $content = [];
    public int $level = 0;
    public SectionType $type;

    public function __construct(SectionType $type)
    {
        $this->type = $type;
    }

    public function __toString(): string
    {
        if (SectionType::HEADLINE === $this->type) {
            $content = "<h{$this->level}>{$this->title}</h{$this->level}>" . PHP_EOL . PHP_EOL;
        } else {
            $content = '';
        }

        foreach ($this->content as $line) {
            $content .= $line . PHP_EOL;
        }

        return $content;
    }
}

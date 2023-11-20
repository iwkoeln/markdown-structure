<?php

namespace Iwm\MarkdownStructure\Value;

/* ENHANCEMENT:
 * Additional attributes for MarkdownProject: "MediaFiles" can be added,
 * by separating Markdown and other files using nested if statements.
 * Use a new FileObject for each medium, instead of a simple string.
 */
class MediaFile
{
    public function __construct(
        public string $path,
        public string $image = '',
        public array $errors = []
    ) {
        if ('' === $this->image) {
            $this->image = $this->path;
        }
    }

    public function __toString(): string
    {
        return $this->path;
    }
}

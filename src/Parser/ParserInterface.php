<?php

namespace Iwm\MarkdownStructure\Parser;

interface ParserInterface
{

    public function fileIsParsable(string $fileType): bool;
    public function parse(mixed $file): mixed;
}
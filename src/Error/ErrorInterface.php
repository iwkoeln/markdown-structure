<?php

namespace Iwm\MarkdownStructure\Error;

interface ErrorInterface
{
    public function getErrorMessage(): string;

    public function __toString(): string;
}

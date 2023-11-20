<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;

interface ValidatorInterface
{
    public function fileCanBeValidated(MarkdownFile|MediaFile $file): bool;

    public function validate(MarkdownFile|MediaFile $file, array $markdownFiles, array $mediaFiles): void;
}

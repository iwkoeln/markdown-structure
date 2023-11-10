<?php

namespace Iwm\MarkdownStructure\Validator;

interface ValidatorInterface
{
    public function fileCanBeValidated(string $path): bool;
    public function validate(?string $parsedResult, string $path, array $markdownFiles, array $mediaFiles): array;
}

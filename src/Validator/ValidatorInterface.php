<?php

namespace Iwm\MarkdownStructure\Validator;

use League\CommonMark\Output\RenderedContentInterface;

// TODO Enhance Interface and make them add-able on set up
interface ValidatorInterface
{
    public function fileCanBeValidated(string $path): bool;
    public function validate(string|null $parsedResult, string $path, array $fileList): array;
}

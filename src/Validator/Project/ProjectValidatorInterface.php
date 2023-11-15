<?php

namespace Iwm\MarkdownStructure\Validator\Project;

interface ProjectValidatorInterface
{
    //TODO: FINISH IT
    public function fileCanBeValidated(string $path): bool;
    public function validate(?string $parsedResult, string $path, array $markdownFiles, array $mediaFiles): array;
}

<?php

namespace Iwm\MarkdownStructure\Validator\MarkdownProject;

use Iwm\MarkdownStructure\Value\MarkdownProject;

interface MarkdownProjectValidatorInterface
{
    public function validate(MarkdownProject $project): void;
}

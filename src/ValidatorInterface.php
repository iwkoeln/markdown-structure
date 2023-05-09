<?php

namespace IWM\MarkdownStructure;

use League\CommonMark\Output\RenderedContentInterface;

interface ValidatorInterface
{
    public function validate(RenderedContentInterface $parsedResult, string $path, array $fileList): array;
}

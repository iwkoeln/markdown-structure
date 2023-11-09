<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\Utility\PathUtility;
use League\CommonMark\Output\RenderedContentInterface;

class MediaFileValidator implements ValidatorInterface
{

    public function validate(?RenderedContentInterface $parsedResult, string $path, array $fileList): array
    {
        $errors = [];

        if (!$this->fileCanBeValidated($path)) {
            return $errors;
        }

        // TODO: Implement validate() method.

        return $errors;
    }

    public function fileCanBeValidated(string $path): bool
    {
        return PathUtility::isMediaFile($path);
    }
}

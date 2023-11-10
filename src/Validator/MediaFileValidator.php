<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\Error\ImageDoesNotExistError;
use Iwm\MarkdownStructure\Error\ImageTooLargeError;
use Iwm\MarkdownStructure\Utility\PathUtility;

class MediaFileValidator implements ValidatorInterface
{

    public function validate(?string $parsedResult, string $path, array $fileList): array
    {
        $errors = [];

        if (!$this->fileCanBeValidated($path)) {
            return $errors;
        }

        $errors = array_merge($errors, $this->checkFileSize($path));
        $errors = array_merge($errors, $this->checkFileExistence($path));

        return $errors;
    }

    public function checkFileSize(string $path): array
    {
        $errors = [];

        // Check if the file size is over 1 MB (1048576 bytes).
        if (filesize($path) > 1048576) {
            $errors[] = new ImageTooLargeError($path, filesize($path));
        }

        return $errors;
    }

    public function checkFileExistence(string $path): array
    {
        $errors = [];

        if (!file_exists($path)) {
            $errors[] = new ImageDoesNotExistError($path);
        }

        return $errors;
    }


    public function fileCanBeValidated(string $path): bool
    {
        return PathUtility::isMediaFile($path);
    }
}

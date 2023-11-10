<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\Error\LinkTargetNotFoundError;
use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Utility\PathUtility;

class MarkdownLinksValidator implements ValidatorInterface
{
    public function fileCanBeValidated(string $path): bool
    {
        return PathUtility::isMarkdownFile($path);
    }

    public function validate(?string $parsedResult, string $path, array $fileList): array
    {
        $errors = [];

        if (!$this->fileCanBeValidated($path) || $parsedResult === null) {
            return $errors;
        }

        $markdownLinks = DomLinkExtractor::extractLinks($parsedResult, $path);

        foreach ($markdownLinks as $markdownLink) {
            $absolutePath = $markdownLink->absolutePath();
            if (!in_array($absolutePath, $fileList)) {
                $errors[] = new LinkTargetNotFoundError(
                    $path,
                    $absolutePath,
                    $markdownLink->linkText
                );
            }
        }

        return $errors;
    }
}

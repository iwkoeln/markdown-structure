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

    public function validate(?string $parsedResult, string $path, array $markdownFiles, array $mediaFiles): array
    {
        $errors = [];

        if (!$this->fileCanBeValidated($path) || $parsedResult === null) {
            return $errors;
        }

        $markdownLinks = DomLinkExtractor::extractLinks($parsedResult, $path);

        foreach ($markdownLinks as $markdownLink) {
            $absolutePath = $markdownLink->absolutePath();
            if (!array_key_exists($absolutePath, $markdownFiles) && !array_key_exists($absolutePath, $mediaFiles)) {
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

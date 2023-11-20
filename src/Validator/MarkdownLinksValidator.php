<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\Error\LinkTargetNotFoundError;
use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;

class MarkdownLinksValidator implements ValidatorInterface
{
    public function fileCanBeValidated(MarkdownFile|MediaFile $file): bool
    {
        return $file instanceof MarkdownFile;
    }

    public function validate(MarkdownFile|MediaFile $file, array $markdownFiles, array $mediaFiles): void
    {
        if ($file instanceof MarkdownFile) {
            $errors = [];
            $markdownLinks = DomLinkExtractor::extractLinks($file->html, $file->path);

            foreach ($markdownLinks as $markdownLink) {
                $absolutePath = $markdownLink->absolutePath();
                if (!array_key_exists($absolutePath, $markdownFiles) && !array_key_exists($absolutePath, $mediaFiles)) {
                    $errors[] = new LinkTargetNotFoundError(
                        $file->path,
                        $absolutePath,
                        $markdownLink->linkText
                    );
                }
            }

            $file->errors = array_merge($file->errors, $errors);
        }
    }
}

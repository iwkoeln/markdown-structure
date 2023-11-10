<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\Error\ImageDoesNotExistError;
use Iwm\MarkdownStructure\Error\LinkTargetNotFoundError;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Symfony\Component\DomCrawler\Crawler;
use DOMElement;

class MarkdownImageValidator implements ValidatorInterface
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

        $domCrawler = new Crawler($parsedResult);
        $imageNodes = $domCrawler->filter('img');

        foreach ($imageNodes as $imageNode) {
            if ($imageNode instanceof DOMElement) {
                $src = $imageNode->getAttribute('src');

                if (!empty($src)) {
                    $absolutePath = PathUtility::resolveRelativePath($path, $src);

                    if (!in_array($absolutePath, $fileList)) {
                        $errors[] = new ImageDoesNotExistError(
                            $path,
                            $src,
                        );
                    }
                }
            }
        }

        return $errors;
    }
}

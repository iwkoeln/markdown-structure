<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\Error\ImageDoesNotExistError;
use SplFileInfo;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Symfony\Component\DomCrawler\Crawler;
use DOMElement;

class MarkdownImageValidator implements ValidatorInterface
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

        $domCrawler = new Crawler($parsedResult);
        $imageNodes = $domCrawler->filter('img');

        foreach ($imageNodes as $imageNode) {
            if ($imageNode instanceof DOMElement) {
                $src = $imageNode->getAttribute('src');

                if (!empty($src)) {
                    $absolutePath = PathUtility::resolveAbsolutePath($path, $src);
                    if (!array_key_exists($absolutePath, $mediaFiles)) {
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

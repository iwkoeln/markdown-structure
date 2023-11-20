<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\Error\ImageDoesNotExistError;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Symfony\Component\DomCrawler\Crawler;

class MarkdownImageValidator implements ValidatorInterface
{
    public function fileCanBeValidated(MarkdownFile|MediaFile $file): bool
    {
        return $file instanceof MarkdownFile;
    }

    public function validate(MarkdownFile|MediaFile $file, array $markdownFiles, array $mediaFiles): void
    {
        if ($this->fileCanBeValidated($file)) {
            $errors = [];
            $domCrawler = new Crawler($file->html);
            $imageNodes = $domCrawler->filter('img');

            foreach ($imageNodes as $imageNode) {
                if ($imageNode instanceof \DOMElement) {
                    $src = $imageNode->getAttribute('src');

                    if (!empty($src)) {
                        $absolutePath = PathUtility::resolveAbsolutePath($file->path, $src);
                        if (!array_key_exists($absolutePath, $mediaFiles)) {
                            $errors[] = new ImageDoesNotExistError(
                                $file->path,
                                $src,
                            );
                        }
                    }
                }
            }

            $file->errors = array_merge($file->errors, $errors);
        }
    }
}

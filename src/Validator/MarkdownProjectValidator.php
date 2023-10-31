<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\ErrorHandler\LinkTargetNotFoundError;
use Iwm\MarkdownStructure\Utility\PathUtility;
use League\CommonMark\Output\RenderedContentInterface;
use Symfony\Component\DomCrawler\Crawler;

class MarkdownProjectValidator implements ValidatorInterface
{
    public function validate(RenderedContentInterface|null $parsedResult, string $path, array $fileList): array
    {
        $errors = [];

        if(!$this->fileCanBeValidated($path) || $parsedResult === null) {
            return $errors;
        }

        $domCrawler = new Crawler($parsedResult->getContent());
        $linkNodes = $domCrawler->filter('a');
        foreach ($linkNodes as $linkNode) {
            if ($linkNode instanceof \DOMElement && !PathUtility::isExternalUrl($href = $linkNode->getAttribute('href'))) {
                $urlParts = parse_url($href);
                if (!isset($urlParts['path']) || str_starts_with($href, 'mailto:')) {
                    continue;
                }
                if (!PathUtility::isPathInRoot($urlParts['path'], $path)) {
                    $linkNode->setAttribute('class', 'link-no-docs');
                } else {
                    // Extend file list, by directories
                    foreach ($fileList as $filePath) {
                        $dir = dirname($filePath);
                        if (!in_array($dir, $fileList, true)) {
                            $fileList[] = $dir;
                        }
                    }

                    $absolutPath = PathUtility::resolveAbsolutPath($path, $urlParts['path']);
                    if (!empty($absolutPath) && !in_array($absolutPath, $fileList)) {
                        $error = new LinkTargetNotFoundError($path, 'Link target not found');
                        $error->setUnfoundFilePath($absolutPath);
                        if ($linkNode->nodeValue) {
                            $error->setLinkText($linkNode->nodeValue);
                        }
                        $errors[] = $error;
                    }
                }
            }
        }

        return $errors;
    }

    public function fileCanBeValidated(string $path): bool
    {
        if (PathUtility::isMarkdownFile($path)) {
            return true;
        } else {
            return false;
        }
    }
}

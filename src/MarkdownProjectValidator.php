<?php

namespace IWM\MarkdownStructure;

use App\Utility\PathUtility;
use League\CommonMark\Output\RenderedContentInterface;
use Symfony\Component\DomCrawler\Crawler;

class MarkdownProjectValidator implements ValidatorInterface
{

    public function validate(RenderedContentInterface $parsedResult, string $path, array $fileList): array
    {
        $errors = [];

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

                    $path2 = PathUtility::resolveAbsolutePath($path, $urlParts['path']);
                    if (!empty($path2) && !in_array($path2, $fileList, true)) {
                        $error = sprintf('Ziel von Link "%s" nicht vorhanden!', $path2);
                        if ($linkNode->nodeValue) {
                            $error .= ' Linktext: ' . $linkNode->nodeValue;
                        }
                        $errors[] = $error;
                    }
                }
            }
        }

        return $errors;
    }
}

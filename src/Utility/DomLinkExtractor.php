<?php

namespace Iwm\MarkdownStructure\Utility;

use Iwm\MarkdownStructure\Value\MarkdownLink;
use Symfony\Component\DomCrawler\Crawler;

class DomLinkExtractor
{
    /**
     * @return array<MarkdownLink>
     */
    public static function extractLinks(string $parsedResult, string $sourcePath): array
    {
        $domCrawler = new Crawler($parsedResult);
        $linkNodes = $domCrawler->filter('a');

        $links = [];
        foreach ($linkNodes as $linkNode) {
            if ($linkNode instanceof \DOMElement) {
                $href = $linkNode->getAttribute('href');
                $isExternal = PathUtility::isExternalUrl($href);
                $linkText = $linkNode->textContent ?? '';

                if (!$isExternal && !str_starts_with($href, '#')) {
                    $links[] = new MarkdownLink($sourcePath, $href, false, $linkText);
                }
            }
        }

        return $links;
    }

    /**
     * @return array<MarkdownLink>
     */
    public static function extractImages(string $parsedResult, string $sourcePath): array
    {
        $domCrawler = new Crawler($parsedResult);
        $linkNodes = $domCrawler->filter('img');

        $links = [];
        foreach ($linkNodes as $linkNode) {
            if ($linkNode instanceof \DOMElement) {
                $href = $linkNode->getAttribute('src');
                $isExternal = PathUtility::isExternalUrl($href);
                $linkText = $linkNode->textContent ?? '';

                if (!$isExternal && !str_starts_with($href, '#')) {
                    $links[] = new MarkdownLink($sourcePath, $href, false, $linkText);
                }
            }
        }

        return $links;
    }
}

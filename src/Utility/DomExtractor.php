<?php

namespace Iwm\MarkdownStructure\Utility;

use Iwm\MarkdownStructure\Value\MarkdownLink;
use Symfony\Component\DomCrawler\Crawler;

class DomExtractor
{
    public static function extractFirstHeadline(string $parsedResult): string
    {
        $domCrawler = new Crawler($parsedResult);

        $headlineNodes = $domCrawler->filter('h1, h2, h3, h4, h5, h6');
        foreach ($headlineNodes as $headlineNode) {
            foreach ($headlineNode->childNodes as $childNode) {
                if ($childNode instanceof \DOMText) {
                    return $childNode->wholeText;
                }
            }

            return $headlineNode->textContent;
        }

        return '';
    }

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

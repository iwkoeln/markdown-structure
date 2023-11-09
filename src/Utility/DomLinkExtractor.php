<?php

namespace Iwm\MarkdownStructure\Utility;

use Iwm\MarkdownStructure\Value\MarkdownLink;
use League\CommonMark\Output\RenderedContentInterface;
use Symfony\Component\DomCrawler\Crawler;
use DOMElement;

class DomLinkExtractor
{
    public static function extractLinks(string $parsedResult, string $sourcePath): array
    {
        $domCrawler = new Crawler($parsedResult);
        $linkNodes = $domCrawler->filter('a');

        $links = [];
        foreach ($linkNodes as $linkNode) {
            if ($linkNode instanceof DOMElement) {
                $href = $linkNode->getAttribute('href');
                $isExternal = PathUtility::isExternalUrl($href);
                $linkText = $linkNode->textContent ?? '';

                if (!$isExternal) {
                    $links[] = new MarkdownLink($sourcePath, $href, false, $linkText);
                }
            }
        }

        return $links;
    }
}

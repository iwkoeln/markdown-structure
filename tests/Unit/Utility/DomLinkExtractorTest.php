<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Utility;

use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Value\MarkdownLink;
use PHPUnit\Framework\TestCase;

class DomLinkExtractorTest extends TestCase
{
    public function testExtractLinksNoLinks()
    {
        $parsedResult = '<p>No links in this content.</p>';
        $sourcePath = '/path/to/source.md';

        $links = DomLinkExtractor::extractLinks($parsedResult, $sourcePath);

        $this->assertEquals([], $links);
    }

    public function testExtractLinksExternalLink()
    {
        $parsedResult = '<a href="https://www.example.com">External Link</a>';
        $sourcePath = '/path/to/source.md';

        $links = DomLinkExtractor::extractLinks($parsedResult, $sourcePath);

        $this->assertEquals([], $links);
    }

    public function testExtractLinksInternalLink()
    {
        $parsedResult = '<a href="internal-link.md">Internal Link</a>';
        $sourcePath = '/path/to/source.md';

        $links = DomLinkExtractor::extractLinks($parsedResult, $sourcePath);

        $expectedLink = new MarkdownLink('/path/to/source.md', 'internal-link.md', false, 'Internal Link');
        $this->assertEquals([$expectedLink], $links);
    }

    public function testExtractLinksAnchorLink()
    {
        $parsedResult = '<a href="#section1">Anchor Link</a>';
        $sourcePath = '/path/to/source.md';

        $links = DomLinkExtractor::extractLinks($parsedResult, $sourcePath);

        $this->assertEquals([], $links);
    }

    public function testExtractLinksMultipleLinks()
    {
        $parsedResult = '<a href="internal-link.md">Internal Link</a> <a href="external-link">External Link</a>';
        $sourcePath = '/path/to/source.md';

        $links = DomLinkExtractor::extractLinks($parsedResult, $sourcePath);

        $expectedLink1 = new MarkdownLink('/path/to/source.md', 'internal-link.md', false, 'Internal Link');
        $expectedLink2 = new MarkdownLink('/path/to/source.md', 'external-link', false, 'External Link');
        $this->assertEquals([$expectedLink1, $expectedLink2], $links);
    }

}


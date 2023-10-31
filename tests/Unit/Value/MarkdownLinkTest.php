<?php

namespace Iwm\MarkdownStructure\Unit\Value;

use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Value\MarkdownLink;
use PHPUnit\Framework\TestCase;

class MarkdownLinkTest extends TestCase
{
    /**
     * @test
     * @testdox MarkdownLink is marked as external or internal
     */
    public function testLinkIsExternal()
    {
        $markdownLink = new MarkdownLink('https://www.google.com', PathUtility::isExternalUrl('https://www.google.com'), 'test.md');
        $this->assertTrue($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('http://www.google.com', PathUtility::isExternalUrl('http://www.google.com'), 'test.md');
        $this->assertTrue($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('mailto:mai@iwkoeln.de', PathUtility::isExternalUrl('mailto:mai@iwkoeln.de'), 'test.md');
        $this->assertTrue($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('iwkoeln.de', PathUtility::isExternalUrl('iwkoeln.de'), 'test.md');
        $this->assertTrue($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('/var/image.jpg', PathUtility::isExternalUrl('/var/image.jpg'), 'test.md');
        $this->assertFalse($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('../index.md', PathUtility::isExternalUrl('../index.md'), 'test.md');
        $this->assertFalse($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('index.md', PathUtility::isExternalUrl('../index.md'), 'test.md');
        $this->assertFalse($markdownLink->isExternal);
    }
}
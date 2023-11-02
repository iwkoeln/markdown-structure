<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Value;

use Iwm\MarkdownStructure\Tests\Functional\AbstractTestCase;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Value\MarkdownLink;
use PHPUnit\Framework\TestCase;

class MarkdownLinkTest extends AbstractTestCase
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

    /**
     * @test
     * @testdox MarkdownLink can convert to an absolute path
     */
    public function testMarkdownLinkAbsolutLink()
    {
        $markdownLink = new MarkdownLink('../index.md', PathUtility::isExternalUrl('../index.md'), 'var/www/html/src/some/data/test.md');
        $this->assertEquals($markdownLink->absolutePath(), 'var/www/html/src/some/index.md');
    }
}

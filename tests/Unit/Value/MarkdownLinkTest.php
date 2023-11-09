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
        $markdownLink = new MarkdownLink('https://www.google.com', 'test.md', PathUtility::isExternalUrl('https://www.google.com'));
        $this->assertTrue($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('http://www.google.com', 'test.md', PathUtility::isExternalUrl('http://www.google.com'));
        $this->assertTrue($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('mailto:mai@iwkoeln.de', 'test.md', PathUtility::isExternalUrl('mailto:mai@iwkoeln.de'));
        $this->assertTrue($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('iwkoeln.de', 'test.md', PathUtility::isExternalUrl('iwkoeln.de'));
        $this->assertTrue($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('/var/image.jpg', 'test.md', PathUtility::isExternalUrl('/var/image.jpg'));
        $this->assertFalse($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('../index.md', 'test.md', PathUtility::isExternalUrl('../index.md'));
        $this->assertFalse($markdownLink->isExternal);
        $markdownLink = new MarkdownLink('index.md', 'test.md', PathUtility::isExternalUrl('../index.md'));
        $this->assertFalse($markdownLink->isExternal);
    }

    /**
     * @test
     * @testdox MarkdownLink can convert to an absolute path
     */
    public function testMarkdownLinkAbsolutLink()
    {
        $markdownLink = new MarkdownLink('var/www/html/src/some/data/test/index.md', '../test.md', PathUtility::isExternalUrl('../test.md'));
        $this->assertEquals('var/www/html/src/some/data/test.md', $markdownLink->absolutePath());
    }
}

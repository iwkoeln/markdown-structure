<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Value;

use Iwm\MarkdownStructure\Tests\AbstractTestCase;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Value\MarkdownLink;

class MarkdownLinkTest extends AbstractTestCase
{
    public static function externalLinkProvider()
    {
        return [
            ['https://www.google.com'],
            ['http://www.google.com'],
            ['mailto:mai@iwkoeln.de'],
            ['iwkoeln.de'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider externalLinkProvider
     *
     * @testdox MarkdownLink is marked as external for external links
     */
    public function testLinkIsExternalForExternalLinks($url)
    {
        $markdownLink = new MarkdownLink($url, 'test.md', PathUtility::isExternalUrl($url));
        $this->assertTrue($markdownLink->isExternal);
    }

    public static function internalLinkProvider()
    {
        return [
            ['/var/image.jpg'],
            ['../index.md'],
            ['index.md'],
        ];
    }

    /**
     * @test
     *
     * @dataProvider internalLinkProvider
     *
     * @testdox MarkdownLink is marked as internal for internal links
     */
    public function testLinkIsInternalForInternalLinks($url)
    {
        $markdownLink = new MarkdownLink($url, 'test.md', PathUtility::isExternalUrl($url));
        $this->assertFalse($markdownLink->isExternal);
    }

    /**
     * @test
     *
     * @testdox MarkdownLink can convert to an absolute path
     */
    public function testMarkdownLinkAbsolutePath()
    {
        $markdownLink = new MarkdownLink('var/www/html/src/some/data/test/index.md', '../test.md', PathUtility::isExternalUrl('../test.md'));
        $this->assertEquals('var/www/html/src/some/data/test.md', $markdownLink->absolutePath());
    }
}

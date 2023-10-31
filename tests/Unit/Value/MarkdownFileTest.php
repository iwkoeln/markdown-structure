<?php

namespace Iwm\MarkdownStructure\Unit\Value;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use PHPUnit\Framework\TestCase;

class MarkdownFileTest extends TestCase
{
    /**
     * @test
     * @testdox MarkdownFile is to string convertable
     */
    public function testToStringConversion()
    {
        $path = '/var/www/html/tests/Data/index.md';
        $markdown = '';
        $html = '';

        $markdownFile = new MarkdownFile($path, $markdown, $html);
        $this->assertEquals($path, (string) $markdownFile);
    }
}
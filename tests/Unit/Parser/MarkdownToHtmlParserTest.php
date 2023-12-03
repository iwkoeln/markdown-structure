<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Parser;

use Iwm\MarkdownStructure\Parser\MarkdownToHtmlParser;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use PHPUnit\Framework\TestCase;

class MarkdownToHtmlParserTest extends TestCase
{
    public function testFileIsParsable(): void
    {
        $parser = new MarkdownToHtmlParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', 'Markdown content');

        $this->assertTrue($parser->fileIsParsable($markdownFile));
    }

    public function testFileIsNotParsableForMediaFile(): void
    {
        $parser = new MarkdownToHtmlParser();
        $mediaFile = new MediaFile('/path', 'image.jpg');

        $this->assertFalse($parser->fileIsParsable($mediaFile));
    }

    public function testParseMarkdownFileToHtml(): void
    {
        $parser = new MarkdownToHtmlParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', '');
        $markdownFile->markdown = '**Bold Text** *Italic Text* [Link Text](https://example.com)';

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser converts markdown to HTML
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertStringContainsString('<strong>Bold Text</strong>', $parsedFile->html);
        $this->assertStringContainsString('<em>Italic Text</em>', $parsedFile->html);
        $this->assertStringContainsString('<a href="https://example.com">Link Text</a>', $parsedFile->html);
    }

    public function testParseMarkdownFileWithEmptyContentToHtml(): void
    {
        $parser = new MarkdownToHtmlParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', '');

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser converts empty content to empty HTML
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertEquals('', $parsedFile->html);
    }
}

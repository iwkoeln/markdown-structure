<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Parser;

use Iwm\MarkdownStructure\Parser\MarkdownParagraphToHTMLParser;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;
use Iwm\MarkdownStructure\Value\SectionType;
use PHPUnit\Framework\TestCase;

class MarkdownParagraphToHTMLParserTest extends TestCase
{
    public function testFileIsParsable(): void
    {
        $parser = new MarkdownParagraphToHTMLParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', 'Markdown content');

        $this->assertTrue($parser->fileIsParsable($markdownFile));
    }

    public function testFileIsNotParsableForMediaFile(): void
    {
        $parser = new MarkdownParagraphToHTMLParser();
        $mediaFile = new MediaFile('/path', 'image.jpg');

        $this->assertFalse($parser->fileIsParsable($mediaFile));
    }

    public function testParseMarkdownFileWithParagraphsToHtml(): void
    {
        $parser = new MarkdownParagraphToHTMLParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', '');
        $section1 = (new Section(SectionType::PARAGRAPH));
        $section1->content = [
            'This is a paragraph.',
            'Another paragraph.',
        ];

        $markdownFile->sectionedResult = [$section1];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser converts paragraphs to HTML
        $sectionedResult = $parsedFile->sectionedResult;
        $section = $sectionedResult[0];
        $this->assertCount(2, $section->content);
        $this->assertInstanceOf(Section::class, $section);
        $this->assertStringContainsString('<p>This is a paragraph.</p>', implode("\n", $section->content));
        $this->assertStringContainsString('<p>Another paragraph.</p>', implode("\n", $section->content));
    }

    public function testParseMarkdownFileWithNoParagraphs(): void
    {
        $parser = new MarkdownParagraphToHTMLParser();
        $markdownContent = "This is a single line.";
        $markdownFile = new MarkdownFile('/path', 'file.md', $markdownContent);

        $section1 = (new Section(SectionType::PARAGRAPH));
        $section1->content = [
            'This is a single line.',
        ];

        $markdownFile->sectionedResult = [$section1];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser doesn't modify the content when there are no paragraphs
        $sectionedResult = $parsedFile->sectionedResult;
        $section = $sectionedResult[0];
        $this->assertCount(1, $section->content);
        $this->assertInstanceOf(Section::class, $section);
        $this->assertEquals("<p>This is a single line.</p>\n", implode("\n", $section->content));
    }
}

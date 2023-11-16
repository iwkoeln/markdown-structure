<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Parser;

use Iwm\MarkdownStructure\Parser\SectionsToHtmlParser;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;
use Iwm\MarkdownStructure\Value\SectionType;
use PHPUnit\Framework\TestCase;

class SectionsToHtmlParserTest extends TestCase
{
    public function testFileIsParsable(): void
    {
        $parser = new SectionsToHtmlParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', 'Markdown content');
        $markdownFile->sectionedResult = [new Section(SectionType::HEADLINE)];

        $this->assertTrue($parser->fileIsParsable($markdownFile));
    }

    public function testFileIsNotParsableForMediaFile(): void
    {
        $parser = new SectionsToHtmlParser();
        $mediaFile = new MediaFile('/path', 'image.jpg');

        $this->assertFalse($parser->fileIsParsable($mediaFile));
    }

    public function testParseMarkdownSectionsToHtml(): void
    {
        $parser = new SectionsToHtmlParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', '');

        $section1 = new Section(SectionType::HEADLINE);
        $section1->title = 'Section 1';
        $section1->level = 1;
        $section1->content = ['Content 1', 'Content 2'];

        $section2 = new Section(SectionType::HEADLINE);
        $section2->title = 'Section 2';
        $section2->level = 2;
        $section2->content = ['Content 3', 'Content 4'];

        $markdownFile->sectionedResult = [$section1, $section2];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser converts Markdown sections to HTML
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);

        $expectedHtml = '<h1>Section 1</h1>' .
            'Content 1' . "\n" .
            'Content 2' . "\n" .
            '<h2>Section 2</h2>' .
            'Content 3' . "\n" .
            'Content 4' . "\n";

        $this->assertEquals($expectedHtml, $parsedFile->html);
    }

    public function testParseMarkdownFileWithNoSectionsToHtml(): void
    {
        $parser = new SectionsToHtmlParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', 'Content 1' . "\n" . 'Content 2');
        $markdownFile->html = 'Content 1' . "\n" . 'Content 2';

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser doesn't modify content with no sections
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertEquals('Content 1' . "\n" . 'Content 2', $parsedFile->html);
    }
}

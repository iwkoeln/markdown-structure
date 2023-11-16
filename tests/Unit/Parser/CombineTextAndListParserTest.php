<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Parser;

use Iwm\MarkdownStructure\Parser\CombineTextAndListParser;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;
use Iwm\MarkdownStructure\Value\SectionType;
use PHPUnit\Framework\TestCase;

class CombineTextAndListParserTest extends TestCase
{
    public function testFileIsParsable(): void
    {
        $parser = new CombineTextAndListParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', 'Markdown content');

        $this->assertTrue($parser->fileIsParsable($markdownFile));
    }

    public function testFileIsNotParsableForMediaFile(): void
    {
        $parser = new CombineTextAndListParser();
        $mediaFile = new MediaFile('/path', 'image.jpg');

        $this->assertFalse($parser->fileIsParsable($mediaFile));
    }

    public function testParseMarkdownFileWithCombinedTextAndList(): void
    {
        $parser = new CombineTextAndListParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', '');
        $section1 = (new Section(SectionType::HEADLINE));
        $section1->content = [
            'Some text',
            '- List item 1',
        ];

        $markdownFile->sectionedResult = [$section1];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser combined text and list items within sections
        $sectionedResult = $parsedFile->sectionedResult;
        $section = $sectionedResult[0];
        $this->assertCount(1, $section->content);
        $this->assertInstanceOf(Section::class, $section);
        $this->assertEquals("Some text\n\n- List item 1", implode("\n", $section->content));
    }

    public function testParseMarkdownFileWithNoCombinedTextAndList(): void
    {
        $parser = new CombineTextAndListParser();
        $markdownContent = "- List item 1\n- List item 2\n- List item 3";
        $markdownFile = new MarkdownFile('/path', 'file.md', $markdownContent);

        $section1 = (new Section(SectionType::HEADLINE));
        $section1->content = [
            '- List item 1',
            '- List item 2',
            '- List item 3',
        ];

        $markdownFile->sectionedResult = [$section1];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser didn't modify the content
        $sectionedResult = $parsedFile->sectionedResult;
        $section = $sectionedResult[0];
        $this->assertCount(3, $section->content);
        $this->assertInstanceOf(Section::class, $section);
        $this->assertEquals($markdownContent, implode("\n", $section->content));
    }

    public function testParseMarkdownFileWithDifferentSections(): void
    {
        $parser = new CombineTextAndListParser();
        $markdownContent = "Some text\n- List item 1\n# Section 1\n- List item 2\n## Section 2";
        $markdownFile = new MarkdownFile('/path', 'file.md', $markdownContent);

        $section1 = (new Section(SectionType::HEADLINE));
        $section1->content = [
            'Some text',
            'Some text',
            'Some text',
        ];

        $section2 = (new Section(SectionType::HEADLINE));
        $section2->content = [
            '- List item 1',
            'Some text',
            '- List item 2',
        ];

        $section3 = (new Section(SectionType::HEADLINE));
        $section3->content = [
            'Some text',
            '- List item 1',
            '![Image 1](image1.jpg)',
        ];

        $markdownFile->sectionedResult = [$section1, $section2, $section3];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser combined text and list items only within sections
        $sectionedResult = $parsedFile->sectionedResult;
        $this->assertCount(3, $sectionedResult);
        $this->assertInstanceOf(Section::class, $sectionedResult[0]);
        $this->assertCount(3, $sectionedResult[0]->content);
        $this->assertEquals("Some text\nSome text\nSome text", implode("\n", $sectionedResult[0]->content));
        $this->assertInstanceOf(Section::class, $sectionedResult[1]);
        $this->assertCount(2, $sectionedResult[1]->content);
        $this->assertEquals("- List item 1\nSome text\n\n- List item 2", implode("\n", $sectionedResult[1]->content));
        $this->assertInstanceOf(Section::class, $sectionedResult[2]);
        $this->assertCount(2, $sectionedResult[2]->content);
        $this->assertEquals("Some text\n\n- List item 1\n![Image 1](image1.jpg)", implode("\n", $sectionedResult[2]->content));
    }
}

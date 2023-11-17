<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Parser;

use Iwm\MarkdownStructure\Parser\SplitByEmptyLineParser;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use PHPUnit\Framework\TestCase;

class SplitByEmptyLineParserTest extends TestCase
{
    public function testFileIsParsable(): void
    {
        $parser = new SplitByEmptyLineParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', 'Markdown content');

        $this->assertTrue($parser->fileIsParsable($markdownFile));
    }

    public function testFileIsNotParsableForMediaFile(): void
    {
        $parser = new SplitByEmptyLineParser();
        $mediaFile = new MediaFile('/path', 'image.jpg');

        $this->assertFalse($parser->fileIsParsable($mediaFile));
    }

    public function testParseMarkdownFileWithEmptyLineSplit(): void
    {
        $parser = new SplitByEmptyLineParser();
        $markdownContent = "Section 1\nContent 1\n\nSection 2\nContent 2";
        $markdownFile = new MarkdownFile('/path', 'file.md', $markdownContent);

        $expectedSectionedResult = [
            "Section 1\nContent 1",
            "Section 2\nContent 2",
        ];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser splits the content by empty lines
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertEquals($expectedSectionedResult, $parsedFile->sectionedResult);
    }

    public function testParseMarkdownFileWithNoEmptyLines(): void
    {
        $parser = new SplitByEmptyLineParser();
        $markdownContent = "Section 1\nContent 1\nSection 2\nContent 2";
        $markdownFile = new MarkdownFile('/path', 'file.md', $markdownContent);

        $expectedSectionedResult = [
            "Section 1\nContent 1\nSection 2\nContent 2",
        ];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser returns the content as a single section if there are no empty lines
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertEquals($expectedSectionedResult, $parsedFile->sectionedResult);
    }

    public function testParseMarkdownFileWithMultipleEmptyLines(): void
    {
        $parser = new SplitByEmptyLineParser();
        $markdownContent = "Section 1\nContent 1\n\n\n\n\nSection 2\nContent 2";
        $markdownFile = new MarkdownFile('/path', 'file.md', $markdownContent);

        $expectedSectionedResult = [
            "Section 1\nContent 1",
            "\nSection 2\nContent 2",
        ];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser handles multiple consecutive empty lines
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertEquals($expectedSectionedResult, $parsedFile->sectionedResult);
    }
}

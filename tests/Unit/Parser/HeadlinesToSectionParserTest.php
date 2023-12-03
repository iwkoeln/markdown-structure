<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Parser;

use Iwm\MarkdownStructure\Parser\HeadlinesToSectionParser;
use Iwm\MarkdownStructure\Parser\SplitByEmptyLineParser;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use PHPUnit\Framework\TestCase;

class HeadlinesToSectionParserTest extends TestCase
{
    public function testFileIsParsable(): void
    {
        $parser = new HeadlinesToSectionParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', 'Markdown content');

        $this->assertTrue($parser->fileIsParsable($markdownFile));
    }

    public function testFileIsNotParsableForMediaFile(): void
    {
        $parser = new HeadlinesToSectionParser();
        $mediaFile = new MediaFile('/path', 'image.jpg');

        $this->assertFalse($parser->fileIsParsable($mediaFile));
    }

    public function testParseMarkdownFileWithHeadlinesToSections(): void
    {
        $parser = new HeadlinesToSectionParser();
        $preProcessor = new SplitByEmptyLineParser();
        $markdownContent = "# Section 1\n\nContent 1\n\n## Subsection 1\n\nContent 2\n\n# Section 2\n\nContent 3";
        $markdownFile = new MarkdownFile('/path', 'file.md', $markdownContent);

        $markdownFile = $preProcessor->parse($markdownFile, [], [], []);
        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser converts headlines to sections
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);

        // Ensure the sectionedResult contains an array of sections with content
        $sectionedResult = $parsedFile->sectionedResult;
        $this->assertCount(3, $sectionedResult);

        $this->assertEquals('Section 1', $sectionedResult[0]->title);
        $this->assertEquals(1, $sectionedResult[0]->level);
        $this->assertCount(1, $sectionedResult[0]->content);
        $this->assertEquals('Content 1', $sectionedResult[0]->content[0]);

        $this->assertEquals('Subsection 1', $sectionedResult[1]->title);
        $this->assertEquals(2, $sectionedResult[1]->level);
        $this->assertCount(1, $sectionedResult[1]->content);
        $this->assertEquals('Content 2', $sectionedResult[1]->content[0]);

        $this->assertEquals('Section 2', $sectionedResult[2]->title);
        $this->assertEquals(1, $sectionedResult[2]->level);
        $this->assertCount(1, $sectionedResult[2]->content);
        $this->assertEquals('Content 3', $sectionedResult[2]->content[0]);
    }

    public function testParseMarkdownFileWithNoHeadlines(): void
    {
        $parser = new HeadlinesToSectionParser();
        $markdownContent = "Content 1\n\nContent 2\n\nContent 3";
        $markdownFile = new MarkdownFile('/path', 'file.md', $markdownContent);

        $preProcessor = new SplitByEmptyLineParser();
        $markdownFile = $preProcessor->parse($markdownFile, [], [], []);

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser doesn't modify the content when there are no headlines
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertEquals(['Content 1', 'Content 2', 'Content 3'], $parsedFile->sectionedResult[0]->content);
    }
}

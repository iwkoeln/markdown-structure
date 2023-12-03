<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Parser;

use Iwm\MarkdownStructure\Parser\ParagraphToContainerParser;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;
use Iwm\MarkdownStructure\Value\SectionType;
use PHPUnit\Framework\TestCase;

class ParagraphToContainerParserTest extends TestCase
{
    public function testFileIsParsable(): void
    {
        $parser = new ParagraphToContainerParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', 'Markdown content');

        $this->assertTrue($parser->fileIsParsable($markdownFile));
    }

    public function testFileIsNotParsableForMediaFile(): void
    {
        $parser = new ParagraphToContainerParser();
        $mediaFile = new MediaFile('/path', 'image.jpg');

        $this->assertFalse($parser->fileIsParsable($mediaFile));
    }

    public function testParseMarkdownFileWithParagraphsToContainers(): void
    {
        $parser = new ParagraphToContainerParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', '');
        $section = (new Section(SectionType::PARAGRAPH));
        $section->content = ['Paragraph 1', 'Paragraph 2'];
        $markdownFile->sectionedResult = [$section];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser wraps paragraphs in containers
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertCount(2, $parsedFile->sectionedResult[0]->content);
        $this->assertStringContainsString('<div>Paragraph 1</div>', $parsedFile->sectionedResult[0]->content[0]);
        $this->assertStringContainsString('<div>Paragraph 2</div>', $parsedFile->sectionedResult[0]->content[1]);
    }

    public function testParseMarkdownFileWithEmptyContent(): void
    {
        $parser = new ParagraphToContainerParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', '');

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser converts empty content to empty content
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertFalse(isset($parsedFile->sectionedResult[0]));
    }
}

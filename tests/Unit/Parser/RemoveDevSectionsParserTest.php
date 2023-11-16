<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Parser;

use Iwm\MarkdownStructure\Parser\RemoveDevSectionsParser;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Value\Section;
use Iwm\MarkdownStructure\Value\SectionType;
use PHPUnit\Framework\TestCase;

class RemoveDevSectionsParserTest extends TestCase
{
    public function testFileIsParsable(): void
    {
        $parser = new RemoveDevSectionsParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', 'Markdown content');

        $this->assertTrue($parser->fileIsParsable($markdownFile));
    }

    public function testFileIsNotParsableForMediaFile(): void
    {
        $parser = new RemoveDevSectionsParser();
        $mediaFile = new MediaFile('/path', 'image.jpg');

        $this->assertFalse($parser->fileIsParsable($mediaFile));
    }

    public function testParseMarkdownFileWithDevSections(): void
    {
        $parser = new RemoveDevSectionsParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', '');

        $section1 = new Section(SectionType::HEADLINE);
        $section1->title = 'Dev: Section 1';

        $section2 = new Section(SectionType::HEADLINE);
        $section2->title = 'Dev: Section 2';

        $section3 = new Section(SectionType::HEADLINE);
        $section3->title = 'Regular Section';

        $markdownFile->sectionedResult = [$section1, $section2, $section3];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser removes Dev sections
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertCount(1, $parsedFile->sectionedResult);
        $this->assertEquals('Regular Section', $parsedFile->sectionedResult[0]->title);
    }

    public function testParseMarkdownFileWithoutDevSections(): void
    {
        $parser = new RemoveDevSectionsParser();
        $markdownFile = new MarkdownFile('/path', 'file.md', '');

        $section1 = new Section(SectionType::HEADLINE);
        $section1->title = 'Regular Section 1';

        $section2 = new Section(SectionType::HEADLINE);
        $section2->title = 'Regular Section 2';

        $markdownFile->sectionedResult = [$section1, $section2];

        $parsedFile = $parser->parse($markdownFile, [], [], []);

        // Check that the parser doesn't remove sections without 'Dev:' prefix
        $this->assertInstanceOf(MarkdownFile::class, $parsedFile);
        $this->assertCount(2, $parsedFile->sectionedResult);
        $this->assertEquals('Regular Section 1', $parsedFile->sectionedResult[0]->title);
        $this->assertEquals('Regular Section 2', $parsedFile->sectionedResult[1]->title);
    }
}

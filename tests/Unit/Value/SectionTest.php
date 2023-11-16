<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Value;

use Iwm\MarkdownStructure\Tests\Functional\AbstractTestCase;
use Iwm\MarkdownStructure\Value\Section;
use Iwm\MarkdownStructure\Value\SectionType;

class SectionTest extends AbstractTestCase
{
    /**
     * @test
     * @testdox Section can be created with a type
     */
    public function testSectionCreation()
    {
        $sectionType = SectionType::HEADLINE;
        $section = new Section($sectionType);

        $this->assertInstanceOf(Section::class, $section);
        $this->assertEquals($sectionType, $section->type);
    }

    /**
     * @test
     * @testdox Section properties can be set and retrieved
     */
    public function testSectionProperties()
    {
        $sectionType = SectionType::HEADLINE;
        $section = new Section($sectionType);

        $section->title = 'Sample Title';
        $section->content = ['Content Line 1', 'Content Line 2'];
        $section->level = 2;

        $this->assertEquals('Sample Title', $section->title);
        $this->assertEquals(['Content Line 1', 'Content Line 2'], $section->content);
        $this->assertEquals(2, $section->level);
    }
}

<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Value;

use Iwm\MarkdownStructure\Value\SectionType;
use PHPUnit\Framework\TestCase;

class SectionTypeTest extends TestCase
{
    /**
     * @test
     * @testdox SectionType::HEADLINE is defined and accessible
     */
    public function testHeadlineSectionType()
    {
        $this->assertInstanceOf(SectionType::class, SectionType::HEADLINE);
        $this->assertEquals(SectionType::HEADLINE, SectionType::HEADLINE);
    }
}

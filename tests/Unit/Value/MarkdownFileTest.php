<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Value;

use Iwm\MarkdownStructure\Tests\Functional\AbstractTestCase;
use Iwm\MarkdownStructure\Value\MarkdownFile;

class MarkdownFileTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->workspacePath . '/docs', 0777, true);

        copy(__DIR__ . '/../../Fixtures/docs/index.md', $this->workspacePath . '/docs/index.md');
    }

    /**
     * @test
     * @testdox MarkdownFile is to string convertable
     */
    public function testToStringConversion()
    {
        $path = $this->workspacePath . '/docs/index.md';
        $markdown = '';
        $html = '';

        $markdownFile = new MarkdownFile($this->workspacePath, $path, $markdown, $html);
        $this->assertEquals($path, (string) $markdownFile);
    }
}

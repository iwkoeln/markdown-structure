<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Value;

use Iwm\MarkdownStructure\Tests\AbstractTestCase;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Value\MarkdownFile;

class MarkdownFileTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        PathUtility::mkdir($this->workspacePath . '/docs');

        copy(__DIR__ . '/../../Fixtures/docs/index.md', $this->workspacePath . '/docs/index.md');
    }

    public function testMarkdownFileIsToStringConvertable()
    {
        $path = $this->workspacePath . '/docs/index.md';
        $markdown = '';
        $html = '';

        $markdownFile = new MarkdownFile($this->workspacePath, $path, $markdown, $html);
        $this->assertEquals($path, (string)$markdownFile);
    }

    public function testMarkdownFileHasDefaultValues()
    {
        $path = $this->workspacePath . '/docs/index.md';

        $markdownFile = new MarkdownFile($this->workspacePath, $path, '');

        $this->assertSame('', $markdownFile->markdown);
        $this->assertSame('', $markdownFile->html);
        $this->assertNull($markdownFile->fallbackUrl);
        $this->assertSame([], $markdownFile->errors);
    }

    public function testMarkdownFileHasAssignedValues()
    {
        $path = $this->workspacePath . '/docs/index.md';
        $markdown = 'Markdown content';
        $html = '<p>HTML content</p>';
        $fallbackUrl = 'https://example.com/fallback';

        $markdownFile = new MarkdownFile($this->workspacePath, $path, $markdown, $html, $fallbackUrl, ['error1', 'error2']);

        $this->assertSame($markdown, $markdownFile->markdown);
        $this->assertSame($html, $markdownFile->html);
        $this->assertSame($fallbackUrl, $markdownFile->fallbackUrl);
        $this->assertSame(['error1', 'error2'], $markdownFile->errors);
    }
}

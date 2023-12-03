<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Finisher;

use Iwm\MarkdownStructure\Finisher\FallbackUrlForProjectFileLinksFinisher;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use PHPUnit\Framework\TestCase;

class FallbackUrlForProjectFileLinksFinisherTest extends TestCase
{
    public function testFileCanBeFinishedWithMarkdownFile()
    {
        $finisher = new FallbackUrlForProjectFileLinksFinisher();
        $markdownFile = new MarkdownFile('/path/to/file.md', 'Markdown content', '/base/url', '/fallback/url');

        $result = $finisher->fileCanBeFinished($markdownFile);

        $this->assertTrue($result);
    }

    public function testFileCannotBeFinishedWithMediaFile()
    {
        $finisher = new FallbackUrlForProjectFileLinksFinisher();
        $mediaFile = new MediaFile('/path/to/image.jpg', '/fallback/url');

        $result = $finisher->fileCanBeFinished($mediaFile);

        $this->assertFalse($result);
    }

    public function testFinishMarkdownFileWithEmptyDocumentationFiles()
    {
        $finisher = new FallbackUrlForProjectFileLinksFinisher();
        $markdownFile = new MarkdownFile('/path/to/', '/path/to/file.md', 'Markdown content', '<a href="/path/to/image.png">Some link</a>', 'https://fallback.url');

        $result = $finisher->finish($markdownFile, [], [], []);

        $this->assertEquals('<a href="/path/to/image.png">Some link</a>', $result->html);
    }

    public function testFinishMarkdownFileWithFallbackUrl()
    {
        $finisher = new FallbackUrlForProjectFileLinksFinisher();
        $markdownFile = new MarkdownFile('/path/', '/path/to/file.md', '', '<a href="/path/to/image.png">Some link</a>');
        $documentationFiles = ['/path/to/other.md', '/path/to/image.png'];

        $result = $finisher->finish($markdownFile, $documentationFiles, [], []);

        $expectedHtml = '<a href="/path/to/image.png">Some link</a>';
        $this->assertEquals($expectedHtml, $result->html);
    }

    public function testFinishMarkdownFileWithLinksReplaced()
    {
        $finisher = new FallbackUrlForProjectFileLinksFinisher();
        $markdownFile = new MarkdownFile('/path/', '/path/to/file.md', '', '<a href="../image.png">Some link</a>', 'https://fallback.url');
        $documentationFiles = ['/path/to/other.md', '/path/to/image.png'];

        $result = $finisher->finish($markdownFile, $documentationFiles, [], []);

        $expectedHtml = '<a href="https://fallback.url/image.png">Some link</a>';
        $this->assertEquals($expectedHtml, $result->html);
    }
}

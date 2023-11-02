<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Value;

use Iwm\MarkdownStructure\Tests\Functional\AbstractTestCase;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;
use PHPUnit\Framework\TestCase;

class MarkdownProjectTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->workspacePath . '/docs', 0777, true);
        mkdir($this->workspacePath . '/docs/dev', 0777, true);
        mkdir($this->workspacePath . '/docs/features', 0777, true);
        mkdir($this->workspacePath . '/docs/features/img', 0777, true);
        mkdir($this->workspacePath . '/docs/img', 0777, true);

        copy(__DIR__ . '/../../Fixtures/docs/index.md', $this->workspacePath . '/docs/index.md');

        copy(__DIR__ . '/../../Fixtures/docs/img/example.gif', $this->workspacePath . '/docs/img/example.gif');
        copy(__DIR__ . '/../../Fixtures/docs/img/example.svg', $this->workspacePath . '/docs/img/example.svg');
        copy(__DIR__ . '/../../Fixtures/docs/img/image.jpg', $this->workspacePath . '/docs/img/image.jpg');
        copy(__DIR__ . '/../../Fixtures/docs/img/image.png', $this->workspacePath . '/docs/img/image.png');

        copy(__DIR__ . '/../../Fixtures/docs/features/img/image.png', $this->workspacePath . '/docs/features/img/image.png');
        copy(__DIR__ . '/../../Fixtures/docs/features/img/image.jpg', $this->workspacePath . '/docs/features/img/image.jpg');

        copy(__DIR__ . '/../../Fixtures/docs/features/another-feature.md', $this->workspacePath . '/docs/features/another-feature.md');
        copy(__DIR__ . '/../../Fixtures/docs/features/feature.md', $this->workspacePath . '/docs/features/feature.md');

        copy(__DIR__ . '/../../Fixtures/docs/dev/some-dev-doc.md', $this->workspacePath . '/docs/dev/some-dev-doc.md');
    }

    /**
     * @test
     * @testdox MarkdownFiles can be found by path
     */
    public function test()
    {
        $rootPath = $this->workspacePath;
        $projectFiles = [
            $this->workspacePath . '/docs/index.md' =>
                new MarkdownFile($this->workspacePath . '/docs/index.md', '', ''),
            $this->workspacePath . '/docs/features/another-feature.md' =>
                new MarkdownFile($this->workspacePath . '/docs/features/another-feature.md', '', ''),
            $this->workspacePath . '/docs/features/feature.md' =>
                new MarkdownFile($this->workspacePath . '/docs/features/feature.md', '', ''),
            $this->workspacePath . '/docs/dev/some-dev-doc.md' =>
                new MarkdownFile($this->workspacePath . '/docs/dev/some-dev-doc.md', '', ''),
        ];
        $projectMediaFiles = [
            $this->workspacePath . '/docs/features/img/image.png' =>
                new MediaFile($this->workspacePath . '/docs/features/img/image.png'),
            $this->workspacePath . '/docs/features/img/image.png' =>
                new MediaFile($this->workspacePath . '/docs/features/img/image.png')
        ];
        $indexPath = $this->workspacePath . '/docs/index.md';
        $projectFilesNested = [];
        $errors = null;

        $markdownProjekt = new MarkdownProject($rootPath, $rootPath, $projectFiles, $projectMediaFiles, $indexPath, [], [], $projectFilesNested, $errors);

        $markdownFile = $markdownProjekt->getFileByPath($this->workspacePath . '/docs/index.md');
        $this->assertEquals($this->workspacePath . '/docs/index.md', (string) $markdownFile);
        $markdownFile = $markdownProjekt->getFileByPath($this->workspacePath . '/docs/features/another-feature.md');
        $this->assertEquals($this->workspacePath . '/docs/features/another-feature.md', (string) $markdownFile);
        $markdownFile = $markdownProjekt->getFileByPath($this->workspacePath . '/docs/features/feature.md');
        $this->assertEquals($this->workspacePath . '/docs/features/feature.md', (string) $markdownFile);
    }
}

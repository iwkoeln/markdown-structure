<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Value;

use Iwm\MarkdownStructure\Tests\Functional\AbstractTestCase;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;

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

        // Additional fixture for testing getFileByPath
        copy(__DIR__ . '/../../Fixtures/docs/dev/some-dev-doc.md', $this->workspacePath . '/docs/some-dev-doc.md');
    }

    /**
     * @test
     * @testdox MarkdownFiles can be found by path
     */
    public function testGetFileByPath()
    {
        $rootPath = $this->workspacePath;
        $documentationFiles = [
            $this->workspacePath . '/docs/index.md' =>
                new MarkdownFile($this->workspacePath, $this->workspacePath . '/docs/index.md', '', ''),
            $this->workspacePath . '/docs/features/another-feature.md' =>
                new MarkdownFile($this->workspacePath, $this->workspacePath . '/docs/features/another-feature.md', '', ''),
            $this->workspacePath . '/docs/features/feature.md' =>
                new MarkdownFile($this->workspacePath, $this->workspacePath . '/docs/features/feature.md', '', ''),
            $this->workspacePath . '/docs/dev/some-dev-doc.md' =>
                new MarkdownFile($this->workspacePath, $this->workspacePath . '/docs/dev/some-dev-doc.md', '', ''),
        ];
        $documentationMediaFiles = [
            $this->workspacePath . '/docs/features/img/image.png' =>
                new MediaFile($this->workspacePath . '/docs/features/img/image.png'),
            $this->workspacePath . '/docs/features/img/image.png' =>
                new MediaFile($this->workspacePath . '/docs/features/img/image.png')
        ];
        $projectFilesNested = [];
        $errors = null;

        $markdownProjekt = new MarkdownProject($rootPath, '/docs', '/index.md', $documentationFiles, $documentationMediaFiles, [], [], $projectFilesNested);

        $markdownFile = $markdownProjekt->getFileByPath($this->workspacePath . '/docs/index.md');
        $this->assertEquals($this->workspacePath . '/docs/index.md', (string) $markdownFile);
        $markdownFile = $markdownProjekt->getFileByPath($this->workspacePath . '/docs/features/another-feature.md');
        $this->assertEquals($this->workspacePath . '/docs/features/another-feature.md', (string) $markdownFile);
        $markdownFile = $markdownProjekt->getFileByPath($this->workspacePath . '/docs/dev/some-dev-doc.md');
        $this->assertEquals($this->workspacePath . '/docs/dev/some-dev-doc.md', (string) $markdownFile);
    }

    /**
     * @test
     * @testdox MarkdownProject can be created with empty values
     */
    public function testCreateMarkdownProjectWithEmptyValues()
    {
        $markdownProjekt = new MarkdownProject('', '', '', [], [], [], [], []);

        // Assert that the project can be created without errors
        $this->assertInstanceOf(MarkdownProject::class, $markdownProjekt);
    }
}

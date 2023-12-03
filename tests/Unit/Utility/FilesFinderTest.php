<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Utility;

use Iwm\MarkdownStructure\Tests\AbstractTestCase;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Iwm\MarkdownStructure\Utility\PathUtility;

class FilesFinderTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        PathUtility::mkdir($this->workspacePath . '/docs');
        PathUtility::mkdir($this->workspacePath . '/docs/dev');
        PathUtility::mkdir($this->workspacePath . '/docs/features');
        PathUtility::mkdir($this->workspacePath . '/docs/features/img');
        PathUtility::mkdir($this->workspacePath . '/docs/img');

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
     *
     * @testdox Files Finder finds all example files
     */
    public function testIfFilesFinderFindsFiles()
    {
        $filesFinder = FilesFinder::findFilesByPath($this->workspacePath . '/docs');
        $expectedFiles = [
            $this->workspacePath . '/docs/dev/some-dev-doc.md' => $this->workspacePath . '/docs/dev/some-dev-doc.md',
            $this->workspacePath . '/docs/features/another-feature.md' => $this->workspacePath . '/docs/features/another-feature.md',
            $this->workspacePath . '/docs/features/feature.md' => $this->workspacePath . '/docs/features/feature.md',
            $this->workspacePath . '/docs/features/img/image.jpg' => $this->workspacePath . '/docs/features/img/image.jpg',
            $this->workspacePath . '/docs/features/img/image.png' => $this->workspacePath . '/docs/features/img/image.png',
            $this->workspacePath . '/docs/img/example.gif' => $this->workspacePath . '/docs/img/example.gif',
            $this->workspacePath . '/docs/img/example.svg' => $this->workspacePath . '/docs/img/example.svg',
            $this->workspacePath . '/docs/img/image.jpg' => $this->workspacePath . '/docs/img/image.jpg',
            $this->workspacePath . '/docs/img/image.png' => $this->workspacePath . '/docs/img/image.png',
            $this->workspacePath . '/docs/index.md' => $this->workspacePath . '/docs/index.md',
        ];

        $actualFiles = iterator_to_array($filesFinder);

        $this->assertEquals($expectedFiles, $actualFiles);
    }
}

<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Utility;

use Iwm\MarkdownStructure\Tests\Functional\AbstractTestCase;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use PHPUnit\Framework\TestCase;

class FilesFinderTest extends AbstractTestCase
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
     * @testdox Files Finder finds all example files
     */
    public function testIfFilesFinderFindsFiles()
    {

        $files = FilesFinder::findFilesbyPath($this->workspacePath . '/docs');
        $expectedFiles = [
            $this->workspacePath . '/docs/dev/some-dev-doc.md',
            $this->workspacePath . '/docs/features/another-feature.md',
            $this->workspacePath . '/docs/features/feature.md',
            $this->workspacePath . '/docs/features/img/image.jpg',
            $this->workspacePath . '/docs/features/img/image.png',
            $this->workspacePath . '/docs/img/example.gif',
            $this->workspacePath . '/docs/img/example.svg',
            $this->workspacePath . '/docs/img/image.jpg',
            $this->workspacePath . '/docs/img/image.png',
            $this->workspacePath . '/docs/index.md',
        ];
        $this->assertEquals($expectedFiles, $files);
    }
}

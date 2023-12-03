<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Utility;

use Iwm\MarkdownStructure\Tests\AbstractTestCase;
use Iwm\MarkdownStructure\Utility\FileTreeBuilder;
use Iwm\MarkdownStructure\Value\MarkdownFile;

class FileTreeBuilderTest extends AbstractTestCase
{
    private array $filePaths = [];

    private MarkdownFile $fileA;
    private MarkdownFile $fileB;
    private MarkdownFile $fileC;

    public function setUp(): void
    {
        $this->fileA = new MarkdownFile('/path/to', '/index.md', '');
        $this->fileB = new MarkdownFile('/path/to', '/features/extra-info.md', '');
        $this->fileC = new MarkdownFile('/path/to', '/features/extra-info/sub-file.md', '');

        $this->filePaths = [
            '/index.md' => $this->fileA,
            '/features/extra-info.md' => $this->fileB,
            '/features/extra-info/sub-file.md' => $this->fileC,
        ];
    }

    /**
     * @testdox FileTreeBuilder can build a file tree
     */
    public function testBuildFileTree(): void
    {
        $expectedFileTree = [
            'index.md' => $this->fileA,
            'features' => [
                'extra-info.md' => $this->fileB,
                'extra-info' => [
                    'sub-file.md' => $this->fileC,
                ],
            ],
        ];

        $fileTree = FileTreeBuilder::buildFileTree($this->filePaths);

        $this->assertSame($expectedFileTree, $fileTree);
    }
}

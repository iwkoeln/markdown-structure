<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Utility;

use Iwm\MarkdownStructure\Tests\AbstractTestCase;
use Iwm\MarkdownStructure\Utility\FileTreeBuilder;

class FileTreeBuilderTest extends AbstractTestCase
{
    public array $filePaths = [
        '/var/www/html/tests/Data/index.md',
        '/var/www/html/tests/Data/extra-info.md',
        '/var/www/html/README.md',
        '/var/www/html/tests/Data/extra-info/sub-file.md',
        '/var/www/html/tests/Data/img/image.jpg',
        '/var/www/html/tests/Data/img/image.png',
    ];

    /**
     * @test
     *
     * @testdox FileTreeBuilder can build a file tree
     */
    public function testBuildFileTree(): void
    {
        $expectedFileTree = [
            '' => [
                'var' => [
                    'www' => [
                        'html' => [
                            'tests' => [
                                'Data' => [
                                    'index.md' => '/var/www/html/tests/Data/index.md',
                                    'extra-info.md' => '/var/www/html/tests/Data/extra-info.md',
                                    'extra-info' => [
                                        'sub-file.md' => '/var/www/html/tests/Data/extra-info/sub-file.md',
                                    ],
                                    'img' => [
                                        'image.jpg' => '/var/www/html/tests/Data/img/image.jpg',
                                        'image.png' => '/var/www/html/tests/Data/img/image.png',
                                    ],
                                ],
                            ],
                            'README.md' => '/var/www/html/README.md',
                        ],
                    ],
                ],
            ],
        ];

        $fileTree = FileTreeBuilder::buildFileTree($this->filePaths);

        $this->assertSame($expectedFileTree, $fileTree);
    }
}

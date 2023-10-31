<?php

namespace Iwm\MarkdownStructure\Unit\Utility;

use Iwm\MarkdownStructure\Utility\PathUtility;
use PHPUnit\Framework\TestCase;

class PathUtilityTest extends TestCase
{
    /**
     * @test
     * @testdox Path Utility can make directories
     */
    public function testMakeDirectory(): void
    {
        $tempDir = '/var/www/html/temp';
        PathUtility::mkdir($tempDir);

        $this->assertDirectoryExists($tempDir);

        PathUtility::rmdir($tempDir);
        $this->assertDirectoryDoesNotExist($tempDir);
    }
    /**
     * @test
     * @testdox Path Utility can provide directory names
     */
    public function testDirectoryName(): void
    {
        $fileName = '/var/www/html/tests/Data/index.md';
        $expectedDirName = '/var/www/html/tests/Data';

        $dirName = PathUtility::dirname($fileName);
        $this->assertEquals($expectedDirName, $dirName);

        $expectedDirName = '/var/www/html/tests';
        $dirName = PathUtility::dirname($fileName, 2);
        $this->assertEquals($expectedDirName, $dirName);
    }

    /**
     * @test
     * @testdox Path Utility can check if a path is a media file
     */
    public function testIsMediaFile(): void
    {
        // Test for media file extensions
        $this->assertTrue(PathUtility::isMediaFile('/var/www/html/tests/Data/img/image.jpg'));
        $this->assertTrue(PathUtility::isMediaFile('/var/www/html/tests/Data/img/image.png'));

        // Test for directory names 'img' and 'image'
        $this->assertFalse(PathUtility::isMediaFile('/var/www/html/tests/Data/img/non-image.txt'));
        $this->assertFalse(PathUtility::isMediaFile('/var/www/html/tests/Data/img/jpg/non-image.txt'));

        // Test for non-media file extensions
        $this->assertFalse(PathUtility::isMediaFile('/var/www/html/README.md'));
        $this->assertFalse(PathUtility::isMediaFile('/var/www/html/example.php'));
    }

    /**
     * @test
     * @testdox Path Utility can guess mime type from path
     */
    public function testGuessMimeTypeFromPath(): void
    {
        // Test for image file extensions
        $this->assertSame('image/jpg', PathUtility::guessMimeTypeFromPath('/var/www/html/tests/Data/img/image.jpg'));
        $this->assertSame('image/png', PathUtility::guessMimeTypeFromPath('/var/www/html/tests/Data/img/image.png'));
        $this->assertSame('image/gif', PathUtility::guessMimeTypeFromPath('/var/www/html/tests/Data/img/example.gif'));
        $this->assertSame('image/svg', PathUtility::guessMimeTypeFromPath('/var/www/html/tests/Data/img/example.svg'));

        // Test for non-image file extensions
        $this->assertSame('application/octet-stream', PathUtility::guessMimeTypeFromPath('/var/www/html/README.md'));
        $this->assertSame('application/octet-stream', PathUtility::guessMimeTypeFromPath('/var/www/html/example.php'));
    }

    /**
     * @test
     * @testdox Path Utility can test if a path is an external url
     */
    public function testIsExternalUrl(): void
    {
        $this->assertTrue(PathUtility::isExternalUrl('https://www.google.com'));
        $this->assertTrue(PathUtility::isExternalUrl('http://www.google.com'));
        $this->assertTrue(PathUtility::isExternalUrl('mailto:mai@iwkoeln.de'));
        $this->assertFalse(PathUtility::isExternalUrl('/var/www/html/tests/Data/img/image.jpg'));
        $this->assertFalse(PathUtility::isExternalUrl('/var/www/html/tests/Data/index.md'));
    }

    /**
     * @test
     * @testdox Path Utility can build a file tree
     */
    public function testBuildFileTree(): void
    {
        $filePaths = [
            '/var/www/html/tests/Data/index.md',
            '/var/www/html/tests/Data/extra-info.md',
            '/var/www/html/README.md',
            '/var/www/html/tests/Data/extra-info/sub-file.md',
            '/var/www/html/tests/Data/img/image.jpg',
            '/var/www/html/tests/Data/img/image.png'
        ];

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
            ]
        ];

        $fileTree = PathUtility::buildFileTree($filePaths);

        $this->assertSame($expectedFileTree, $fileTree);
    }

    /**
     * @test
     * @testdox Path Utility can check if path is in root
     */
    public function testIsPathInRoot(): void
    {
        $this->assertTrue(PathUtility::isPathInRoot(
            'extra-info.md',
            '/var/www/html/tests/Data/index.md'
        ));

        $this->assertTrue(PathUtility::isPathInRoot(
            'extra-info/sub-file.md',
            '/var/www/html/tests/Data/extra-info.md'
        ));

        $this->assertFalse(PathUtility::isPathInRoot(
            '../../index.md',
            'index.md'
        ));
    }

    /**
     * @test
     * @testdox Path Utility can resolve absolute paths
     */
    public function testResolveAbsolutUrl(): void
    {
        $expectedPath = '/var/www/html/README.md';

        $this->assertEquals($expectedPath, PathUtility::resolveAbsolutPath(
            '/var/www/html/tests/Data/index.md',
            '../../README.md'
        ));
    }

    /**
     * @test
     * @testdox Path Utility can resolve relative paths
     */
    public function testResolveRelativeUrl(): void
    {
        $expectedPath = '../index.md';

        $this->assertEquals($expectedPath, PathUtility::resolveRelativePath(
            '/var/www/html/tests/Data/extra-info/sub-file.md',
            '/var/www/html/tests/Data/index.md'
        ));

        $this->assertEquals($expectedPath, PathUtility::resolveRelativePath(
            '/test/sub-file.md',
            '/index.md'
        ));
    }

    /**
     * @test
     * @testdox Path Utility can sanitize file names
     */
    public function testSanitizeFileName(): void
    {
        $this->assertEquals('index.md', PathUtility::sanitizeFileName('Index.md'));
        $this->assertEquals('afile.md', PathUtility::sanitizeFileName('A File.md'));
        $this->assertEquals('images12345.md', PathUtility::sanitizeFileName('Images 12345.md'));
    }
}

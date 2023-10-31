<?php

namespace Iwm\MarkdownStructure\Unit\Utility;

use Iwm\MarkdownStructure\Utility\FilesFinder;
use PHPUnit\Framework\TestCase;

class FilesFinderTest extends TestCase
{
    /**
     * @test
     * @testdox Files Finder finds all example files
     */
    public function testIfFilesFinderFindsFiles()
    {
        $files = FilesFinder::findFilesbyPath('/var/www/html/tests/Data');
        $expectedFiles = [
            "/var/www/html/tests/Data/index.md",
            "/var/www/html/tests/Data/extra-info/sub-file.md",
            "/var/www/html/tests/Data/img/example.gif",
            "/var/www/html/tests/Data/img/image.png",
            "/var/www/html/tests/Data/img/jpg/non-image.txt",
            "/var/www/html/tests/Data/img/non-image.txt",
            "/var/www/html/tests/Data/img/example.svg",
            "/var/www/html/tests/Data/img/image.jpg",
            "/var/www/html/tests/Data/validator/cause-error.md",
            "/var/www/html/tests/Data/extra-info.md",
        ];
        $this->assertEquals($expectedFiles, $files);
    }
}
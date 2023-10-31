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
        $basePath = getenv('BASE_PATH') ?: '/var/www/html';

        $dataPath = "$basePath/tests/Data";
        $files = FilesFinder::findFilesbyPath($dataPath);
        $expectedFiles = [
            "$dataPath/index.md",
            "$dataPath/extra-info/sub-file.md",
            "$dataPath/img/example.gif",
            "$dataPath/img/image.png",
            "$dataPath/img/jpg/non-image.txt",
            "$dataPath/img/non-image.txt",
            "$dataPath/img/example.svg",
            "$dataPath/img/image.jpg",
            "$dataPath/validator/cause-error.md",
            "$dataPath/extra-info.md",
        ];
        $this->assertEquals($expectedFiles, $files);
    }
}
<?php

namespace Iwm\MarkdownStructure\Unit\Value;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;
use PHPUnit\Framework\TestCase;

class MarkdownProjectTest extends TestCase
{
    /**
     * @test
     * @testdox MarkdownFiles can be found by path
     */
    public function test()
    {
        $rootPath = '/var/www/html/tests/Data/';
        $projectFiles = [
            '/var/www/html/tests/Data/index.md' => new MarkdownFile('/var/www/html/tests/Data/index.md', '', ''),
            '/var/www/html/tests/Data/extra-info.md' => new MarkdownFile('/var/www/html/tests/Data/extra-info.md', '', ''),
            '/var/www/html/tests/Data/extra-info/sub-file.md' => new MarkdownFile('/var/www/html/tests/Data/extra-info/sub-file.md', '', ''),
        ];
        $projectMediaFiles = [
            '/var/www/html/tests/Data/img/image.jpg' => new MediaFile('/var/www/html/tests/Data/img/image.jpg'),
            '/var/www/html/tests/Data/img/image.png' => new MediaFile('/var/www/html/tests/Data/img/image.png')
        ];
        $indexPath = '/var/www/html/tests/Data/index.md';
        $projectFilesNested = [];
        $errors = null;

        $markdownProjekt = new MarkdownProject($rootPath, $rootPath, $projectFiles, $projectMediaFiles, $indexPath, [], [], $projectFilesNested, $errors);

        $markdownFile = $markdownProjekt->getFileByPath('/var/www/html/tests/Data/index.md');
        $this->assertEquals('/var/www/html/tests/Data/index.md', (string) $markdownFile);
        $markdownFile = $markdownProjekt->getFileByPath('/var/www/html/tests/Data/extra-info.md');
        $this->assertEquals('/var/www/html/tests/Data/extra-info.md', (string) $markdownFile);
        $markdownFile = $markdownProjekt->getFileByPath('/var/www/html/tests/Data/extra-info/sub-file.md');
        $this->assertEquals('/var/www/html/tests/Data/extra-info/sub-file.md', (string) $markdownFile);
    }
}
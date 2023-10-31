<?php

namespace Iwm\MarkdownStructure\Unit;

use ErrorException;
use InvalidArgumentException;
use Iwm\MarkdownStructure\MarkdownProjectFactory;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use PHPUnit\Framework\TestCase;

class MarkdownProjectFactoryTest extends TestCase
{
    //TODO: Finish these tests

    /**
     * @test
     * @testdox MarkdownProjectFactory should throw an exception if the base path does not exists
     */
    public function testException(): void
    {
        $basePath = getenv('BASE_PATH') ?: '/var/www/html';
        $mdProjectPath = dirname($basePath);
        $indexPath = "$mdProjectPath/index.md";
        $url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

        $this->expectException(InvalidArgumentException::class);
        $factory = new MarkdownProjectFactory($basePath, $url, $mdProjectPath, $indexPath);
    }


    /**
     * @test
     * @testdox MarkdownProjectFactory should create a markdown project
     */
    public function testMarkdownProject()
    {
        $basePath = getenv('BASE_PATH') ?: '/var/www/html';
        $mdProjectPath = "$basePath/tests/Data";
        $indexPath = "$mdProjectPath/index.md";
        $url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

        $factory = new MarkdownProjectFactory($basePath, $url, $mdProjectPath, $indexPath);

        $project = $factory->create();

//        $this->assertEquals($expectedProject, $project);
//        $this->assertEquals(
//            new MarkdownFile(),
//            $project
//        );
        $this->assertTrue(true);
    }
}
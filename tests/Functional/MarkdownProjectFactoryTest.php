<?php

namespace Iwm\MarkdownStructure\Tests\Functional;

use ErrorException;
use InvalidArgumentException;
use Iwm\MarkdownStructure\MarkdownProjectFactory;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use PHPUnit\Framework\TestCase;

class MarkdownProjectFactoryTest extends AbstractTestCase
{
    //TODO: Finish these tests

    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->getBasePath() . $this->workspacePath . '/docs', 0777, true);
        mkdir($this->getBasePath() . $this->workspacePath . '/docs/dev', 0777, true);
        mkdir($this->getBasePath() . $this->workspacePath . '/docs/features', 0777, true);
        mkdir($this->getBasePath() . $this->workspacePath . '/docs/features/img', 0777, true);
        mkdir($this->getBasePath() . $this->workspacePath . '/docs/img', 0777, true);

        copy(__DIR__ . '/../../Fixtures/docs/index.md', $this->getBasePath() . $this->workspacePath . '/docs/index.md');

        copy(__DIR__ . '/../../Fixtures/docs/img/example.gif', $this->getBasePath() . $this->workspacePath . '/docs/img/example.gif');
        copy(__DIR__ . '/../../Fixtures/docs/img/example.svg', $this->getBasePath() . $this->workspacePath . '/docs/img/example.svg');
        copy(__DIR__ . '/../../Fixtures/docs/img/image.jpg', $this->getBasePath() . $this->workspacePath . '/docs/img/image.jpg');
        copy(__DIR__ . '/../../Fixtures/docs/img/image.png', $this->getBasePath() . $this->workspacePath . '/docs/img/image.png');

        copy(__DIR__ . '/../../Fixtures/docs/features/img/image.png', $this->getBasePath() . $this->workspacePath . '/docs/features/img/image.png');
        copy(__DIR__ . '/../../Fixtures/docs/features/img/image.jpg', $this->getBasePath() . $this->workspacePath . '/docs/features/img/image.jpg');

        copy(__DIR__ . '/../../Fixtures/docs/features/another-feature.md', $this->getBasePath() . $this->workspacePath . '/docs/features/another-feature.md');
        copy(__DIR__ . '/../../Fixtures/docs/features/feature.md', $this->getBasePath() . $this->workspacePath . '/docs/features/feature.md');

        copy(__DIR__ . '/../../Fixtures/docs/dev/some-dev-doc.md', $this->getBasePath() . $this->workspacePath . '/docs/dev/some-dev-doc.md');
    }

    /**
     * @test
     * @testdox MarkdownProjectFactory should throw an exception if the base path does not exists
     */
    public function testException(): void
    {
        $basePath = $this->getBasePath() . $this->workspacePath;
        $mdProjectPath = dirname($this->getBasePath() . $this->workspacePath);
        $indexPath = "/index.md";
        $url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

        $this->expectException(InvalidArgumentException::class);
        $factory = new MarkdownProjectFactory($basePath, $mdProjectPath, $indexPath, $url);
    }


    /**
     * @test
     * @testdox MarkdownProjectFactory should create a markdown project
     */
    public function testMarkdownProject()
    {
        $basePath = $this->getBasePath() . $this->workspacePath;
        $mdProjectPath = '/docs';
        $indexPath = "/index.md";
        $url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

        $factory = new MarkdownProjectFactory($basePath, $mdProjectPath, $indexPath, $url);

        $project = $factory->create();

//        $this->assertEquals($expectedProject, $project);
//        $this->assertEquals(
//            new MarkdownFile(),
//            $project
//        );
        $this->assertTrue(true);
    }
}

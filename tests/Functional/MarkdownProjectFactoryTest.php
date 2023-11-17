<?php

namespace Iwm\MarkdownStructure\Tests\Functional;

use ErrorException;
use InvalidArgumentException;
use Iwm\MarkdownStructure\MarkdownProjectFactory;
use Iwm\MarkdownStructure\Parser\ParagraphToContainerParser;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Iwm\MarkdownStructure\Validator\MediaFileValidator;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
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
        $basePath = $this->getBasePath() . $this->workspacePath . '/folder-that-does-not-exists';
        $mdProjectPath = $this->getBasePath() . $this->workspacePath . '/folder-that-does-not-exists';
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

        $this->assertInstanceOf(MarkdownProject::class, $project);
    }

    /**
     * @test
     * @testdox MarkdownProjectFactory should create a MarkdownProject with custom parsers and validators
     */
    public function testCreateMarkdownProjectWithCustomParsersAndValidators(): void
    {
        $basePath = $this->getBasePath() . $this->workspacePath;
        $factory = new MarkdownProjectFactory($basePath);

        $customParser = new ParagraphToContainerParser();
        $customValidator = new MediaFileValidator();
        $factory->registerParser([$customParser]);
        $factory->registerValidators([$customValidator]);

        $markdownProject = $factory->create();

        $this->assertInstanceOf(MarkdownProject::class, $markdownProject);
    }

    /**
     * @test
     * @testdox MarkdownProjectFactory should create a MarkdownProject with a custom fallback base URL
     */
    public function testCreateMarkdownProjectWithCustomFallbackBaseUrl(): void
    {
        $basePath = $this->getBasePath() . $this->workspacePath;
        $customFallbackBaseUrl = 'https://custom.example.com/';
        $factory = new MarkdownProjectFactory($basePath, '/docs', '/index.md', $customFallbackBaseUrl);

        $markdownProject = $factory->create();

        $this->assertInstanceOf(MarkdownProject::class, $markdownProject);
    }

    /**
     * @test
     * @testdox MarkdownProjectFactory should throw an exception when adding invalid files
     */
    public function testAddInvalidFiles(): void
    {
        $basePath = $this->getBasePath() . $this->workspacePath;
        $factory = new MarkdownProjectFactory($basePath);

        $invalidFile = 'non_existent_file.md';

//        $this->expectException(InvalidArgumentException::class);

        $factory->addFile($invalidFile);
    }
}

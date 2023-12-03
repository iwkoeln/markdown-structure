<?php

namespace Iwm\MarkdownStructure\Tests\Functional;

use Iwm\MarkdownStructure\MarkdownProjectFactory;
use Iwm\MarkdownStructure\Parser\ParagraphToContainerParser;
use Iwm\MarkdownStructure\Tests\AbstractTestCase;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Validator\MediaFileValidator;
use Iwm\MarkdownStructure\Value\MarkdownProject;

class MarkdownProjectFactoryTest extends AbstractTestCase
{
    private MarkdownProjectFactory $subject;

    public function setUp(): void
    {
        parent::setUp();

        PathUtility::mkdir($this->workspacePath . '/docs');
        PathUtility::mkdir($this->workspacePath . '/docs/dev');
        PathUtility::mkdir($this->workspacePath . '/docs/features');
        PathUtility::mkdir($this->workspacePath . '/docs/features/img');
        PathUtility::mkdir($this->workspacePath . '/docs/img');

        copy(__DIR__ . '/../Fixtures/docs/index.md', $this->workspacePath . '/docs/index.md');

        copy(__DIR__ . '/../Fixtures/docs/img/example.gif', $this->workspacePath . '/docs/img/example.gif');
        copy(__DIR__ . '/../Fixtures/docs/img/example.svg', $this->workspacePath . '/docs/img/example.svg');
        copy(__DIR__ . '/../Fixtures/docs/img/image.jpg', $this->workspacePath . '/docs/img/image.jpg');
        copy(__DIR__ . '/../Fixtures/docs/img/image.png', $this->workspacePath . '/docs/img/image.png');

        copy(__DIR__ . '/../Fixtures/docs/features/img/image.png', $this->workspacePath . '/docs/features/img/image.png');
        copy(__DIR__ . '/../Fixtures/docs/features/img/image.jpg', $this->workspacePath . '/docs/features/img/image.jpg');

        copy(__DIR__ . '/../Fixtures/docs/features/another-feature.md', $this->workspacePath . '/docs/features/another-feature.md');
        copy(__DIR__ . '/../Fixtures/docs/features/feature.md', $this->workspacePath . '/docs/features/feature.md');

        copy(__DIR__ . '/../Fixtures/docs/dev/some-dev-doc.md', $this->workspacePath . '/docs/dev/some-dev-doc.md');
        copy(__DIR__ . '/../Fixtures/docs/dev/no-headline.md', $this->workspacePath . '/docs/dev/no-headline.md');

        $this->subject = new MarkdownProjectFactory($this->workspacePath);
        $this->subject->addFiles();
    }

    /**
     * @test
     *
     * @testdox MarkdownProjectFactory should throw an exception if the base path does not exists
     */
    public function testException(): void
    {
        $basePath = $this->workspacePath . '/folder-that-does-not-exists';
        $mdProjectPath = $this->workspacePath . '/folder-that-does-not-exists';
        $indexPath = '/index.md';
        $url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

        $this->expectException(\InvalidArgumentException::class);
        new MarkdownProjectFactory($basePath, $mdProjectPath, $indexPath, $url);
    }

    /**
     * @test
     *
     * @testdox MarkdownProjectFactory should create a markdown project
     */
    public function testMarkdownProject()
    {
        $mdProjectPath = '/docs';
        $indexPath = '/index.md';
        $url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

        $factory = new MarkdownProjectFactory($this->workspacePath, $mdProjectPath, $indexPath, $url);

        $project = $factory->create();

        $this->assertInstanceOf(MarkdownProject::class, $project);
    }

    /**
     * @test
     *
     * @testdox MarkdownProjectFactory should create a MarkdownProject with custom parsers and validators
     */
    public function testCreateMarkdownProjectWithCustomParsersAndValidators(): void
    {
        $customParser = new ParagraphToContainerParser();
        $customValidator = new MediaFileValidator();
        $this->subject->registerParser([$customParser]);
        $this->subject->registerValidators([$customValidator]);

        $markdownProject = $this->subject->create();

        $this->assertInstanceOf(MarkdownProject::class, $markdownProject);
        $this->assertSame('Documentation example', $markdownProject->documentationFiles['/index.md']->getTitle());
        $this->assertSame('No Headline', $markdownProject->documentationFiles['/dev/no-headline.md']->getTitle());
        $this->assertSame('/dev/no-headline.md', $markdownProject->documentationFiles['/dev/no-headline.md']->getRelativePath());
    }

    /**
     * @test
     *
     * @testdox MarkdownProjectFactory should create a MarkdownProject with a custom fallback base URL
     */
    public function testCreateMarkdownProjectWithCustomFallbackBaseUrl(): void
    {
        $customFallbackBaseUrl = 'https://custom.example.com/';
        $factory = new MarkdownProjectFactory($this->workspacePath, '/docs', '/index.md', $customFallbackBaseUrl);

        $markdownProject = $factory->create();

        $this->assertInstanceOf(MarkdownProject::class, $markdownProject);
    }
}

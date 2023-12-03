<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Validator;

use Iwm\MarkdownStructure\Error\LinkTargetNotFoundError;
use Iwm\MarkdownStructure\Tests\AbstractTestCase;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Validator\MarkdownLinksValidator;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownLinksValidatorTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        PathUtility::mkdir($this->workspacePath . '/docs-with-errors');
        PathUtility::mkdir($this->workspacePath . '/docs-with-errors/validator');
        PathUtility::mkdir($this->workspacePath . '/docs-with-errors/img');

        copy(__DIR__ . '/../../Fixtures/docs-with-errors/index.md', $this->workspacePath . '/docs-with-errors/index.md');

        copy(__DIR__ . '/../../Fixtures/docs-with-errors/image-not-in-img.png', $this->workspacePath . '/docs-with-errors/image-not-in-img.png');
        copy(__DIR__ . '/../../Fixtures/docs-with-errors/img/non-image.txt', $this->workspacePath . '/docs-with-errors/img/non-image.txt');

        copy(__DIR__ . '/../../Fixtures/docs-with-errors/validator/cause-error.md', $this->workspacePath . '/docs-with-errors/validator/cause-error.md');
    }

    /**
     * @test
     *
     * @testdox MarkdownProjectValidator logs error if linked markdown file not exists in rootline
     */
    public function testValidation()
    {
        $markdown = '[An error](cause-error.md)';
        $parser = new GithubFlavoredMarkdownConverter([]);
        $parser->getEnvironment()->addExtension(new HeadingPermalinkExtension());
        $html = $parser->convert($markdown);

        $file = new MarkdownFile(
            $this->workspacePath,
            $this->workspacePath . '/docs-with-errors/index.md',
            $markdown,
            $html,
        );

        (new MarkdownLinksValidator())->validate($file, [], []);

        $expectedError = new LinkTargetNotFoundError(
            $this->workspacePath . '/docs-with-errors/index.md',
            $this->workspacePath . '/docs-with-errors/cause-error.md',
            'An error'
        );

        $this->assertEquals(
            [
                $expectedError,
            ],
            $file->errors
        );
    }

    /**
     * @test
     *
     * @testdox MarkdownProjectValidator can skip non md files
     */
    public function testSkipIfFileCannotBeValidated()
    {
        $markdownFile = new MarkdownFile(
            $this->workspacePath,
            $this->workspacePath . '/docs-with-errors/index.md',
            '',
        );

        $this->assertTrue((new MarkdownLinksValidator())->fileCanBeValidated($markdownFile));

        $mediaFile = new MediaFile(
            $this->workspacePath . '/docs-with-errors/img/non-image.txt',
            $this->workspacePath . '/docs-with-errors/img/non-image.txt',
        );

        $this->assertFalse((new MarkdownLinksValidator())->fileCanBeValidated($mediaFile));
    }
}

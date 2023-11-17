<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Validator;

use Iwm\MarkdownStructure\Error\LinkTargetNotFoundError;
use Iwm\MarkdownStructure\Tests\Functional\AbstractTestCase;
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

        mkdir($this->getBasePath() . $this->workspacePath . '/docs-with-errors', 0777, true);
        mkdir($this->getBasePath() . $this->workspacePath . '/docs-with-errors/validator', 0777, true);
        mkdir($this->getBasePath() . $this->workspacePath . '/docs-with-errors/img', 0777, true);

        copy(__DIR__ . '/../../Fixtures/docs-with-errors/index.md', $this->getBasePath() . $this->workspacePath . '/docs-with-errors/index.md');

        copy(__DIR__ . '/../../Fixtures/docs-with-errors/image-not-in-img.png', $this->getBasePath() . $this->workspacePath . '/docs-with-errors/image-not-in-img.png');
        copy(__DIR__ . '/../../Fixtures/docs-with-errors/img/non-image.txt', $this->getBasePath() . $this->workspacePath . '/docs-with-errors/img/non-image.txt');

        copy(__DIR__ . '/../../Fixtures/docs-with-errors/validator/cause-error.md', $this->getBasePath() . $this->workspacePath . '/docs-with-errors/validator/cause-error.md');
    }

    /**
     * @test
     * @testdox MarkdownProjectValidator logs error if linked markdown file not exists in rootline
     */
    public function testValidation()
    {
        $markdown = '[An error](cause-error.md)';
        $parser = new GithubFlavoredMarkdownConverter([]);
        $parser->getEnvironment()->addExtension(new HeadingPermalinkExtension());
        $html = $parser->convert($markdown);

        $file = new MarkdownFile(
            $this->getBasePath() . $this->workspacePath,
            $this->getBasePath() . $this->workspacePath . '/docs-with-errors/index.md',
            $markdown,
            $html,
        );

        (new MarkdownLinksValidator())->validate($file, [], []);

        $expectedError = new LinkTargetNotFoundError(
            $this->getBasePath() . $this->workspacePath . '/docs-with-errors/index.md',
            $this->getBasePath() . $this->workspacePath . '/docs-with-errors/cause-error.md',
            'An error'
        );

        $this->assertEquals(
            [
                $expectedError
            ],
            $file->errors
        );
    }


    /**
     * @test
     * @testdox MarkdownProjectValidator can skip non md files
     */
    public function testSkipIfFileCannotBeValidated()
    {
        $markdownFile = new MarkdownFile(
            $this->getBasePath() . $this->workspacePath,
            $this->getBasePath() . $this->workspacePath . '/docs-with-errors/index.md',
            '',
        );

        $this->assertTrue((new MarkdownLinksValidator())->fileCanBeValidated($markdownFile));

        $mediaFile = new MediaFile(
            $this->getBasePath() . $this->workspacePath . '/docs-with-errors/img/non-image.txt',
            $this->getBasePath() . $this->workspacePath . '/docs-with-errors/img/non-image.txt',
        );

        $this->assertFalse((new MarkdownLinksValidator())->fileCanBeValidated($mediaFile));
    }

}

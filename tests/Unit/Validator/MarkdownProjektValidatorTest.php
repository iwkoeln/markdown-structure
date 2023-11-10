<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Validator;

use Iwm\MarkdownStructure\Error\LinkTargetNotFoundError;
use Iwm\MarkdownStructure\Tests\Functional\AbstractTestCase;
use Iwm\MarkdownStructure\Validator\MarkdownLinksValidator;
use League\CommonMark\Extension\HeadingPermalink\HeadingPermalinkExtension;
use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownProjektValidatorTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->workspacePath . '/docs-with-errors', 0777, true);
        mkdir($this->workspacePath . '/docs-with-errors/validator', 0777, true);
        mkdir($this->workspacePath . '/docs-with-errors/img', 0777, true);

        copy(__DIR__ . '/../../Fixtures/docs-with-errors/index.md', $this->workspacePath . '/docs-with-errors/index.md');

        copy(__DIR__ . '/../../Fixtures/docs-with-errors/image-not-in-img.png', $this->workspacePath . '/docs-with-errors/image-not-in-img.png');
        copy(__DIR__ . '/../../Fixtures/docs-with-errors/img/non-image.txt', $this->workspacePath . '/docs-with-errors/img/non-image.txt');

        copy(__DIR__ . '/../../Fixtures/docs-with-errors/validator/cause-error.md', $this->workspacePath . '/docs-with-errors/validator/cause-error.md');
    }

    /**
     * @test
     * @testdox MarkdownProjectValidator logs error if linked markdown file not exists in rootline
     */
    public function testValidation()
    {
        $this->assertEquals(
            [],
            (new MarkdownLinksValidator())->validate(null, $this->workspacePath . '/docs-with-errors/img/non-image.txt', [], [])
        );

        $markdown = '[An error](cause-error.md)';
        $parser = new GithubFlavoredMarkdownConverter([]);
        $parser->getEnvironment()->addExtension(new HeadingPermalinkExtension());
        $html = $parser->convert($markdown);

        $expectedError = new LinkTargetNotFoundError(
            $this->workspacePath . '/docs-with-errors/index.md',
            $this->workspacePath . '/docs-with-errors/cause-error.md',
            'An error'
        );

        $this->assertEquals(
            [
                $expectedError
            ],
            (new MarkdownLinksValidator())->validate(
                $html,
                $this->workspacePath . '/docs-with-errors/index.md',
                [],
                []
            )
        );
    }

    /**
     * @test
     * @testdox MarkdownProjectValidator can skip non md files
     */
    public function testSkipIfFileCannotBeValidated()
    {
        $this->assertTrue((new MarkdownLinksValidator())->fileCanBeValidated($this->workspacePath . '/docs-with-errors/index.md'));
        $this->assertFalse((new MarkdownLinksValidator())->fileCanBeValidated($this->workspacePath . '/docs-with-errors/img/non-image.txt'));
    }
}

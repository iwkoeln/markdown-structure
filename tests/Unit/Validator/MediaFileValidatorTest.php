<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Validator;

use Iwm\MarkdownStructure\Tests\Functional\AbstractTestCase;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;
use Iwm\MarkdownStructure\Error\ImageDoesNotExistError;
use Iwm\MarkdownStructure\Error\ImageTooLargeError;
use Iwm\MarkdownStructure\Validator\MediaFileValidator;
use PHPUnit\Framework\TestCase;

class MediaFileValidatorTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->getBasePath() . $this->workspacePath . '/docs-with-errors', 0777, true);
        mkdir($this->getBasePath() . $this->workspacePath . '/docs-with-errors/validator', 0777, true);
        mkdir($this->getBasePath() . $this->workspacePath . '/docs-with-errors/img', 0777, true);

        copy(__DIR__ . '/../../Fixtures/docs/img/image.jpg', $this->getBasePath() . $this->workspacePath . '/docs-with-errors/img/image.jpg');

        copy(__DIR__ . '/../../Fixtures/docs-with-errors/img/large_image.png', $this->getBasePath() . $this->workspacePath . '/docs-with-errors/img/large_image.png');
        copy(__DIR__ . '/../../Fixtures/docs-with-errors/img/non-image.txt', $this->getBasePath() . $this->workspacePath . '/docs-with-errors/img/non-image.txt');
    }

    public function testFileCanBeValidatedForMediaFile()
    {
        $validator = new MediaFileValidator();
        $mediaFile = new MediaFile('/path/to/media.jpg');

        $this->assertTrue($validator->fileCanBeValidated($mediaFile));
    }

    public function testFileCannotBeValidatedForMarkdownFile()
    {
        $validator = new MediaFileValidator();
        $markdownFile = new MarkdownFile('/path/to/markdown.md', '', '', '');

        $this->assertFalse($validator->fileCanBeValidated($markdownFile));
    }

    public function testCheckFileSizeWithinLimit()
    {
        $validator = new MediaFileValidator();
        $path = $this->getBasePath() . $this->workspacePath . '/docs-with-errors/img/image.jpg';

        $errors = $validator->checkFileSize($path);

        $this->assertEmpty($errors);
    }

    public function testCheckFileSizeTooLarge()
    {
        $validator = new MediaFileValidator();
        $path = $this->getBasePath() . $this->workspacePath . '/docs-with-errors/img/large_image.png';

        $errors = $validator->checkFileSize($path);

        $this->assertCount(1, $errors);
        $this->assertInstanceOf(ImageTooLargeError::class, $errors[0]);
        $this->assertEquals(
            'Error: Image file size exceeds 1 MB: in ' . $this->getBasePath() . $this->workspacePath . '/docs-with-errors/img/large_image.png' . '. File size: 20419686 bytes.',
            $errors[0]->getErrorMessage()
        );
    }

    public function testCheckFileExistenceExists()
    {
        $validator = new MediaFileValidator();
        $path = __DIR__ . '/../../Fixtures/docs/img/image.jpg';

        $errors = $validator->checkFileExistence($path);

        $this->assertEmpty($errors);
    }

    public function testCheckFileExistenceNotExists()
    {
        $validator = new MediaFileValidator();
        $path = $this->getBasePath() . $this->workspacePath . '/Fixtures/docs/img/non_existing_image.jpg';

        $errors = $validator->checkFileExistence($path);

        $this->assertCount(1, $errors);
        $this->assertInstanceOf(ImageDoesNotExistError::class, $errors[0]);
        $this->assertEquals(
            'Error: Image file does not exist: in ' . $this->getBasePath() . $this->workspacePath . '/Fixtures/docs/img/non_existing_image.jpg' . '.',
            $errors[0]->getErrorMessage()
        );
    }
}

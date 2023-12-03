<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Validator;

use Iwm\MarkdownStructure\Error\ImageDoesNotExistError;
use Iwm\MarkdownStructure\Tests\AbstractTestCase;
use Iwm\MarkdownStructure\Validator\MarkdownImageValidator;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MediaFile;

class MarkdownImageValidatorTest extends AbstractTestCase
{
    public function testFileCanBeValidatedForMarkdownFile()
    {
        $validator = new MarkdownImageValidator();
        $markdownFile = new MarkdownFile('', '', '', '', '');

        $this->assertTrue($validator->fileCanBeValidated($markdownFile));
    }

    public function testFileCannotBeValidatedForMediaFile()
    {
        $validator = new MarkdownImageValidator();
        $mediaFile = new MediaFile('', '');

        $this->assertFalse($validator->fileCanBeValidated($mediaFile));
    }

    public function testValidateWithMissingImages()
    {
        $validator = new MarkdownImageValidator();
        $markdownFile = new MarkdownFile('/path/to/markdown.md', '/path/to/markdown.md', '', '');
        $markdownFile->errors = [];

        $mediaFiles = [
            '/path/to/image.jpg' => new MediaFile('/path/to/image.jpg', '/path/to/image.jpg'),
            '/path/to/image.png' => new MediaFile('/path/to/image.png', '/path/to/image.png'),
        ];

        // Create a Crawler with an img tag pointing to a missing image
        $markdownFile->html = '<img src="/path/to/missing-image.jpg" alt="Missing Image">';

        $validator->validate($markdownFile, [], $mediaFiles);

        $this->assertCount(1, $markdownFile->errors);
        $error = $markdownFile->errors[0];

        $this->assertInstanceOf(ImageDoesNotExistError::class, $error);
        $this->assertEquals('Error: Image file does not exist: in /path/to/markdown.md.', $error->getErrorMessage());
    }

    public function testValidateWithExistingImages()
    {
        $validator = new MarkdownImageValidator();
        $markdownFile = new MarkdownFile('/path/to/markdown.md', '/path/to/markdown.md', '');
        $markdownFile->errors = [];

        $mediaFiles = [
            '/path/to/image.jpg' => new MediaFile('/path/to/image.jpg', '/path/to/image.jpg'),
            '/path/to/image.png' => new MediaFile('/path/to/image.jpg', '/path/to/image.jpg'),
        ];

        $markdownFile->html = '<img src="image.jpg" alt="Image 1"><img src="image.png" alt="Image 2">';

        $validator->validate($markdownFile, [], $mediaFiles);

        $this->assertCount(0, $markdownFile->errors);
    }
}

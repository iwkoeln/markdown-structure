<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Error;

use Iwm\MarkdownStructure\Error\ImageDoesNotExistError;
use PHPUnit\Framework\TestCase;

class ImageDoesNotExistErrorTest extends TestCase
{
    public function testConstructorWithMissingImageFile()
    {
        $errorSource = '/path/to/source.md';
        $error = new ImageDoesNotExistError($errorSource);

        $this->assertEquals('Error: Image file does not exist: in /path/to/source.md.', $error->getErrorMessage());
    }

    public function testConstructorWithCustomErrorMessage()
    {
        $errorSource = '/path/to/source.md';
        $errorMessage = 'Custom error message';
        $error = new ImageDoesNotExistError($errorSource, '', $errorMessage);

        $this->assertEquals('Error: Custom error message in /path/to/source.md.', $error->getErrorMessage());
    }

    public function testConstructorWithMissingImageFileAndCustomErrorMessage()
    {
        $errorSource = '/path/to/source.md';
        $missingImageFile = '/path/to/missing/image.png';
        $errorMessage = 'Custom error message';
        $error = new ImageDoesNotExistError($errorSource, $missingImageFile, $errorMessage);

        $this->assertEquals('Error: Custom error message in /path/to/source.md.', $error->getErrorMessage());
    }

    public function testGetErrorMessage()
    {
        $errorSource = '/path/to/source.md';
        $errorMessage = 'Custom error message';
        $error = new ImageDoesNotExistError($errorSource, '', $errorMessage);

        $expectedErrorMessage = sprintf('Error: %s in %s.', $errorMessage, $errorSource);

        $this->assertEquals($expectedErrorMessage, $error->getErrorMessage());
    }
}

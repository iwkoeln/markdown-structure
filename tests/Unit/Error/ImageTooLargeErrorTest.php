<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Error;

use Iwm\MarkdownStructure\Error\ImageTooLargeError;
use PHPUnit\Framework\TestCase;

class ImageTooLargeErrorTest extends TestCase
{
    public function testConstructorWithDefaultErrorMessage()
    {
        $errorSource = '/path/to/source.md';
        $fileSize = 1024;
        $error = new ImageTooLargeError($errorSource, $fileSize);

        $this->assertEquals('Error: Image file size exceeds 1 MB: in /path/to/source.md. File size: 1024 bytes.', $error->getErrorMessage());
    }

    public function testConstructorWithCustomErrorMessage()
    {
        $errorSource = '/path/to/source.md';
        $fileSize = 2048;
        $errorMessage = 'Custom error message';
        $error = new ImageTooLargeError($errorSource, $fileSize, $errorMessage);

        $this->assertEquals('Error: Custom error message in /path/to/source.md. File size: 2048 bytes.', $error->getErrorMessage());
    }

    public function testGetErrorMessage()
    {
        $errorSource = '/path/to/source.md';
        $fileSize = 4096;
        $errorMessage = 'Custom error message';
        $error = new ImageTooLargeError($errorSource, $fileSize, $errorMessage);

        $expectedErrorMessage = sprintf(
            'Error: %s in %s. File size: %s bytes.',
            $errorMessage,
            $errorSource,
            $fileSize
        );

        $this->assertEquals($expectedErrorMessage, $error->getErrorMessage());
    }
}

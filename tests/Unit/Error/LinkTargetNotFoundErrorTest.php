<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Error;

use Iwm\MarkdownStructure\Error\LinkTargetNotFoundError;
use PHPUnit\Framework\TestCase;

class LinkTargetNotFoundErrorTest extends TestCase
{
    public function testConstructorWithDefaultErrorMessage()
    {
        $errorSource = '/path/to/source.md';
        $pathOfUnfoundFile = '/path/to/unfound.md';
        $linkText = 'Link Text';
        $error = new LinkTargetNotFoundError($errorSource, $pathOfUnfoundFile, $linkText);

        $this->assertEquals('Error: Link target not found in /path/to/source.md. Could not find link target: "/path/to/unfound.md" with label: "Link Text"', $error->getErrorMessage());
    }

    public function testConstructorWithCustomErrorMessage()
    {
        $errorSource = '/path/to/source.md';
        $pathOfUnfoundFile = '/path/to/unfound.md';
        $linkText = 'Link Text';
        $errorMessage = 'Custom error message';
        $error = new LinkTargetNotFoundError($errorSource, $pathOfUnfoundFile, $linkText, $errorMessage);

        $this->assertEquals('Error: Custom error message in /path/to/source.md. Could not find link target: "/path/to/unfound.md" with label: "Link Text"', $error->getErrorMessage());
    }

    public function testGetErrorMessage()
    {
        $errorSource = '/path/to/source.md';
        $pathOfUnfoundFile = '/path/to/unfound.md';
        $linkText = 'Link Text';
        $errorMessage = 'Custom error message';
        $error = new LinkTargetNotFoundError($errorSource, $pathOfUnfoundFile, $linkText, $errorMessage);

        $expectedErrorMessage = sprintf(
            'Error: %s in %s. Could not find link target: "%s" with label: "%s"',
            $errorMessage,
            $errorSource,
            $pathOfUnfoundFile,
            $linkText
        );

        $this->assertEquals($expectedErrorMessage, $error->getErrorMessage());
    }
}

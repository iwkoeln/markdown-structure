<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Value;

use Iwm\MarkdownStructure\Tests\AbstractTestCase;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Value\MediaFile;

class MediaFileTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        PathUtility::mkdir($this->workspacePath . '/docs');
        PathUtility::mkdir($this->workspacePath . '/docs/img');

        copy(__DIR__ . '/../../Fixtures/docs/img/image.jpg', $this->workspacePath . '/docs/img/image.jpg');
        copy(__DIR__ . '/../../Fixtures/docs/img/image.png', $this->workspacePath . '/docs/img/image.png');
    }

    /**
     * @test
     *
     * @testdox MediaFile is to string convertable
     */
    public function testToStringConversion()
    {
        $path = $this->workspacePath . '/docs/img/image.jpg';
        $image = $this->workspacePath . '/docs/img/image.jpg';

        $mediaFile = new MediaFile($path, $image);
        $this->assertEquals($path, (string)$mediaFile);
    }

    /**
     * @test
     *
     * @testdox MediaFile constructors
     */
    public function testMediaFileConstructors()
    {
        $path = $this->workspacePath . '/docs/img/image.jpg';
        $image = $this->workspacePath . '/docs/img/image.jpg';

        $mediaFile = new MediaFile($path, $image);
        $this->assertEquals($path, (string)$mediaFile);

        $mediaFile = new MediaFile($path);
        $this->assertEquals($path, (string)$mediaFile);
    }

    /**
     * @test
     *
     * @testdox MediaFile has errors attribute
     */
    public function testMediaFileErrorsAttribute()
    {
        $path = $this->workspacePath . '/docs/img/image.jpg';
        $image = $this->workspacePath . '/docs/img/image.jpg';

        $mediaFile = new MediaFile($path, $image, ['Error 1', 'Error 2']);

        $this->assertIsArray($mediaFile->errors);
        $this->assertCount(2, $mediaFile->errors);
        $this->assertEquals(['Error 1', 'Error 2'], $mediaFile->errors);
    }

    /**
     * @test
     *
     * @testdox MediaFile errors attribute is empty by default
     */
    public function testMediaFileDefaultErrorsAttribute()
    {
        $path = $this->workspacePath . '/docs/img/image.jpg';
        $image = $this->workspacePath . '/docs/img/image.jpg';

        $mediaFile = new MediaFile($path, $image);

        $this->assertIsArray($mediaFile->errors);
        $this->assertEmpty($mediaFile->errors);
    }
}

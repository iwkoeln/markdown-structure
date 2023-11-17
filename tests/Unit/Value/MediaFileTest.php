<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Value;

use Iwm\MarkdownStructure\Tests\Functional\AbstractTestCase;
use Iwm\MarkdownStructure\Value\MediaFile;
use PHPUnit\Framework\TestCase;

class MediaFileTest extends AbstractTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        mkdir($this->getBasePath() . $this->workspacePath . '/docs', 0777, true);
        mkdir($this->getBasePath() . $this->workspacePath . '/docs/img', 0777, true);

        copy(__DIR__ . '/../../Fixtures/docs/img/image.jpg', $this->getBasePath() . $this->workspacePath . '/docs/img/image.jpg');
        copy(__DIR__ . '/../../Fixtures/docs/img/image.png', $this->getBasePath() . $this->workspacePath . '/docs/img/image.png');
    }

    /**
     * @test
     * @testdox MediaFile is to string convertable
     */
    public function testToStringConversion()
    {
        $path = $this->getBasePath() . $this->workspacePath . '/docs/img/image.jpg';
        $image = $this->getBasePath() . $this->workspacePath . '/docs/img/image.jpg';

        $mediaFile = new MediaFile($path, $image);
        $this->assertEquals($path, (string) $mediaFile);
    }

    /**
     * @test
     * @testdox MediaFile constructors
     */
    public function testMediaFileConstructors()
    {
        $path = $this->getBasePath() . $this->workspacePath . '/docs/img/image.jpg';
        $image = $this->getBasePath() . $this->workspacePath . '/docs/img/image.jpg';

        $mediaFile = new MediaFile($path, $image);
        $this->assertEquals($path, (string) $mediaFile);

        $mediaFile = new MediaFile($path);
        $this->assertEquals($path, (string) $mediaFile);
    }

    /**
     * @test
     * @testdox MediaFile has errors attribute
     */
    public function testMediaFileErrorsAttribute()
    {
        $path = $this->getBasePath() . $this->workspacePath . '/docs/img/image.jpg';
        $image = $this->getBasePath() . $this->workspacePath . '/docs/img/image.jpg';

        $mediaFile = new MediaFile($path, $image, ['Error 1', 'Error 2']);

        $this->assertIsArray($mediaFile->errors);
        $this->assertCount(2, $mediaFile->errors);
        $this->assertEquals(['Error 1', 'Error 2'], $mediaFile->errors);
    }

    /**
     * @test
     * @testdox MediaFile errors attribute is empty by default
     */
    public function testMediaFileDefaultErrorsAttribute()
    {
        $path = $this->getBasePath() . $this->workspacePath . '/docs/img/image.jpg';
        $image = $this->getBasePath() . $this->workspacePath . '/docs/img/image.jpg';

        $mediaFile = new MediaFile($path, $image);

        $this->assertIsArray($mediaFile->errors);
        $this->assertEmpty($mediaFile->errors);
    }
}

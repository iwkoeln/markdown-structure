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

        mkdir($this->workspacePath . '/docs', 0777, true);
        mkdir($this->workspacePath . '/docs/img', 0777, true);

        copy(__DIR__ . '/../../Fixtures/docs/img/image.jpg', $this->workspacePath . '/docs/img/image.jpg');
        copy(__DIR__ . '/../../Fixtures/docs/img/image.png', $this->workspacePath . '/docs/img/image.png');
    }

    /**
     * @test
     * @testdox MediaFile is to string convertable
     */
    public function testToStringConversion()
    {
        $path = $this->workspacePath . '/docs/img/image.jpg';
        $image = $this->workspacePath . '/docs/img/image.jpg';

        $mediaFile = new MediaFile($path, $image);
        $this->assertEquals($path, (string) $mediaFile);
    }
    /**
     * @test
     * @testdox MediaFile constructors
     */
    public function testMediaFileConstructors()
    {
        $path = $this->workspacePath . '/docs/img/image.jpg';
        $image = $this->workspacePath . '/docs/img/image.jpg';

        $mediaFile = new MediaFile($path, $image);
        $this->assertEquals($path, (string) $mediaFile);

        $mediaFile = new MediaFile($path);
        $this->assertEquals($path, (string) $mediaFile);
    }
}

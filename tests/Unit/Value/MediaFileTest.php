<?php

namespace Iwm\MarkdownStructure\Unit\Value;

use Iwm\MarkdownStructure\Value\MediaFile;
use PHPUnit\Framework\TestCase;

class MediaFileTest extends TestCase
{
    /**
     * @test
     * @testdox MediaFile is to string convertable
     */
    public function testToStringConversion()
    {
        $path = '/var/www/html/tests/Data/img/image.png';
        $image = '/var/www/html/tests/Data/img/image.png';

        $mediaFile = new MediaFile($path, $image);
        $this->assertEquals($path, (string) $mediaFile);
    }
    /**
     * @test
     * @testdox MediaFile constructors
     */
    public function testMediaFileConstructors()
    {
        $path = '/var/www/html/tests/Data/img/image.png';
        $image = '/var/www/html/tests/Data/img/image.png';

        $mediaFile = new MediaFile($path, $image);
        $this->assertEquals($path, (string) $mediaFile);

        $mediaFile = new MediaFile($path);
        $this->assertEquals($path, (string) $mediaFile);
    }
}
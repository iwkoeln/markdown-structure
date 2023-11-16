<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Collection;

use Iwm\MarkdownStructure\Collection\FinisherCollection;
use Iwm\MarkdownStructure\Finisher\FallbackUrlForProjectFileLinksFinisher;
use PHPUnit\Framework\TestCase;

class AbstractCollectionTest extends TestCase
{
    public function testAddItem()
    {
        $collection = new FinisherCollection();
        $item = new FallbackUrlForProjectFileLinksFinisher();

        $collection->add($item);

        $this->assertEquals($item, $collection->getItem(get_class($item)));
    }

    public function testRemoveItem()
    {
        $collection = new FinisherCollection();
        $item = new FallbackUrlForProjectFileLinksFinisher();

        $collection->add($item);
        $collection->remove($item);

        $this->assertTrue($collection->isEmpty());
    }

    public function testIsEmpty()
    {
        $collection = new FinisherCollection();
        $item = new FallbackUrlForProjectFileLinksFinisher();

        $this->assertTrue($collection->isEmpty());

        $collection->add($item);

        $this->assertFalse($collection->isEmpty());
    }
}


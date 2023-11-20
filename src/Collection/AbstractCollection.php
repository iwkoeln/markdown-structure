<?php

namespace Iwm\MarkdownStructure\Collection;

abstract class AbstractCollection
{
    private $items = [];

    public function add($item): void
    {
        $this->items[get_class($item)] = $item;
    }

    public function remove($item): void
    {
        if (($key = array_search($item, $this->items)) !== false) {
            unset($this->items[$key]);
        }
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function getItem(string $className): object
    {
        return $this->items[$className];
    }

    public function isEmpty(): bool
    {
        return empty($this->items);
    }
}

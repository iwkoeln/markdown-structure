<?php

namespace Iwm\MarkdownStructure\Collection;

abstract class AbstractCollection
{
    /**
     * @var array<object>
     */
    private array $items = [];

    public function add(object $item): void
    {
        $this->items[get_class($item)] = $item;
    }

    public function remove(object $item): void
    {
        if (($key = array_search($item, $this->items)) !== false) {
            unset($this->items[$key]);
        }
    }

    /**
     * @return array<object>
     */
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

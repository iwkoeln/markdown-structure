<?php

namespace Iwm\MarkdownStructure\Utility;

use Symfony\Component\Finder\Finder;

class FilesFinder
{
    /**
     * Find files by path.
     */
    public static function findFilesByPath(string $absolutePath): Finder
    {
        if (!is_dir($absolutePath)) {
            throw new \InvalidArgumentException('Invalid directory path: ' . $absolutePath);
        }

        $finder = new Finder();

        return $finder->files()->in($absolutePath);
    }
}

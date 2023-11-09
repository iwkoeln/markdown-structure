<?php

namespace Iwm\MarkdownStructure\Utility;

use Symfony\Component\Finder\Finder;

class FilesFinder
{
    /**
     * Find files by path.
     */
    public static function findFilesByPath(string $path): array
    {
        if (!is_dir($path)) {
            throw new \InvalidArgumentException('Invalid directory path: ' . $path);
        }

        $finder = new Finder();
        $finder->files()->in($path);

        $files = [];
        foreach ($finder as $file) {
            $files[] = $file->getRealPath();
        }

        sort($files);
        return $files;
    }
}

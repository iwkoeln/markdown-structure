<?php

namespace Iwm\MarkdownStructure\Utility;

class FilesFinder
{
    /**
     * Find files by path.
     *
     * @param string $path The directory path to search.
     *
     * @return array An array of file paths found.
     */
    public static function findFilesByPath(string $path): array
    {
        // TODO: Implement with Symfony Finder
        $files = [];

        // Validate the directory path
        if (!is_dir($path)) {
            throw new \InvalidArgumentException('Invalid directory path: ' . $path);
        }

        // Open the directory handle
        $dirHandle = opendir($path);

        if ($dirHandle === false) {
            throw new \RuntimeException('Failed to open directory: ' . $path);
        }

        // Iterate through the directory and find files
        while (($file = readdir($dirHandle)) !== false) {
            // Exclude "." and ".." entries
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $path . DIRECTORY_SEPARATOR . $file;

            // Check if the entry is a file
            if (is_file($filePath)) {
                $files[] = $filePath;
            }

            // Recursively search subdirectories
            if (is_dir($filePath)) {
                $subDirectoryFiles = self::findFilesByPath($filePath);
                $files = array_merge($files, $subDirectoryFiles);
            }
        }

        // Close the directory handle
        closedir($dirHandle);

        sort($files);
        return $files;
    }
}

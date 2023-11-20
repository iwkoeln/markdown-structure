<?php

namespace Iwm\MarkdownStructure\Utility;

class FileTreeBuilder
{
    /**
     * Converts flat list of file paths to nested array.
     *
     * @param array|string[] $filePaths
     *
     * @return array<string, mixed> recursive file tree, with absolute file path as values
     */
    public static function buildFileTree(array $filePaths): array
    {
        $tree = [];

        foreach (array_values($filePaths) as $path) {
            $parts = explode('/', $path);
            if (is_array($tree)) {
                $tree = self::addNodeToTree($tree, $parts, $path);
            }
        }

        if (is_string($tree)) {
            return [$tree => $tree];
        }

        return $tree;
    }

    /**
     * @param array<string, mixed>  $node
     * @param array|string[]        $path
     * @param array|string[]|string $value
     *
     * @return array<string, mixed>
     */
    private static function addNodeToTree(array $node, array $path, array|string $value): array|string
    {
        if (0 === count($path)) {
            return $value;
        }

        $key = array_shift($path);
        $node[$key] = self::addNodeToTree($node[$key] ?? [], $path, $value);

        return $node;
    }

    /**
     * @param array<mixed|string> $nestedFiles
     */
    public static function setValueFromNestedReferencesArray(array &$nestedFiles, string $newFilePath, mixed $newFileObject): void
    {
        $keys = explode('/', $newFilePath);
        $lastKey = array_pop($keys);
        $current = &$nestedFiles;
        foreach ($keys as $key) {
            $current = &$current[$key];
        }
        $current[$lastKey] = $newFileObject;
    }
}

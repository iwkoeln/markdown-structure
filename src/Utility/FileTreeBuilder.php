<?php

namespace Iwm\MarkdownStructure\Utility;

use Iwm\MarkdownStructure\Value\MarkdownFile;

class FileTreeBuilder
{
    /**
     * Converts flat list of file paths to nested array.
     *
     * @param array<string, MarkdownFile> $filePaths
     *
     * @return array<string, mixed> recursive file tree
     */
    public static function buildFileTree(array $filePaths): array
    {
        $tree = [];

        foreach ($filePaths as $path => $markdownFile) {
            $parts = explode('/', ltrim($path, '/'));
            if (is_array($tree)) {
                $tree = self::addNodeToTree($tree, $parts, $markdownFile);
            }
        }

        if (!is_array($tree)) {
            throw new \UnexpectedValueException('Error with input, return value should be always a nested array.');
        }

        return $tree;
    }

    /**
     * @param array<string, mixed> $node
     * @param array|string[]       $path
     *
     * @return array<string, mixed>|MarkdownFile
     */
    private static function addNodeToTree(array $node, array $path, MarkdownFile $value): array|MarkdownFile
    {
        if (0 === count($path)) {
            return $value;
        }

        $key = array_shift($path);
        $node[$key] = self::addNodeToTree($node[$key] ?? [], $path, $value);

        return $node;
    }
}

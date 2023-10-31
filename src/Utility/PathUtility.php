<?php

declare(strict_types = 1);

namespace Iwm\MarkdownStructure\Utility;

use SplFileInfo;
use Symfony\Component\Process\Process;

class PathUtility
{
    public static function mkdir(string $directory, int $permissions = 0777): bool
    {
        if (file_exists($directory)) {
            return true;
        }
        if (!mkdir($directory, $permissions, true) && !is_dir($directory)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $directory));
        }

        return true;
    }

    public static function isMediaFile(string $path): bool
    {
        return (
                in_array(substr($path, -3), ['jpg', 'png', 'gif', 'svg', 'pdf', 'doc', 'xls', 'ppt']) ||
                in_array(substr($path, -4), ['jpeg', 'docx', 'xlsx', 'pptx'])
            )
            &&
            (
                'img' === strtolower(basename(dirname($path))) ||
                'image' === strtolower(basename(dirname($path))
            )
        );
    }

    public static function isMarkdownFile(string $path): bool
    {
        return str_ends_with($path, '.md');
    }

    public static function guessMimeTypeFromPath(string $path): string
    {
        if (in_array(substr($path, -3), ['jpg', 'png', 'gif', 'svg'])) {
            return 'image/' . substr($path, -3);
        }

        return 'application/octet-stream';
    }

    public static function isExternalUrl(string $pathOrUrl): bool
    {
        // Check if the link starts with http://, https://, www. or ends with .de or .com
        if (
            preg_match('/^https?:\/\//i', $pathOrUrl) ||
            preg_match('/^http?:\/\//i', $pathOrUrl) ||
            preg_match('/^www\./i', $pathOrUrl) ||
            preg_match('/^mailto:/i', $pathOrUrl) ||
            preg_match('/\.de$|\.com$/i', $pathOrUrl)
        ) {
            // External link
            return true;
        } else {
            // Local file path or invalid link format
            return false;
        }
    }

    public static function buildFileTree(array $filePaths): array
    {
        $tree = [];

        foreach ($filePaths as $path) {
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

    private static function addNodeToTree(array $node, array $path, array|string $value): array|string
    {
        if (0 === count($path)) {
            return $value;
        }

        $key = array_shift($path);
        $node[$key] = self::addNodeToTree($node[$key] ?? [], $path, $value);

        return $node;
    }

    public static function isPathInRoot(string $path, string $root): bool
    {
        $levelsToRoot = count(explode(DIRECTORY_SEPARATOR, $root)) - 1;
        $depthOfPath = 0;
        foreach (explode(DIRECTORY_SEPARATOR, $path) as $part) {
            if ('..' === $part) {
                ++$depthOfPath;
            }
        }
        return $depthOfPath <= $levelsToRoot;
    }

    public static function isPathBefore(string $path, string $root): bool
    {
        $pathParts = explode(DIRECTORY_SEPARATOR, $path);
        $rootParts = explode(DIRECTORY_SEPARATOR, $root);

        while (count($pathParts) > 0 && count($rootParts) > 0 && $pathParts[0] === $rootParts[0]) {
            array_shift($pathParts);
            array_shift($rootParts);
        }

        return count($rootParts) <= count($pathParts);
    }

    public static function resolveAbsolutPath(string $basePath, string $relativePath): string
    {
        $baseParts = explode(DIRECTORY_SEPARATOR, $basePath);
        $relativeParts = explode(DIRECTORY_SEPARATOR, $relativePath);

        array_pop($baseParts);

        foreach ($relativeParts as $part) {
            if ('..' === $part) {
                array_pop($baseParts);
            } elseif ('.' === $part) {
                continue;
            } else {
                $baseParts[] = $part;
            }
        }

        return implode(DIRECTORY_SEPARATOR, $baseParts);
    }

    public static function resolveRelativePath(string $basePath, string $absolutePath): string
    {
        $baseParts = explode(DIRECTORY_SEPARATOR, rtrim($basePath, DIRECTORY_SEPARATOR));
        $absoluteParts = explode(DIRECTORY_SEPARATOR, rtrim($absolutePath, DIRECTORY_SEPARATOR));

        // TODO Find out why this is needed
        //if (isset($baseParts[0]) && empty($baseParts[0])) {
        //    return $absolutePath;
        //}

        while (count($baseParts) > 0 && count($absoluteParts) > 0 && $baseParts[0] === $absoluteParts[0]) {
            array_shift($baseParts);
            array_shift($absoluteParts);
        }

        $relativeParts = [];
        for ($i = 1; $i < count($baseParts); ++$i) {
            $relativeParts[] = '..';
        }

        return implode(DIRECTORY_SEPARATOR, array_merge($relativeParts, $absoluteParts));
    }

    public static function rmdir(string $gitProjectPath): void
    {
        $process = new Process(['rm', '-rf', $gitProjectPath]);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new \RuntimeException('Unable to remove git repository in "' . $gitProjectPath . '"!');
        }
    }

    public static function sanitizeFileName(string $fileName): string
    {
        return preg_replace('/[^a-z0-9\-\_\.]/', '', strtolower($fileName)) ?? '';
    }

    public static function dirname(string $filePath, int $levels = 1): string
    {
        $dirname = dirname($filePath, $levels);
        if ('.' === $dirname) {
            return '';
        }

        return $dirname;
    }
}

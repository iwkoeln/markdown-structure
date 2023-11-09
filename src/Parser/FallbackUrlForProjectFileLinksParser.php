<?php

namespace Iwm\MarkdownStructure\Parser;

class FallbackUrlForProjectFileLinksParser implements ParserInterface
{
    public function parse(mixed $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): mixed
    {
        if (!$this->fileIsParsable(get_class($file)) || $documentationFiles === null || $projectFiles !== null) {
            return $file;
        }

        // replace links to project files with links that have the fallback base url as prefix
        // for the markdown content
        $file->markdown = preg_replace_callback(
            '/\]\(([^)]*)\)/',
            function ($matches) use ($documentationFiles, $file) {
                $link = $matches[1];
                if (isset($documentationFiles[$link])) {
                    $link = $file->fallbackUrl . $link;
                }
                return '](' . $link . ')';
            },
            $file->markdown
        );

        // for the html
        $file->html = preg_replace_callback(
            '/href="([^"]*)"/',
            function ($matches) use ($documentationFiles, $file) {
                $link = $matches[1];
                if (isset($documentationFiles[$link])) {
                    $link = $file->fallbackUrl . $link;
                }
                return 'href="' . $link . '"';
            },
            $file->html
        );

        return $file;
    }

    public function fileIsParsable(string $fileType): bool
    {
        return $fileType === 'Iwm\MarkdownStructure\Value\MarkdownFile';
    }
}

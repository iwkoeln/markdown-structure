<?php

namespace Iwm\MarkdownStructure\Parser;

use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownLink;

class FallbackUrlForProjectFileLinksParser implements ParserInterface
{
    /*
     * @param MarkdownFile $file
     */
    public function parse(mixed $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): mixed
    {
        if (!$this->fileIsParsable(get_class($file)) || $documentationFiles === null || $projectFiles === null || $file->fallbackUrl === null) {
            return $file;
        }
        if ($file instanceof MarkdownFile) {
            // replace links to project files with links that have the fallback base url as prefix
            // for the markdown content
            $links = DomLinkExtractor::extractLinks($file->html, $file->path);

            foreach ($links as $link) {
                if ($link instanceof MarkdownLink && !in_array($link->absolutePath(), $documentationFiles)) {
                    $absolutePath = $link->absolutePath();
                    $relativePath = PathUtility::resolveRelativePath($file->basePath, $absolutePath);
                    $file->html = str_replace($link->target, $file->fallbackUrl . $relativePath, $file->html);
                }
            }
        }
        return $file;
    }

    public function fileIsParsable(string $fileType): bool
    {
        return $fileType === 'Iwm\MarkdownStructure\Value\MarkdownFile';
    }
}

<?php

namespace Iwm\MarkdownStructure\Finisher;

use Iwm\MarkdownStructure\Parser\ParserInterface;
use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownLink;
use Iwm\MarkdownStructure\Value\MediaFile;

class FallbackUrlForProjectFileLinksFinisher implements FinisherInterface
{
    public function fileCanBeFinished(MarkdownFile|MediaFile $file): bool
    {
        return $file instanceof MarkdownFile;
    }

    public function finish(MarkdownFile|MediaFile $file, ?array $documentationFiles, ?array $documentationMediaFiles, ?array $projectFiles): MarkdownFile|MediaFile
    {
        if (!$this->fileCanBeFinished($file)) {
            return $file;
        }
        if (empty($documentationFiles) || $file->fallbackUrl === null) {
            return $file;
        }

        // Replace links to project files with links that have the fallback base url as prefix for the markdown content
        $links = DomLinkExtractor::extractLinks($file->html, $file->path);

        foreach ($links as $link) {
            if ($link instanceof MarkdownLink && !in_array($link->absolutePath(), $documentationFiles)) {
                $absolutePath = $link->absolutePath();
                $relativePath = PathUtility::resolveRelativePath($file->basePath, $absolutePath);
                $file->html = str_replace($link->target, $file->fallbackUrl . $relativePath, $file->html);
            }
        }
        return $file;
    }
}
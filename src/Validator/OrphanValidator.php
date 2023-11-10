<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Utility\PathUtility;

class OrphanValidator implements ValidatorInterface
{
    private array $referencedFiles = [];

    public function fileCanBeValidated(string $path): bool
    {
        return PathUtility::isMarkdownFile($path);
    }

    public function validate(?string $parsedResult, string $path, array $markdownFiles, array $mediaFiles): array
    {
        if ($parsedResult === null || !$this->fileCanBeValidated($path)) {
            return [];
        }

        $markdownLinks = DomLinkExtractor::extractLinks($parsedResult, $path);

        foreach ($markdownLinks as $markdownLink) {
            $absolutePath = $markdownLink->absolutePath();
            $this->referencedFiles[] = $absolutePath;
        }

        // Remove duplicates to maintain a clean list of referenced files
        $this->referencedFiles = array_unique($this->referencedFiles);

        // Since this validator is for collecting references, not for error reporting, return an empty array
        return [];
    }

    public function getOrphanFiles(array $allFiles): array
    {
        // Compare the total list of files with the referenced files to find orphans
        $orphanFiles = array_diff($allFiles, $this->referencedFiles);

        // Return the list of orphan files
        // array_values to reset keys
        $orphans = array_values($orphanFiles);

        // Make the File Path the key for the orphans array
        return array_combine($orphans, $orphans);
    }
}

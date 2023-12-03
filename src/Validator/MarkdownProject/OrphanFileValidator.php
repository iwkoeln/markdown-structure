<?php

namespace Iwm\MarkdownStructure\Validator\MarkdownProject;

use Iwm\MarkdownStructure\Utility\DomExtractor;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;

class OrphanFileValidator implements MarkdownProjectValidatorInterface
{
    /**
     * @var array<string>
     */
    private array $referencedFiles = [];
    /**
     * @var array<string>
     */
    private array $referencedImages = [];

    public function validate(MarkdownProject $project): void
    {
        foreach ($project->documentationFiles as $markdownFile) {
            $this->validateFile($markdownFile->html, $markdownFile->path);
        }

        $project->orphans = $this->getOrphanFiles($project->documentationFiles, $project->documentationMediaFiles);
    }

    public function validateFile(?string $parsedResult, string $path): void
    {
        if (null === $parsedResult) {
            return;
        }
        $markdownLinks = DomExtractor::extractLinks($parsedResult, $path);
        $markdownImages = DomExtractor::extractImages($parsedResult, $path);

        foreach ($markdownLinks as $markdownLink) {
            $absolutePath = $markdownLink->absolutePath();
            $this->referencedFiles[] = $absolutePath;
        }

        foreach ($markdownImages as $markdownImage) {
            $absolutePath = $markdownImage->absolutePath();
            $this->referencedImages[] = $absolutePath;
        }

        // Remove duplicates to maintain a clean list of referenced files
        $this->referencedFiles = array_unique($this->referencedFiles);
        $this->referencedImages = array_unique($this->referencedImages);
    }

    /**
     * @param array<MarkdownFile> $documentationFiles
     * @param array<MediaFile>    $documentationMediaFiles
     *
     * @return array<string, string>
     */
    public function getOrphanFiles(array $documentationFiles, array $documentationMediaFiles): array
    {
        // Compare the total list of files with the referenced files to find orphans
        $orphanFiles = array_diff($documentationFiles, $this->referencedFiles);
        $orphanImages = array_diff($documentationMediaFiles, $this->referencedImages);

        // Merge the orphan files and orphan images
        $orphans = array_merge($orphanFiles, $orphanImages);

        // Return the list of orphan files
        // array_values to reset keys
        $orphans = array_values($orphans);

        // Make the File Path the key for the orphans array
        return array_combine($orphans, $orphans);
    }
}

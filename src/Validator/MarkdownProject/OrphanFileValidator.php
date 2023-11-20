<?php

namespace Iwm\MarkdownStructure\Validator\MarkdownProject;

use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;

class OrphanFileValidator implements MarkdownProjectValidatorInterface
{
    private array $referencedFiles = [];
    private array $referencedImages = [];
    public function validate(MarkdownProject $project): void
    {
        /** @var MarkdownFile $markdownFile */
        foreach ($project->documentationFiles as $markdownFile) {
            $this->validateFile($markdownFile->html, $markdownFile->path);
        }

        $project->orphans = $this->getOrphanFiles($project->documentationFiles, $project->documentationMediaFiles);
    }


    public function validateFile(?string $parsedResult, string $path): void
    {
        $markdownLinks = DomLinkExtractor::extractLinks($parsedResult, $path);
        $markdownImages = DomLinkExtractor::extractImages($parsedResult, $path);

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

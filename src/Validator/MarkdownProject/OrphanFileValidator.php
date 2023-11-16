<?php

namespace Iwm\MarkdownStructure\Validator\MarkdownProject;

use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Iwm\MarkdownStructure\Validator\ValidatorInterface;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;

class OrphanFileValidator implements MarkdownProjectValidatorInterface
{

    public function validate(MarkdownProject $project): void
    {
        /** @var MarkdownFile $markdownFile */
        foreach ($project->documentationFiles as $markdownFile) {
            $this->validateFile($markdownFile->html, $markdownFile->path);
        }

        $project->orphans = $this->getOrphanFiles($project->documentationFiles);
    }

    private array $referencedFiles = [];

    public function validateFile(?string $parsedResult, string $path): void
    {
        $markdownLinks = DomLinkExtractor::extractLinks($parsedResult, $path);

        foreach ($markdownLinks as $markdownLink) {
            $absolutePath = $markdownLink->absolutePath();
            $this->referencedFiles[] = $absolutePath;
        }

        // Remove duplicates to maintain a clean list of referenced files
        $this->referencedFiles = array_unique($this->referencedFiles);
    }

    public function getOrphanFiles(array $documentationFiles): array
    {
        // Compare the total list of files with the referenced files to find orphans
        $orphanFiles = array_diff($documentationFiles, $this->referencedFiles);

        // Return the list of orphan files
        // array_values to reset keys
        $orphans = array_values($orphanFiles);

        // Make the File Path the key for the orphans array
        return array_combine($orphans, $orphans);
    }
}

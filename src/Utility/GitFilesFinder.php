<?php

namespace Iwm\MarkdownStructure\Utility;

class GitFilesFinder
{
    /**
     * List all files tracked by a bare Git repository.
     */
    public static function listTrackedFiles(string $repositoryPath): array
    {
        if (!is_dir($repositoryPath)) {
            throw new \InvalidArgumentException('Invalid repository path: ' . $repositoryPath);
        }

        // The command to list all files in the repository
        $command = "git --git-dir=" . escapeshellarg($repositoryPath) . " ls-tree -r HEAD --name-only";

        // Execute the command and capture the output
        exec($command, $output, $returnVar);

        // Check for command execution errors
        if ($returnVar !== 0) {
            throw new \RuntimeException('Failed to list files in the repository: ' . $repositoryPath);
        }

        // Prefix each entry with the repository path
        return array_map(function ($filePath) use ($repositoryPath) {
            return $repositoryPath . DIRECTORY_SEPARATOR . $filePath;
        }, $output);
    }
}

<?php

namespace Iwm\MarkdownStructure\Validator;

use Iwm\MarkdownStructure\Error\ImageDoesNotExistError;
use Iwm\MarkdownStructure\Error\ImageTooLargeError;
use Iwm\MarkdownStructure\Utility\PathUtility;
use Symfony\Component\Finder\SplFileInfo;

class MediaFileValidator implements ValidatorInterface
{

    public function validate(?string $parsedResult, string $path, array $markdownFiles, array $mediaFiles): array
    {
        $errors = [];

        if (!$this->fileCanBeValidated($path)) {
            return $errors;
        }

        $errors = array_merge($errors, $this->checkFileSize($path));
        $errors = array_merge($errors, $this->checkFileExistence($path));

        return $errors;
    }
    public function checkFileSize(string $path): array
    {
        $errors = [];

        $fileInfo = new SplFileInfo($path, $path, $path);
        if (PathUtility::isGitRepository($path)) {
            // The project path is a Git repository
            $gitRepositoryPath = PathUtility::getGitRepositoryPath($path);

            // Extract the relative path from the full file path
            $relativePath = PathUtility::resolveRelativePath($gitRepositoryPath, $path);

            // Use the git ls-tree command to check if the file exists in the repository
            $branchOrTag = 'master';
            $lsTreeCommand = sprintf('git --git-dir=%s ls-tree %s %s',
                escapeshellarg($gitRepositoryPath),
                escapeshellarg($branchOrTag),
                escapeshellarg($relativePath));

            $lsTreeOutput = shell_exec($lsTreeCommand);

            if ($lsTreeOutput === null) {
                throw new \RuntimeException(sprintf('Failed to check file existence in Git repository: %s', $path));
            }

            if (empty($lsTreeOutput)) {
                $errors[] = new ImageDoesNotExistError($path);
            } elseif ($fileInfo->isFile() && $fileInfo->getSize() > 1048576) {
                $errors[] = new ImageTooLargeError($path, $fileInfo->getSize());
            }
        } elseif ($fileInfo->isFile() && $fileInfo->getSize() > 1048576) {
            $errors[] = new ImageTooLargeError($path, $fileInfo->getSize());
        } elseif (!$fileInfo->isFile() || !$fileInfo->isDir()) {
            $errors[] = new ImageDoesNotExistError($path);
        }

        return $errors;
    }

    public function checkFileExistence(string $path): array
    {
        $errors = [];

        $fileInfo = new SplFileInfo($path, $path, $path);

        if (PathUtility::isGitRepository($path)) {
            // The project path is a Git repository
            $gitRepositoryPath = PathUtility::getGitRepositoryPath($path);

            // Extract the relative path from the full file path
            $relativePath = PathUtility::resolveRelativePath($gitRepositoryPath, $path);

            // Use the git ls-tree command to check if the file exists in the repository
            $branchOrTag = 'master';
            $lsTreeCommand = sprintf('git --git-dir=%s ls-tree %s %s',
                escapeshellarg($gitRepositoryPath),
                escapeshellarg($branchOrTag),
                escapeshellarg($relativePath));

            $lsTreeOutput = shell_exec($lsTreeCommand);

            if ($lsTreeOutput === null) {
                throw new \RuntimeException(sprintf('Failed to check file existence in Git repository: %s', $path));
            }

            if (empty($lsTreeOutput)) {
                $errors[] = new ImageDoesNotExistError($path);
            }
        } elseif (!$fileInfo->isFile() || !$fileInfo->isDir()) {
            $errors[] = new ImageDoesNotExistError($path);
        }

        return $errors;
    }

    public function fileCanBeValidated(string $path): bool
    {
        return PathUtility::isMediaFile($path);
    }
}

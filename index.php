<?php
// ---------------------------
// Composer and Namespace Setup
// ---------------------------
require_once 'vendor/autoload.php';

use Iwm\MarkdownStructure\MarkdownProjectFactory;
use Iwm\MarkdownStructure\Parser\CombineTextAndImagesParser;
use Iwm\MarkdownStructure\Parser\CombineTextAndListParser;
use Iwm\MarkdownStructure\Parser\HeadlinesToSectionParser;
use Iwm\MarkdownStructure\Parser\SplitByEmptyLineParser;
use Iwm\MarkdownStructure\Utility\FilesFinder;
use Symfony\Component\ErrorHandler\Debug;

// -----------------
// Error Handling Setup
// -----------------
Debug::enable();

// --------------------
// Project Configuration
// --------------------
$root = getenv('BASE_PATH') ?: '/var/www/html';
$projectRootPath = $root . '/tests/Fixtures'; // Standard Test
$projectRootPath = $root . '/tests/Fixtures/general-editors-guide.git'; // Test Bare Git Repository
// $documentationPath = "/docs"; // Standard Test
//$documentationPath = "/docs-with-errors"; // Standard Test with Errors
$documentationPath = "/docs"; // Test Bare Git Repository
$indexPath = "/index.md";
$url = 'https://bitbucket.org/iwm/markdown-structure/src/master/';

// ------------------
// Markdown Project Factory
// ------------------
$factory = new MarkdownProjectFactory($projectRootPath, $documentationPath, $indexPath, $url);

// ----------------------
// Register and Configure Parsers (Recommended)
// ----------------------
$factory->registerParser([
    new SplitByEmptyLineParser(),
    new HeadlinesToSectionParser(),
    // new RemoveDevSectionsParser(),
    new CombineTextAndImagesParser(),
    new CombineTextAndListParser(),
]);

// ----------------------
// Register Validators (Optional)
// ----------------------
$factory->registerValidators([
    // new MediaFileValidator(),
]);

// ----------------------
// Register Finishers (Optional)
// ----------------------
$factory->registerFinisher([
    // new ParagraphToContainerParser(),
    // new SectionsToHtmlParser()
]);

// ------------------
// Add Markdown Files
// ------------------

// Manually add files
//$factory->addFile('docs/index.md');
//$factory->addFile('docs/feature/baum/index.md');
//$factory->addFile('/var/www/html/docs/feature/baum/kuchen.md');

// Add files programmatically
//$factory->addFiles(
//    [
//        $basePath . '/tests/Fixtures/some-code-file.yml',
//    ]
//);

// Add files for normal folders
//$factory->addFiles(FilesFinder::findFilesByPath($factory->absoluteDocumentationPath));

// Add files for Git repositories (Bare)
$factory->addFiles(listTrackedFiles($projectRootPath));

// ------------------------
// Create and Process Project
// ------------------------
$project = $factory->create();
dump($project);

// ------------------------
// Helper Function to List Tracked Files in a Git Repository
// ------------------------
function listTrackedFiles(string $repositoryPath): array
{
    if (!is_dir($repositoryPath)) {
        throw new \InvalidArgumentException('Invalid repository path: ' . $repositoryPath);
    }

    // Command to list all files in the repository
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

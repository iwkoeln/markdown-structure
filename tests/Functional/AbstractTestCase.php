<?php declare(strict_types = 1);
namespace Iwm\MarkdownStructure\Tests\Functional;

use PHPUnit\Framework\TestCase;

/**
 * Creates and cleans up .markdown-structure and any amount of files
 * in dedicated workspace path before and after testing.
 */
abstract class AbstractTestCase extends TestCase
{

    /**
     * @var string On this location (relativ to current working dir) the given files and
     *             .markdown-structure are created during set up.
     */
    protected $workspacePath = '.build/.cache/current_test';

    /**
     * @var string .markdown-structure content
     */
    protected $markdownStructure = '';

    /**
     * @var string[] Key is filename, value is file content
     */
    protected $files = [];


    public function setUp(): void
    {
        if (!is_dir($this->workspacePath)) {
            mkdir($this->workspacePath, 0777, true);
                    } else {
            $this->cleanUpWorkspace();
        }

        foreach ($this->files as $filePath => $fileContents) {
            file_put_contents($this->workspacePath . '/' . $filePath, $fileContents);
        }

        putenv('COLUMNS=240');
    }

    public function tearDown(): void
    {
        $this->cleanUpWorkspace();
    }

    private function cleanUpWorkspace(): void
    {
        exec('rm -rf ' . $this->workspacePath . '/* ');
    }
}

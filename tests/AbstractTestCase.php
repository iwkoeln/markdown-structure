<?php

declare(strict_types = 1);

namespace Iwm\MarkdownStructure\Tests;

use Iwm\MarkdownStructure\Utility\PathUtility;
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
    protected string $workspacePath = '.build/current_test';

    public function setUp(): void
    {
        if (!is_dir($this->workspacePath)) {
            PathUtility::mkdir($this->workspacePath);
        } else {
            $this->cleanUpWorkspace();
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

<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Validator\MarkdownProject;
use Iwm\MarkdownStructure\Utility\DomLinkExtractor;
use Iwm\MarkdownStructure\Validator\MarkdownProject\OrphanFileValidator;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MarkdownLink;
use PHPUnit\Framework\TestCase;

class OrphanFileValidatorTest extends TestCase
{
    public function testValidate()
    {
        // Create a sample MarkdownFile and a MarkdownProject
        $markdownFile1 = new MarkdownFile('/path/to/markdown1.md', '/path/to/markdown1.md', 'Markdown content 1', 'HTML content 1');
        // Create a MarkdownFile that references the MarkdownLink
        $markdownFile2 = new MarkdownFile('/path/to/markdown2.md', '/path/to/markdown2.md', 'Markdown content 2', '<a href="markdown3.md">Link Text</a>');
        $markdownFile3 = new MarkdownFile('/path/to/markdown3.md', '/path/to/markdown3.md', 'Markdown content 3', '<a href="markdown1.md">Link Text</a>');

        // Create the MarkdownProject with the MarkdownFiles
        $markdownProject = new MarkdownProject(
            '/path/to/project',
            '/path/to/documentation',
            '/path/to/documentation/index.md',
            [$markdownFile1, $markdownFile2, $markdownFile3],
            [],
            [],
            [],
            []
        );

        $orphanFileValidator = new OrphanFileValidator();

        // Call the validate method
        $orphanFileValidator->validate($markdownProject);

        // Check that the orphans have been correctly identified
        $expectedOrphans = [
            '/path/to/markdown2.md' => '/path/to/markdown2.md',
        ];
        $this->assertEquals($expectedOrphans, $markdownProject->orphans);
    }
}



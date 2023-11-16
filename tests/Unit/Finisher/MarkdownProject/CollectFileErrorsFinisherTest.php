<?php

namespace Iwm\MarkdownStructure\Tests\Unit\Finisher\MarkdownProject;

use Iwm\MarkdownStructure\Finisher\MarkdownProject\CollectFileErrorsFinisher;
use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;
use PHPUnit\Framework\TestCase;

class CollectFileErrorsFinisherTest extends TestCase
{
    public function testFinish()
    {
        $finisher = new CollectFileErrorsFinisher();

        // Create some mock MarkdownFile and MediaFile objects with errors
        $markdownFile1 = new MarkdownFile('/path', '/path/to/file1.md', 'Markdown content', '', '');
        $markdownFile1->errors = ['Error 1', 'Error 2'];

        $markdownFile2 = new MarkdownFile('/path', '/path/to/file2.md', 'Markdown content', '', '');
        $markdownFile2->errors = ['Error 3'];


        $mediaFile1 = new MediaFile('/path/to/image1.jpg');
        $mediaFile1->errors = ['Error 4'];

        $mediaFile2 = new MediaFile('/path/to/image2.png');

        // Create a MarkdownProject with the mock files
        $project = new MarkdownProject(
            '/path/to/project',
            '/docs',
            '/index.md',
            [
                '/path/to/file1.md' => $markdownFile1,
                '/path/to/file2.md' => $markdownFile2,
            ],
            [
                '/path/to/image1.jpg' => $mediaFile1,
                '/path/to/image2.png' => $mediaFile2,
            ],
            [],
            [],
            []
        );

        // Run the finisher
        $finisher->finish($project);

        // Assert that the errors from MarkdownFile and MediaFile objects have been collected in the project's errors property
        $expectedErrors = [
            '/path/to/file1.md' => ['Error 1', 'Error 2'],
            '/path/to/file2.md' => ['Error 3'],
            '/path/to/image1.jpg' => ['Error 4'],
            '/path/to/image2.png' => [],
        ];

        $this->assertEquals($expectedErrors, $project->errors);
    }
}

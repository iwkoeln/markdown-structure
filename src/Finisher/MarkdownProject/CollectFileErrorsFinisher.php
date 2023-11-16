<?php

namespace Iwm\MarkdownStructure\Finisher\MarkdownProject;

use Iwm\MarkdownStructure\Value\MarkdownFile;
use Iwm\MarkdownStructure\Value\MarkdownProject;
use Iwm\MarkdownStructure\Value\MediaFile;

class CollectFileErrorsFinisher implements MarkdownProjectFinisherInterface
{

    public function finish(MarkdownProject $project): void
    {
        $errors = [];
        /** @var MarkdownFile $markdownFile */
        foreach ($project->documentationFiles as $markdownFile) {
            $errors[$markdownFile->path] = $markdownFile->errors;
        }
        /** @var MediaFile $mediaFile */
        foreach ($project->documentationMediaFiles as $mediaFile) {
            $errors[$mediaFile->path] = $mediaFile->errors;
        }
        $project->errors = $errors;
    }
}

<?php

namespace Iwm\MarkdownStructure\Finisher\MarkdownProject;

use Iwm\MarkdownStructure\Error\ErrorInterface;
use Iwm\MarkdownStructure\Value\MarkdownProject;

final class CollectFileErrorsFinisher implements MarkdownProjectFinisherInterface
{
    /**
     * @var array<array<ErrorInterface>|ErrorInterface>
     */
    protected array $errors = [];

    public function finish(MarkdownProject $project): void
    {
        foreach ($project->documentationFiles as $markdownFile) {
            if (!empty($markdownFile->errors)) {
                $this->errors[$markdownFile->path] = $markdownFile->errors;
            }
        }
        foreach ($project->documentationMediaFiles as $mediaFile) {
            if (!empty($mediaFile->errors)) {
                $this->errors[$mediaFile->path] = $mediaFile->errors;
            }
        }
        $project->errors = $this->errors;
    }
}

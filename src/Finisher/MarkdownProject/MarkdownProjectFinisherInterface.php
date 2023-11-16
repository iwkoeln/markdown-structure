<?php

namespace Iwm\MarkdownStructure\Finisher\MarkdownProject;

use Iwm\MarkdownStructure\Value\MarkdownProject;

interface MarkdownProjectFinisherInterface
{
    public function finish(MarkdownProject $project): void;
}

<?php

namespace Iwm\MarkdownStructure\Error;

class LinkTargetNotFoundError extends AbstractError
{
    public string $linkText = '';
    public string $pathOfUnfoundFile = '';

    public function setLinkText(string $linkText): void
    {
        $this->linkText = $linkText;
    }
    public function setUnfoundFilePath(string $pathOfUnfoundFile): void
    {
        $this->pathOfUnfoundFile = $pathOfUnfoundFile;
    }

    public function getErrorMessage(): string
    {
        return sprintf(
            'Error: %s in %s. Could not find link target: "%s" with label: "%s"',
            $this->errorMessage,
            $this->errorSource,
            $this->pathOfUnfoundFile,
            $this->linkText
        );
    }
}

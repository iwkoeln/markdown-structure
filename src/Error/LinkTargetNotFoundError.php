<?php

namespace Iwm\MarkdownStructure\Error;

final class LinkTargetNotFoundError extends AbstractError
{

    public function __construct(
        protected string $errorSource,
        protected string $errorMessage,
        protected string $linkText,
        protected string $pathOfUnfoundFile
    )
    {
        parent::__construct($errorSource, $errorMessage);
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

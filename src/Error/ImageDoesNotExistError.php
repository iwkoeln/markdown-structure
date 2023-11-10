<?php

namespace Iwm\MarkdownStructure\Error;

final class ImageDoesNotExistError extends AbstractError
{

    public function __construct(
        protected string $errorSource,
        protected string $missingImageFile = '',
        protected string $errorMessage = 'Image file does not exist:'
    )
    {
        if (empty($missingImageFile)) {
            $this->missingImageFile = $errorSource;
        }
        parent::__construct($errorSource, $errorMessage);
    }

    public function getErrorMessage(): string
    {
        return sprintf(
            'Error: %s in %s.',
            $this->errorMessage,
            $this->errorSource,
        );
    }
}

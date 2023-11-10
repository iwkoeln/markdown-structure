<?php

namespace Iwm\MarkdownStructure\Error;

final class ImageTooLargeError extends AbstractError
{

    public function __construct(
        protected string $errorSource,
        protected int $fileSize,
        protected string $errorMessage = 'Image file size exceeds 1 MB:'
    )
    {
        parent::__construct($errorSource, $errorMessage);
    }

    public function getErrorMessage(): string
    {
        return sprintf(
            'Error: %s in %s. File size: %s bytes.',
            $this->errorMessage,
            $this->errorSource,
            $this->fileSize
        );
    }
}

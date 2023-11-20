<?php

namespace Iwm\MarkdownStructure\Error;

abstract class AbstractError implements ErrorInterface
{
    public function __construct(
        protected string $errorSource,
        protected string $errorMessage
    ) {
    }

    // Implement getErrorMessage method in the abstract class if it's common for all errors
    // Otherwise, leave it to the concrete classes to implement.
    abstract public function getErrorMessage(): string;

    public function __toString(): string
    {
        return $this->getErrorMessage();
    }
}

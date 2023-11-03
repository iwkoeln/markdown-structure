<?php

namespace Iwm\MarkdownStructure\Error;

class AbstractError implements ErrorInterface
{
    protected string $errorSource;
    protected string $errorMessage;

    public function __construct(string $errorSource, string $errorMessage)
    {
        $this->errorSource = $errorSource;
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }

    public function setErrorMessage(string $errorMessage): void
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorSource(): string
    {
        return $this->errorSource;
    }

    public function setErrorSource(string $errorSource): void
    {
        $this->errorSource = $errorSource;
    }

    public function __toString(): string
    {
        return $this->errorMessage;
    }
}

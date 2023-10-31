<?php

namespace Iwm\MarkdownStructure\ErrorHandler;

interface ErrorInterface
{
    public function __construct(string $errorSource, string $errorMessage);
    public function getErrorMessage(): string;
    public function setErrorMessage(string $errorMessage): void;
    public function getErrorSource(): string;
    public function setErrorSource(string $filePath): void;
    public function __toString(): string;
}
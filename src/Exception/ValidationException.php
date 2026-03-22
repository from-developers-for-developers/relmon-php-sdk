<?php

namespace RelMon\Exception;

use JetBrains\PhpStorm\Pure;

class ValidationException extends RelMonException
{
    #[Pure]
    public function __construct(
        private readonly array $violations,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    )
    {
        parent::__construct($message, $code, $previous);
    }

    public function getViolations(): array
    {
        return $this->violations;
    }
}

<?php

namespace FromDevelopersForDevelopers\RelMon\Exception;

use FromDevelopersForDevelopers\RelMon\Dto\ViolationDto;
use JetBrains\PhpStorm\Pure;

class ValidationException extends RelMonException
{
    #[Pure]
    public function __construct(
        /** @var ViolationDto[] */
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

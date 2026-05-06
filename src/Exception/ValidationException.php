<?php

namespace FromDevelopersForDevelopers\RelMon\Exception;

use FromDevelopersForDevelopers\RelMon\Dto\ViolationDto;

class ValidationException extends RelMonException
{
    public function __construct(
        /** @var ViolationDto[] */
        private array $violations,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function getViolations(): array
    {
        return $this->violations;
    }
}

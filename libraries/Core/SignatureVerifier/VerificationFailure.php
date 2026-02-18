<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\SignatureVerifier;

class VerificationFailure
{
    private $errorMessage;

    public function __construct(string $errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    public function getErrorMessage(): string
    {
        return $this->errorMessage;
    }
}

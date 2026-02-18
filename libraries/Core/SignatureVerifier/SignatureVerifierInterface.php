<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\SignatureVerifier;

use EDD\Vendor\Symfony\Component\HttpFoundation\Request;
use EDD\Vendor\Core\SignatureVerifier\VerificationFailure;

interface SignatureVerifierInterface
{
    /**
     * Verifies the signature of a request.
     *
     * @param Request $request
     * @return VerificationFailure|true
     */
    public function verify(Request $request);
}

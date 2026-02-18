<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\SignatureVerifier;

use EDD\Vendor\Symfony\Component\HttpFoundation\Request;
use EDD\Vendor\Core\SignatureVerifier\VerificationFailure;
use EDD\Vendor\Core\SignatureVerifier\SignatureVerifierInterface;

class HmacSignatureVerifier implements SignatureVerifierInterface
{
    private const VALID_ENCODINGS = ['hex', 'base64', 'base64url'];
    private const VALID_ALGORITHMS = ['sha256', 'sha512'];

    private $secretKey;
    private $signatureVerificationFailureCreator;
    private $signatureHeader;
    private $algorithm;
    private $encoding;
    private $signatureValueTemplate;
    private $templateResolver;

    public function __construct(
        string $secretKey,
        callable $signatureVerificationFailureCreator,
        string $signatureHeader,
        ?callable $templateResolver = null,
        string $algorithm = 'sha256',
        string $encoding = 'hex',
        ?string $signatureValueTemplate = null
    ) {
        if (empty($secretKey)) {
            throw new \InvalidArgumentException('secretKey must be a non-empty string');
        }
        if (empty($signatureHeader)) {
            throw new \InvalidArgumentException('signatureHeader must be a non-empty string');
        }
        if (!in_array(strtolower($algorithm), self::VALID_ALGORITHMS, true)) {
            throw new \InvalidArgumentException('algorithm must be one of: ' . implode(', ', self::VALID_ALGORITHMS));
        }
        if (!in_array(strtolower($encoding), self::VALID_ENCODINGS, true)) {
            throw new \InvalidArgumentException('encoding must be one of: ' . implode(', ', self::VALID_ENCODINGS));
        }

        $this->secretKey = $secretKey;
        $this->signatureVerificationFailureCreator = $signatureVerificationFailureCreator;
        $this->signatureHeader = $signatureHeader;
        $this->algorithm = strtolower($algorithm);
        $this->encoding = strtolower($encoding);
        $this->signatureValueTemplate = $signatureValueTemplate;
        $this->templateResolver = $templateResolver;
    }

    /**
     * @param Request $request
     * @return VerificationFailure | true
     */
    public function verify(Request $request)
    {
        $receivedSignature = $request->headers->get($this->signatureHeader);

        if ($receivedSignature === null) {
            return call_user_func($this->signatureVerificationFailureCreator, 'Missing signature header');
        }

        if ($this->templateResolver !== null) {
            $signingData = call_user_func($this->templateResolver, $request);

            if ($signingData === null || $signingData === '') {
                $signingData = $request->getContent();
            }
        } else {
            $signingData = $request->getContent();
        }

        $hash = hash_hmac($this->algorithm, $signingData, $this->secretKey, true);
        $expectedSignature = $this->encodeHash($hash);

        if ($this->signatureValueTemplate !== null) {
            $expectedSignature = str_replace('{digest}', $expectedSignature, $this->signatureValueTemplate);
        }

        if (!hash_equals($expectedSignature, $receivedSignature)) {
            return call_user_func($this->signatureVerificationFailureCreator, 'Signature mismatch');
        }

        return true;
    }

    private function encodeHash(string $hash): string
    {
        if ($this->encoding === 'hex') {
            return bin2hex($hash);
        }
        if ($this->encoding === 'base64') {
            return base64_encode($hash);
        }
        return rtrim(strtr(base64_encode($hash), '+/', '-_'), '=');
    }
}

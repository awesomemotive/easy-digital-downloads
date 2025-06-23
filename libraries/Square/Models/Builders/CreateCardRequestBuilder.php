<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\Card;
use EDD\Vendor\Square\Models\CreateCardRequest;

/**
 * Builder for model CreateCardRequest
 *
 * @see CreateCardRequest
 */
class CreateCardRequestBuilder
{
    /**
     * @var CreateCardRequest
     */
    private $instance;

    private function __construct(CreateCardRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Create Card Request Builder object.
     *
     * @param string $idempotencyKey
     * @param string $sourceId
     * @param Card $card
     */
    public static function init(string $idempotencyKey, string $sourceId, Card $card): self
    {
        return new self(new CreateCardRequest($idempotencyKey, $sourceId, $card));
    }

    /**
     * Sets verification token field.
     *
     * @param string|null $value
     */
    public function verificationToken(?string $value): self
    {
        $this->instance->setVerificationToken($value);
        return $this;
    }

    /**
     * Initializes a new Create Card Request object.
     */
    public function build(): CreateCardRequest
    {
        return CoreHelper::clone($this->instance);
    }
}

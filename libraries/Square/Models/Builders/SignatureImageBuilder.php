<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SignatureImage;

/**
 * Builder for model SignatureImage
 *
 * @see SignatureImage
 */
class SignatureImageBuilder
{
    /**
     * @var SignatureImage
     */
    private $instance;

    private function __construct(SignatureImage $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Signature Image Builder object.
     */
    public static function init(): self
    {
        return new self(new SignatureImage());
    }

    /**
     * Sets image type field.
     *
     * @param string|null $value
     */
    public function imageType(?string $value): self
    {
        $this->instance->setImageType($value);
        return $this;
    }

    /**
     * Sets data field.
     *
     * @param string|null $value
     */
    public function data(?string $value): self
    {
        $this->instance->setData($value);
        return $this;
    }

    /**
     * Initializes a new Signature Image object.
     */
    public function build(): SignatureImage
    {
        return CoreHelper::clone($this->instance);
    }
}

<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\SaveCardOptions;

/**
 * Builder for model SaveCardOptions
 *
 * @see SaveCardOptions
 */
class SaveCardOptionsBuilder
{
    /**
     * @var SaveCardOptions
     */
    private $instance;

    private function __construct(SaveCardOptions $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Save Card Options Builder object.
     *
     * @param string $customerId
     */
    public static function init(string $customerId): self
    {
        return new self(new SaveCardOptions($customerId));
    }

    /**
     * Sets card id field.
     *
     * @param string|null $value
     */
    public function cardId(?string $value): self
    {
        $this->instance->setCardId($value);
        return $this;
    }

    /**
     * Sets reference id field.
     *
     * @param string|null $value
     */
    public function referenceId(?string $value): self
    {
        $this->instance->setReferenceId($value);
        return $this;
    }

    /**
     * Unsets reference id field.
     */
    public function unsetReferenceId(): self
    {
        $this->instance->unsetReferenceId();
        return $this;
    }

    /**
     * Initializes a new Save Card Options object.
     */
    public function build(): SaveCardOptions
    {
        return CoreHelper::clone($this->instance);
    }
}

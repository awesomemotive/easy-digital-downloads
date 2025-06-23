<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Models\Builders;

use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Models\RegisterDomainRequest;

/**
 * Builder for model RegisterDomainRequest
 *
 * @see RegisterDomainRequest
 */
class RegisterDomainRequestBuilder
{
    /**
     * @var RegisterDomainRequest
     */
    private $instance;

    private function __construct(RegisterDomainRequest $instance)
    {
        $this->instance = $instance;
    }

    /**
     * Initializes a new Register Domain Request Builder object.
     *
     * @param string $domainName
     */
    public static function init(string $domainName): self
    {
        return new self(new RegisterDomainRequest($domainName));
    }

    /**
     * Initializes a new Register Domain Request object.
     */
    public function build(): RegisterDomainRequest
    {
        return CoreHelper::clone($this->instance);
    }
}

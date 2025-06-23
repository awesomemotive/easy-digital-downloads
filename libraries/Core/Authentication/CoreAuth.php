<?php

declare(strict_types=1);

namespace EDD\Vendor\Core\Authentication;

use EDD\Vendor\CoreInterfaces\Core\Authentication\AuthInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\ParamInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestSetterInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\TypeValidatorInterface;
use InvalidArgumentException;

/**
 * Use to apply authentication parameters to the request
 */
class CoreAuth implements AuthInterface
{
    private $parameters;
    private $isValid = false;

    /**
     * @param ParamInterface ...$parameters
     */
    public function __construct(...$parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @throws InvalidArgumentException
     */
    public function validate(TypeValidatorInterface $validator): void
    {
        array_walk($this->parameters, function ($param) use ($validator): void {
            $param->validate($validator);
        });
        $this->isValid = true;
    }

    public function apply(RequestSetterInterface $request): void
    {
        if (!$this->isValid) {
            return;
        }
        array_walk($this->parameters, function ($param) use ($request): void {
            $param->apply($request);
        });
    }
}

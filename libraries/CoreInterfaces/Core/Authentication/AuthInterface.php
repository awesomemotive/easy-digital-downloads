<?php

namespace EDD\Vendor\CoreInterfaces\Core\Authentication;

use EDD\Vendor\CoreInterfaces\Core\Request\RequestSetterInterface;
use EDD\Vendor\CoreInterfaces\Core\Request\TypeValidatorInterface;
use InvalidArgumentException;

interface AuthInterface
{
    /**
     * @throws InvalidArgumentException
     */
    public function validate(TypeValidatorInterface $validator): void;
    public function apply(RequestSetterInterface $request): void;
}

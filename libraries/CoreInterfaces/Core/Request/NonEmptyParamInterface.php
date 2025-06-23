<?php

namespace EDD\Vendor\CoreInterfaces\Core\Request;

interface NonEmptyParamInterface extends ParamInterface
{
    public function requiredNonEmpty();
}

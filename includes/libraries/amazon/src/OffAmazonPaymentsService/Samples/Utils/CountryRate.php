<?php

/* *****************************************************************************
 * Copyright 2013 Amazon.com, Inc. or its affiliates. All Rights Reserved.
 * Licensed under the Apache License, Version 2.0 (the "License");
 *
 * You may not use this file except in compliance with the License.
 * You may obtain a copy of the License at:
 * http://aws.amazon.com/apache2.0
 * This file is distributed on an "AS IS" BASIS, WITHOUT WARRANTIES OR
 * CONDITIONS OF ANY KIND, either express or implied. See the License for the
 * specific language governing permissions and limitations under the License.
 * *****************************************************************************
 */

/**
 * Utility class to help store data required for our payment scenario
 */
class CountryRates
{

    public $shippingRates;

    public $taxRates;

    /**
     * Creates a new instance of the country rates class with
     * the specified tax rate and shipping rate maps
     *
     * @param array $shippingRates
     *            association of shipping types to costs
     * @param array $taxRates
     *            association of states to tax rate %
     *
     * @return new CountryRates
     */
    public function __construct ($shippingRates, $taxRates)
    {
        $this->shippingRates = $shippingRates;
        $this->taxRates = $taxRates;
    }
}
?>
<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service;

class CountrySpecService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * Lists all Country Spec objects available in the API.
     *
     * @param null|array $params
     * @param null|array|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Collection<\EDD\Vendor\Stripe\CountrySpec>
     */
    public function all($params = null, $opts = null)
    {
        return $this->requestCollection('get', '/v1/country_specs', $params, $opts);
    }

    /**
     * Returns a Country Spec for a given Country code.
     *
     * @param string $id
     * @param null|array $params
     * @param null|array|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\CountrySpec
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/country_specs/%s', $id), $params, $opts);
    }
}

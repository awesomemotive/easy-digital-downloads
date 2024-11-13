<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service\Tax;

/**
 * @phpstan-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 */
class TransactionService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * Retrieves the line items of a committed standalone transaction as a collection.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Collection<\EDD\Vendor\Stripe\Tax\TransactionLineItem>
     */
    public function allLineItems($id, $params = null, $opts = null)
    {
        return $this->requestCollection('get', $this->buildPath('/v1/tax/transactions/%s/line_items', $id), $params, $opts);
    }

    /**
     * Creates a Tax Transaction from a calculation, if that calculation hasn’t
     * expired. Calculations expire after 90 days.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Tax\Transaction
     */
    public function createFromCalculation($params = null, $opts = null)
    {
        return $this->request('post', '/v1/tax/transactions/create_from_calculation', $params, $opts);
    }

    /**
     * Partially or fully reverses a previously created <code>Transaction</code>.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Tax\Transaction
     */
    public function createReversal($params = null, $opts = null)
    {
        return $this->request('post', '/v1/tax/transactions/create_reversal', $params, $opts);
    }

    /**
     * Retrieves a Tax <code>Transaction</code> object.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Tax\Transaction
     */
    public function retrieve($id, $params = null, $opts = null)
    {
        return $this->request('get', $this->buildPath('/v1/tax/transactions/%s', $id), $params, $opts);
    }
}

<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service\TestHelpers\Issuing;

/**
 * @phpstan-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 */
class TransactionService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * Allows the user to capture an arbitrary amount, also known as a forced capture.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Issuing\Transaction
     */
    public function createForceCapture($params = null, $opts = null)
    {
        return $this->request('post', '/v1/test_helpers/issuing/transactions/create_force_capture', $params, $opts);
    }

    /**
     * Allows the user to refund an arbitrary amount, also known as a unlinked refund.
     *
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Issuing\Transaction
     */
    public function createUnlinkedRefund($params = null, $opts = null)
    {
        return $this->request('post', '/v1/test_helpers/issuing/transactions/create_unlinked_refund', $params, $opts);
    }

    /**
     * Refund a test-mode Transaction.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Issuing\Transaction
     */
    public function refund($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/issuing/transactions/%s/refund', $id), $params, $opts);
    }
}

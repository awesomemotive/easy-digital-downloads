<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe\Service\TestHelpers\Treasury;

/**
 * @phpstan-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 * @psalm-import-type RequestOptionsArray from \EDD\Vendor\Stripe\Util\RequestOptions
 */
class OutboundTransferService extends \EDD\Vendor\Stripe\Service\AbstractService
{
    /**
     * Transitions a test mode created OutboundTransfer to the <code>failed</code>
     * status. The OutboundTransfer must already be in the <code>processing</code>
     * state.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Treasury\OutboundTransfer
     */
    public function fail($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/treasury/outbound_transfers/%s/fail', $id), $params, $opts);
    }

    /**
     * Transitions a test mode created OutboundTransfer to the <code>posted</code>
     * status. The OutboundTransfer must already be in the <code>processing</code>
     * state.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Treasury\OutboundTransfer
     */
    public function post($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/treasury/outbound_transfers/%s/post', $id), $params, $opts);
    }

    /**
     * Transitions a test mode created OutboundTransfer to the <code>returned</code>
     * status. The OutboundTransfer must already be in the <code>processing</code>
     * state.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Treasury\OutboundTransfer
     */
    public function returnOutboundTransfer($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/treasury/outbound_transfers/%s/return', $id), $params, $opts);
    }

    /**
     * Updates a test mode created OutboundTransfer with tracking details. The
     * OutboundTransfer must not be cancelable, and cannot be in the
     * <code>canceled</code> or <code>failed</code> states.
     *
     * @param string $id
     * @param null|array $params
     * @param null|RequestOptionsArray|\EDD\Vendor\Stripe\Util\RequestOptions $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Treasury\OutboundTransfer
     */
    public function update($id, $params = null, $opts = null)
    {
        return $this->request('post', $this->buildPath('/v1/test_helpers/treasury/outbound_transfers/%s', $id), $params, $opts);
    }
}

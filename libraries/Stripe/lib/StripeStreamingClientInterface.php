<?php

namespace EDD\Vendor\Stripe;

/**
 * Interface for a EDD\Vendor\Stripe client.
 */
interface StripeStreamingClientInterface extends BaseStripeClientInterface
{
    public function requestStream($method, $path, $readBodyChunkCallable, $params, $opts);
}

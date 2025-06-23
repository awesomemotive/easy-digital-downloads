<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\RegisterDomainRequest;
use EDD\Vendor\Square\Models\RegisterDomainResponse;

class ApplePayApi extends BaseApi
{
    /**
     * Activates a domain for use with Apple Pay on the Web and Square. A validation
     * is performed on this domain by Apple to ensure that it is properly set up as
     * an Apple Pay enabled domain.
     *
     * This endpoint provides an easy way for platform developers to bulk activate
     * Apple Pay on the Web with EDD\Vendor\Square for merchants using their platform.
     *
     * Note: You will need to host a valid domain verification file on your domain to support Apple Pay.
     * The
     * current version of this file is always available at https://app.squareup.com/digital-wallets/apple-
     * pay/apple-developer-merchantid-domain-association,
     * and should be hosted at `.well_known/apple-developer-merchantid-domain-association` on your
     * domain.  This file is subject to change; we strongly recommend checking for updates regularly and
     * avoiding
     * long-lived caches that might not keep in sync with the correct file version.
     *
     * To learn more about the Web Payments SDK and how to add Apple Pay, see [Take an Apple Pay
     * Payment](https://developer.squareup.com/docs/web-payments/apple-pay).
     *
     * @param RegisterDomainRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function registerDomain(RegisterDomainRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/apple-pay/domains')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(RegisterDomainResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}

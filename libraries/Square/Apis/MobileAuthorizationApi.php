<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\CreateMobileAuthorizationCodeRequest;
use EDD\Vendor\Square\Models\CreateMobileAuthorizationCodeResponse;

class MobileAuthorizationApi extends BaseApi
{
    /**
     * Generates code to authorize a mobile application to connect to a EDD\Vendor\Square card reader.
     *
     * Authorization codes are one-time-use codes and expire 60 minutes after being issued.
     *
     * __Important:__ The `Authorization` header you provide to this endpoint must have the following
     * format:
     *
     * ```
     * Authorization: Bearer ACCESS_TOKEN
     * ```
     *
     * Replace `ACCESS_TOKEN` with a
     * [valid production authorization credential](https://developer.squareup.com/docs/build-basics/access-
     * tokens).
     *
     * @param CreateMobileAuthorizationCodeRequest $body An object containing the fields to POST for
     *        the request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createMobileAuthorizationCode(CreateMobileAuthorizationCodeRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/mobile/authorization-code')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()
            ->type(CreateMobileAuthorizationCodeResponse::class)
            ->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}

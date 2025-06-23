<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\ObtainTokenRequest;
use EDD\Vendor\Square\Models\ObtainTokenResponse;
use EDD\Vendor\Square\Models\RetrieveTokenStatusResponse;
use EDD\Vendor\Square\Models\RevokeTokenRequest;
use EDD\Vendor\Square\Models\RevokeTokenResponse;

class OAuthApi extends BaseApi
{
    /**
     * Revokes an access token generated with the OAuth flow.
     *
     * If an account has more than one OAuth access token for your application, this
     * endpoint revokes all of them, regardless of which token you specify.
     *
     * __Important:__ The `Authorization` header for this endpoint must have the
     * following format:
     *
     * ```
     * Authorization: Client APPLICATION_SECRET
     * ```
     *
     * Replace `APPLICATION_SECRET` with the application secret on the **OAuth**
     * page for your application in the Developer Dashboard.
     *
     * @param RevokeTokenRequest $body An object containing the fields to POST for the request. See
     *        the corresponding object definition for field details.
     * @param string $authorization Client APPLICATION_SECRET
     *
     * @return ApiResponse Response from the API call
     */
    public function revokeToken(RevokeTokenRequest $body, string $authorization): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/oauth2/revoke')
            ->parameters(
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body),
                HeaderParam::init('Authorization', $authorization)
            );

        $_resHandler = $this->responseHandler()->type(RevokeTokenResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Returns an OAuth access token and a refresh token unless the
     * `short_lived` parameter is set to `true`, in which case the endpoint
     * returns only an access token.
     *
     * The `grant_type` parameter specifies the type of OAuth request. If
     * `grant_type` is `authorization_code`, you must include the authorization
     * code you received when a seller granted you authorization. If `grant_type`
     * is `refresh_token`, you must provide a valid refresh token. If you're using
     * an old version of the EDD\Vendor\Square APIs (prior to March 13, 2019), `grant_type`
     * can be `migration_token` and you must provide a valid migration token.
     *
     * You can use the `scopes` parameter to limit the set of permissions granted
     * to the access token and refresh token. You can use the `short_lived` parameter
     * to create an access token that expires in 24 hours.
     *
     * __Note:__ OAuth tokens should be encrypted and stored on a secure server.
     * Application clients should never interact directly with OAuth tokens.
     *
     * @param ObtainTokenRequest $body An object containing the fields to POST for the request. See
     *        the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function obtainToken(ObtainTokenRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/oauth2/token')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(ObtainTokenResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Returns information about an [OAuth access token](https://developer.squareup.com/docs/build-
     * basics/access-tokens#get-an-oauth-access-token) or an application’s [personal access token](https:
     * //developer.squareup.com/docs/build-basics/access-tokens#get-a-personal-access-token).
     *
     * Add the access token to the Authorization header of the request.
     *
     * __Important:__ The `Authorization` header you provide to this endpoint must have the following
     * format:
     *
     * ```
     * Authorization: Bearer ACCESS_TOKEN
     * ```
     *
     * where `ACCESS_TOKEN` is a
     * [valid production authorization credential](https://developer.squareup.com/docs/build-basics/access-
     * tokens).
     *
     * If the access token is expired or not a valid access token, the endpoint returns an `UNAUTHORIZED`
     * error.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveTokenStatus(): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/oauth2/token/status')->auth('global');

        $_resHandler = $this->responseHandler()->type(RetrieveTokenStatusResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}

<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\ListSitesResponse;

class SitesApi extends BaseApi
{
    /**
     * Lists the EDD\Vendor\Square Online sites that belong to a seller. Sites are listed in descending order by the
     * `created_at` date.
     *
     *
     * __Note:__ EDD\Vendor\Square Online APIs are publicly available as part of an early access program. For more
     * information, see [Early access program for EDD\Vendor\Square Online APIs](https://developer.squareup.
     * com/docs/online-api#early-access-program-for-square-online-apis).
     *
     * @return ApiResponse Response from the API call
     */
    public function listSites(): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/sites')->auth('global');

        $_resHandler = $this->responseHandler()->type(ListSitesResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}

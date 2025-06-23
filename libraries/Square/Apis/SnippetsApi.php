<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\DeleteSnippetResponse;
use EDD\Vendor\Square\Models\RetrieveSnippetResponse;
use EDD\Vendor\Square\Models\UpsertSnippetRequest;
use EDD\Vendor\Square\Models\UpsertSnippetResponse;

class SnippetsApi extends BaseApi
{
    /**
     * Removes your snippet from a EDD\Vendor\Square Online site.
     *
     * You can call [ListSites]($e/Sites/ListSites) to get the IDs of the sites that belong to a seller.
     *
     *
     * __Note:__ EDD\Vendor\Square Online APIs are publicly available as part of an early access program. For more
     * information, see [Early access program for EDD\Vendor\Square Online APIs](https://developer.squareup.
     * com/docs/online-api#early-access-program-for-square-online-apis).
     *
     * @param string $siteId The ID of the site that contains the snippet.
     *
     * @return ApiResponse Response from the API call
     */
    public function deleteSnippet(string $siteId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::DELETE, '/v2/sites/{site_id}/snippet')
            ->auth('global')
            ->parameters(TemplateParam::init('site_id', $siteId));

        $_resHandler = $this->responseHandler()->type(DeleteSnippetResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves your snippet from a EDD\Vendor\Square Online site. A site can contain snippets from multiple snippet
     * applications, but you can retrieve only the snippet that was added by your application.
     *
     * You can call [ListSites]($e/Sites/ListSites) to get the IDs of the sites that belong to a seller.
     *
     *
     * __Note:__ EDD\Vendor\Square Online APIs are publicly available as part of an early access program. For more
     * information, see [Early access program for EDD\Vendor\Square Online APIs](https://developer.squareup.
     * com/docs/online-api#early-access-program-for-square-online-apis).
     *
     * @param string $siteId The ID of the site that contains the snippet.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveSnippet(string $siteId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/sites/{site_id}/snippet')
            ->auth('global')
            ->parameters(TemplateParam::init('site_id', $siteId));

        $_resHandler = $this->responseHandler()->type(RetrieveSnippetResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Adds a snippet to a EDD\Vendor\Square Online site or updates the existing snippet on the site.
     * The snippet code is appended to the end of the `head` element on every page of the site, except
     * checkout pages. A snippet application can add one snippet to a given site.
     *
     * You can call [ListSites]($e/Sites/ListSites) to get the IDs of the sites that belong to a seller.
     *
     *
     * __Note:__ EDD\Vendor\Square Online APIs are publicly available as part of an early access program. For more
     * information, see [Early access program for EDD\Vendor\Square Online APIs](https://developer.squareup.
     * com/docs/online-api#early-access-program-for-square-online-apis).
     *
     * @param string $siteId The ID of the site where you want to add or update the snippet.
     * @param UpsertSnippetRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function upsertSnippet(string $siteId, UpsertSnippetRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/sites/{site_id}/snippet')
            ->auth('global')
            ->parameters(
                TemplateParam::init('site_id', $siteId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpsertSnippetResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}

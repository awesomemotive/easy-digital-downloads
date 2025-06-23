<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\CreateDeviceCodeRequest;
use EDD\Vendor\Square\Models\CreateDeviceCodeResponse;
use EDD\Vendor\Square\Models\GetDeviceCodeResponse;
use EDD\Vendor\Square\Models\GetDeviceResponse;
use EDD\Vendor\Square\Models\ListDeviceCodesResponse;
use EDD\Vendor\Square\Models\ListDevicesResponse;

class DevicesApi extends BaseApi
{
    /**
     * List devices associated with the merchant. Currently, only Terminal API
     * devices are supported.
     *
     * @param string|null $cursor A pagination cursor returned by a previous call to this endpoint.
     *        Provide this cursor to retrieve the next set of results for the original query.
     *        See [Pagination](https://developer.squareup.com/docs/build-basics/common-api-
     *        patterns/pagination) for more information.
     * @param string|null $sortOrder The order in which results are listed. - `ASC` - Oldest to
     *        newest.
     *        - `DESC` - Newest to oldest (default).
     * @param int|null $limit The number of results to return in a single page.
     * @param string|null $locationId If present, only returns devices at the target location.
     *
     * @return ApiResponse Response from the API call
     */
    public function listDevices(
        ?string $cursor = null,
        ?string $sortOrder = null,
        ?int $limit = null,
        ?string $locationId = null
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/devices')
            ->auth('global')
            ->parameters(
                QueryParam::init('cursor', $cursor),
                QueryParam::init('sort_order', $sortOrder),
                QueryParam::init('limit', $limit),
                QueryParam::init('location_id', $locationId)
            );

        $_resHandler = $this->responseHandler()->type(ListDevicesResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Lists all DeviceCodes associated with the merchant.
     *
     * @param string|null $cursor A pagination cursor returned by a previous call to this endpoint.
     *        Provide this to retrieve the next set of results for your original query.
     *
     *        See [Paginating results](https://developer.squareup.com/docs/working-with-
     *        apis/pagination) for more information.
     * @param string|null $locationId If specified, only returns DeviceCodes of the specified
     *        location.
     *        Returns DeviceCodes of all locations if empty.
     * @param string|null $productType If specified, only returns DeviceCodes targeting the
     *        specified product type.
     *        Returns DeviceCodes of all product types if empty.
     * @param string|null $status If specified, returns DeviceCodes with the specified statuses.
     *        Returns DeviceCodes of status `PAIRED` and `UNPAIRED` if empty.
     *
     * @return ApiResponse Response from the API call
     */
    public function listDeviceCodes(
        ?string $cursor = null,
        ?string $locationId = null,
        ?string $productType = null,
        ?string $status = null
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/devices/codes')
            ->auth('global')
            ->parameters(
                QueryParam::init('cursor', $cursor),
                QueryParam::init('location_id', $locationId),
                QueryParam::init('product_type', $productType),
                QueryParam::init('status', $status)
            );

        $_resHandler = $this->responseHandler()->type(ListDeviceCodesResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a DeviceCode that can be used to login to a EDD\Vendor\Square Terminal device to enter the connected
     * terminal mode.
     *
     * @param CreateDeviceCodeRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createDeviceCode(CreateDeviceCodeRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/devices/codes')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateDeviceCodeResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves DeviceCode with the associated ID.
     *
     * @param string $id The unique identifier for the device code.
     *
     * @return ApiResponse Response from the API call
     */
    public function getDeviceCode(string $id): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/devices/codes/{id}')
            ->auth('global')
            ->parameters(TemplateParam::init('id', $id));

        $_resHandler = $this->responseHandler()->type(GetDeviceCodeResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves Device with the associated `device_id`.
     *
     * @param string $deviceId The unique ID for the desired `Device`.
     *
     * @return ApiResponse Response from the API call
     */
    public function getDevice(string $deviceId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/devices/{device_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('device_id', $deviceId));

        $_resHandler = $this->responseHandler()->type(GetDeviceResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}

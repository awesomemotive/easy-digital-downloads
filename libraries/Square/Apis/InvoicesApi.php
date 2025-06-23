<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\FormParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\CancelInvoiceRequest;
use EDD\Vendor\Square\Models\CancelInvoiceResponse;
use EDD\Vendor\Square\Models\CreateInvoiceAttachmentRequest;
use EDD\Vendor\Square\Models\CreateInvoiceAttachmentResponse;
use EDD\Vendor\Square\Models\CreateInvoiceRequest;
use EDD\Vendor\Square\Models\CreateInvoiceResponse;
use EDD\Vendor\Square\Models\DeleteInvoiceAttachmentResponse;
use EDD\Vendor\Square\Models\DeleteInvoiceResponse;
use EDD\Vendor\Square\Models\GetInvoiceResponse;
use EDD\Vendor\Square\Models\ListInvoicesResponse;
use EDD\Vendor\Square\Models\PublishInvoiceRequest;
use EDD\Vendor\Square\Models\PublishInvoiceResponse;
use EDD\Vendor\Square\Models\SearchInvoicesRequest;
use EDD\Vendor\Square\Models\SearchInvoicesResponse;
use EDD\Vendor\Square\Models\UpdateInvoiceRequest;
use EDD\Vendor\Square\Models\UpdateInvoiceResponse;
use EDD\Vendor\Square\Utils\FileWrapper;

class InvoicesApi extends BaseApi
{
    /**
     * Returns a list of invoices for a given location. The response
     * is paginated. If truncated, the response includes a `cursor` that you
     * use in a subsequent request to retrieve the next set of invoices.
     *
     * @param string $locationId The ID of the location for which to list invoices.
     * @param string|null $cursor A pagination cursor returned by a previous call to this endpoint.
     *        Provide this cursor to retrieve the next set of results for your original query.
     *
     *        For more information, see [Pagination](https://developer.squareup.com/docs/build-
     *        basics/common-api-patterns/pagination).
     * @param int|null $limit The maximum number of invoices to return (200 is the maximum `limit`).
     *        If not provided, the server uses a default limit of 100 invoices.
     *
     * @return ApiResponse Response from the API call
     */
    public function listInvoices(string $locationId, ?string $cursor = null, ?int $limit = null): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/invoices')
            ->auth('global')
            ->parameters(
                QueryParam::init('location_id', $locationId),
                QueryParam::init('cursor', $cursor),
                QueryParam::init('limit', $limit)
            );

        $_resHandler = $this->responseHandler()->type(ListInvoicesResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a draft [invoice]($m/Invoice)
     * for an order created using the Orders API.
     *
     * A draft invoice remains in your account and no action is taken.
     * You must publish the invoice before EDD\Vendor\Square can process it (send it to the customer's email address
     * or charge the customer’s card on file).
     *
     * @param CreateInvoiceRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createInvoice(CreateInvoiceRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/invoices')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateInvoiceResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Searches for invoices from a location specified in
     * the filter. You can optionally specify customers in the filter for whom to
     * retrieve invoices. In the current implementation, you can only specify one location and
     * optionally one customer.
     *
     * The response is paginated. If truncated, the response includes a `cursor`
     * that you use in a subsequent request to retrieve the next set of invoices.
     *
     * @param SearchInvoicesRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function searchInvoices(SearchInvoicesRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/invoices/search')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(SearchInvoicesResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Deletes the specified invoice. When an invoice is deleted, the
     * associated order status changes to CANCELED. You can only delete a draft
     * invoice (you cannot delete a published invoice, including one that is scheduled for processing).
     *
     * @param string $invoiceId The ID of the invoice to delete.
     * @param int|null $version The version of the [invoice](entity:Invoice) to delete. If you do
     *        not know the version, you can call [GetInvoice](api-endpoint:Invoices-GetInvoice) or
     *        [ListInvoices](api-endpoint:Invoices-ListInvoices).
     *
     * @return ApiResponse Response from the API call
     */
    public function deleteInvoice(string $invoiceId, ?int $version = null): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::DELETE, '/v2/invoices/{invoice_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('invoice_id', $invoiceId), QueryParam::init('version', $version));

        $_resHandler = $this->responseHandler()->type(DeleteInvoiceResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves an invoice by invoice ID.
     *
     * @param string $invoiceId The ID of the invoice to retrieve.
     *
     * @return ApiResponse Response from the API call
     */
    public function getInvoice(string $invoiceId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/invoices/{invoice_id}')
            ->auth('global')
            ->parameters(TemplateParam::init('invoice_id', $invoiceId));

        $_resHandler = $this->responseHandler()->type(GetInvoiceResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Updates an invoice. This endpoint supports sparse updates, so you only need
     * to specify the fields you want to change along with the required `version` field.
     * Some restrictions apply to updating invoices. For example, you cannot change the
     * `order_id` or `location_id` field.
     *
     * @param string $invoiceId The ID of the invoice to update.
     * @param UpdateInvoiceRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function updateInvoice(string $invoiceId, UpdateInvoiceRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::PUT, '/v2/invoices/{invoice_id}')
            ->auth('global')
            ->parameters(
                TemplateParam::init('invoice_id', $invoiceId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UpdateInvoiceResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Uploads a file and attaches it to an invoice. This endpoint accepts HTTP multipart/form-data file
     * uploads
     * with a JSON `request` part and a `file` part. The `file` part must be a `readable stream` that
     * contains a file
     * in a supported format: GIF, JPEG, PNG, TIFF, BMP, or PDF.
     *
     * Invoices can have up to 10 attachments with a total file size of 25 MB. Attachments can be added
     * only to invoices
     * in the `DRAFT`, `SCHEDULED`, `UNPAID`, or `PARTIALLY_PAID` state.
     *
     * @param string $invoiceId The ID of the [invoice](entity:Invoice) to attach the file to.
     * @param CreateInvoiceAttachmentRequest|null $request Represents a
     *        [CreateInvoiceAttachment]($e/Invoices/CreateInvoiceAttachment) request.
     * @param FileWrapper|null $imageFile
     *
     * @return ApiResponse Response from the API call
     */
    public function createInvoiceAttachment(
        string $invoiceId,
        ?CreateInvoiceAttachmentRequest $request = null,
        ?FileWrapper $imageFile = null
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/invoices/{invoice_id}/attachments')
            ->auth('global')
            ->parameters(
                TemplateParam::init('invoice_id', $invoiceId),
                FormParam::init('request', $request)
                    ->encodingHeader('Content-Type', 'application/json; charset=utf-8'),
                FormParam::init('image_file', $imageFile)->encodingHeader('Content-Type', 'image/jpeg')
            );

        $_resHandler = $this->responseHandler()->type(CreateInvoiceAttachmentResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Removes an attachment from an invoice and permanently deletes the file. Attachments can be removed
     * only
     * from invoices in the `DRAFT`, `SCHEDULED`, `UNPAID`, or `PARTIALLY_PAID` state.
     *
     * @param string $invoiceId The ID of the [invoice](entity:Invoice) to delete the attachment
     *        from.
     * @param string $attachmentId The ID of the [attachment](entity:InvoiceAttachment) to delete.
     *
     * @return ApiResponse Response from the API call
     */
    public function deleteInvoiceAttachment(string $invoiceId, string $attachmentId): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(
            RequestMethod::DELETE,
            '/v2/invoices/{invoice_id}/attachments/{attachment_id}'
        )
            ->auth('global')
            ->parameters(
                TemplateParam::init('invoice_id', $invoiceId),
                TemplateParam::init('attachment_id', $attachmentId)
            );

        $_resHandler = $this->responseHandler()->type(DeleteInvoiceAttachmentResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Cancels an invoice. The seller cannot collect payments for
     * the canceled invoice.
     *
     * You cannot cancel an invoice in the `DRAFT` state or in a terminal state: `PAID`, `REFUNDED`,
     * `CANCELED`, or `FAILED`.
     *
     * @param string $invoiceId The ID of the [invoice](entity:Invoice) to cancel.
     * @param CancelInvoiceRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function cancelInvoice(string $invoiceId, CancelInvoiceRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/invoices/{invoice_id}/cancel')
            ->auth('global')
            ->parameters(
                TemplateParam::init('invoice_id', $invoiceId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(CancelInvoiceResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Publishes the specified draft invoice.
     *
     * After an invoice is published, EDD\Vendor\Square
     * follows up based on the invoice configuration. For example, EDD\Vendor\Square
     * sends the invoice to the customer's email address, charges the customer's card on file, or does
     * nothing. EDD\Vendor\Square also makes the invoice available on a Square-hosted invoice page.
     *
     * The invoice `status` also changes from `DRAFT` to a status
     * based on the invoice configuration. For example, the status changes to `UNPAID` if
     * EDD\Vendor\Square emails the invoice or `PARTIALLY_PAID` if EDD\Vendor\Square charges a card on file for a portion of the
     * invoice amount.
     *
     * In addition to the required `ORDERS_WRITE` and `INVOICES_WRITE` permissions, `CUSTOMERS_READ`
     * and `PAYMENTS_WRITE` are required when publishing invoices configured for card-on-file payments.
     *
     * @param string $invoiceId The ID of the invoice to publish.
     * @param PublishInvoiceRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function publishInvoice(string $invoiceId, PublishInvoiceRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/invoices/{invoice_id}/publish')
            ->auth('global')
            ->parameters(
                TemplateParam::init('invoice_id', $invoiceId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(PublishInvoiceResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}

<?php

declare(strict_types=1);

namespace EDD\Vendor\Square\Apis;

use EDD\Vendor\Core\Request\Parameters\BodyParam;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\QueryParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\CoreInterfaces\Core\Request\RequestMethod;
use EDD\Vendor\Square\Http\ApiResponse;
use EDD\Vendor\Square\Models\CreateGiftCardRequest;
use EDD\Vendor\Square\Models\CreateGiftCardResponse;
use EDD\Vendor\Square\Models\LinkCustomerToGiftCardRequest;
use EDD\Vendor\Square\Models\LinkCustomerToGiftCardResponse;
use EDD\Vendor\Square\Models\ListGiftCardsResponse;
use EDD\Vendor\Square\Models\RetrieveGiftCardFromGANRequest;
use EDD\Vendor\Square\Models\RetrieveGiftCardFromGANResponse;
use EDD\Vendor\Square\Models\RetrieveGiftCardFromNonceRequest;
use EDD\Vendor\Square\Models\RetrieveGiftCardFromNonceResponse;
use EDD\Vendor\Square\Models\RetrieveGiftCardResponse;
use EDD\Vendor\Square\Models\UnlinkCustomerFromGiftCardRequest;
use EDD\Vendor\Square\Models\UnlinkCustomerFromGiftCardResponse;

class GiftCardsApi extends BaseApi
{
    /**
     * Lists all gift cards. You can specify optional filters to retrieve
     * a subset of the gift cards. Results are sorted by `created_at` in ascending order.
     *
     * @param string|null $type If a [type](entity:GiftCardType) is provided, the endpoint returns
     *        gift cards of the specified type.
     *        Otherwise, the endpoint returns gift cards of all types.
     * @param string|null $state If a [state](entity:GiftCardStatus) is provided, the endpoint
     *        returns the gift cards in the specified state.
     *        Otherwise, the endpoint returns the gift cards of all states.
     * @param int|null $limit If a limit is provided, the endpoint returns only the specified number
     *        of results per page.
     *        The maximum value is 200. The default value is 30.
     *        For more information, see [Pagination](https://developer.squareup.com/docs/working-
     *        with-apis/pagination).
     * @param string|null $cursor A pagination cursor returned by a previous call to this endpoint.
     *        Provide this cursor to retrieve the next set of results for the original query.
     *        If a cursor is not provided, the endpoint returns the first page of the results.
     *        For more information, see [Pagination](https://developer.squareup.com/docs/working-
     *        with-apis/pagination).
     * @param string|null $customerId If a customer ID is provided, the endpoint returns only the
     *        gift cards linked to the specified customer.
     *
     * @return ApiResponse Response from the API call
     */
    public function listGiftCards(
        ?string $type = null,
        ?string $state = null,
        ?int $limit = null,
        ?string $cursor = null,
        ?string $customerId = null
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/gift-cards')
            ->auth('global')
            ->parameters(
                QueryParam::init('type', $type),
                QueryParam::init('state', $state),
                QueryParam::init('limit', $limit),
                QueryParam::init('cursor', $cursor),
                QueryParam::init('customer_id', $customerId)
            );

        $_resHandler = $this->responseHandler()->type(ListGiftCardsResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Creates a digital gift card or registers a physical (plastic) gift card. The resulting gift card
     * has a `PENDING` state. To activate a gift card so that it can be redeemed for purchases, call
     * [CreateGiftCardActivity]($e/GiftCardActivities/CreateGiftCardActivity) and create an `ACTIVATE`
     * activity with the initial balance. Alternatively, you can use
     * [RefundPayment]($e/Refunds/RefundPayment)
     * to refund a payment to the new gift card.
     *
     * @param CreateGiftCardRequest $body An object containing the fields to POST for the request.
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function createGiftCard(CreateGiftCardRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/gift-cards')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(CreateGiftCardResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a gift card using the gift card account number (GAN).
     *
     * @param RetrieveGiftCardFromGANRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveGiftCardFromGAN(RetrieveGiftCardFromGANRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/gift-cards/from-gan')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(RetrieveGiftCardFromGANResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a gift card using a secure payment token that represents the gift card.
     *
     * @param RetrieveGiftCardFromNonceRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveGiftCardFromNonce(RetrieveGiftCardFromNonceRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/gift-cards/from-nonce')
            ->auth('global')
            ->parameters(HeaderParam::init('Content-Type', 'application/json'), BodyParam::init($body));

        $_resHandler = $this->responseHandler()->type(RetrieveGiftCardFromNonceResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Links a customer to a gift card, which is also referred to as adding a card on file.
     *
     * @param string $giftCardId The ID of the gift card to be linked.
     * @param LinkCustomerToGiftCardRequest $body An object containing the fields to POST for the
     *        request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function linkCustomerToGiftCard(string $giftCardId, LinkCustomerToGiftCardRequest $body): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/gift-cards/{gift_card_id}/link-customer')
            ->auth('global')
            ->parameters(
                TemplateParam::init('gift_card_id', $giftCardId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(LinkCustomerToGiftCardResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Unlinks a customer from a gift card, which is also referred to as removing a card on file.
     *
     * @param string $giftCardId The ID of the gift card to be unlinked.
     * @param UnlinkCustomerFromGiftCardRequest $body An object containing the fields to POST for
     *        the request.
     *
     *        See the corresponding object definition for field details.
     *
     * @return ApiResponse Response from the API call
     */
    public function unlinkCustomerFromGiftCard(
        string $giftCardId,
        UnlinkCustomerFromGiftCardRequest $body
    ): ApiResponse {
        $_reqBuilder = $this->requestBuilder(RequestMethod::POST, '/v2/gift-cards/{gift_card_id}/unlink-customer')
            ->auth('global')
            ->parameters(
                TemplateParam::init('gift_card_id', $giftCardId),
                HeaderParam::init('Content-Type', 'application/json'),
                BodyParam::init($body)
            );

        $_resHandler = $this->responseHandler()->type(UnlinkCustomerFromGiftCardResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }

    /**
     * Retrieves a gift card using the gift card ID.
     *
     * @param string $id The ID of the gift card to retrieve.
     *
     * @return ApiResponse Response from the API call
     */
    public function retrieveGiftCard(string $id): ApiResponse
    {
        $_reqBuilder = $this->requestBuilder(RequestMethod::GET, '/v2/gift-cards/{id}')
            ->auth('global')
            ->parameters(TemplateParam::init('id', $id));

        $_resHandler = $this->responseHandler()->type(RetrieveGiftCardResponse::class)->returnApiResponse();

        return $this->execute($_reqBuilder, $_resHandler);
    }
}

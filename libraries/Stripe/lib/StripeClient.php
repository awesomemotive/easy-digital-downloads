<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe;

/**
 * Client used to send requests to Stripe's API.
 *
 * @property \EDD\Vendor\Stripe\Service\AccountLinkService $accountLinks
 * @property \EDD\Vendor\Stripe\Service\AccountService $accounts
 * @property \EDD\Vendor\Stripe\Service\ApplePayDomainService $applePayDomains
 * @property \EDD\Vendor\Stripe\Service\ApplicationFeeService $applicationFees
 * @property \EDD\Vendor\Stripe\Service\BalanceService $balance
 * @property \EDD\Vendor\Stripe\Service\BalanceTransactionService $balanceTransactions
 * @property \EDD\Vendor\Stripe\Service\BillingPortal\BillingPortalServiceFactory $billingPortal
 * @property \EDD\Vendor\Stripe\Service\ChargeService $charges
 * @property \EDD\Vendor\Stripe\Service\Checkout\CheckoutServiceFactory $checkout
 * @property \EDD\Vendor\Stripe\Service\CountrySpecService $countrySpecs
 * @property \EDD\Vendor\Stripe\Service\CouponService $coupons
 * @property \EDD\Vendor\Stripe\Service\CreditNoteService $creditNotes
 * @property \EDD\Vendor\Stripe\Service\CustomerService $customers
 * @property \EDD\Vendor\Stripe\Service\DisputeService $disputes
 * @property \EDD\Vendor\Stripe\Service\EphemeralKeyService $ephemeralKeys
 * @property \EDD\Vendor\Stripe\Service\EventService $events
 * @property \EDD\Vendor\Stripe\Service\ExchangeRateService $exchangeRates
 * @property \EDD\Vendor\Stripe\Service\FileLinkService $fileLinks
 * @property \EDD\Vendor\Stripe\Service\FileService $files
 * @property \EDD\Vendor\Stripe\Service\FinancialConnections\FinancialConnectionsServiceFactory $financialConnections
 * @property \EDD\Vendor\Stripe\Service\Identity\IdentityServiceFactory $identity
 * @property \EDD\Vendor\Stripe\Service\InvoiceItemService $invoiceItems
 * @property \EDD\Vendor\Stripe\Service\InvoiceService $invoices
 * @property \EDD\Vendor\Stripe\Service\Issuing\IssuingServiceFactory $issuing
 * @property \EDD\Vendor\Stripe\Service\MandateService $mandates
 * @property \EDD\Vendor\Stripe\Service\OAuthService $oauth
 * @property \EDD\Vendor\Stripe\Service\OrderReturnService $orderReturns
 * @property \EDD\Vendor\Stripe\Service\OrderService $orders
 * @property \EDD\Vendor\Stripe\Service\PaymentIntentService $paymentIntents
 * @property \EDD\Vendor\Stripe\Service\PaymentLinkService $paymentLinks
 * @property \EDD\Vendor\Stripe\Service\PaymentMethodService $paymentMethods
 * @property \EDD\Vendor\Stripe\Service\PayoutService $payouts
 * @property \EDD\Vendor\Stripe\Service\PlanService $plans
 * @property \EDD\Vendor\Stripe\Service\PriceService $prices
 * @property \EDD\Vendor\Stripe\Service\ProductService $products
 * @property \EDD\Vendor\Stripe\Service\PromotionCodeService $promotionCodes
 * @property \EDD\Vendor\Stripe\Service\QuoteService $quotes
 * @property \EDD\Vendor\Stripe\Service\Radar\RadarServiceFactory $radar
 * @property \EDD\Vendor\Stripe\Service\RefundService $refunds
 * @property \EDD\Vendor\Stripe\Service\Reporting\ReportingServiceFactory $reporting
 * @property \EDD\Vendor\Stripe\Service\ReviewService $reviews
 * @property \EDD\Vendor\Stripe\Service\SetupAttemptService $setupAttempts
 * @property \EDD\Vendor\Stripe\Service\SetupIntentService $setupIntents
 * @property \EDD\Vendor\Stripe\Service\ShippingRateService $shippingRates
 * @property \EDD\Vendor\Stripe\Service\Sigma\SigmaServiceFactory $sigma
 * @property \EDD\Vendor\Stripe\Service\SkuService $skus
 * @property \EDD\Vendor\Stripe\Service\SourceService $sources
 * @property \EDD\Vendor\Stripe\Service\SubscriptionItemService $subscriptionItems
 * @property \EDD\Vendor\Stripe\Service\SubscriptionScheduleService $subscriptionSchedules
 * @property \EDD\Vendor\Stripe\Service\SubscriptionService $subscriptions
 * @property \EDD\Vendor\Stripe\Service\TaxCodeService $taxCodes
 * @property \EDD\Vendor\Stripe\Service\TaxRateService $taxRates
 * @property \EDD\Vendor\Stripe\Service\Terminal\TerminalServiceFactory $terminal
 * @property \EDD\Vendor\Stripe\Service\TestHelpers\TestHelpersServiceFactory $testHelpers
 * @property \EDD\Vendor\Stripe\Service\TokenService $tokens
 * @property \EDD\Vendor\Stripe\Service\TopupService $topups
 * @property \EDD\Vendor\Stripe\Service\TransferService $transfers
 * @property \EDD\Vendor\Stripe\Service\WebhookEndpointService $webhookEndpoints
 */
class StripeClient extends BaseStripeClient
{
    /**
     * @var \EDD\Vendor\Stripe\Service\CoreServiceFactory
     */
    private $coreServiceFactory;

    public function __get($name)
    {
        if (null === $this->coreServiceFactory) {
            $this->coreServiceFactory = new \EDD\Vendor\Stripe\Service\CoreServiceFactory($this);
        }

        return $this->coreServiceFactory->__get($name);
    }
}

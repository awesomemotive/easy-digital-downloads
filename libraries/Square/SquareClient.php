<?php

declare(strict_types=1);

namespace EDD\Vendor\Square;

use EDD\Vendor\Core\ClientBuilder;
use EDD\Vendor\Core\Request\Parameters\AdditionalHeaderParams;
use EDD\Vendor\Core\Request\Parameters\HeaderParam;
use EDD\Vendor\Core\Request\Parameters\TemplateParam;
use EDD\Vendor\Core\Utils\CoreHelper;
use EDD\Vendor\Square\Apis\ApplePayApi;
use EDD\Vendor\Square\Apis\BankAccountsApi;
use EDD\Vendor\Square\Apis\BookingCustomAttributesApi;
use EDD\Vendor\Square\Apis\BookingsApi;
use EDD\Vendor\Square\Apis\CardsApi;
use EDD\Vendor\Square\Apis\CashDrawersApi;
use EDD\Vendor\Square\Apis\CatalogApi;
use EDD\Vendor\Square\Apis\CheckoutApi;
use EDD\Vendor\Square\Apis\CustomerCustomAttributesApi;
use EDD\Vendor\Square\Apis\CustomerGroupsApi;
use EDD\Vendor\Square\Apis\CustomersApi;
use EDD\Vendor\Square\Apis\CustomerSegmentsApi;
use EDD\Vendor\Square\Apis\DevicesApi;
use EDD\Vendor\Square\Apis\DisputesApi;
use EDD\Vendor\Square\Apis\EmployeesApi;
use EDD\Vendor\Square\Apis\EventsApi;
use EDD\Vendor\Square\Apis\GiftCardActivitiesApi;
use EDD\Vendor\Square\Apis\GiftCardsApi;
use EDD\Vendor\Square\Apis\InventoryApi;
use EDD\Vendor\Square\Apis\InvoicesApi;
use EDD\Vendor\Square\Apis\LaborApi;
use EDD\Vendor\Square\Apis\LocationCustomAttributesApi;
use EDD\Vendor\Square\Apis\LocationsApi;
use EDD\Vendor\Square\Apis\LoyaltyApi;
use EDD\Vendor\Square\Apis\MerchantCustomAttributesApi;
use EDD\Vendor\Square\Apis\MerchantsApi;
use EDD\Vendor\Square\Apis\MobileAuthorizationApi;
use EDD\Vendor\Square\Apis\OAuthApi;
use EDD\Vendor\Square\Apis\OrderCustomAttributesApi;
use EDD\Vendor\Square\Apis\OrdersApi;
use EDD\Vendor\Square\Apis\PaymentsApi;
use EDD\Vendor\Square\Apis\PayoutsApi;
use EDD\Vendor\Square\Apis\RefundsApi;
use EDD\Vendor\Square\Apis\SitesApi;
use EDD\Vendor\Square\Apis\SnippetsApi;
use EDD\Vendor\Square\Apis\SubscriptionsApi;
use EDD\Vendor\Square\Apis\TeamApi;
use EDD\Vendor\Square\Apis\TerminalApi;
use EDD\Vendor\Square\Apis\TransactionsApi;
use EDD\Vendor\Square\Apis\V1TransactionsApi;
use EDD\Vendor\Square\Apis\VendorsApi;
use EDD\Vendor\Square\Apis\WebhookSubscriptionsApi;
use EDD\Vendor\Square\Authentication\BearerAuthCredentialsBuilder;
use EDD\Vendor\Square\Authentication\BearerAuthManager;
use EDD\Vendor\Square\Utils\CompatibilityConverter;
use EDD\Vendor\Unirest\Configuration;
use EDD\Vendor\Unirest\HttpClient;

class SquareClient implements ConfigurationInterface
{
    private $mobileAuthorization;

    private $oAuth;

    private $v1Transactions;

    private $applePay;

    private $bankAccounts;

    private $bookings;

    private $bookingCustomAttributes;

    private $cards;

    private $cashDrawers;

    private $catalog;

    private $customers;

    private $customerCustomAttributes;

    private $customerGroups;

    private $customerSegments;

    private $devices;

    private $disputes;

    private $employees;

    private $events;

    private $giftCards;

    private $giftCardActivities;

    private $inventory;

    private $invoices;

    private $labor;

    private $locations;

    private $locationCustomAttributes;

    private $checkout;

    private $transactions;

    private $loyalty;

    private $merchants;

    private $merchantCustomAttributes;

    private $orders;

    private $orderCustomAttributes;

    private $payments;

    private $payouts;

    private $refunds;

    private $sites;

    private $snippets;

    private $subscriptions;

    private $team;

    private $terminal;

    private $vendors;

    private $webhookSubscriptions;

    private $bearerAuthManager;

    private $config;

    private $client;

    /**
     * @see SquareClientBuilder::init()
     * @see SquareClientBuilder::build()
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        $this->config = array_merge(ConfigurationDefaults::_ALL, CoreHelper::clone($config));
        $this->bearerAuthManager = new BearerAuthManager($this->config);
        $this->validateConfig();
        $this->client = ClientBuilder::init(new HttpClient(Configuration::init($this)))
            ->converter(new CompatibilityConverter())
            ->jsonHelper(ApiHelper::getJsonHelper())
            ->apiCallback($this->config['httpCallback'] ?? null)
            ->userAgent(
                'Square-PHP-SDK/40.0.0.20250123 ({api-version}) {engine}/{engine-version} ({os-' .
                'info}) {detail}'
            )
            ->userAgentConfig(
                [
                    '{api-version}' => $this->getSquareVersion(),
                    '{detail}' => rawurlencode($this->getUserAgentDetail())
                ]
            )
            ->globalConfig($this->getGlobalConfiguration())
            ->globalRuntimeParam(AdditionalHeaderParams::init($this->getAdditionalHeaders()))
            ->serverUrls(self::ENVIRONMENT_MAP[$this->getEnvironment()], Server::DEFAULT_)
            ->authManagers(['global' => $this->bearerAuthManager])
            ->build();
    }

    /**
     * Create a builder with the current client's configurations.
     *
     * @return SquareClientBuilder SquareClientBuilder instance
     */
    public function toBuilder(): SquareClientBuilder
    {
        $builder = SquareClientBuilder::init()
            ->timeout($this->getTimeout())
            ->enableRetries($this->shouldEnableRetries())
            ->numberOfRetries($this->getNumberOfRetries())
            ->retryInterval($this->getRetryInterval())
            ->backOffFactor($this->getBackOffFactor())
            ->maximumRetryWaitTime($this->getMaximumRetryWaitTime())
            ->retryOnTimeout($this->shouldRetryOnTimeout())
            ->httpStatusCodesToRetry($this->getHttpStatusCodesToRetry())
            ->httpMethodsToRetry($this->getHttpMethodsToRetry())
            ->squareVersion($this->getSquareVersion())
            ->additionalHeaders($this->getAdditionalHeaders())
            ->userAgentDetail($this->getUserAgentDetail())
            ->environment($this->getEnvironment())
            ->customUrl($this->getCustomUrl())
            ->httpCallback($this->config['httpCallback'] ?? null);

        $bearerAuth = $this->getBearerAuthCredentialsBuilder();
        if ($bearerAuth != null) {
            $builder->bearerAuthCredentials($bearerAuth);
        }
        return $builder;
    }

    public function getTimeout(): int
    {
        return $this->config['timeout'] ?? ConfigurationDefaults::TIMEOUT;
    }

    public function shouldEnableRetries(): bool
    {
        return $this->config['enableRetries'] ?? ConfigurationDefaults::ENABLE_RETRIES;
    }

    public function getNumberOfRetries(): int
    {
        return $this->config['numberOfRetries'] ?? ConfigurationDefaults::NUMBER_OF_RETRIES;
    }

    public function getRetryInterval(): float
    {
        return $this->config['retryInterval'] ?? ConfigurationDefaults::RETRY_INTERVAL;
    }

    public function getBackOffFactor(): float
    {
        return $this->config['backOffFactor'] ?? ConfigurationDefaults::BACK_OFF_FACTOR;
    }

    public function getMaximumRetryWaitTime(): int
    {
        return $this->config['maximumRetryWaitTime'] ?? ConfigurationDefaults::MAXIMUM_RETRY_WAIT_TIME;
    }

    public function shouldRetryOnTimeout(): bool
    {
        return $this->config['retryOnTimeout'] ?? ConfigurationDefaults::RETRY_ON_TIMEOUT;
    }

    public function getHttpStatusCodesToRetry(): array
    {
        return $this->config['httpStatusCodesToRetry'] ?? ConfigurationDefaults::HTTP_STATUS_CODES_TO_RETRY;
    }

    public function getHttpMethodsToRetry(): array
    {
        return $this->config['httpMethodsToRetry'] ?? ConfigurationDefaults::HTTP_METHODS_TO_RETRY;
    }

    public function getSquareVersion(): string
    {
        return $this->config['squareVersion'] ?? ConfigurationDefaults::SQUARE_VERSION;
    }

    public function getAdditionalHeaders(): array
    {
        return $this->config['additionalHeaders'] ?? ConfigurationDefaults::ADDITIONAL_HEADERS;
    }

    public function getUserAgentDetail(): string
    {
        return $this->config['userAgentDetail'] ?? ConfigurationDefaults::USER_AGENT_DETAIL;
    }

    public function getEnvironment(): string
    {
        return $this->config['environment'] ?? ConfigurationDefaults::ENVIRONMENT;
    }

    public function getCustomUrl(): string
    {
        return $this->config['customUrl'] ?? ConfigurationDefaults::CUSTOM_URL;
    }

    public function getBearerAuthCredentials(): BearerAuthCredentials
    {
        return $this->bearerAuthManager;
    }

    public function getBearerAuthCredentialsBuilder(): ?BearerAuthCredentialsBuilder
    {
        if (empty($this->bearerAuthManager->getAccessToken())) {
            return null;
        }
        return BearerAuthCredentialsBuilder::init($this->bearerAuthManager->getAccessToken());
    }

    /**
     * Get the client configuration as an associative array
     *
     * @see SquareClientBuilder::getConfiguration()
     */
    public function getConfiguration(): array
    {
        return $this->toBuilder()->getConfiguration();
    }

    /**
     * Clone this client and override given configuration options
     *
     * @see SquareClientBuilder::build()
     */
    public function withConfiguration(array $config): self
    {
        return new self(array_merge($this->config, $config));
    }

    /**
     * Get current SDK version
     */
    public function getSdkVersion(): string
    {
        return '40.0.0.20250123';
    }

    /**
     * Validate required configuration variables
     */
    private function validateConfig(): void
    {
        SquareClientBuilder::init()
            ->additionalHeaders($this->getAdditionalHeaders())
            ->userAgentDetail($this->getUserAgentDetail());
    }

    /**
     * Get the base uri for a given server in the current environment.
     *
     * @param string $server Server name
     *
     * @return string Base URI
     */
    public function getBaseUri(string $server = Server::DEFAULT_): string
    {
        return $this->client->getGlobalRequest($server)->getQueryUrl();
    }

    /**
     * Returns Mobile Authorization Api
     */
    public function getMobileAuthorizationApi(): MobileAuthorizationApi
    {
        if ($this->mobileAuthorization == null) {
            $this->mobileAuthorization = new MobileAuthorizationApi($this->client);
        }
        return $this->mobileAuthorization;
    }

    /**
     * Returns O Auth Api
     */
    public function getOAuthApi(): OAuthApi
    {
        if ($this->oAuth == null) {
            $this->oAuth = new OAuthApi($this->client);
        }
        return $this->oAuth;
    }

    /**
     * Returns V1 Transactions Api
     */
    public function getV1TransactionsApi(): V1TransactionsApi
    {
        if ($this->v1Transactions == null) {
            $this->v1Transactions = new V1TransactionsApi($this->client);
        }
        return $this->v1Transactions;
    }

    /**
     * Returns Apple Pay Api
     */
    public function getApplePayApi(): ApplePayApi
    {
        if ($this->applePay == null) {
            $this->applePay = new ApplePayApi($this->client);
        }
        return $this->applePay;
    }

    /**
     * Returns Bank Accounts Api
     */
    public function getBankAccountsApi(): BankAccountsApi
    {
        if ($this->bankAccounts == null) {
            $this->bankAccounts = new BankAccountsApi($this->client);
        }
        return $this->bankAccounts;
    }

    /**
     * Returns Bookings Api
     */
    public function getBookingsApi(): BookingsApi
    {
        if ($this->bookings == null) {
            $this->bookings = new BookingsApi($this->client);
        }
        return $this->bookings;
    }

    /**
     * Returns Booking Custom Attributes Api
     */
    public function getBookingCustomAttributesApi(): BookingCustomAttributesApi
    {
        if ($this->bookingCustomAttributes == null) {
            $this->bookingCustomAttributes = new BookingCustomAttributesApi($this->client);
        }
        return $this->bookingCustomAttributes;
    }

    /**
     * Returns Cards Api
     */
    public function getCardsApi(): CardsApi
    {
        if ($this->cards == null) {
            $this->cards = new CardsApi($this->client);
        }
        return $this->cards;
    }

    /**
     * Returns Cash Drawers Api
     */
    public function getCashDrawersApi(): CashDrawersApi
    {
        if ($this->cashDrawers == null) {
            $this->cashDrawers = new CashDrawersApi($this->client);
        }
        return $this->cashDrawers;
    }

    /**
     * Returns Catalog Api
     */
    public function getCatalogApi(): CatalogApi
    {
        if ($this->catalog == null) {
            $this->catalog = new CatalogApi($this->client);
        }
        return $this->catalog;
    }

    /**
     * Returns Customers Api
     */
    public function getCustomersApi(): CustomersApi
    {
        if ($this->customers == null) {
            $this->customers = new CustomersApi($this->client);
        }
        return $this->customers;
    }

    /**
     * Returns Customer Custom Attributes Api
     */
    public function getCustomerCustomAttributesApi(): CustomerCustomAttributesApi
    {
        if ($this->customerCustomAttributes == null) {
            $this->customerCustomAttributes = new CustomerCustomAttributesApi($this->client);
        }
        return $this->customerCustomAttributes;
    }

    /**
     * Returns Customer Groups Api
     */
    public function getCustomerGroupsApi(): CustomerGroupsApi
    {
        if ($this->customerGroups == null) {
            $this->customerGroups = new CustomerGroupsApi($this->client);
        }
        return $this->customerGroups;
    }

    /**
     * Returns Customer Segments Api
     */
    public function getCustomerSegmentsApi(): CustomerSegmentsApi
    {
        if ($this->customerSegments == null) {
            $this->customerSegments = new CustomerSegmentsApi($this->client);
        }
        return $this->customerSegments;
    }

    /**
     * Returns Devices Api
     */
    public function getDevicesApi(): DevicesApi
    {
        if ($this->devices == null) {
            $this->devices = new DevicesApi($this->client);
        }
        return $this->devices;
    }

    /**
     * Returns Disputes Api
     */
    public function getDisputesApi(): DisputesApi
    {
        if ($this->disputes == null) {
            $this->disputes = new DisputesApi($this->client);
        }
        return $this->disputes;
    }

    /**
     * Returns Employees Api
     */
    public function getEmployeesApi(): EmployeesApi
    {
        if ($this->employees == null) {
            $this->employees = new EmployeesApi($this->client);
        }
        return $this->employees;
    }

    /**
     * Returns Events Api
     */
    public function getEventsApi(): EventsApi
    {
        if ($this->events == null) {
            $this->events = new EventsApi($this->client);
        }
        return $this->events;
    }

    /**
     * Returns Gift Cards Api
     */
    public function getGiftCardsApi(): GiftCardsApi
    {
        if ($this->giftCards == null) {
            $this->giftCards = new GiftCardsApi($this->client);
        }
        return $this->giftCards;
    }

    /**
     * Returns Gift Card Activities Api
     */
    public function getGiftCardActivitiesApi(): GiftCardActivitiesApi
    {
        if ($this->giftCardActivities == null) {
            $this->giftCardActivities = new GiftCardActivitiesApi($this->client);
        }
        return $this->giftCardActivities;
    }

    /**
     * Returns Inventory Api
     */
    public function getInventoryApi(): InventoryApi
    {
        if ($this->inventory == null) {
            $this->inventory = new InventoryApi($this->client);
        }
        return $this->inventory;
    }

    /**
     * Returns Invoices Api
     */
    public function getInvoicesApi(): InvoicesApi
    {
        if ($this->invoices == null) {
            $this->invoices = new InvoicesApi($this->client);
        }
        return $this->invoices;
    }

    /**
     * Returns Labor Api
     */
    public function getLaborApi(): LaborApi
    {
        if ($this->labor == null) {
            $this->labor = new LaborApi($this->client);
        }
        return $this->labor;
    }

    /**
     * Returns Locations Api
     */
    public function getLocationsApi(): LocationsApi
    {
        if ($this->locations == null) {
            $this->locations = new LocationsApi($this->client);
        }
        return $this->locations;
    }

    /**
     * Returns Location Custom Attributes Api
     */
    public function getLocationCustomAttributesApi(): LocationCustomAttributesApi
    {
        if ($this->locationCustomAttributes == null) {
            $this->locationCustomAttributes = new LocationCustomAttributesApi($this->client);
        }
        return $this->locationCustomAttributes;
    }

    /**
     * Returns Checkout Api
     */
    public function getCheckoutApi(): CheckoutApi
    {
        if ($this->checkout == null) {
            $this->checkout = new CheckoutApi($this->client);
        }
        return $this->checkout;
    }

    /**
     * Returns Transactions Api
     */
    public function getTransactionsApi(): TransactionsApi
    {
        if ($this->transactions == null) {
            $this->transactions = new TransactionsApi($this->client);
        }
        return $this->transactions;
    }

    /**
     * Returns Loyalty Api
     */
    public function getLoyaltyApi(): LoyaltyApi
    {
        if ($this->loyalty == null) {
            $this->loyalty = new LoyaltyApi($this->client);
        }
        return $this->loyalty;
    }

    /**
     * Returns Merchants Api
     */
    public function getMerchantsApi(): MerchantsApi
    {
        if ($this->merchants == null) {
            $this->merchants = new MerchantsApi($this->client);
        }
        return $this->merchants;
    }

    /**
     * Returns Merchant Custom Attributes Api
     */
    public function getMerchantCustomAttributesApi(): MerchantCustomAttributesApi
    {
        if ($this->merchantCustomAttributes == null) {
            $this->merchantCustomAttributes = new MerchantCustomAttributesApi($this->client);
        }
        return $this->merchantCustomAttributes;
    }

    /**
     * Returns Orders Api
     */
    public function getOrdersApi(): OrdersApi
    {
        if ($this->orders == null) {
            $this->orders = new OrdersApi($this->client);
        }
        return $this->orders;
    }

    /**
     * Returns Order Custom Attributes Api
     */
    public function getOrderCustomAttributesApi(): OrderCustomAttributesApi
    {
        if ($this->orderCustomAttributes == null) {
            $this->orderCustomAttributes = new OrderCustomAttributesApi($this->client);
        }
        return $this->orderCustomAttributes;
    }

    /**
     * Returns Payments Api
     */
    public function getPaymentsApi(): PaymentsApi
    {
        if ($this->payments == null) {
            $this->payments = new PaymentsApi($this->client);
        }
        return $this->payments;
    }

    /**
     * Returns Payouts Api
     */
    public function getPayoutsApi(): PayoutsApi
    {
        if ($this->payouts == null) {
            $this->payouts = new PayoutsApi($this->client);
        }
        return $this->payouts;
    }

    /**
     * Returns Refunds Api
     */
    public function getRefundsApi(): RefundsApi
    {
        if ($this->refunds == null) {
            $this->refunds = new RefundsApi($this->client);
        }
        return $this->refunds;
    }

    /**
     * Returns Sites Api
     */
    public function getSitesApi(): SitesApi
    {
        if ($this->sites == null) {
            $this->sites = new SitesApi($this->client);
        }
        return $this->sites;
    }

    /**
     * Returns Snippets Api
     */
    public function getSnippetsApi(): SnippetsApi
    {
        if ($this->snippets == null) {
            $this->snippets = new SnippetsApi($this->client);
        }
        return $this->snippets;
    }

    /**
     * Returns Subscriptions Api
     */
    public function getSubscriptionsApi(): SubscriptionsApi
    {
        if ($this->subscriptions == null) {
            $this->subscriptions = new SubscriptionsApi($this->client);
        }
        return $this->subscriptions;
    }

    /**
     * Returns Team Api
     */
    public function getTeamApi(): TeamApi
    {
        if ($this->team == null) {
            $this->team = new TeamApi($this->client);
        }
        return $this->team;
    }

    /**
     * Returns Terminal Api
     */
    public function getTerminalApi(): TerminalApi
    {
        if ($this->terminal == null) {
            $this->terminal = new TerminalApi($this->client);
        }
        return $this->terminal;
    }

    /**
     * Returns Vendors Api
     */
    public function getVendorsApi(): VendorsApi
    {
        if ($this->vendors == null) {
            $this->vendors = new VendorsApi($this->client);
        }
        return $this->vendors;
    }

    /**
     * Returns Webhook Subscriptions Api
     */
    public function getWebhookSubscriptionsApi(): WebhookSubscriptionsApi
    {
        if ($this->webhookSubscriptions == null) {
            $this->webhookSubscriptions = new WebhookSubscriptionsApi($this->client);
        }
        return $this->webhookSubscriptions;
    }

    /**
     * Get the defined global configurations
     */
    private function getGlobalConfiguration(): array
    {
        return [
            TemplateParam::init('custom_url', $this->getCustomUrl())->dontEncode(),
            HeaderParam::init('Square-Version', $this->getSquareVersion())
        ];
    }

    /**
     * A map of all base urls used in different environments and servers
     *
     * @var array
     */
    private const ENVIRONMENT_MAP = [
        Environment::PRODUCTION => [Server::DEFAULT_ => 'https://connect.squareup.com'],
        Environment::SANDBOX => [Server::DEFAULT_ => 'https://connect.squareupsandbox.com'],
        Environment::CUSTOM => [Server::DEFAULT_ => '{custom_url}']
    ];
}

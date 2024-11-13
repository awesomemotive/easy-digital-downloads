<?php

// File generated from our OpenAPI spec

namespace EDD\Vendor\Stripe;

/**
 * PaymentMethodConfigurations control which payment methods are displayed to your customers when you don't explicitly specify payment method types. You can have multiple configurations with different sets of payment methods for different scenarios.
 *
 * There are two types of PaymentMethodConfigurations. Which is used depends on the <a href="https://stripe.com/docs/connect/charges">charge type</a>:
 *
 * <strong>Direct</strong> configurations apply to payments created on your account, including Connect destination charges, Connect separate charges and transfers, and payments not involving Connect.
 *
 * <strong>Child</strong> configurations apply to payments created on your connected accounts using direct charges, and charges with the on_behalf_of parameter.
 *
 * Child configurations have a <code>parent</code> that sets default values and controls which settings connected accounts may override. You can specify a parent ID at payment time, and EDD\Vendor\Stripe will automatically resolve the connected account’s associated child configuration. Parent configurations are <a href="https://dashboard.stripe.com/settings/payment_methods/connected_accounts">managed in the dashboard</a> and are not available in this API.
 *
 * Related guides:
 * - <a href="https://stripe.com/docs/connect/payment-method-configurations">Payment Method Configurations API</a>
 * - <a href="https://stripe.com/docs/payments/multiple-payment-method-configs">Multiple configurations on dynamic payment methods</a>
 * - <a href="https://stripe.com/docs/connect/multiple-payment-method-configurations">Multiple configurations for your Connect accounts</a>
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
 * @property null|\EDD\Vendor\Stripe\StripeObject $acss_debit
 * @property bool $active Whether the configuration can be used for new payments.
 * @property null|\EDD\Vendor\Stripe\StripeObject $affirm
 * @property null|\EDD\Vendor\Stripe\StripeObject $afterpay_clearpay
 * @property null|\EDD\Vendor\Stripe\StripeObject $alipay
 * @property null|\EDD\Vendor\Stripe\StripeObject $amazon_pay
 * @property null|\EDD\Vendor\Stripe\StripeObject $apple_pay
 * @property null|string $application For child configs, the Connect application associated with the configuration.
 * @property null|\EDD\Vendor\Stripe\StripeObject $au_becs_debit
 * @property null|\EDD\Vendor\Stripe\StripeObject $bacs_debit
 * @property null|\EDD\Vendor\Stripe\StripeObject $bancontact
 * @property null|\EDD\Vendor\Stripe\StripeObject $blik
 * @property null|\EDD\Vendor\Stripe\StripeObject $boleto
 * @property null|\EDD\Vendor\Stripe\StripeObject $card
 * @property null|\EDD\Vendor\Stripe\StripeObject $cartes_bancaires
 * @property null|\EDD\Vendor\Stripe\StripeObject $cashapp
 * @property null|\EDD\Vendor\Stripe\StripeObject $customer_balance
 * @property null|\EDD\Vendor\Stripe\StripeObject $eps
 * @property null|\EDD\Vendor\Stripe\StripeObject $fpx
 * @property null|\EDD\Vendor\Stripe\StripeObject $giropay
 * @property null|\EDD\Vendor\Stripe\StripeObject $google_pay
 * @property null|\EDD\Vendor\Stripe\StripeObject $grabpay
 * @property null|\EDD\Vendor\Stripe\StripeObject $ideal
 * @property bool $is_default The default configuration is used whenever a payment method configuration is not specified.
 * @property null|\EDD\Vendor\Stripe\StripeObject $jcb
 * @property null|\EDD\Vendor\Stripe\StripeObject $klarna
 * @property null|\EDD\Vendor\Stripe\StripeObject $konbini
 * @property null|\EDD\Vendor\Stripe\StripeObject $link
 * @property bool $livemode Has the value <code>true</code> if the object exists in live mode or the value <code>false</code> if the object exists in test mode.
 * @property null|\EDD\Vendor\Stripe\StripeObject $mobilepay
 * @property null|\EDD\Vendor\Stripe\StripeObject $multibanco
 * @property string $name The configuration's name.
 * @property null|\EDD\Vendor\Stripe\StripeObject $oxxo
 * @property null|\EDD\Vendor\Stripe\StripeObject $p24
 * @property null|string $parent For child configs, the configuration's parent configuration.
 * @property null|\EDD\Vendor\Stripe\StripeObject $paynow
 * @property null|\EDD\Vendor\Stripe\StripeObject $paypal
 * @property null|\EDD\Vendor\Stripe\StripeObject $promptpay
 * @property null|\EDD\Vendor\Stripe\StripeObject $revolut_pay
 * @property null|\EDD\Vendor\Stripe\StripeObject $sepa_debit
 * @property null|\EDD\Vendor\Stripe\StripeObject $sofort
 * @property null|\EDD\Vendor\Stripe\StripeObject $swish
 * @property null|\EDD\Vendor\Stripe\StripeObject $twint
 * @property null|\EDD\Vendor\Stripe\StripeObject $us_bank_account
 * @property null|\EDD\Vendor\Stripe\StripeObject $wechat_pay
 * @property null|\EDD\Vendor\Stripe\StripeObject $zip
 */
class PaymentMethodConfiguration extends ApiResource
{
    const OBJECT_NAME = 'payment_method_configuration';

    use ApiOperations\Update;

    /**
     * Creates a payment method configuration.
     *
     * @param null|array $params
     * @param null|array|string $options
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\PaymentMethodConfiguration the created resource
     */
    public static function create($params = null, $options = null)
    {
        self::_validateParams($params);
        $url = static::classUrl();

        list($response, $opts) = static::_staticRequest('post', $url, $params, $options);
        $obj = \EDD\Vendor\Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        $obj->setLastResponse($response);

        return $obj;
    }

    /**
     * List payment method configurations.
     *
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\Collection<\EDD\Vendor\Stripe\PaymentMethodConfiguration> of ApiResources
     */
    public static function all($params = null, $opts = null)
    {
        $url = static::classUrl();

        return static::_requestPage($url, \EDD\Vendor\Stripe\Collection::class, $params, $opts);
    }

    /**
     * Retrieve payment method configuration.
     *
     * @param array|string $id the ID of the API resource to retrieve, or an options array containing an `id` key
     * @param null|array|string $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\PaymentMethodConfiguration
     */
    public static function retrieve($id, $opts = null)
    {
        $opts = \EDD\Vendor\Stripe\Util\RequestOptions::parse($opts);
        $instance = new static($id, $opts);
        $instance->refresh();

        return $instance;
    }

    /**
     * Update payment method configuration.
     *
     * @param string $id the ID of the resource to update
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \EDD\Vendor\Stripe\Exception\ApiErrorException if the request fails
     *
     * @return \EDD\Vendor\Stripe\PaymentMethodConfiguration the updated resource
     */
    public static function update($id, $params = null, $opts = null)
    {
        self::_validateParams($params);
        $url = static::resourceUrl($id);

        list($response, $opts) = static::_staticRequest('post', $url, $params, $opts);
        $obj = \EDD\Vendor\Stripe\Util\Util::convertToStripeObject($response->json, $opts);
        $obj->setLastResponse($response);

        return $obj;
    }
}

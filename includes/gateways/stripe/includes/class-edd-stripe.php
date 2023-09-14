<?php
/**
 * Main plugin class.
 *
 * @package EDD_Stripe
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_Stripe class.
 *
 * @since 2.6.0
 */
class EDD_Stripe {

	/**
	 * Singleton instance.
	 *
	 * @since 2.6.0
	 * @var EDD_Stripe
	 */
	private static $instance;

	/**
	 * Rate limiting component.
	 *
	 * @since 2.6.19
	 * @var EDD_Stripe_Rate_Limiting
	 */
	public $rate_limiting;

	/**
	 * Has Regional Support.
	 *
	 * @since 2.9.2.2
	 * @var bool
	 */
	public $has_regional_support;

	/**
	 * Regional Support class.
	 *
	 * @since 2.9.2.2
	 * @var EDD_Stripe_Country_Base
	 */
	public $regional_support;

	/**
	 * Stripe Connect status class.
	 *
	 * @since 2.9.3
	 * @var \EDD\Stripe\Connect
	 */
	public $connect;

	/**
	 * The application fee class.
	 *
	 * @var \EDD\Gateways\Stripe\ApplicationFee
	 */
	public $application_fee;

	/**
	 * Instantiates or returns the singleton instance.
	 *
	 * @since 2.6.0
	 *
	 * @return EDD_Stripe
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Stripe ) ) {
			self::$instance = new EDD_Stripe();
			self::$instance->includes();
			self::$instance->setup_classes();
			self::$instance->filters();
		}

		return self::$instance;
	}

	/**
	 * Includes files.
	 *
	 * @since 2.6.0
	 */
	private function includes() {

		require_once EDDS_PLUGIN_DIR . '/includes/functions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/class-stripe-api.php';

		// We need this one to load early so we can use it in the upcoming includes.
		require_once EDDS_PLUGIN_DIR . '/includes/elements/functions.php';
		$elements_mode = edds_get_elements_mode();

		require_once EDDS_PLUGIN_DIR . '/includes/utils/exceptions/class-stripe-api-unmet-requirements.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/exceptions/class-attribute-not-found.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/exceptions/class-stripe-object-not-found.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/exceptions/class-gateway-exception.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/interface-static-registry.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/class-registry.php';
		require_once EDDS_PLUGIN_DIR . '/includes/utils/modal.php';

		require_once EDDS_PLUGIN_DIR . '/includes/deprecated.php';
		require_once EDDS_PLUGIN_DIR . '/includes/compat.php';
		require_once EDDS_PLUGIN_DIR . '/includes/i18n.php';
		require_once EDDS_PLUGIN_DIR . '/includes/emails.php';
		require_once EDDS_PLUGIN_DIR . '/includes/payment-receipt.php';
		require_once EDDS_PLUGIN_DIR . '/includes/card-actions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/gateway-actions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/gateway-filters.php';

		// Payment Actions, separated by elements type.
		require_once EDDS_PLUGIN_DIR . '/includes/payment-actions/functions.php';
		switch ( $elements_mode ) {
			case 'card-elements':
				require_once EDDS_PLUGIN_DIR . '/includes/payment-actions/card-elements-actions.php';
				break;

			case 'payment-elements':
				require_once EDDS_PLUGIN_DIR . '/includes/payment-actions/payment-elements-actions.php';
				break;
		}

		require_once EDDS_PLUGIN_DIR . '/includes/webhooks.php';
		require_once EDDS_PLUGIN_DIR . '/includes/scripts.php';
		require_once EDDS_PLUGIN_DIR . '/includes/template-functions.php';
		require_once EDDS_PLUGIN_DIR . '/includes/class-edd-stripe-rate-limiting.php';

		// Load Apple Pay functions.
		require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/apple-pay.php';

		// Stripe Elements, separated by elements type.
		switch ( $elements_mode ) {
			case 'card-elements':
				require_once EDDS_PLUGIN_DIR . '/includes/elements/card-elements.php';
				require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/payment-request/index.php';
				break;

			case 'payment-elements':
				require_once EDDS_PLUGIN_DIR . '/includes/elements/payment-elements.php';
				break;
		}

		// Payment Methods.
		require_once EDDS_PLUGIN_DIR . '/includes/payment-methods/buy-now/index.php';

		if ( is_admin() ) {
			require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices-registry.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/class-notices.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/notices.php';

			require_once EDDS_PLUGIN_DIR . '/includes/admin/admin-actions.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/admin-filters.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/settings/stripe-connect.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/settings.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/upgrade-functions.php';
			require_once EDDS_PLUGIN_DIR . '/includes/admin/site-health.php';
		}

		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			require_once EDDS_PLUGIN_DIR . '/includes/integrations/wp-cli.php';
		}

		if ( defined( 'EDD_ALL_ACCESS_VER' ) && EDD_ALL_ACCESS_VER ) {
			require_once EDDS_PLUGIN_DIR . '/includes/integrations/edd-all-access.php';
		}

		if ( class_exists( 'EDD_Auto_Register' ) ) {
			require_once EDDS_PLUGIN_DIR . '/includes/integrations/edd-auto-register.php';
		}
	}

	/**
	 * Applies various hooks.
	 *
	 * @since 2.6.0
	 */
	private function actions() {}

	/**
	 * Applies various filters.
	 *
	 * @since 2.6.0
	 */
	private function filters() {
		add_filter( 'edd_payment_gateways', array( self::$instance, 'register_gateway' ) );
	}

	/**
	 * Configures core components.
	 *
	 * @since 2.6.19
	 */
	private function setup_classes() {
		$this->rate_limiting   = new EDD_Stripe_Rate_Limiting();
		$this->application_fee = new EDD\Gateways\Stripe\ApplicationFee();
		new EDD\Gateways\Stripe\Admin\LicenseManager();
	}

	/**
	 * Gets the Stripe Connect utility class.
	 *
	 * @since 2.9.3
	 */
	public function connect() {
		if ( ! is_null( $this->connect ) ) {
			return $this->connect;
		}
		require_once EDDS_PLUGIN_DIR . '/includes/class-stripe-connect.php';
		$this->connect = new EDD\Stripe\Connect();

		return $this->connect;
	}

	/**
	 * Registers the gateway.
	 *
	 * @param array $gateways Payment gateways.
	 * @return array
	 */
	public function register_gateway( $gateways ) {
		// Format: ID => Name.
		$gateways['stripe'] = array(
			'admin_label'    => 'Stripe',
			'checkout_label' => __( 'Credit Card', 'easy-digital-downloads' ),
			'supports'       => array(
				'buy_now',
			),
			'icons'          => array(
				'mastercard',
				'visa',
				'discover',
				'americanexpress',
			),
		);

		return $gateways;
	}

	/**
	 * Checks whether Stripe requires regional support.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	public function has_regional_support() {
		if ( ! is_null( $this->has_regional_support ) ) {
			return $this->has_regional_support;
		}

		$this->has_regional_support = $this->load_regional_support();

		return $this->has_regional_support;
	}

	/**
	 * Loads regional support.
	 *
	 * @since 2.9.2.2
	 * @return bool
	 */
	private function load_regional_support() {

		$base_country            = edd_get_option( 'base_country', 'US' );
		$stripe_account_country  = strtolower( edd_get_option( 'stripe_connect_account_country', $base_country ) );
		$regions_needing_support = array( 'in' );
		if ( ! in_array( $stripe_account_country, $regions_needing_support, true ) ) {
			return false;
		}

		$possible_region_file = 'class-edd-stripe-region-' . $stripe_account_country . '.php';
		$possible_region_path = EDDS_PLUGIN_DIR . 'includes/utils/regional-support/' . $possible_region_file;
		if ( ! file_exists( $possible_region_path ) ) {
			return false;
		}

		// Regional Support is needed.
		require_once EDDS_PLUGIN_DIR . 'includes/utils/regional-support/class-edd-stripe-region-base.php';
		require_once $possible_region_path;
		$possible_region_class = 'EDD_Stripe_Region_' . strtoupper( $stripe_account_country );
		if ( class_exists( $possible_region_class ) ) {
			require_once EDDS_PLUGIN_DIR . 'includes/utils/regional-support/' . $possible_region_file;
			$this->regional_support = new $possible_region_class();

			return true;
		}

		return false;
	}

	/**
	 * Performs database upgrades.
	 *
	 * @since 2.6.0
	 * @deprecated 3.2.0
	 */
	public function database_upgrades() {}

	/**
	 * Loads the plugin text domain.
	 *
	 * @since 2.6.0
	 * @deprecated 3.2.0
	 */
	public function load_textdomain() {}
}

<?php
namespace PayWithAmazon;

/**
 * Amazon Payments Gateway
 *
 * @package     EDD
 * @subpackage  Gateways
 * @copyright   Copyright (c) 2015, Pippin's Pages, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

final class EDD_Amazon_Payments {

	private static $instance;
	private $gateway_id   = 'amazon';
	private $client       = null;
	private $redirect_uri = null;

	private function __construct() {

		if ( version_compare( phpversion(), 5.3, '<' ) ) {
			// The Amazon Login & Pay libraries require PHP 5.3
			return;
		}

		// Run this separate so we can ditch as early as possible
		$this->register();

		if ( ! edd_is_gateway_active( $this->gateway_id ) ) {
			return;
		}

		$this->config();
		$this->includes();
		$this->filters();
		$this->actions();
	}

	public static function getInstance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof EDD_Amazon_Payments ) ) {
			self::$instance = new EDD_Amazon_Payments;
		}

		return self::$instance;
	}

	private function register() {
		add_filter( 'edd_payment_gateways', array( $this, 'register_gateway' ), 1, 1 );
	}

	private function config() {

		if ( ! defined( 'EDD_AMAZON_CLASS_DIR' ) ) {
			$path = trailingslashit( plugin_dir_path( EDD_PLUGIN_FILE ) ) . 'includes/gateways/libs/amazon';
			define( 'EDD_AMAZON_CLASS_DIR', trailingslashit( $path ) );
		}

	}

	private function includes() {

		// Include the Amazon Library
		require_once EDD_AMAZON_CLASS_DIR . 'Client.php'; // Requires the other files itself
		require_once EDD_AMAZON_CLASS_DIR . 'IpnHandler.php';

	}

	private function filters() {
		if ( is_admin() ) {
			add_filter( 'edd_settings_gateways', array( $this, 'register_gateway_settings' ), 1, 1 );
		}
	}

	private function actions() {
		add_action( 'wp_enqueue_scripts', array( $this, 'print_client' ), 10 );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ), 11 );
		add_action( 'init',               array( $this, 'capture_oauth' ) );
		add_action( 'edd_amazon_cc_form', array( $this, 'credit_card_form' ) );
	}

	private function get_client() {

		if ( ! is_null( $this->client ) ) {
			return $this->client;
		}

		$this->client = $this->setup_client();

		return $this->client;
	}

	private function setup_client() {
		$config = array(
			'merchant_id' => edd_get_option( 'amazon_seller_id', '' ),
			'client_id'   => edd_get_option( 'amazon_client_id', '' ),
			'access_key'  => edd_get_option( 'amazon_mws_access_key', '' ),
			'secret_key'  => edd_get_option( 'amazon_mws_secret_key', '' ),
			'region'      => edd_get_shop_country(),
			'sandbox'     => edd_is_test_mode(),
		);

		$config = apply_filters( 'edd_amazon_client_config', $config );
		$client = new Client( $config );

		return $client;
	}

	public function register_gateway( $gateways ) {

		$default_amazon_info = array(
			$this->gateway_id => array(
				'admin_label'    => __( 'Amazon', 'edd' ),
				'checkout_label' => __( 'Amazon', 'edd' ),
				'supports'       => array( 'buy_now' )
			),
		);

		$default_amazon_info = apply_filters( 'edd_register_amazon_gateway', $default_amazon_info );
		$gateways            = array_merge( $gateways, $default_amazon_info );

		return $gateways;

	}

	public function register_gateway_settings( $gateway_settings ) {

		$default_amazon_settings = array(
			'amazon' => array(
				'id'   => 'amazon',
				'name' => '<strong>' . __( 'Login & Pay with Amazon Settings', 'edd' ) . '</strong>',
				'desc' => __( 'Configure the Amazon settings', 'edd' ),
				'type' => 'header',
			),
			'amazon_client_id' => array(
				'id'   => 'amazon_client_id',
				'name' => __( 'Client ID', 'edd' ),
				'desc' => __( 'The Amazon Client ID. Should look like `amzn1.application-oa2...`', 'edd' ),
				'type' => 'text',
				'size' => 'regular',
			),
			'amazon_seller_id' => array(
				'id'   => 'amazon_seller_id',
				'name' => __( 'Seller ID', 'edd' ),
				'desc' => __( 'Found in the Integration settings. Also called a Merchent ID', 'edd' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'amazon_mws_access_key' => array(
				'id'   => 'amazon_mws_access_key',
				'name' => __( 'MWS Access Key', 'edd' ),
				'desc' => __( 'Found on Seller Central in the MWS Keys section', 'edd' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'amazon_mws_secret_key' => array(
				'id'   => 'amazon_mws_secret_key',
				'name' => __( 'MWS Secret Key', 'edd' ),
				'desc' => __( 'Found on Seller Central in the MWS Keys section', 'edd' ),
				'type' => 'text',
				'size' => 'regular'
			),
			'amazon_mws_callback_url' => array(
				'id'       => 'amazon_callback_url',
				'name'     => __( 'Amazon MWS Callback URL', 'edd' ),
				'desc'     => __( 'The Callback URL to provide in your MWS Application', 'edd' ),
				'type'     => 'text',
				'size'     => 'large',
				'std'      => $this->get_amazon_checkout_redirect(),
				'readonly' => true,
			),
		);

		$default_amazon_settings = apply_filters( 'edd_default_amazon_settings', $default_amazon_settings );
		$gateway_settings        = array_merge( $gateway_settings, $default_amazon_settings );

		return $gateway_settings;

	}

	public function load_scripts() {

		if ( ! edd_is_checkout() ) {
			return;
		}

		$test_mode = edd_is_test_mode();
		$seller_id = edd_get_option( 'amazon_seller_id', '' );
		$client_id = edd_get_option( 'amazon_client_id', '' );

		$default_amazon_scope = array(
			'profile',
			'postal_code',
			'payments:widget',
		);

		if ( edd_use_taxes() ) {
			$default_amazon_scope[] = 'payments:shipping_address';
		}

		$default_amazon_button_settings = array(
			'type'  => 'PwA',
			'color' => 'Gold',
			'size'  => 'medium',
			'scope' => implode( ' ', $default_amazon_scope ),
			'popup' => true,
		);

		$amazon_button_settings = apply_filters( 'edd_amazon_button_settings', $default_amazon_button_settings );
		$base_url = '';

		switch ( edd_get_shop_country() ) {
			case 'GB':
				$base_url = 'https://static-eu.payments-amazon.com/OffAmazonPayments/uk/';
			break;
			case 'DE':
				$base_url = 'https://static-eu.payments-amazon.com/OffAmazonPayments/de/';
			break;
			default:
				$base_url = 'https://static-na.payments-amazon.com/OffAmazonPayments/us/';
			break;
		}

		if ( ! empty( $base_url ) ) {
			$url = $base_url . ( $test_mode ? 'sandbox/' : '' ) . 'js/Widgets.js?sellerId=' . $seller_id;

			wp_enqueue_script( 'edd-amazon-widgets', $url, array( 'jquery' ), null, false );
			wp_localize_script( 'edd-amazon-widgets', 'edd_amazon', apply_filters( 'edd_amazon_checkout_vars', array(
				'sellerId'    => $seller_id,
				'clientId'    => $client_id,
				'buttonType'  => $amazon_button_settings['type'],
				'buttonColor' => $amazon_button_settings['color'],
				'buttonSize'  => $amazon_button_settings['size'],
				'scope'       => $amazon_button_settings['scope'],
				'popup'       => $amazon_button_settings['popup'],
				'redirectUri' => $this->get_amazon_checkout_redirect(),
			) ) );

		}

	}

	public function print_client() {
		?>
		<script>
			window.onAmazonLoginReady = function() {
				amazon.Login.setClientId(edd_amazon.clientId);
			};
		</script>
		<?php
	}

	public function capture_oauth() {

		if ( ! isset( $_GET['edd-listener'] ) || $_GET['edd-listener'] !== 'amazon' ) {
			return;
		}

		if ( ! isset( $_GET['state'] ) || $_GET['state'] !== 'return_auth' ) {
			return;
		}

		if( empty( $_GET['access_token'] ) || false === strpos( $_GET['access_token'], 'Atza' ) ) {
			return;
		}

		$profile = $this->get_client()->getUserInfo( $_GET['access_token'] );
		
		if( is_user_logged_in() ) {

			// Do something, probably with address

		} else {

			$user = get_user_by( 'email', $profile['email'] );

			if( $user ) {

				edd_log_user_in( $user->ID, $user->user_login, '' );

			} else {

				$args = array(
					'user_email'   => $profile['email'],
					'user_login'   => $profile['email'],
					'display_name' => $profile['name'],
					'user_pass'    => wp_generate_password( 20 )
				);

				$user_id = wp_insert_user( $args );

				edd_log_user_in( $user_id, $args['user_login'], $args['user_pass'] );
			}

		}

		EDD()->session->set( 'amazon_access_token', $_GET['access_token'] );

		wp_redirect( edd_get_checkout_uri( array( 'payment-method' => 'amazon', 'state' => 'authorized' ) ) ); exit;

	}


	public function credit_card_form() {
		ob_start(); ?>

		<?php do_action( 'edd_before_cc_fields' ); ?>

		<fieldset id="edd_cc_fields" class="edd-amazon-fields">

			<?php if ( EDD()->session->get( 'amazon_access_token' ) ) : ?>

				<style type="text/css">
					#walletWidgetDiv{width: 400px; height: 228px;}
				</style>

				<div id="walletWidgetDiv"></div>
				<script>
				  new OffAmazonPayments.Widgets.Wallet({
					sellerId: edd_amazon.sellerId,
					design: {
					  size: {width:'400px', height:'260px'}
					},
					onPaymentSelect: function(orderReference) {
					  // Display your custom complete purchase button
					},
					onError: function(error) {
					  // Write your custom error handling
					}
				  }).bind("walletWidgetDiv");
				</script>

			<?php else: ?>

				<div id="edd-amazon-pay-button"></div>
				<script type="text/javascript">
					OffAmazonPayments.Button('edd-amazon-pay-button', edd_amazon.sellerId, {
						type:  edd_amazon.buttonType,
						color: edd_amazon.buttonColor,
						size:  edd_amazon.buttonSize,

						authorization: function() {
							loginOptions = {
								scope: edd_amazon.scope,
								popup: edd_amazon.popup
							};

							authRequest = amazon.Login.authorize (loginOptions,  edd_amazon.redirectUri);
						}, onError: function(error) {
							// your error handling code
						}
					});
				</script>

			<?php endif; ?>

		</fieldset>

		<?php
		do_action( 'edd_after_cc_fields' );
		$form = ob_get_clean();
		echo $form;

	}

	private function get_amazon_checkout_redirect() {

		if ( is_null( $this->redirect_uri ) ) {
			$this->redirect_uri = esc_url_raw( add_query_arg( array( 'edd-listener' => 'amazon', 'state' => 'return_auth' ), home_url() ) );
		}

		return $this->redirect_uri;

	}

}

function EDD_Amazon() {
	return EDD_Amazon_Payments::getInstance();
}

EDD_Amazon();

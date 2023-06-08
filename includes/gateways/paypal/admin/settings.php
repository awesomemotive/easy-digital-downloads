<?php
/**
 * PayPal Settings
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\Gateways\PayPal\Admin;

use EDD\Gateways\PayPal;

/**
 * Returns the URL to the PayPal Commerce settings page.
 *
 * @since 2.11
 *
 * @return string
 */
function get_settings_url() {
	return admin_url( 'edit.php?post_type=download&page=edd-settings&tab=gateways&section=paypal_commerce' );
}


/**
 * Register the PayPal Standard gateway subsection
 *
 * @param array $gateway_sections Current Gateway Tab subsections
 *
 * @since 2.11
 * @return array                    Gateway subsections with PayPal Standard
 */
function register_paypal_gateway_section( $gateway_sections ) {
	$gateway_sections['paypal_commerce'] = __( 'PayPal', 'easy-digital-downloads' );

	return $gateway_sections;
}
add_filter( 'edd_settings_sections_gateways', __NAMESPACE__ . '\register_paypal_gateway_section', 1, 1 );

/**
 * Registers the PayPal Standard settings for the PayPal Standard subsection
 *
 * @param array $gateway_settings Gateway tab settings
 *
 * @since 2.11
 * @return array Gateway tab settings with the PayPal Standard settings
 */
function register_gateway_settings( $gateway_settings ) {

	$paypal_settings = array(
		'paypal_settings'              => array(
			'id'   => 'paypal_settings',
			'name' => '<h3>' . __( 'PayPal Settings', 'easy-digital-downloads' ) . '</h3>',
			'type' => 'header',
		),
		'paypal_connect_button'        => array(
			'id'    => 'paypal_connect_button',
			'name'  => __( 'Connection Status', 'easy-digital-downloads' ),
			'class' => 'edd-paypal-connect-row',
			'type'  => 'hook',
		),
		'paypal_sandbox_client_id'     => array(
			'id'    => 'paypal_sandbox_client_id',
			'name'  => __( 'Test Client ID', 'easy-digital-downloads' ),
			'desc'  => __( 'Enter your test client ID.', 'easy-digital-downloads' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden',
		),
		'paypal_sandbox_client_secret' => array(
			'id'    => 'paypal_sandbox_client_secret',
			'name'  => __( 'Test Client Secret', 'easy-digital-downloads' ),
			'desc'  => __( 'Enter your test client secret.', 'easy-digital-downloads' ),
			'type'  => 'password',
			'size'  => 'regular',
			'class' => 'edd-hidden',
		),
		'paypal_live_client_id'        => array(
			'id'    => 'paypal_live_client_id',
			'name'  => __( 'Live Client ID', 'easy-digital-downloads' ),
			'desc'  => __( 'Enter your live client ID.', 'easy-digital-downloads' ),
			'type'  => 'text',
			'size'  => 'regular',
			'class' => 'edd-hidden',
		),
		'paypal_live_client_secret'    => array(
			'id'    => 'paypal_live_client_secret',
			'name'  => __( 'Live Client Secret', 'easy-digital-downloads' ),
			'desc'  => __( 'Enter your live client secret.', 'easy-digital-downloads' ),
			'type'  => 'password',
			'size'  => 'regular',
			'class' => 'edd-hidden',
		),
		'paypal_documentation'         => array(
			'id'   => 'paypal_documentation',
			'name' => '',
			'type' => 'hook',
		),
	);

	$is_connected = PayPal\has_rest_api_connection();
	if ( ! $is_connected ) {
		$paypal_settings['paypal_settings']['tooltip_title'] = __( 'Connect with PayPal', 'easy-digital-downloads' );
		$paypal_settings['paypal_settings']['tooltip_desc']  = __( 'Connecting your store with PayPal allows Easy Digital Downloads to automatically configure your store to securely communicate with PayPal.<br \><br \>You may see "Sandhills Development, LLC", mentioned during the process&mdash;that is the company behind Easy Digital Downloads.', 'easy-digital-downloads' );
	}

	/**
	 * Filters the PayPal Settings.
	 *
	 * @param array $paypal_settings
	 */
	$paypal_settings                     = apply_filters( 'edd_paypal_settings', $paypal_settings );
	$gateway_settings['paypal_commerce'] = $paypal_settings;

	return $gateway_settings;
}

add_filter( 'edd_settings_gateways', __NAMESPACE__ . '\register_gateway_settings', 1, 1 );

/**
 * Returns the content for the documentation settings.
 *
 * @since 2.11
 * @return string
 */
function documentation_settings_field() {
	?>
	<p>
		<a class="button button-secondary" href="https://easydigitaldownloads.com/docs/paypal-setup/" target="_blank">
			<?php esc_html_e( 'View Documentation', 'easy-digital-downloads' ); ?>
		</a>

		<a id="edd-paypal-commerce-get-help" class="edd-hidden" href="https://easydigitaldownloads.com/support/" target="_blank">
			<?php esc_html_e( 'Get Help', 'easy-digital-downloads' ); ?>
		</a>
	</p>
	<?php
	if ( ! is_ssl() ) {
		?>
		<div class="notice notice-warning inline">
			<p>
				<?php
				echo wp_kses( sprintf(
					__( 'PayPal requires an SSL certificate to accept payments. You can learn more about obtaining an SSL certificate in our <a href="%s" target="_blank">SSL setup article</a>.', 'easy-digital-downloads' ),
					'https://easydigitaldownloads.com/docs/do-i-need-an-ssl-certificate/'
				), array( 'a' => array( 'href' => true, 'target' => true ) ) );
				?>
			</p>
		</div>
		<?php
	}
}
add_action( 'edd_paypal_documentation', __NAMESPACE__ . '\documentation_settings_field' );

<?php
/*
 * Site Health
 *
 * @package EDD_Stripe\Admin\SiteHealth
 * @copyright Copyright (c) 2023, Easy Digital Downloads
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.9.6
 */

defined( 'ABSPATH' ) || exit;

/**
 * Adds a Stripe Connect site health test.
 *
 * @since 2.9.3
 * @param array $tests The array of Site Health tests.
 * @return array
 */
function edds_stripe_connect_site_health_test( $tests ) {
	$active_gateways = edd_get_enabled_payment_gateways();
	if ( ! empty( $active_gateways['stripe'] ) && current_user_can( 'manage_shop_settings' ) ) {
		$tests['direct']['edds_stripe_connect'] = array(
			'label' => __( 'Stripe Connect', 'easy-digital-downloads' ),
			'test'  => 'edds_get_test_stripe_connect',
		);
	}

	return $tests;
}
add_filter( 'site_status_tests', 'edds_stripe_connect_site_health_test' );

/**
 * Adds the Stripe Connect Site Health test.
 *
 * @since 2.9.3
 * @return array
 */
function edds_get_test_stripe_connect() {
	$result = array(
		'label'       => __( 'You are securely connected to Stripe', 'easy-digital-downloads' ),
		'status'      => 'good',
		'badge'       => array(
			'label' => __( 'Easy Digital Downloads: Stripe', 'easy-digital-downloads' ),
			'color' => 'blue',
		),
		'description' => sprintf(
			'<p>%s</p>',
			__( 'Stripe Connect helps ensure easy setup and security.', 'easy-digital-downloads' )
		),
		'actions'     => '',
		'test'        => 'edds_stripe_connect',
	);

	$elements_mode = edds_get_elements_mode();
	if ( edd_stripe()->connect()->is_connected ) {
		if ( 'payment-elements' === $elements_mode ) {
			return $result;
		}

		// User is connected but on the Card Elements, we should give them a recommendation to use the Payment Elements.
		$result['label']          = __( 'You are using the legacy Card Elements fields', 'easy-digital-downloads' );
		$result['status']         = 'recommended';
		$result['badge']['color'] = 'orange';
		$result['description']    = sprintf(
			'<p>%s</p>',
			esc_html__( 'Increase conversions, security, and reliability by using the Payment Elements integration for Stripe.', 'easy-digital-downloads' )
		);
		$result['actions']        = sprintf(
			'<a href="%s" class="button button-primary"><span>%s</span></a>',
			esc_url(
				edd_get_admin_url(
					array(
						'page'    => 'edd-settings',
						'tab'     => 'gateways',
						'section' => 'edd-stripe',
					)
				)
			),
			esc_html__( 'Switch to Payment Elements', 'easy-digital-downloads' )
		);

	} else {
		$result['label']          = __( 'You are using manually managed Stripe API keys', 'easy-digital-downloads' );
		$result['status']         = 'critical';
		$result['badge']['color'] = 'red';
		$result['description']    = sprintf(
			'<p>%s</p>',
			esc_html__( 'By securely connecting your Easy Digital Downloads store with Stripe Connect, you\'ll get access to more reliable payments and use managed API keys which are more secure.', 'easy-digital-downloads' )
		);
		$result['actions']        = sprintf(
			'<a href="%s" class="edd-stripe-connect"><span>%s</span></a>',
			esc_url( edds_stripe_connect_url() ),
			esc_html__( 'Connect with Stripe', 'easy-digital-downloads' )
		);

	}

	edd_stripe_connect_admin_style();

	return $result;
}

add_filter( 'edd_debug_information', 'edds_debug_information' );
/**
 * Add Stripe debugging information to the EDD information.
 *
 * @since 2.9.6
 * @param array $information The EDD debug information.
 * @return array
 */
function edds_debug_information( $information ) {
	$stripe = array(
		'edd_stripe' => array(
			'label'  => __( 'Easy Digital Downloads &mdash; Stripe', 'easy-digital-downloads' ),
			'fields' => array(
				'connect'              => array(
					'label' => 'Stripe Connect',
					'value' => edd_stripe()->connect()->is_connected ? 'Connected' : 'Not Connected',
				),
				'mode'                 => array(
					'label' => 'Elements Mode',
					'value' => 'payment-elements' === edds_get_elements_mode() ? 'Payment Elements' : 'Legacy Card Elements',
				),
				'preapproved_payments' => array(
					'label' => 'Preapproved Payments',
					'value' => edds_is_preapprove_enabled() ? 'Enabled' : 'Disabled',
				),
				'assets'               => array(
					'label' => 'Stripe Assets',
					'value' => edd_get_option( 'stripe_restrict_assets', false ) ? 'Limited' : 'Global',
				),
			),
		),
	);

	$position = array_search( 'edd_gateways', array_keys( $information ), true );

	return array_merge(
		array_slice( $information, 0, $position + 1 ),
		$stripe,
		array_slice( $information, $position + 1 )
	);
}

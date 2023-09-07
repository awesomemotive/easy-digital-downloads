<?php
/**
 * Bootstraps and outputs notices.
 *
 * @package EDD_Stripe
 * @since   2.6.19
 */

/**
 * Registers scripts to manage dismissing notices.
 *
 * @since 2.6.19
 */
function edds_admin_notices_scripts() {
	wp_register_script(
		'edds-admin-notices',
		EDD_PLUGIN_URL . 'assets/js/stripe-notices.js',
		array(
			'wp-util',
			'jquery',
		),
		EDD_VERSION,
		true
	);
}
add_action( 'admin_enqueue_scripts', 'edds_admin_notices_scripts' );

/**
 * Registers admin notices.
 *
 * @since 2.6.19
 *
 * @return true|WP_Error True if all notices are registered, otherwise WP_Error.
 */
function edds_admin_notices_register() {
	$registry = edds_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return new WP_Error( 'edds-invalid-registry', esc_html__( 'Unable to locate registry', 'easy-digital-downloads' ) );
	}

	try {
		// Recurring requirement.
		$registry->add(
			'edd-recurring-requirement',
			array(
				'message'     => function() {
					ob_start();
					require_once EDDS_PLUGIN_DIR . '/includes/admin/notices/edd-recurring-requirement.php';
					return ob_get_clean();
				},
				'type'        => 'error',
				'dismissible' => false,
			)
		);

		// Enable gateway.
		$registry->add(
			'edd-stripe-core',
			array(
				'message'     => function() {
					ob_start();
					require_once EDDS_PLUGIN_DIR . '/includes/admin/notices/edd-stripe-core.php';
					return ob_get_clean();
				},
				'type'        => 'info',
				'dismissible' => true,
			)
		);
	} catch ( Exception $e ) {
		return new WP_Error(
			'edds-invalid-notices-registration',
			esc_html( $e->getMessage() )
		);
	};

	return true;
}
add_action( 'admin_init', 'edds_admin_notices_register' );

/**
 * Conditionally prints registered notices.
 *
 * @since 2.6.19
 */
function edds_admin_notices_print() {
	// Current user needs capability to dismiss notices.
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$registry = edds_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return;
	}

	$notices = new EDD_Stripe_Admin_Notices( $registry );

	wp_enqueue_script( 'edds-admin-notices' );

	try {
		// Recurring 2.10.0 requirement.
		if ( false === edds_has_met_requirements( 'recurring' ) ) {
			$notices->output( 'edd-recurring-requirement' );
		}

		// Stripe in Core notice.
		if ( false === edds_is_pro() && false === edd_is_gateway_active( 'stripe' ) ) {
			$notices->output( 'edd-stripe-core' );
		}
	} catch( Exception $e ) {}
}
add_action( 'admin_notices', 'edds_admin_notices_print' );

/**
 * Handles AJAX dismissal of notices.
 *
 * WordPress automatically removes the notices, so the response here is arbitrary.
 * If the notice cannot be dismissed it will simply reappear when the page is refreshed.
 *
 * @since 2.6.19
 */
function edds_admin_notices_dismiss_ajax() {
	$notice_id = isset( $_REQUEST[ 'id' ] ) ? esc_attr( $_REQUEST['id'] ) : false;
	$nonce     = isset( $_REQUEST[ 'nonce' ] ) ? esc_attr( $_REQUEST['nonce'] ) : false;

	if ( ! ( $notice_id && $nonce ) ) {
		return wp_send_json_error();
	}

	if ( ! wp_verify_nonce( $nonce, "edds-dismiss-{$notice_id}-nonce" ) ) {
		return wp_send_json_error();
	}

	$registry = edds_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return wp_send_json_error();
	}

	$notices   = new EDD_Stripe_Admin_Notices( $registry );
	$dismissed = $notices->dismiss( $notice_id );

	if ( true === $dismissed ) {
		return wp_send_json_success();
	} else {
		return wp_send_json_error();
	}
}
add_action( 'wp_ajax_edds_admin_notices_dismiss_ajax', 'edds_admin_notices_dismiss_ajax' );

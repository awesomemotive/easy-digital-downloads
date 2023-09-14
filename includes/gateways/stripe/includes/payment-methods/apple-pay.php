<?php
/**
 * Payment Request: Apple Pay
 *
 * @package EDD_Stripe
 * @since   2.8.0
 */

/**
 * Registers admin notices.
 *
 * @since 2.8.0
 *
 * @return true|WP_Error True if all notices are registered, otherwise WP_Error.
 */
function edds_apple_pay_admin_notices_register() {
	$registry = edds_get_registry( 'admin-notices' );

	if ( ! $registry ) {
		return new WP_Error( 'edds-invalid-registry', esc_html__( 'Unable to locate registry', 'easy-digital-downloads' ) );
	}

	try {
		// General error message.
		$message = (
			'<strong>' . esc_html__( 'Apple Pay domain verification error.', 'easy-digital-downloads' ) . '</strong><br />' .
			edd_get_option( 'stripe_apple_pay_domain_error', '' )
		);

		$registry->add(
			'apple-pay-' . $_SERVER['HTTP_HOST'],
			array(
				'message'     => wp_kses(
					wpautop( $message ),
					array(
						'code'   => true,
						'br'     => true,
						'strong' => true,
						'p'      => true,
						'a'      => array(
							'href'   => true,
							'rel'    => true,
							'target' => true,
						),
					)
				),
				'type'        => 'error',
				'dismissible' => true,
			)
		);
	} catch( Exception $e ) {
		return new WP_Error( 'edds-invalid-notices-registration', esc_html( $e->getMessage() ) );
	};

	return true;
}
add_action( 'admin_init', 'edds_apple_pay_admin_notices_register', 30 );

/**
 * Conditionally prints registered notices.
 *
 * @since 2.8.0
 */
function edds_apple_pay_admin_notices_print() {
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
		$error     = edd_get_option( 'stripe_apple_pay_domain_error', '' );
		$test_mode = edd_is_test_mode();

		if ( ! empty( edd_stripe()->connect()->is_connected ) && ! empty( $error ) && false === $test_mode ) {
			$notices->output( 'apple-pay-' . $_SERVER['HTTP_HOST'] );
		}
	} catch( Exception $e ) {}
}
add_action( 'admin_notices', 'edds_apple_pay_admin_notices_print' );

/**
 * Returns information associated with the name/location of the domain verification file.
 *
 * @since 2.8.0
 *
 * @return array Domain verification file information.
 */
function edds_apple_pay_get_fileinfo() {
	$path = untrailingslashit( $_SERVER['DOCUMENT_ROOT'] );
	$dir  = '.well-known';
	$file = 'apple-developer-merchantid-domain-association';

	return array(
		'path'     => $path,
		'dir'      => $dir,
		'file'     => $file,
		'fullpath' => $path . '/' . $dir . '/' . $file,
	);
}

/**
 * Determines if the current website is setup to use Apple Pay.
 *
 * @since 2.8.0
 *
 * @return bool True if the domain has been verified and the association file exists.
 */
function edds_apple_pay_is_valid() {
	return (
		edds_apple_pay_has_domain_verification_file() &&
		edds_apple_pay_has_domain_verification()
	);
}

/**
 * Determines if the domain verification file already exists.
 *
 * @since 2.8.0
 *
 * @return bool True if the domain verification file exists.
 */
function edds_apple_pay_has_domain_verification_file() {
	$fileinfo = edds_apple_pay_get_fileinfo();

	if ( ! @file_exists( $fileinfo['fullpath'] ) ) {
		return false;
	}

	return true;
}

/**
 * Determines if the currently verified domain matches the current site.
 *
 * @since 2.8.0
 *
 * @return bool True if the saved verified domain matches the current site.
 */
function edds_apple_pay_has_domain_verification() {
	return edd_get_option( 'stripe_prb_apple_pay_domain' ) === $_SERVER['HTTP_HOST'];
}

/**
 * Attempts to create a directory in the server root and copy the domain verification file.
 *
 * @since 2.8.0
 *
 * @throws \Exception If the directory or file cannot be created.
 */
function edds_apple_pay_create_directory_and_move_file() {
	$file = edds_apple_pay_has_domain_verification_file();

	if ( true === $file ) {
		return;
	}

	$fileinfo = edds_apple_pay_get_fileinfo();

	// Create directory if it does not exist.
	if ( ! file_exists( trailingslashit( $fileinfo['path'] ) . $fileinfo['dir'] ) ) {
		if ( ! @mkdir( trailingslashit( $fileinfo['path'] ) . $fileinfo['dir'], 0755 ) ) { // @codingStandardsIgnoreLine
			throw new \Exception( __( 'Unable to create domain association folder in domain root.', 'easy-digital-downloads' ) );
		}
	}

	// Move file if needed.
	if ( ! edds_apple_pay_has_domain_verification_file() ) {
		if ( ! @copy( trailingslashit( EDDS_PLUGIN_DIR ) . $fileinfo['file'], $fileinfo['fullpath'] ) ) { // @codingStandardsIgnoreLine
			throw new \Exception( __( 'Unable to copy domain association file to domain .well-known directory.', 'easy-digital-downloads' ) );
		}
	}
}

/**
 * Checks Apple Pay domain verification if there is an existing error.
 * If the domain was added to the Stripe Dashboard clear the error.
 *
 * @since 2.8.0
 */
function edds_apple_pay_check_domain() {
	if ( empty( edd_stripe()->connect()->is_connected ) ) {
		return;
	}

	$error = edd_get_option( 'stripe_apple_pay_domain_error', '' );

	if ( empty( $error ) ) {
		return;
	}

	try {
		$domains = edds_api_request( 'ApplePayDomain', 'all' );

		foreach ( $domains->autoPagingIterator() as $domain ) {
			if ( $domain->domain_name === $_SERVER['HTTP_HOST'] ) {
				edd_delete_option( 'stripe_apple_pay_domain_error' );
				edd_update_option( 'stripe_prb_apple_pay_domain', $_SERVER['HTTP_HOST'] );
				break;
			}
		}
	} catch ( \Exception $e ) {}
}
add_action( 'admin_init', 'edds_apple_pay_check_domain', 10 );

/**
 * Verifies the current domain.
 *
 * @since 2.8.0
 */
function edds_apple_pay_verify_domain() {
	// If Stripe isn't connected, just return.
	if ( empty( edd_stripe()->connect()->is_connected ) ) {
		return;
	}

	// Payment Request Button is not enabled, and card-elements is used.
	$elements_mode = edds_get_elements_mode();

	if ( 'card-elements' === $elements_mode ) {
		if ( function_exists( 'edds_prb_is_enabled' ) && false === edds_prb_is_enabled() ) {
			return;
		}
	}

	// Avoid getting caught in AJAX requests.
	if ( defined( 'DOING_AJAX' ) && true === DOING_AJAX ) {
		return;
	}

	// Must be verified in Live Mode.
	if ( true === edd_is_test_mode() ) {
		return;
	}

	// Current site is a development environment, Apple Pay won't be able to be used, do nothing.
	if ( false !== edd_is_dev_environment() ) {
		return;
	}

	// Current domain matches and the file exists, do nothing.
	if ( true === edds_apple_pay_is_valid() ) {
		return;
	}

	try {
		// Create directory and move file if needed.
		edds_apple_pay_create_directory_and_move_file();

		$stripe_connect_account_id = edd_stripe()->connect()->get_connect_id();

		if (
			empty( $stripe_connect_account_id ) || // If we don't have a stripe connect account ID
			( ! empty( $stripe_connect_account_id ) && empty( edd_get_option( 'stripe_prb_apple_pay_domain') ) ) // Or if we do have a stripe connect account ID, but we haven't registered the domain for Apple Pay
			) {
			edds_api_request(
				'ApplePayDomain',
				'create',
				array(
					'domain_name' => $_SERVER['HTTP_HOST'],
				)
			);

			edd_update_option( 'stripe_prb_apple_pay_domain', $_SERVER['HTTP_HOST'] );

		// Set an error that the domain needs to be manually added.
		// Using Stripe Connect API keys does not allow this to be done automatically.
		} else {
			throw new \Exception(
				sprintf(
					/* translators: %1$s Opening anchor tag, do not translate. %2$s Closing anchor tag, do not translate. */
					( __( 'Please %1$smanually add your domain%2$s %3$s to use Apple Pay.', 'easy-digital-downloads' ) . '<br />' ),
					'<a href="https://dashboard.stripe.com/settings/payments/apple_pay" target="_blank" rel="noopener noreferrer">',
					'</a>',
					'<code>' . $_SERVER['HTTP_HOST'] . '</code>'
				)
			);
		}
	} catch ( \Exception $e ) {
		// Set error if something went wrong.
		edd_update_option( 'stripe_apple_pay_domain_error', $e->getMessage() );
	}
}
add_action( 'admin_init', 'edds_apple_pay_verify_domain', 20 );

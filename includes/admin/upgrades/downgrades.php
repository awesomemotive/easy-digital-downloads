<?php
/**
 * Downgrades
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

/**
 * Checks if the current site has downgraded, and if so, sets any necessary flags
 * and updates the version number.
 *
 * @since 2.11
 */
add_action( 'admin_init', function() {
	$did_downgrade   = false;
	$edd_version     = preg_replace( '/[^0-9.].*/', '', get_option( 'edd_version' ) );
	$downgraded_from = get_option( 'edd_version_downgraded_from' );

	/**
	 * Check for downgrade from 3.0 to 2.11.
	 */
	if ( version_compare( EDD_VERSION, '3.0-beta1', '<' ) ) {
		if (
			version_compare( $edd_version, '3.0-beta1', '>=' ) ||
			( $downgraded_from && version_compare( $downgraded_from, '3.0-beta1', '>=' ) )
		) {
			/*
			 * This site probably just downgraded from EDD 3.0. Let's store a flag so we can give them the option to properly roll back.
			 * @see edd_show_downgrade_notices()
			 */
			update_option( 'edd_v3_downgrade', time() );
			$did_downgrade = true;
		}
	}

	if ( $did_downgrade ) {
		update_option( 'edd_version', preg_replace( '/[^0-9.].*/', '', EDD_VERSION ) );
		delete_option( 'edd_version_downgraded_from' );
	}
} );

/**
 * Display downgrade notices.
 *
 * @since 2.11
 */
function edd_show_downgrade_notices() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	/**
	 * EDD 3.0 downgrade.
	 */
	if ( get_option( 'edd_v3_downgrade' ) ) {
		$downgrade_url = wp_nonce_url( add_query_arg( 'edd_action', 'downgrade_v3' ), 'edd_downgrade_v3' );
		$dismiss_url   = wp_nonce_url( add_query_arg( 'edd_action', 'dismiss_v3_downgrade' ), 'edd_dismiss_v3_downgrade' );
		?>
		<div class="notice notice-warning">
			<h2><?php esc_html_e( 'Did you downgrade from Easy Digital Downloads 3.0?', 'easy-digital-downloads' ); ?></h2>
			<p>
				<?php _e( 'We\'ve detected that your site may have just downgraded from Easy Digital Downloads version 3.0. If that is correct, please click the "Complete Downgrade" button below to complete this process.', 'easy-digital-downloads' ); ?>
			</p>
			<p>
				<?php _e( 'If you believe this message to be in error, please click "Dismiss Notice" instead, which will remove this notice with no further action.', 'easy-digital-downloads' ); ?>
			</p>
			<p>
				<a href="<?php echo esc_url( $downgrade_url ); ?>" class="button button-primary"><?php esc_html_e( 'Complete Downgrade', 'easy-digital-downloads' ); ?></a>
				<a href="<?php echo esc_url( $dismiss_url ); ?>" class="button"><?php esc_html_e( 'Dismiss Notice', 'easy-digital-downloads' ); ?></a>
			</p>
		</div>
		<?php
	}
}

add_action( 'admin_notices', 'edd_show_downgrade_notices' );

/**
 * Handles the downgrade from EDD 3.0.
 *
 * @since 2.11
 */
add_action( 'edd_downgrade_v3', function () {
	if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'edd_downgrade_v3' ) ) {
		wp_die(
			__( 'You do not have permission to perform this action.', 'easy-digital-downloads' ),
			__( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 )
		);
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			__( 'You do not have permission to perform this action.', 'easy-digital-downloads' ),
			__( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 )
		);
	}

	if ( ! get_option( 'edd_v3_downgrade' ) ) {
		wp_die(
			__( 'Unexpected rollback operation.', 'easy-digital-downloads' ),
			__( 'Error', 'easy-digital-downloads' ), array( 'response' => 400 )
		);
	}

	global $wpdb;
	$customer_meta_table = EDD()->customer_meta->table_name;
	$wpdb->query( "ALTER TABLE {$customer_meta_table} CHANGE `edd_customer_id` `edd_customer` bigint(20) unsigned NOT NULL default '0'" );
	$wpdb->query( "ALTER TABLE {$customer_meta_table} DROP INDEX edd_customer_id" );
	$wpdb->query( "ALTER TABLE {$customer_meta_table} ADD INDEX customer_id (customer_id)" );
	EDD()->customer_meta->create_table(); // This re-adds the version number for us.

	EDD()->customers->create_table();

	delete_option( 'edd_v3_downgrade' );

	wp_safe_redirect( remove_query_arg( array( '_wpnonce', 'edd_action' ) ) );
	exit;
} );

/**
 * Handles dismissing the downgrade notice.
 *
 * @since 2.11
 */
add_action( 'edd_dismiss_v3_downgrade', function () {
	if ( empty( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'edd_dismiss_v3_downgrade' ) ) {
		wp_die(
			__( 'You do not have permission to perform this action.', 'easy-digital-downloads' ),
			__( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 )
		);
	}

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die(
			__( 'You do not have permission to perform this action.', 'easy-digital-downloads' ),
			__( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 )
		);
	}

	delete_option( 'edd_v3_downgrade' );

	wp_safe_redirect( remove_query_arg( array( '_wpnonce', 'edd_action' ) ) );
	exit;
} );

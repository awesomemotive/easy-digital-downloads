<?php

/**
 * Admin Deprecated Functions
 *
 * All admin functions that have been deprecated.
 *
 * @package     EDD
 * @subpackage  Deprecated
 * @copyright   Copyright (c) 2020, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

/**
 * Display the ban emails tab
 *
 * @since 2.0
 * @deprecated 3.0 replaced by Order Blocking in settings.
 */
function edd_tools_banned_emails_display() {
	_edd_deprecated_function( __FUNCTION__, '3.0' );
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	do_action( 'edd_tools_banned_emails_before' );
	?>
	<div class="postbox">
		<h3><span><?php esc_html_e( 'Banned Emails', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p><?php esc_html_e( 'Emails placed in the box below will not be allowed to make purchases.', 'easy-digital-downloads' ); ?></p>
			<form method="post"
					action="<?php echo esc_url( edd_get_admin_url( array( 'page' => 'edd-tools', 'tab' => 'general' ) ) ); ?>">
				<p>
					<textarea name="banned_emails" rows="10"
								class="large-text"><?php echo esc_textarea( implode( "\n", edd_get_banned_emails() ) ); ?></textarea>
					<span class="description"><?php esc_html_e( 'Enter emails and/or domains (starting with "@") and/or TLDs (starting with ".") to disallow, one per line.', 'easy-digital-downloads' ); ?></span>
				</p>
				<p>
					<input type="hidden" name="edd_action" value="save_banned_emails"/>
					<?php wp_nonce_field( 'edd_banned_emails_nonce', 'edd_banned_emails_nonce' ); ?>
					<?php submit_button( __( 'Save', 'easy-digital-downloads' ), 'secondary', 'submit', false ); ?>
				</p>
			</form>
		</div><!-- .inside -->
	</div><!-- .postbox -->
	<?php
	do_action( 'edd_tools_banned_emails_after' );
	do_action( 'edd_tools_after' );
}

/**
 * Trigger a Purchase Deletion
 *
 * @since 1.3.4
 * @deprecated 3.0 replaced by edd_trigger_destroy_order.
 * @param array $data Arguments passed.
 * @return void
 */
function edd_trigger_purchase_delete( $data ) {
	if ( wp_verify_nonce( $data['_wpnonce'], 'edd_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		edd_delete_purchase( $payment_id );

		edd_redirect( admin_url( 'edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted' ) );
	}
}
add_action( 'edd_delete_payment', 'edd_trigger_purchase_delete' );

/**
 * Add-ons Page
 *
 * Renders the add-ons page content.
 *
 * @since 1.0
 * @deprecated 3.1.1
 * @return void
 */
function edd_add_ons_page() {

	_edd_deprecated_function( __FUNCTION__, '3.1.1' );

	// Filter the add-ons tabs.
	$add_ons_tabs = apply_filters( 'edd_add_ons_tabs', array(
		'popular' => __( 'Popular', 'easy-digital-downloads' ),
		'new'     => __( 'New',     'easy-digital-downloads' ),
		'all'     => __( 'All',     'easy-digital-downloads' )
	) );

	// Active tab.
	$active_tab = isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], $add_ons_tabs )
		? sanitize_key( $_GET['tab'] )
		: 'popular';

	// Empty tabs array.
	$tabs = array();

	// Loop through add-ons and make array of tabs.
	foreach ( $add_ons_tabs as $tab_id => $tab_name ) {

		// "All"
		if ( 'all' === $tab_id ) {
			$tab_url = edd_link_helper(
				'https://easydigitaldownloads.com/downloads/',
				array(
					'utm_medium'  => 'addons-page',
					'utm_content' => 'all-extensions',
				)
			);

		// All other tabs besides "All".
		} else {
			$tab_url = add_query_arg( array(
				'settings-updated' => false,
				'tab'              => sanitize_key( $tab_id ),
			) );
		}

		// Active?
		$active = ( $active_tab === $tab_id )
			? 'current'
			: '';

		// Count.
		$count = ( 'all' === $tab_id )
			? '150+'
			: '29';

		// The link.
		$tab  = '<li class="' . esc_attr( $tab_id ) . '">';
		$tab .= ( 'all' === $tab_id )
			? '<a href="' . esc_url( $tab_url ) . '" class="' . esc_attr( $active ) . '" target="_blank">'
			: '<a href="' . esc_url( $tab_url ) . '" class="' . esc_attr( $active ) . '">';

		$tab .= esc_html( $tab_name );
		$tab .= ' <span class="count">(' . esc_html( $count ) . ')</span>';

		// "All" is an external link, so denote it as such.
		if ( 'all' === $tab_id ) {
			$tab .= '<span class="dashicons dashicons-external"></span>';
		}

		$tab .= '</a>';
		$tab .= '</li>';

		// Set the tab.
		$tabs[] = $tab;
	}

	// Start a buffer.
	ob_start(); ?>

	<div class="wrap" id="edd-add-ons">
		<h1>
			<?php _e( 'Apps and Integrations for Easy Digital Downloads', 'easy-digital-downloads' ); ?>
			<span>
				<?php
				$url = edd_link_helper(
					'https://easydigitaldownloads.com/downloads/',
					array(
						'utm_medium'  => 'addons-page',
						'utm_content' => 'browse-all',
					)
				);
				?>
				&nbsp;&nbsp;<a href="<?php echo $url; ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'Browse All Integrations', 'easy-digital-downloads' ); ?></a>
			</span>
		</h1>
		<p><?php _e( 'These <em><strong>add functionality</strong></em> to your Easy Digital Downloads powered store.', 'easy-digital-downloads' ); ?></p>

		<ul class="subsubsub"><?php echo implode( ' | ', $tabs ); ?></ul>

		<div class="edd-add-ons-container">
			<?php
			// Display all add ons.
			echo wp_kses_post( edd_add_ons_get_feed( $active_tab ) );
			?>
			<div class="clear"></div>
		</div>

		<div class="edd-add-ons-footer">
			<?php
			$url = edd_link_helper(
				'https://easydigitaldownloads.com/downloads/',
				array(
					'utm_medium'  => 'addons-page',
					'utm_content' => 'browse-all',
				)
			);
			?>
			<a href="<?php echo $url; ?>" class="button button-primary" target="_blank"><?php esc_html_e( 'Browse All Integrations', 'easy-digital-downloads' ); ?></a>
		</div>
	</div>

	<?php

	// Output the current buffer.
	echo ob_get_clean();
}

/**
 * Add-ons Get Feed
 *
 * Gets the add-ons page feed.
 *
 * @since 1.0
 * @deprecated 3.1.1
 * @return void
 */
function edd_add_ons_get_feed( $tab = 'popular' ) {
	_edd_deprecated_function( __FUNCTION__, '3.1.1' );

	// Transient.
	$trans_key = 'easydigitaldownloads_add_ons_feed_' . $tab;
	$cache     = get_transient( $trans_key );

	// No add ons, so reach out and get some.
	if ( false === $cache ) {
		$url = 'https://easydigitaldownloads.com/?feed=addons';

		// Popular.
		if ( 'popular' !== $tab ) {
			$url = add_query_arg( array( 'display' => sanitize_key( $tab ) ), $url );
		}

		// Remote request.
		$feed = wp_remote_get( esc_url_raw( $url ), array( 'sslverify' => false ) );

		// Handle error.
		if ( empty( $feed ) || is_wp_error( $feed ) ) {
			$cache = '<div class="error"><p>' . __( 'These extensions could not be retrieved from the server. Please try again later.', 'easy-digital-downloads' ) . '</div>';

		// Cache the results.
		} elseif ( isset( $feed['body'] ) && strlen( $feed['body'] ) > 0 ) {
			$cache = wp_remote_retrieve_body( $feed );
			set_transient( $trans_key, $cache, HOUR_IN_SECONDS );
		}
	}

	return $cache;
}

/**
 * Create the Extensions submenu page under the "Downloads" menu
 *
 * @since 3.0
 *
 * @global $edd_add_ons_page
 */
function edd_add_extentions_link() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}
	global $submenu, $edd_add_ons_page;

	$edd_add_ons_page = add_submenu_page( 'edit.php?post_type=download', __( 'EDD Extensions', 'easy-digital-downloads' ), __( 'Extensions', 'easy-digital-downloads' ), 'manage_shop_settings', 'edd-addons', 'edd_add_ons_page' );
	$pass_manager = new \EDD\Admin\Pass_Manager();
	if ( ! $pass_manager->has_pass() ) {
		$submenu[ 'edit.php?post_type=download' ][] = array(
			'<span class="edd-menu-highlight">' . esc_html__( 'Upgrade to Pro', 'easy-digital-downloads' ) . '</span>',
			'manage_shop_settings',
			edd_link_helper(
				'https://easydigitaldownloads.com/lite-upgrade',
				array(
					'utm_medium'  => 'admin-menu',
					'utm_content' => 'upgrade-to-pro',
				)
			)
		);

		add_action( 'admin_print_styles', function() {
			?>
			<style>#menu-posts-download li:last-child {background-color: #1da867;}#menu-posts-download li:last-child a,#menu-posts-download li:last-child a:hover{color: #FFFFFF !important;font-weight: 600;}</style>
			<?php
		} );
	}
}

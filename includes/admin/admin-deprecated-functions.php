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
					action="
					<?php
					echo esc_url(
						edd_get_admin_url(
							array(
								'page' => 'edd-tools',
								'tab'  => 'general',
							)
						)
					);
					?>
							">
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
	_edd_deprecated_function( __FUNCTION__, '3.0' );
	if ( wp_verify_nonce( $data['_wpnonce'], 'edd_payment_nonce' ) ) {

		$payment_id = absint( $data['purchase_id'] );

		if ( ! current_user_can( 'delete_shop_payments', $payment_id ) ) {
			wp_die( __( 'You do not have permission to edit this payment record', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		edd_delete_purchase( $payment_id );

		edd_redirect( admin_url( 'edit.php?post_type=download&page=edd-payment-history&edd-message=payment_deleted' ) );
	}
}

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
	$add_ons_tabs = apply_filters(
		'edd_add_ons_tabs',
		array(
			'popular' => __( 'Popular', 'easy-digital-downloads' ),
			'new'     => __( 'New', 'easy-digital-downloads' ),
			'all'     => __( 'All', 'easy-digital-downloads' ),
		)
	);

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
			$tab_url = add_query_arg(
				array(
					'settings-updated' => false,
					'tab'              => sanitize_key( $tab_id ),
				)
			);
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
	ob_start();
	?>

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
	$pass_manager     = new \EDD\Admin\Pass_Manager();
	if ( ! $pass_manager->has_pass() ) {
		$submenu['edit.php?post_type=download'][] = array(
			'<span class="edd-menu-highlight">' . esc_html__( 'Upgrade to Pro', 'easy-digital-downloads' ) . '</span>',
			'manage_shop_settings',
			edd_link_helper(
				'https://easydigitaldownloads.com/lite-upgrade',
				array(
					'utm_medium'  => 'admin-menu',
					'utm_content' => 'upgrade-to-pro',
				)
			),
		);

		add_action(
			'admin_print_styles',
			function () {
				?>
			<style>#menu-posts-download li:last-child {background-color: #1da867;}#menu-posts-download li:last-child a,#menu-posts-download li:last-child a:hover{color: #FFFFFF !important;font-weight: 600;}</style>
				<?php
			}
		);
	}
}

/**
 * Display the system info tab
 *
 * @deprecated 3.1.2
 * @since 2.0
 */
function edd_tools_sysinfo_display() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}
	_edd_deprecated_function( __FUNCTION__, '3.1.2' );

	?>

	<div class="postbox">
		<h3><span><?php esc_html_e( 'System Information', 'easy-digital-downloads' ); ?></span></h3>
		<div class="inside">
			<p>
				<?php esc_html_e( 'Use the system information below to help troubleshoot problems.', 'easy-digital-downloads' ); ?>
			</p>

			<form id="edd-system-info" action="<?php echo esc_url( admin_url( 'edit.php?post_type=download&page=edd-tools&tab=system_info' ) ); ?>" method="post" dir="ltr">
				<textarea readonly="readonly" onclick="this.focus(); this.select()" id="system-info-textarea" class="edd-tools-textarea" name="edd-sysinfo"
					><?php echo edd_tools_sysinfo_get(); ?></textarea>

				<p>
					<input type="hidden" name="edd-action" value="download_sysinfo"/>
					<?php
					wp_nonce_field( 'edd_download_system_info', 'edd_system_info' );
					submit_button( __( 'Download System Info File', 'easy-digital-downloads' ), 'primary', 'edd-download-sysinfo', false );
					submit_button( __( 'Copy to Clipboard', 'easy-digital-downloads' ), 'secondary edd-inline-button', 'edd-copy-system-info', false, array( 'onclick' => "this.form['edd-sysinfo'].focus();this.form['edd-sysinfo'].select();document.execCommand('copy');return false;" ) );
					?>
				</p>
			</form>
		</div>
	</div>

	<?php
}

/**
 * Get system info.
 *
 * @deprecated 3.1.2
 * @since 2.0
 *
 * @return string $return A string containing the info to output
 */
function edd_tools_sysinfo_get() {

	_edd_deprecated_function( __FUNCTION__, '3.1.2' );

	global $wpdb;

	if ( ! class_exists( 'Browser' ) ) {
		require_once EDD_PLUGIN_DIR . 'includes/libraries/browser.php';
	}

	$browser = new Browser();

	// Get theme info
	$theme_data   = wp_get_theme();
	$theme        = $theme_data->Name . ' ' . $theme_data->Version;
	$parent_theme = $theme_data->Template;
	if ( ! empty( $parent_theme ) ) {
		$parent_theme_data = wp_get_theme( $parent_theme );
		$parent_theme      = $parent_theme_data->Name . ' ' . $parent_theme_data->Version;
	}

	// Try to identify the hosting provider
	$host = edd_get_host();

	$return = '### Begin System Info (Generated ' . date( 'Y-m-d H:i:s' ) . ') ###' . "\n\n";

	// Start with the basics...
	$return .= '-- Site Info' . "\n\n";
	$return .= 'Site URL:                 ' . site_url() . "\n";
	$return .= 'Home URL:                 ' . home_url() . "\n";
	$return .= 'Multisite:                ' . ( is_multisite() ? 'Yes' : 'No' ) . "\n";

	$return = apply_filters( 'edd_sysinfo_after_site_info', $return );

	// Can we determine the site's host?
	if ( $host ) {
		$return .= "\n" . '-- Hosting Provider' . "\n\n";
		$return .= 'Host:                     ' . $host . "\n";

		$return = apply_filters( 'edd_sysinfo_after_host_info', $return );
	}

	// The local users' browser information, handled by the Browser class
	$return .= "\n" . '-- User Browser' . "\n\n";
	$return .= $browser;

	$return = apply_filters( 'edd_sysinfo_after_user_browser', $return );

	$locale = get_locale();

	// WordPress configuration
	$return .= "\n" . '-- WordPress Configuration' . "\n\n";
	$return .= 'Version:                  ' . get_bloginfo( 'version' ) . "\n";
	$return .= 'Language:                 ' . ( ! empty( $locale ) ? $locale : 'en_US' ) . "\n";
	$return .= 'Permalink Structure:      ' . ( get_option( 'permalink_structure' ) ? get_option( 'permalink_structure' ) : 'Default' ) . "\n";
	$return .= 'Active Theme:             ' . $theme . "\n";
	$return .= 'WP Timezone:              ' . wp_timezone_string() . "\n";
	$return .= 'EDD Timezone:             ' . edd_get_timezone_abbr() . "\n";
	if ( $parent_theme !== $theme ) {
		$return .= 'Parent Theme:             ' . $parent_theme . "\n";
	}

	$customized_template_files = edd_get_theme_edd_templates();
	$return                   .= "\n" . '-- Customized Templates' . "\n\n";
	if ( empty( $customized_template_files ) ) {
		$return .= 'No custom templates found.' . "\n\n";
	} else {
		foreach ( $customized_template_files as $customized_template_file ) {
			$return .= $customized_template_file . "\n";
		}
	}

	$return .= "\n";

	$return = apply_filters( 'edd_sysinfo_after_customized_templates', $return );

	$return .= 'Show On Front:            ' . get_option( 'show_on_front' ) . "\n";

	// Only show page specs if frontpage is set to 'page'
	if ( get_option( 'show_on_front' ) == 'page' ) {
		$front_page_id = get_option( 'page_on_front' );
		$blog_page_id  = get_option( 'page_for_posts' );

		$return .= 'Page On Front:            ' . ( $front_page_id != 0 ? '#' . $front_page_id : 'Unset' ) . "\n";
		$return .= 'Page For Posts:           ' . ( $blog_page_id != 0 ? '#' . $blog_page_id : 'Unset' ) . "\n";
	}

	$return .= 'ABSPATH:                  ' . ABSPATH . "\n";

	// Make sure wp_remote_post() is working
	$request['cmd'] = '_notify-validate';

	$params = array(
		'sslverify'  => false,
		'timeout'    => 60,
		'user-agent' => 'EDD/' . EDD_VERSION,
		'body'       => $request,
	);

	$response = wp_remote_post( 'https://www.paypal.com/cgi-bin/webscr', $params );

	if ( ! is_wp_error( $response ) && $response['response']['code'] >= 200 && $response['response']['code'] < 300 ) {
		$WP_REMOTE_POST = 'wp_remote_post() works';
	} else {
		$WP_REMOTE_POST = 'wp_remote_post() does not work';
	}

	$return .= 'Remote Post:              ' . $WP_REMOTE_POST . "\n";
	$return .= 'Table Prefix:             ' . 'Length: ' . strlen( $wpdb->prefix ) . '   Status: ' . ( strlen( $wpdb->prefix ) > 16 ? 'ERROR: Too long' : 'Acceptable' ) . "\n";
	// Commented out per https://github.com/easydigitaldownloads/Easy-Digital-Downloads/issues/3475
	// $return .= 'Admin AJAX:               ' . ( edd_test_ajax_works() ? 'Accessible' : 'Inaccessible' ) . "\n";
	$return .= 'WP_DEBUG:                 ' . ( defined( 'WP_DEBUG' ) ? WP_DEBUG ? 'Enabled' : 'Disabled' : 'Not set' ) . "\n";
	$return .= 'Memory Limit:             ' . WP_MEMORY_LIMIT . "\n";
	$return .= 'Registered Post Stati:    ' . implode( ', ', get_post_stati() ) . "\n";

	$return = apply_filters( 'edd_sysinfo_after_wordpress_config', $return );

	// EDD configuration
	$return .= "\n" . '-- EDD Configuration' . "\n\n";
	$return .= 'Version:                  ' . EDD_VERSION . "\n";
	$return .= 'Upgraded From:            ' . get_option( 'edd_version_upgraded_from', 'None' ) . "\n";
	$return .= 'EDD (Pro) Status:         ' . ( edd_is_pro() ? "Enabled\n" : "Disabled\n" );
	$return .= 'EDD (Pro) Activated On:   ' . ( get_option( 'edd_pro_activation_date' ) ? edd_date_i18n( get_option( 'edd_pro_activation_date' ), 'Y-m-d' ) . "\n" : "N/A\n" );
	$return .= 'EDD Pass Status:          ' . ( EDD\Admin\Pass_Manager::isPro() ? "Valid Pass\n" : "Missing\n" );
	$return .= 'Test Mode:                ' . ( edd_is_test_mode() ? "Enabled\n" : "Disabled\n" );
	$return .= 'AJAX:                     ' . ( ! edd_is_ajax_disabled() ? "Enabled\n" : "Disabled\n" );
	$return .= 'Guest Checkout:           ' . ( edd_no_guest_checkout() ? "Disabled\n" : "Enabled\n" );
	$return .= 'Symlinks:                 ' . ( apply_filters( 'edd_symlink_file_downloads', edd_get_option( 'symlink_file_downloads', false ) ) && function_exists( 'symlink' ) ? "Enabled\n" : "Disabled\n" );
	$return .= 'Download Method:          ' . ucfirst( edd_get_file_download_method() ) . "\n";
	$return .= 'Currency Code:            ' . edd_get_currency() . "\n";
	$return .= 'Currency Position:        ' . edd_get_option( 'currency_position', 'before' ) . "\n";
	$return .= 'Decimal Separator:        ' . edd_get_option( 'decimal_separator', '.' ) . "\n";
	$return .= 'Thousands Separator:      ' . edd_get_option( 'thousands_separator', ',' ) . "\n";
	$return .= 'Upgrades Completed:       ' . implode( ',', edd_get_completed_upgrades() ) . "\n";
	$return .= 'Download Link Expiration: ' . edd_get_option( 'download_link_expiration' ) . " hour(s)\n";

	$return = apply_filters( 'edd_sysinfo_after_edd_config', $return );

	// EDD Database tables
	$return .= "\n" . '-- EDD Database Tables' . "\n\n";

	foreach ( EDD()->components as $component ) {

		// Object
		$thing = $component->get_interface( 'table' );
		if ( ! empty( $thing ) ) {
			$return .= str_pad( $thing->name . ': ', 32, ' ' ) . $thing->get_version() . "\n";
		}

		// Meta
		$thing = $component->get_interface( 'meta' );
		if ( ! empty( $thing ) ) {
			$return .= str_pad( $thing->name . ': ', 32, ' ' ) . $thing->get_version() . "\n";
		}
	}

	$return = apply_filters( 'edd_sysinfo_after_edd_database_tables', $return );

	// EDD Database tables
	$return .= "\n" . '-- EDD Database Row Counts' . "\n\n";

	foreach ( EDD()->components as $component ) {

		// Object
		$thing = $component->get_interface( 'table' );
		if ( ! empty( $thing ) ) {
			$return .= str_pad( $thing->name . ': ', 32, ' ' ) . $thing->count() . "\n";
		}

		// Meta
		$thing = $component->get_interface( 'meta' );
		if ( ! empty( $thing ) ) {
			$return .= str_pad( $thing->name . ': ', 32, ' ' ) . $thing->count() . "\n";
		}
	}

	$return = apply_filters( 'edd_sysinfo_after_edd_database_row_counts', $return );

	// EDD pages
	$purchase_page = edd_get_option( 'purchase_page', '' );
	$success_page  = edd_get_option( 'success_page', '' );
	$failure_page  = edd_get_option( 'failure_page', '' );

	$return .= "\n" . '-- EDD Page Configuration' . "\n\n";
	$return .= 'Checkout:                 ' . ( ! empty( $purchase_page ) ? "Valid\n" : "Invalid\n" );
	$return .= 'Checkout Page:            ' . ( ! empty( $purchase_page ) ? get_permalink( $purchase_page ) . "\n" : "Unset\n" );
	$return .= 'Success Page:             ' . ( ! empty( $success_page ) ? get_permalink( $success_page ) . "\n" : "Unset\n" );
	$return .= 'Failure Page:             ' . ( ! empty( $failure_page ) ? get_permalink( $failure_page ) . "\n" : "Unset\n" );
	$return .= 'Downloads Slug:           ' . ( defined( 'EDD_SLUG' ) ? '/' . EDD_SLUG . "\n" : "/downloads\n" );

	$return = apply_filters( 'edd_sysinfo_after_edd_pages', $return );

	// EDD gateways
	$return .= "\n" . '-- EDD Gateway Configuration' . "\n\n";

	$active_gateways = edd_get_enabled_payment_gateways();
	if ( $active_gateways ) {
		$default_gateway_is_active = edd_is_gateway_active( edd_get_default_gateway() );
		if ( $default_gateway_is_active ) {
			$default_gateway = edd_get_default_gateway();
			$default_gateway = $active_gateways[ $default_gateway ]['admin_label'];
		} else {
			$default_gateway = 'Test Payment';
		}

		$gateways = array();
		foreach ( $active_gateways as $gateway ) {
			$gateways[] = $gateway['admin_label'];
		}

		$return .= 'Enabled Gateways:         ' . implode( ', ', $gateways ) . "\n";
		$return .= 'Default Gateway:          ' . $default_gateway . "\n";
	} else {
		$return .= 'Enabled Gateways:         None' . "\n";
	}

	$return = apply_filters( 'edd_sysinfo_after_edd_gateways', $return );

	// EDD Taxes
	$return .= "\n" . '-- EDD Tax Configuration' . "\n\n";
	$return .= 'Taxes:                    ' . ( edd_use_taxes() ? "Enabled\n" : "Disabled\n" );
	$return .= 'Default Rate:             ' . edd_get_formatted_tax_rate() . "\n";
	$return .= 'Display On Checkout:      ' . ( edd_get_option( 'checkout_include_tax', false ) ? "Displayed\n" : "Not Displayed\n" );
	$return .= 'Prices Include Tax:       ' . ( edd_prices_include_tax() ? "Yes\n" : "No\n" );

	$rates = edd_get_tax_rates();
	if ( ! empty( $rates ) ) {
		$return .= 'Country / State Rates:    ' . "\n";
		foreach ( $rates as $rate ) {
			$return .= '                          Country: ' . $rate['country'] . ', State: ' . $rate['state'] . ', Rate: ' . $rate['rate'] . "\n";
		}
	}

	$return = apply_filters( 'edd_sysinfo_after_edd_taxes', $return );

	// EDD Templates
	$dir = get_stylesheet_directory() . '/edd_templates/*';
	if ( is_dir( $dir ) && ( count( glob( "$dir/*" ) ) !== 0 ) ) {
		$return .= "\n" . '-- EDD Template Overrides' . "\n\n";

		foreach ( glob( $dir ) as $file ) {
			$return .= 'Filename:                 ' . basename( $file ) . "\n";
		}

		$return = apply_filters( 'edd_sysinfo_after_edd_templates', $return );
	}

	// Drop Ins
	$dropins = get_dropins();
	if ( count( $dropins ) > 0 ) {
		$return .= "\n" . '-- Drop Ins' . "\n\n";

		foreach ( $dropins as $plugin => $plugin_data ) {
			$return .= str_pad( $plugin_data['Name'] . ': ', 26, ' ' ) . $plugin_data['Version'] . "\n";
		}

		$return = apply_filters( 'edd_sysinfo_after_wordpress_dropin_plugins', $return );
	}

	// Get plugins that have an update
	$updates = get_plugin_updates();

	// Must-use plugins
	// NOTE: MU plugins can't show updates!
	$muplugins = get_mu_plugins();
	if ( count( $muplugins ) > 0 ) {
		$return .= "\n" . '-- Must-Use Plugins' . "\n\n";

		foreach ( $muplugins as $plugin => $plugin_data ) {
			$return .= str_pad( $plugin_data['Name'] . ': ', 26, ' ' ) . $plugin_data['Version'] . "\n";
		}

		$return = apply_filters( 'edd_sysinfo_after_wordpress_mu_plugins', $return );
	}

	// WordPress active plugins
	$return .= "\n" . '-- WordPress Active Plugins' . "\n\n";

	$plugins        = get_plugins();
	$active_plugins = get_option( 'active_plugins', array() );

	foreach ( $plugins as $plugin_path => $plugin ) {
		if ( ! in_array( $plugin_path, $active_plugins ) ) {
			continue;
		}

		$update     = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
		$plugin_url = '';
		if ( ! empty( $plugin['PluginURI'] ) ) {
			$plugin_url = $plugin['PluginURI'];
		} elseif ( ! empty( $plugin['AuthorURI'] ) ) {
			$plugin_url = $plugin['AuthorURI'];
		} elseif ( ! empty( $plugin['Author'] ) ) {
			$plugin_url = $plugin['Author'];
		}
		if ( $plugin_url ) {
			$plugin_url = "\n" . $plugin_url;
		}
		$return .= str_pad( $plugin['Name'] . ': ', 26, ' ' ) . $plugin['Version'] . $update . $plugin_url . "\n\n";
	}

	$return = apply_filters( 'edd_sysinfo_after_wordpress_plugins', $return );

	// WordPress inactive plugins
	$return .= "\n" . '-- WordPress Inactive Plugins' . "\n\n";

	foreach ( $plugins as $plugin_path => $plugin ) {
		if ( in_array( $plugin_path, $active_plugins ) ) {
			continue;
		}

		$update     = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
		$plugin_url = '';
		if ( ! empty( $plugin['PluginURI'] ) ) {
			$plugin_url = $plugin['PluginURI'];
		} elseif ( ! empty( $plugin['AuthorURI'] ) ) {
			$plugin_url = $plugin['AuthorURI'];
		} elseif ( ! empty( $plugin['Author'] ) ) {
			$plugin_url = $plugin['Author'];
		}
		if ( $plugin_url ) {
			$plugin_url = "\n" . $plugin_url;
		}
		$return .= str_pad( $plugin['Name'] . ': ', 26, ' ' ) . $plugin['Version'] . $update . $plugin_url . "\n\n";
	}

	$return = apply_filters( 'edd_sysinfo_after_wordpress_plugins_inactive', $return );

	if ( is_multisite() ) {
		// WordPress Multisite active plugins
		$return .= "\n" . '-- Network Active Plugins' . "\n\n";

		$plugins        = wp_get_active_network_plugins();
		$active_plugins = get_site_option( 'active_sitewide_plugins', array() );

		foreach ( $plugins as $plugin_path ) {
			$plugin_base = plugin_basename( $plugin_path );

			if ( ! array_key_exists( $plugin_base, $active_plugins ) ) {
				continue;
			}

			$update     = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$plugin     = get_plugin_data( $plugin_path );
			$plugin_url = '';
			if ( ! empty( $plugin['PluginURI'] ) ) {
				$plugin_url = $plugin['PluginURI'];
			} elseif ( ! empty( $plugin['AuthorURI'] ) ) {
				$plugin_url = $plugin['AuthorURI'];
			} elseif ( ! empty( $plugin['Author'] ) ) {
				$plugin_url = $plugin['Author'];
			}
			if ( $plugin_url ) {
				$plugin_url = "\n" . $plugin_url;
			}
			$return .= str_pad( $plugin['Name'] . ': ', 26, ' ' ) . $plugin['Version'] . $update . $plugin_url . "\n\n";
		}

		$return = apply_filters( 'edd_sysinfo_after_wordpress_ms_plugins', $return );
	}

	// Server configuration (really just versioning)
	$return .= "\n" . '-- Webserver Configuration' . "\n\n";
	$return .= 'PHP Version:              ' . PHP_VERSION . "\n";
	$return .= 'MySQL Version:            ' . $wpdb->db_version() . "\n";
	$return .= 'Webserver Info:           ' . $_SERVER['SERVER_SOFTWARE'] . "\n";

	$return = apply_filters( 'edd_sysinfo_after_webserver_config', $return );

	// PHP configs... now we're getting to the important stuff
	$return .= "\n" . '-- PHP Configuration' . "\n\n";
	$return .= 'Memory Limit:             ' . ini_get( 'memory_limit' ) . "\n";
	$return .= 'Upload Max Size:          ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Post Max Size:            ' . ini_get( 'post_max_size' ) . "\n";
	$return .= 'Upload Max Filesize:      ' . ini_get( 'upload_max_filesize' ) . "\n";
	$return .= 'Time Limit:               ' . ini_get( 'max_execution_time' ) . "\n";
	$return .= 'Max Input Vars:           ' . ini_get( 'max_input_vars' ) . "\n";
	$return .= 'Display Errors:           ' . ( ini_get( 'display_errors' ) ? 'On (' . ini_get( 'display_errors' ) . ')' : 'N/A' ) . "\n";
	$return .= 'PHP Arg Separator:        ' . edd_get_php_arg_separator_output() . "\n";

	$return = apply_filters( 'edd_sysinfo_after_php_config', $return );

	// PHP extensions and such
	$return .= "\n" . '-- PHP Extensions' . "\n\n";
	$return .= 'cURL:                     ' . ( function_exists( 'curl_init' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'fsockopen:                ' . ( function_exists( 'fsockopen' ) ? 'Supported' : 'Not Supported' ) . "\n";
	$return .= 'SOAP Client:              ' . ( class_exists( 'SoapClient' ) ? 'Installed' : 'Not Installed' ) . "\n";
	$return .= 'Suhosin:                  ' . ( extension_loaded( 'suhosin' ) ? 'Installed' : 'Not Installed' ) . "\n";

	$return = apply_filters( 'edd_sysinfo_after_php_ext', $return );

	// Session stuff
	$return .= "\n" . '-- Session Configuration' . "\n\n";
	$return .= 'EDD Use Sessions:         ' . ( defined( 'EDD_USE_PHP_SESSIONS' ) && EDD_USE_PHP_SESSIONS ? 'Enforced' : ( EDD()->session->use_php_sessions() ? 'Enabled' : 'Disabled' ) ) . "\n";
	$return .= 'Session:                  ' . ( isset( $_SESSION ) ? 'Enabled' : 'Disabled' ) . "\n";

	// The rest of this is only relevant is session is enabled
	if ( isset( $_SESSION ) ) {
		$return .= 'Session Name:             ' . esc_html( ini_get( 'session.name' ) ) . "\n";
		$return .= 'Cookie Path:              ' . esc_html( ini_get( 'session.cookie_path' ) ) . "\n";
		$return .= 'Save Path:                ' . esc_html( ini_get( 'session.save_path' ) ) . "\n";
		$return .= 'Use Cookies:              ' . ( ini_get( 'session.use_cookies' ) ? 'On' : 'Off' ) . "\n";
		$return .= 'Use Only Cookies:         ' . ( ini_get( 'session.use_only_cookies' ) ? 'On' : 'Off' ) . "\n";
	}

	$return = apply_filters( 'edd_sysinfo_after_session_config', $return );

	$return .= "\n" . '### End System Info ###';

	return $return;
}

/**
 * Generates a System Info download file
 *
 * @deprecated 3.1.2
 * @since 2.0
 */
function edd_tools_sysinfo_download() {
	_edd_deprecated_function( __FUNCTION__, '3.1.2' );

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	check_admin_referer( 'edd_download_system_info', 'edd_system_info' );

	nocache_headers();

	header( 'Content-Type: text/plain' );
	header( 'Content-Disposition: attachment; filename="edd-system-info.txt"' );

	echo wp_strip_all_tags( $_POST['edd-sysinfo'] );
	edd_die();
}

/**
 * Process bulk edit actions via AJAX
 *
 * @deprecated 3.1.1.4
 * @since 1.4.4
 * @return void
 */
function edd_save_bulk_edit() {

	_edd_deprecated_function( __FUNCTION__, '3.1.1.4' );
	$post_ids = ! empty( $_POST['post_ids'] )
		? wp_parse_id_list( $_POST['post_ids'] )
		: array();

	if ( ! empty( $post_ids ) && is_array( $post_ids ) ) {
		$price = isset( $_POST['price'] )
			? strip_tags( stripslashes( $_POST['price'] ) )
			: 0;

		foreach ( $post_ids as $post_id ) {
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				continue;
			}

			if ( ! empty( $price ) ) {
				update_post_meta( $post_id, 'edd_price', edd_sanitize_amount( $price ) );
			}
		}
	}

	die();
}

/**
 * Remove sale logs from refunded orders
 *
 * @deprecated 3.1.2
 * @since  2.4.3
 * @return void
 */
function edd_remove_refunded_sale_logs() {
	_edd_deprecated_function( __FUNCTION__, '3.1.2' );

	check_admin_referer( 'edd-upgrade' );
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		wp_die( __( 'You do not have permission to do shop upgrades', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
	}

	edd_set_time_limit();

	$step  = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$total = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : edd_count_payments()->refunded;

	$refunds = edd_get_payments(
		array(
			'status' => 'refunded',
			'number' => 20,
			'page'   => $step,
		)
	);

	if ( ! empty( $refunds ) ) {
		$edd_logs = EDD()->debug_log;
		// Refunded Payments found so process them
		foreach ( $refunds as $refund ) {

			// Remove related sale log entries
			$edd_logs->delete_logs(
				null,
				'sale',
				array(
					array(
						'key'   => '_edd_log_payment_id',
						'value' => $refund->ID,
					),
				)
			);
		}

		++$step;
		$redirect = add_query_arg(
			array(
				'page'        => 'edd-upgrades',
				'edd-upgrade' => 'remove_refunded_sale_logs',
				'step'        => urlencode( $step ),
				'total'       => urlencode( $total ),
				'_wpnonce'    => wp_create_nonce( 'edd-upgrade' ),
			),
			admin_url( 'index.php' )
		);

		edd_redirect( $redirect );

		// No more refunded payments found, finish up
	} else {
		edd_set_upgrade_complete( 'remove_refunded_sale_logs' );
		delete_option( 'edd_doing_upgrade' );
		edd_redirect( admin_url() );
	}
}

/**
 * Sales Log View
 *
 * @deprecated 3.0
 *
 * @since 1.4
 * @uses EDD_Sales_Log_Table::prepare_items()
 * @uses EDD_Sales_Log_Table::display()
 * @return void
 */
function edd_logs_view_sales() {
	_edd_deprecated_function( __FUNCTION__, '3.0' );

	// Setup or bail
	if ( ! edd_logs_view_setup( 'sales' ) ) {
		return;
	}

	$logs_table = new EDD_Sales_Log_Table();

	edd_logs_view_page( $logs_table, 'sales' );
}

/**
 * Product tax settings
 *
 * Outputs the option to mark whether a product is exclusive of tax
 *
 * @since 1.9
 * @deprecated 3.2.7
 * @param int $post_id Download (Post) ID.
 * @return void
 */
function edd_render_dowwn_tax_options( $post_id = 0 ) {
	_edd_deprecated_function( __FUNCTION__, '3.2.7', 'edd_render_down_tax_options' );
	edd_render_down_tax_options( $post_id );
}

/**
 * Email Template Preview
 *
 * @deprecated 3.3.0
 * @access private
 * @since 1.0.8.2
 */
function edd_email_template_preview( $email ) {
	_edd_deprecated_function( __FUNCTION__, '3.3.0' );
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}
	if ( 'purchase_receipt' !== $email->get_id() ) {
		return;
	}

	?>
	<div class="edd-email-editor-actions">
		<a href="<?php echo esc_url( add_query_arg( array( 'edd_action' => 'preview_email' ), home_url() ) ); ?>" class="button-secondary" target="_blank"><?php esc_html_e( 'Preview Purchase Receipt', 'easy-digital-downloads' ); ?></a>
		<a href="
		<?php
		echo esc_url(
			wp_nonce_url(
				add_query_arg(
					array(
						'edd_action' => 'send_test_email',
						'email'      => 'order_receipt',
					)
				),
				'edd-test-email'
			)
		);
		?>
					" class="button-secondary"><?php esc_html_e( 'Send Test Email', 'easy-digital-downloads' ); ?></a>
	</div>
	<?php
}

/**
 * Output the entire options page
 *
 * @since 1.0
 * @deprecated 3.3.0
 * @return void
 */
function edd_options_page() {
	_edd_deprecated_function( __FUNCTION__, '3.3.0', 'EDD\Admin\Settings\Screen::render' );
	EDD\Admin\Settings\Screen::render();
}

/**
 * Output the options page form and fields for this tab & section
 *
 * @since 3.0
 * @deprecated 3.3.0
 * @param string  $active_tab
 * @param string  $section
 * @param boolean $override
 */
function edd_options_page_form( $active_tab = '', $section = '', $override = false ) {
	_edd_deprecated_function( __FUNCTION__, '3.3.0', 'EDD\Admin\Settings\Screen::form' );
	EDD\Admin\Settings\Screen::form( $active_tab, $section, $override );
}

/**
 * Output the primary options page navigation
 *
 * @since 3.0
 *
 * @param array  $tabs       All available tabs.
 * @param string $active_tab Current active tab.
 */
function edd_options_page_primary_nav( $tabs, $active_tab = '' ) {
	_edd_deprecated_function( __FUNCTION__, '3.3.0', 'EDD\Admin\Settings\Screen::primary_navigation' );
	EDD\Admin\Settings\Screen::primary_navigation( $tabs, $active_tab );
}

/**
 * Output the secondary options page navigation
 *
 * @since 3.0
 *
 * @param string $active_tab
 * @param string $section
 * @param array  $sections
 */
function edd_options_page_secondary_nav( $active_tab = '', $section = '', $sections = array() ) {
	_edd_deprecated_function( __FUNCTION__, '3.3.0', 'EDD\Admin\Settings\Screen::secondary_navigation' );
	EDD\Admin\Settings\Screen::secondary_navigation( $active_tab, $section, $sections );
}

/**
 * Shows the tools panel which contains EDD-specific tools including the built-in import/export system.
 *
 * @since 1.8
 * @deprecated 3.3.0
 */
function edd_tools_page() {
	_edd_deprecated_function( __FUNCTION__, '3.3.0', 'EDD\Admin\Tools\Screen::render' );
	EDD\Admin\Tools\Screen::render();
}


/**
 * Retrieve tools tabs.
 *
 * @since 2.0
 *
 * @return array Tabs for the 'Tools' page.
 */
function edd_get_tools_tabs() {
	_edd_deprecated_function( __FUNCTION__, '3.3.0', 'EDD\Admin\Tools\Screen\get_tabs' );
	return EDD\Admin\Tools\Screen::get_tabs();
}

/**
 * Adds the EDD branded header to the EDD settings pages.
 *
 * @since 2.11.3
 * @deprecated 3.3.0
 */
function edd_admin_header() {
	_edd_deprecated_function( __FUNCTION__, '3.3.0', 'EDD\Admin\Menu\Header\render' );
}

/**
 * When the Download list table loads, call the function to view our tabs.
 *
 * @since 2.8.9
 * @since 2.11.3 Unhooked this to revert to standard admin H1 tags.
 * @since 3.0    Added back as download categories/tags have been removed from the admin menu.
 * @deprecated 3.3.0
 *
 * @return void
 */
function edd_products_tabs() {
	_edd_deprecated_function( __FUNCTION__, '3.3.0' );
	$screen = get_current_screen();
	if ( 'download' !== $screen->post_type || 'edit' !== $screen->base ) {
		return;
	}
	edd_display_product_tabs();
}

/**
 * When the Download list table loads, call the function to view our tabs.
 *
 * @since 3.0
 *
 * @return void
 */
function edd_taxonomies_tabs() {
	_edd_deprecated_function( __FUNCTION__, '3.3.0' );

	// Bail if not viewing a taxonomy.
	if ( empty( $_GET['taxonomy'] ) ) {
		return;
	}

	// Get taxonomies.
	$taxonomy   = sanitize_key( $_GET['taxonomy'] );
	$taxonomies = get_object_taxonomies( 'download' );

	// Bail if current taxonomy is not a download taxonomy.
	if ( ! in_array( $taxonomy, $taxonomies, true ) ) {
		return;
	}

	// Output the tabs.
	edd_display_product_tabs();
}

/**
 * Misc File Download Settings Sanitization
 *
 * @since 2.5
 * @deprecated 3.3.3 Moved to EDD\Admin\Settings\Sanitize
 *
 * @param array $input The value inputted in the field.
 *
 * @return string $input Sanitized value
 */
function edd_settings_sanitize_misc_file_downloads( $input ) {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	_edd_deprecated_function( __FUNCTION__, '3.3.3', 'EDD\Admin\Settings\Sanitize\Tabs\Misc\FileDownloads::additional_processing' );

	if ( edd_get_file_download_method() != $input['download_method'] || ! edd_htaccess_exists() ) {
		// Force the .htaccess files to be updated if the Download method was changed.
		edd_create_protection_files( true, $input['download_method'] );
	}

	return $input;
}

/**
 * Misc Accounting Settings Sanitization
 *
 * @since 2.5
 * @deprecated 3.3.3 Moved to EDD\Admin\Settings\Sanitize
 *
 * @param array $input The value inputted in the field.
 *
 * @return array $input Sanitized value
 */
function edd_settings_sanitize_misc_accounting( $input ) {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	return $input;
}

/**
 * Sanitizes banned emails.
 *
 * @since 3.0
 * @deprecated 3.3.3 Moved to EDD\Admin\Settings\Sanitize
 */
function edd_sanitize_banned_emails( $input ) {

	$emails = '';
	if ( ! empty( $input['banned_emails'] ) ) {
		// Sanitize the input.
		$emails = array_map( 'trim', explode( "\n", $input['banned_emails'] ) );
		$emails = array_unique( $emails );
		$emails = array_map( 'sanitize_text_field', $emails );

		foreach ( $emails as $id => $email ) {
			if ( ! is_email( $email ) && $email[0] != '@' && $email[0] != '.' ) {
				unset( $emails[ $id ] );
			}
		}
	}
	$input['banned_emails'] = $emails;

	return $input;
}

/**
 * Payment Gateways Settings Sanitization
 *
 * @since 2.7
 * @deprecated 3.3.3 Moved to EDD\Admin\Settings\Sanitize
 *
 * @param array $input The value inputted in the field
 *
 * @return string $input Sanitized value
 */
function edd_settings_sanitize_gateways( $input = array() ) {

	// Bail if user cannot manage shop settings
	if ( ! current_user_can( 'manage_shop_settings' ) || empty( $input['default_gateway'] ) ) {
		return $input;
	}

	// Unset the default gateway if there are no `gateways` enabled
	if ( empty( $input['gateways'] ) || '-1' == $input['gateways'] ) {
		unset( $input['default_gateway'] );

		// Current gateway is no longer enabled, so
	} elseif ( ! array_key_exists( $input['default_gateway'], $input['gateways'] ) ) {
		$enabled_gateways = $input['gateways'];

		reset( $enabled_gateways );

		$first_gateway = key( $enabled_gateways );

		if ( $first_gateway ) {
			$input['default_gateway'] = $first_gateway;
		}
	}

	return $input;
}

/**
 * Taxes Settings Sanitization
 *
 * Adds a settings error (for the updated message)
 * This also saves the tax rates table
 *
 * @since 1.6
 * @deprecated 3.3.3 Moved to EDD\Admin\Settings\Sanitize
 *
 * @param array $input The value inputted in the field
 *
 * @return array $input Sanitized value.
 */
function edd_settings_sanitize_taxes( $input ) {

	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return $input;
	}

	if ( ! isset( $_POST['tax_rates'] ) ) {
		return $input;
	}

	$tax_rates = ! empty( $_POST['tax_rates'] )
		? $_POST['tax_rates']
		: array();

	foreach ( $tax_rates as $tax_rate ) {

		$scope = isset( $tax_rate['global'] )
			? 'country'
			: 'region';

		$region = isset( $tax_rate['state'] )
			? sanitize_text_field( $tax_rate['state'] )
			: '';

		$name = '*' === $tax_rate['country']
			? ''
			: sanitize_text_field( $tax_rate['country'] );

		if ( empty( $name ) ) {
			$scope = 'global';
		}

		$adjustment_data = array(
			'name'        => $name,
			'type'        => 'tax_rate',
			'scope'       => $scope,
			'amount_type' => 'percent',
			'amount'      => floatval( $tax_rate['rate'] ),
			'description' => $region,
		);

		if ( ( empty( $adjustment_data['name'] ) && 'global' !== $adjustment_data['scope'] ) || $adjustment_data['amount'] < 0 ) {
			continue;
		}

		$existing_adjustment = edd_get_adjustments( $adjustment_data );

		if ( ! empty( $existing_adjustment ) ) {
			$adjustment                = $existing_adjustment[0];
			$adjustment_data['status'] = sanitize_text_field( $tax_rate['status'] );

			edd_update_adjustment( $adjustment->id, $adjustment_data );
		} else {
			$adjustment_data['status'] = 'active';

			edd_add_tax_rate( $adjustment_data );
		}
	}

	return $input;
}

/**
 * Price Section
 *
 * If variable pricing is not enabled, simply output a single input box.
 *
 * If variable pricing is enabled, outputs a table of all current prices.
 * Extensions can add column heads to the table via the `edd_download_file_table_head`
 * hook, and actual columns via `edd_download_file_table_row`
 *
 * @since 1.0
 * @deprecated 3.3.6
 *
 * @see edd_render_price_row()
 *
 * @param int $post_id Download (Post) ID.
 */
function edd_render_price_field( $post_id ) {
	$metabox = new EDD\Admin\Downloads\Metabox();
	$metabox->render_price_fields( $post_id );
}

/**
 * Register all the meta boxes for the Download custom post type
 *
 * @since 1.0
 * @return void
 */
function edd_add_download_meta_box( $post_type = '', $post = null ) {
	$metaboxes = new EDD\Admin\Downloads\Metaboxes();
	$metaboxes->add_meta_boxes( $post_type, $post );
}

/**
 * Download Metabox
 *
 * Extensions (as well as the core plugin) can add items to the main download
 * configuration metabox via the `edd_meta_box_fields` action.
 *
 * @since 1.0
 * @return void
 */
function edd_render_download_meta_box() {
	$post_id = get_the_ID();

	/*
	 * Output the price fields
	 * @since 1.9
	 */
	do_action( 'edd_meta_box_price_fields', $post_id );

	/*
	 * Output the price fields
	 *
	 * Left for backwards compatibility
	 *
	 */
	do_action( 'edd_meta_box_fields', $post_id );

	wp_nonce_field( basename( __FILE__ ), 'edd_download_meta_box_nonce' );
}

/**
 * Download Files Metabox
 *
 * @since 1.9
 * @return void
 */
function edd_render_files_meta_box() {
	/*
	 * Output the files fields
	 * @since 1.9
	 */
	do_action( 'edd_meta_box_files_fields', get_the_ID(), '' );
}

/**
 * Download Settings Metabox
 *
 * @since 1.9
 * @return void
 */
function edd_render_settings_meta_box() {
	/*
	 * Output the files fields
	 * @since 1.9
	 */
	do_action( 'edd_meta_box_settings_fields', get_the_ID() );
}

/**
 * Product Notes Meta Box
 *
 * Renders the Product Notes meta box
 *
 * @since 1.2.1
 *
 * @return void
 */
function edd_render_product_notes_meta_box() {
	do_action( 'edd_product_notes_meta_box_fields', get_the_ID() );
}

/**
 * Render Stats Meta Box
 *
 * @since 1.0
 * @return void
 */
function edd_render_stats_meta_box() {
	$post_id = get_the_ID();

	if ( ! current_user_can( 'view_product_stats', $post_id ) ) {
		return;
	}

	$earnings = edd_get_download_earnings_stats( $post_id );
	$sales    = edd_get_download_sales_stats( $post_id );

	$sales_url = add_query_arg(
		array(
			'page'       => 'edd-payment-history',
			'product-id' => urlencode( $post_id ),
		),
		edd_get_admin_base_url()
	);

	$earnings_report_url = edd_get_admin_url(
		array(
			'page'     => 'edd-reports',
			'view'     => 'downloads',
			'products' => absint( $post_id ),
		)
	);
	?>

	<p class="product-sales-stats">
		<span class="label"><?php esc_html_e( 'Net Sales:', 'easy-digital-downloads' ); ?></span>
		<span><a href="<?php echo esc_url( $sales_url ); ?>"><?php echo esc_html( $sales ); ?></a></span>
	</p>

	<p class="product-earnings-stats">
		<span class="label"><?php esc_html_e( 'Net Revenue:', 'easy-digital-downloads' ); ?></span>
		<span>
			<a href="<?php echo esc_url( $earnings_report_url ); ?>">
				<?php echo edd_currency_filter( edd_format_amount( $earnings ) ); ?>
			</a>
		</span>
	</p>

	<hr />

	<p class="file-download-log">
		<?php
		$url = edd_get_admin_url(
			array(
				'page'     => 'edd-tools',
				'view'     => 'file_downloads',
				'tab'      => 'logs',
				'download' => absint( $post_id ),
			)
		);
		?>
		<span>
			<a href="<?php echo esc_url( $url ); ?>">
				<?php esc_html_e( 'View File Download Log', 'easy-digital-downloads' ); ?>
			</a>
		</span>
		<br/>
	</p>
	<?php
	do_action( 'edd_stats_meta_box' );
}

/**
 * Product type options
 *
 * @since       1.6
 * @param int          $post_id  Download (Post) ID.
 * @param EDD_Download $download Download object.
 * @return      void
 */
function edd_render_product_type_field( $post_id = 0, $download = null ) {
	if ( ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	$types = edd_get_download_types();
	$type  = $download ? $download->type : false;
	ksort( $types );
	?>
	<div class="edd-form-group">
		<label for="_edd_product_type" class="edd-form-group__label">
			<?php
			echo esc_html(
				apply_filters( 'edd_product_type_options_heading', __( 'Product Type Options:', 'easy-digital-downloads' ) )
			);
			?>
		</label>
		<div class="edd-form-group__control">
			<?php
			echo EDD()->html->select(
				array(
					'options'          => $types,
					'name'             => '_edd_product_type',
					'id'               => '_edd_product_type',
					'selected'         => $type,
					'show_option_all'  => false,
					'show_option_none' => false,
					'class'            => 'edd-form-group__input',
				)
			);
			?>
		</div>
		<p class="edd-form-group__help description">
			<?php esc_html_e( 'Sell this item as a single product with download files, or select a custom product type with different options, which may not necessarily include download files.', 'easy-digital-downloads' ); ?>
		</p>
	</div>
	<?php
}

/**
 * Individual Price Row
 *
 * Used to output a table row for each price associated with a download.
 * Can be called directly, or attached to an action.
 *
 * @since 1.2.2
 * @deprecated 3.3.6
 *
 * @param int   $key   The cart item key.
 * @param array $args  Array of arguments for the price row.
 * @param int   $post_id The ID of the download.
 */
function edd_render_price_row( $key, $args, $post_id, $index ) {
	global $wp_filter;

	if ( is_numeric( $post_id ) && ! current_user_can( 'edit_product', $post_id ) ) {
		return;
	}

	if ( is_null( $post_id ) && ! current_user_can( 'edit_products' ) ) {
		return;
	}

	$defaults = array(
		'name'   => null,
		'amount' => null,
	);

	$args = wp_parse_args( $args, $defaults );

	$default_price_id     = edd_get_default_variable_price( $post_id );
	$currency_position    = edd_get_option( 'currency_position', 'before' );
	$custom_price_options = isset( $wp_filter['edd_download_price_option_row'] ) ? true : false;
	?>
	<div class="edd-repeatable-row-header edd-draghandle-anchor">
		<span class="edd-repeatable-row-title" title="<?php _e( 'Click and drag to re-order price options', 'easy-digital-downloads' ); ?>">
			<?php
			printf(
				/* translators: %s: price ID. */
				__( 'Price ID: %s', 'easy-digital-downloads' ),
				'<span class="edd_price_id">' . esc_html( $key ) . '</span>'
			);
			?>
			<input type="hidden" name="edd_variable_prices[<?php echo esc_attr( $key ); ?>][index]" class="edd_repeatable_index" value="<?php echo esc_attr( $index ); ?>"/>
		</span>
		<?php
		$actions = array();
		if ( $custom_price_options ) {
			$actions['show_advanced'] = sprintf(
				'<a href="#" class="toggle-custom-price-option-section">%s</a>',
				__( 'Show advanced settings', 'easy-digital-downloads' )
			);
		}

		$actions['remove'] = sprintf(
			/* translators: %1$s is the remove link, %2$s is the screen reader text. */
			'<a class="edd-remove-row edd-delete" data-type="price">%1$s<span class="screen-reader-text">%2$s</span></a>',
			__( 'Remove', 'easy-digital-downloads' ),
			sprintf(
				/* translators: %s: price ID. */
				__( 'Remove price option %s', 'easy-digital-downloads' ),
				esc_html( $key )
			)
		);
		?>
		<span class="edd-repeatable-row-actions">
			<?php echo implode( '&nbsp;&#124;&nbsp;', $actions ); ?>
		</span>
	</div>

	<div class="edd-repeatable-row-standard-fields">

		<div class="edd-form-group edd-option-name">
			<label for="edd_variable_prices-<?php echo esc_attr( $key ); ?>-name" class="edd-form-group__label edd-repeatable-row-setting-label">
				<?php esc_html_e( 'Option Name', 'easy-digital-downloads' ); ?>
			</label>
			<div class="edd-form-group__control">
			<?php
			echo EDD()->html->text(
				array(
					'name'        => 'edd_variable_prices[' . $key . '][name]',
					'id'          => 'edd_variable_prices-' . $key . '-name',
					'value'       => esc_attr( $args['name'] ),
					'placeholder' => __( 'Option Name', 'easy-digital-downloads' ),
					'class'       => 'edd_variable_prices_name large-text',
				)
			);
			?>
			</div>
		</div>

		<div class="edd-form-group edd-option-price">
			<label for="edd_variable_prices-<?php echo esc_attr( $key ); ?>-amount" class="edd-repeatable-row-setting-label">
				<?php esc_html_e( 'Price', 'easy-digital-downloads' ); ?>
			</label>
			<?php
			$price_args = array(
				'name'        => 'edd_variable_prices[' . $key . '][amount]',
				'id'          => 'edd_variable_prices-' . $key . '-amount',
				'value'       => $args['amount'],
				'placeholder' => edd_format_amount( 9.99 ),
				'class'       => 'edd-form-group__input edd-price-field',
			);
			?>

			<div class="edd-form-group__control edd-price-input-group">
				<?php
				if ( 'before' === $currency_position ) {
					?>
					<span class="edd-amount-control__currency is-before"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
					<?php
					echo EDD()->html->text( $price_args );
				} else {
					echo EDD()->html->text( $price_args );
					?>
					<span class="edd-amount-control__currency is-after"><?php echo esc_html( edd_currency_filter( '' ) ); ?></span>
					<?php
				}
				?>
			</div>
		</div>

		<div class="edd-form-group edd_repeatable_default edd_repeatable_default_wrapper">
			<div class="edd-form-group__control">
			<label for="edd_default_price_id_<?php echo esc_attr( $key ); ?>" class="edd-repeatable-row-setting-label">
				<?php esc_html_e( 'Default', 'easy-digital-downloads' ); ?>
			</label>
			<?php
			printf(
				'<input type="radio" %1$s class="edd_repeatable_default_input" name="_edd_default_price_id" id="%2$s" value="%3$d" />',
				checked( $default_price_id, $key, false ),
				'edd_default_price_id_' . esc_attr( $key ),
				esc_attr( $key )
			);
			?>
			<span class="screen-reader-text">
				<?php
				/* translators: %s: price ID. */
				printf( __( 'Set ID %s as default price', 'easy-digital-downloads' ), $key );
				?>
			</span>
			</div>
		</div>

	</div>

	<?php
	if ( $custom_price_options ) {
		?>

		<div class="edd-custom-price-option-sections-wrap">
			<div class="edd-custom-price-option-sections">
				<?php
					do_action( 'edd_download_price_option_row', $post_id, $key, $args );
				?>
			</div>
		</div>

		<?php
	}
}

/**
 * Add shortcode to settings meta box
 *
 * @since 2.5
 * @deprecated 3.3.6
 * @param int           $post_id  Download (Post) ID.
 * @param \EDD_Download $download Download object.
 * @return void
 */
function edd_render_meta_box_shortcode( $post_id = 0, $download = null ) {}

/**
 * Register a view for the single customer view
 *
 * @since  2.3
 * @deprecated 3.3.7
 *
 * @param  array $views An array of existing views.
 * @return array       The default customer views.
 */
function edd_register_default_customer_views( $views ) {
	return array(
		'overview'  => 'edd_customers_view',
		'emails'    => 'edd_customers_emails_view',
		'addresses' => 'edd_customers_addresses_view',
		'delete'    => 'edd_customers_delete_view',
		'notes'     => 'edd_customer_notes_view',
		'tools'     => 'edd_customer_tools_view',
	);
}

/**
 * Register a tab for the single customer view
 *
 * @since  2.3
 * @deprecated 3.3.7
 *
 * @param  array $tabs An array of existing tabs.
 * @return array       The default customer tabs.
 */
function edd_register_default_customer_tabs( $tabs ) {
	return array(
		'overview'  => array(
			'dashicon' => 'dashicons-admin-users',
			'title'    => _x( 'Profile', 'Customer Details tab title', 'easy-digital-downloads' ),
		),
		'emails'    => array(
			'dashicon' => 'dashicons-email',
			'title'    => _x( 'Emails', 'Customer Emails tab title', 'easy-digital-downloads' ),
		),
		'addresses' => array(
			'dashicon' => 'dashicons-admin-home',
			'title'    => _x( 'Addresses', 'Customer Addresses tab title', 'easy-digital-downloads' ),
		),
		'notes'     => array(
			'dashicon' => 'dashicons-admin-comments',
			'title'    => _x( 'Notes', 'Customer Notes tab title', 'easy-digital-downloads' ),
		),
		'tools'     => array(
			'dashicon' => 'dashicons-admin-tools',
			'title'    => _x( 'Tools', 'Customer Tools tab title', 'easy-digital-downloads' ),
		),
	);
}

/**
 * Register the Delete icon as late as possible so it's at the bottom
 *
 * @since  2.3.1
 * @deprecated 3.3.7
 * @param  array $tabs An array of existing tabs.
 * @return array       The delete tab.
 */
function edd_register_delete_customer_tab( $tabs ) {
	return array(
		'delete' => array(
			'dashicon' => 'dashicons-trash',
			'title'    => _x( 'Delete', 'Delete Customer tab title', 'easy-digital-downloads' ),
		),
	);
}

/**
 * Register the earnings report batch exporter
 *
 * @since  2.7
 * @deprecated 3.3.8
 */
function edd_register_earnings_report_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_earnings_report_batch_processor', 10, 1 );
}

/**
 * Loads the earnings report batch process if needed
 *
 * @since  2.7
 * @deprecated 3.3.8
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_earnings_report_batch_processor( $class ) {}

/**
 * Register the sales and earnings report batch exporter.
 *
 * @since 3.0
 * @deprecated 3.3.8
 */
function edd_register_sales_and_earnings_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_sales_and_earnings_batch_processor', 10, 1 );
}

/**
 * Loads the sales and earnings batch process if needed.
 *
 * @since 3.0
 * @deprecated 3.3.8
 * @param string $class The class being requested to run for the batch export
 */
function edd_include_sales_and_earnings_batch_processor( $class ) {}

/**
 * Register the sales batch exporter.
 *
 * @since 2.7
 * @deprecated 3.3.8
 */
function edd_register_sales_export_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_sales_export_batch_processor', 10, 1 );
}

/**
 * Loads the sales export batch process if needed
 *
 * @since  2.7
 * @deprecated 3.3.8
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_sales_export_batch_processor( $class ) {}

/**
 * Register the payments batch exporter
 *
 * @since  2.4.2
 */
function edd_register_payments_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_payments_batch_processor', 10, 1 );
}

/**
 * Loads the payments batch processor if needed.
 *
 * @since 2.4.2
 * @deprecated 3.3.8
 * @param string $class The class being requested to run for the batch export
 */
function edd_include_payments_batch_processor( $class ) {}

/**
 * Register the taxed orders report batch exporter.
 *
 * @since 3.0
 */
function edd_register_taxed_orders_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_taxed_orders_batch_processor', 10, 1 );
}

/**
 * Loads the taxed orders report batch process if needed.
 *
 * @since 3.0
 *
 * @param string $class The class being requested to run for the batch export
 */
function edd_include_taxed_orders_batch_processor( $class ) {}

/**
 * Register the customers batch exporter.
 *
 * @since 2.4.2
 */
function edd_register_customers_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_customers_batch_processor', 10, 1 );
}

/**
 * Loads the customers batch processor if needed.
 *
 * @since 2.4.2
 *
 * @param string $class The class being requested to run for the batch export.
 */
function edd_include_customers_batch_processor( $class ) {}

/**
 * Register the taxed customers report batch exporter.
 *
 * @since 3.0
 * @deprecated 3.3.8
 */
function edd_register_taxed_customers_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_taxed_customers_batch_processor', 10, 1 );
}

/**
 * Loads the taxed customers report batch process if needed.
 *
 * @since 3.0
 * @deprecated 3.3.8
 * @param string $class The class being requested to run for the batch export
 */
function edd_include_taxed_customers_batch_processor( $class ) {}

/**
 * Register the download products batch exporter
 *
 * @since  2.5
 * @deprecated 3.3.8
 */
function edd_register_downloads_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_downloads_batch_processor', 10, 1 );
}

/**
 * Loads the file downloads batch process if needed
 *
 * @since  2.5
 * @deprecated 3.3.8
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_downloads_batch_processor( $class ) {}

/**
 * Register the API requests batch exporter
 *
 * @since  2.7
 * @deprecated 3.3.8
 */
function edd_register_api_requests_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_api_requests_batch_processor', 10, 1 );
}

/**
 * Loads the API requests batch process if needed
 *
 * @since  2.7
 * @deprecated 3.3.8
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_api_requests_batch_processor( $class ) {}

/**
 * Register the file downloads batch exporter
 *
 * @since  2.4.2
 * @deprecated 3.3.8
 */
function edd_register_file_downloads_batch_export() {
	add_action( 'edd_batch_export_class_include', 'edd_include_file_downloads_batch_processor', 10, 1 );
}

/**
 * Loads the file downloads batch process if needed
 *
 * @since  2.4.2
 * @deprecated 3.3.8
 * @param  string $class The class being requested to run for the batch export
 * @return void
 */
function edd_include_file_downloads_batch_processor( $class ) {}

/**
 * Add an email address to the customer from within the admin and log a customer note
 *
 * @since  2.6
 * @deprecated 3.3.8
 * @param  array $args  Array of arguments: nonce, customer id, and email address.
 * @return mixed        Echos JSON if doing AJAX. Returns array of success (bool) and message (string) if not AJAX.
 */
function edd_add_customer_email( $args = array() ) {
	$emails = new EDD\Admin\Customers\Emails();

	return $emails->maybe_add_email( $args );
}

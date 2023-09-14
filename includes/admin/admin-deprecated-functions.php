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
					submit_button( __( 'Copy to Clipboard',         'easy-digital-downloads' ), 'secondary edd-inline-button', 'edd-copy-system-info', false, array( 'onclick' => "this.form['edd-sysinfo'].focus();this.form['edd-sysinfo'].select();document.execCommand('copy');return false;" ) );
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

	$return  = '### Begin System Info (Generated ' . date( 'Y-m-d H:i:s' ) . ') ###' . "\n\n";

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
	$return .= "\n" . '-- Customized Templates' . "\n\n";
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
	//$return .= 'Admin AJAX:               ' . ( edd_test_ajax_works() ? 'Accessible' : 'Inaccessible' ) . "\n";
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

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
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

		$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
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

			$update = ( array_key_exists( $plugin_path, $updates ) ) ? ' (needs update - ' . $updates[ $plugin_path ]->update->new_version . ')' : '';
			$plugin = get_plugin_data( $plugin_path );
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

	$step    = isset( $_GET['step']  ) ? absint( $_GET['step']  ) : 1;
	$total   = isset( $_GET['total'] ) ? absint( $_GET['total'] ) : edd_count_payments()->refunded;

	$refunds = edd_get_payments( array(
		'status' => 'refunded',
		'number' => 20,
		'page'   => $step
	) );

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
						'value' => $refund->ID
					)
				)
			);
		}

		$step++;
		$redirect = add_query_arg( array(
			'page'        => 'edd-upgrades',
			'edd-upgrade' => 'remove_refunded_sale_logs',
			'step'        => urlencode( $step ),
			'total'       => urlencode( $total ),
			'_wpnonce'    => wp_create_nonce( 'edd-upgrade' ),
		), admin_url( 'index.php' ) );

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

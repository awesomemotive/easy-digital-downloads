<?php
/**
 * Admin Options Page
 *
 * @package     EDD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Output the primary options page navigation
 *
 * @since 3.0
 * @param string $active_tab
 */
function edd_options_page_primary_nav( $active_tab = '' ) {
	$tabs = edd_get_settings_tabs();

	ob_start();?>

	<nav class="nav-tab-wrapper edd-nav-tab-wrapper edd-settings-nav" aria-label="<?php esc_attr_e( 'Secondary menu', 'easy-digital-downloads' ); ?>">
		<?php

		foreach ( $tabs as $tab_id => $tab_name ) {
			$tab_url = add_query_arg( array(
				'settings-updated' => false,
				'tab'              => $tab_id,
			) );

			// Remove the section from the tabs so we always end up at the main section
			$tab_url = remove_query_arg( 'section', $tab_url );
			$active  = $active_tab == $tab_id
				? ' nav-tab-active'
				: '';

			// Link
			echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">';
			echo esc_html( $tab_name );
			echo '</a>';
		}
		?>
	</nav>

	<?php

	echo ob_get_clean();
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

	// Back compat for section'less tabs (Licenses, etc...)
	if ( empty( $sections ) ) {
		$section  = 'main';
		$sections = array(
			'main' => __( 'General', 'easy-digital-downloads' )
		);
	}

	// Default links array
	$links = array();

	// Loop through sections
	foreach ( $sections as $section_id => $section_name ) {

		// Tab & Section
		$tab_url = add_query_arg( array(
			'post_type' => 'download',
			'page'      => 'edd-settings',
			'tab'       => $active_tab,
			'section'   => $section_id
		) );

		// Settings not updated
		$tab_url = remove_query_arg( 'settings-updated', $tab_url );

		// Class for link
		$class = ( $section === $section_id )
			? 'current'
			: '';

		// Add to links array
		$links[ $section_id ] = '<li class="' . esc_attr( $class ) . '"><a class="' . esc_attr( $class ) . '" href="' . esc_url( $tab_url ) . '">' . esc_html( $section_name ) . '</a><li>';
	} ?>

	<div class="wp-clearfix">
		<ul class="subsubsub edd-settings-sub-nav">
			<?php echo implode( '', $links ); ?>
		</ul>
	</div>

	<?php
}

/**
 * Output the options page form and fields for this tab & section
 *
 * @since 3.0
 *
 * @param string  $active_tab
 * @param string  $section
 * @param boolean $override
 */
function edd_options_page_form( $active_tab = '', $section = '', $override = false ) {

	// Setup the action & section suffix
	$suffix = ! empty( $section )
		? $active_tab . '_' . $section
		: $active_tab . '_main';

	// Find out if we're displaying a sidebar.
	$is_promo_active = edd_is_promo_active();
	$wrapper_class   = ( true === $is_promo_active )
		? array( ' edd-has-sidebar' )
		: array();
	?>

	<div class="edd-settings-wrap<?php echo esc_attr( implode( ' ', $wrapper_class ) ); ?> wp-clearfix">
		<div class="edd-settings-content">
			<form method="post" action="options.php" class="edd-settings-form">
				<?php

				settings_fields( 'edd_settings' );

				if ( 'main' === $section ) {
					do_action( 'edd_settings_tab_top', $active_tab );
				}

				do_action( 'edd_settings_tab_top_' . $suffix );

				do_settings_sections( 'edd_settings_' . $suffix );

				do_action( 'edd_settings_tab_bottom_' . $suffix  );

				// For backwards compatibility
				if ( 'main' === $section ) {
					do_action( 'edd_settings_tab_bottom', $active_tab );
				}

				// If the main section was empty and we overrode the view with the
				// next subsection, prepare the section for saving
				if ( true === $override ) {
					?><input type="hidden" name="edd_section_override" value="<?php echo esc_attr( $section ); ?>" /><?php
				}

				submit_button(); ?>
			</form>
		</div>
		<?php
		if ( true === $is_promo_active ) {
			edd_options_sidebar();
		}
		?>
	</div>

	<?php
}

/**
 * Display the sidebar
 *
 * @since 2.9.20
 *
 * @return string
 */
function edd_options_sidebar() {

	// Get settings tab and section info
	$active_tab     = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
	$active_tab     = array_key_exists( $active_tab, edd_get_settings_tabs() ) ? $active_tab : 'general';
	$active_section = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'main';
	$active_section = array_key_exists( $active_section, edd_get_settings_tab_sections( $active_tab ) ) ? $active_section : 'main';

	// The coupon code we're promoting
	$coupon_code = 'BFCM2019';

	// Build the main URL for the promotion
	$args = array(
		'utm_source'   => 'settings',
		'utm_medium'   => 'wp-admin',
		'utm_campaign' => 'bfcm2019',
		'utm_content'  => 'sidebar-promo-' . $active_tab . '-' . $active_section,
	);
	$url  = add_query_arg( $args, 'https://easydigitaldownloads.com/pricing/' );
	?>
	<div class="edd-settings-sidebar">
		<div class="edd-settings-sidebar-content">
			<div class="edd-sidebar-header-section">
				<img class="edd-bfcm-header" src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/promo/bfcm-header.svg' ); ?>">
			</div>
			<div class="edd-sidebar-description-section">
				<p class="edd-sidebar-description"><?php _e( 'Save 25% on all Easy Digital Downloads purchases <strong>this week</strong>, including renewals and upgrades!', 'easy-digital-downloads' ); ?></p>
			</div>
			<div class="edd-sidebar-coupon-section">
				<label for="edd-coupon-code"><?php _e( 'Use code at checkout:', 'easy-digital-downloads' ); ?></label>
				<input id="edd-coupon-code" type="text" value="<?php echo $coupon_code; ?>" readonly>
				<p class="edd-coupon-note"><?php _e( 'Sale ends 23:59 PM December 6th CST. Save 25% on <a href="https://sandhillsdev.com/projects/" target="_blank">our other plugins</a>.', 'easy-digital-downloads' ); ?></p>
			</div>
			<div class="edd-sidebar-footer-section">
				<a class="edd-cta-button" href="<?php echo esc_url( $url ); ?>" target="_blank"><?php _e( 'Shop Now!', 'easy-digital-downloads' ); ?></a>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Output the entire options page
 *
 * @since 1.0
 * @return void
 */
function edd_options_page() {
	// Enqueue scripts.
	wp_enqueue_script( 'edd-admin-settings' );

	// Try to figure out where we are
	$all_settings   = edd_get_registered_settings();
	$settings_tabs  = edd_get_settings_tabs();
	$settings_tabs  = empty( $settings_tabs ) ? array() : $settings_tabs;
	$active_tab     = isset( $_GET['tab']   ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
	$active_tab     = array_key_exists( $active_tab, $settings_tabs ) ? $active_tab : 'general';
	$sections       = edd_get_settings_tab_sections( $active_tab );
	$section        = isset( $_GET['section'] ) ? sanitize_text_field( $_GET['section'] ) : 'main';
	$section        = array_key_exists( $section, $sections ) ? $section : 'main';

	// Default values
	$has_main_settings = true;
	$override          = false;

	// Let's verify we have a 'main' section to show
	if ( empty( $all_settings[ $active_tab ]['main'] ) ) {
		$has_main_settings = false;
	}

	// Check for old non-sectioned settings (see #4211 and #5171)
	if ( false === $has_main_settings ) {
		foreach( $all_settings[ $active_tab ] as $sid => $stitle ) {
			if ( is_string( $sid ) && ! empty( $sections ) && array_key_exists( $sid, $sections ) ) {
				continue;
			} else {
				$has_main_settings = true;
				break;
			}
		}
	}

	// Maybe override section
	if ( false === $has_main_settings ) {
		unset( $sections['main'] );

		if ( 'main' === $section ) {
			foreach ( $sections as $section_key => $section_title ) {
				if ( ! empty( $all_settings[ $active_tab ][ $section_key ] ) ) {
					$section  = $section_key;
					$override = true;
					break;
				}
			}
		}
	}

	// Start a buffer
	ob_start(); ?>

	<div class="wrap <?php echo 'wrap-' . esc_attr( $active_tab ); ?>">
		<h1><?php _e( 'Settings', 'easy-digital-downloads' ); ?></h1><?php

		// Primary nav
		edd_options_page_primary_nav( $active_tab );

		// Secondary nav
		edd_options_page_secondary_nav( $active_tab, $section, $sections );

		// Form
		edd_options_page_form( $active_tab, $section, $override );

		?></div><!-- .wrap --><?php

	// Output the current buffer
	echo ob_get_clean();
}

/**
 * Conditionally shows a notice on the Tax Rates screen if taxes are disabled, to inform users that while they are adding
 * tax rates, they will not be applied until taxes are enabled.
 *
 * @since 3.0
 */
function edd_tax_settings_display_tax_disabled_notice() {
	if ( edd_use_taxes() ) {
		return;
	}

	?>
	<div class="notice-wrap" style="clear: both;">
		<div id="edd-tax-disabled-notice">
			<p>
				<?php _e( 'Taxes are currently disabled. Rates listed below will not be applied to purchases until taxes are enabled.', 'easy-digital-downloads' ); ?>
			</p>
		</div>
	</div>
	<?php

}
add_action( 'edd_settings_tab_top_taxes_rates', 'edd_tax_settings_display_tax_disabled_notice', 10 );

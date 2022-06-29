<?php
/**
 * Admin Options Page
 *
 * @package     EDD
 * @subpackage  Admin/Settings
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Adds the EDD branded header to the EDD settings pages.
 *
 * @since 2.11.3
 */
function edd_admin_header() {
	if ( ! edd_is_admin_page( '', '', false ) ) {
		return;
	}

	$numberNotifications = EDD()->notifications->countActiveNotifications();
	?>
	<div id="edd-header" class="edd-header">
		<div id="edd-header-wrapper">
			<img class="edd-header-logo" alt="" src="<?php echo esc_url( EDD_PLUGIN_URL . '/assets/images/logo-edd-dark.svg' ); ?>" />

			<div id="edd-header-actions">
				<button
					id="edd-notification-button"
					class="edd-round"
					x-data
					x-init="$store.eddNotifications.numberActiveNotifications = <?php echo esc_js( $numberNotifications ); ?>"
					@click="$store.eddNotifications.openPanel()"
				>
					<span
						class="edd-round edd-number<?php echo 0 === $numberNotifications ? ' edd-hidden' : ''; ?>"
						x-show="$store.eddNotifications.numberActiveNotifications > 0"
					>
						<?php echo wp_kses( sprintf(
							/* Translators: %1$s number of notifications; %2$s opening span tag; %3$s closing span tag */
							__( '%1$s %2$sunread notifications%3$s', 'easy-digital-downloads' ),
							'<span x-text="$store.eddNotifications.numberActiveNotifications"></span>',
							'<span class="screen-reader-text">',
							'</span>'
						), array( 'span' => array( 'class' => true, 'x-text' => true ) ) ); ?>
					</span>

					<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="edd-notifications-icon"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.8333 2.5H4.16667C3.25 2.5 2.5 3.25 2.5 4.16667V15.8333C2.5 16.75 3.24167 17.5 4.16667 17.5H15.8333C16.75 17.5 17.5 16.75 17.5 15.8333V4.16667C17.5 3.25 16.75 2.5 15.8333 2.5ZM15.8333 15.8333H4.16667V13.3333H7.13333C7.70833 14.325 8.775 15 10.0083 15C11.2417 15 12.3 14.325 12.8833 13.3333H15.8333V15.8333ZM11.675 11.6667H15.8333V4.16667H4.16667V11.6667H8.34167C8.34167 12.5833 9.09167 13.3333 10.0083 13.3333C10.925 13.3333 11.675 12.5833 11.675 11.6667Z" fill="currentColor"></path></svg>
				</button>
			</div>
		</div>
	</div>
	<?php
	add_action( 'admin_footer', function() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/views/notifications.php';
	} );
}
add_action( 'admin_notices', 'edd_admin_header', 1 );

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @return void
 */
function edd_options_page() {

	$settings_tabs = edd_get_settings_tabs();
	$settings_tabs = empty($settings_tabs) ? array() : $settings_tabs;
	$active_tab    = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'general';
	$active_tab    = array_key_exists( $active_tab, $settings_tabs ) ? $active_tab : 'general';
	$sections      = edd_get_settings_tab_sections( $active_tab );
	$key           = 'main';

	if ( ! empty( $sections ) ) {
		$key = key( $sections );
	}

	$section = ! empty( $_GET['section'] ) && ! empty( $sections[ $_GET['section'] ] ) ? sanitize_text_field( $_GET['section'] ) : $key;

	$all_settings = edd_get_registered_settings();

	// Remove tabs that don't have settings fields.
	foreach ( array_keys( $settings_tabs ) as $settings_tab ) {
		if ( empty( $all_settings[ $settings_tab ] ) && in_array( $settings_tab, array( 'extensions', 'licenses' ), true ) ) {
			unset( $settings_tabs[ $settings_tab ] );
		}
	}

	// Let's verify we have a 'main' section to show
	$has_main_settings = true;
	if ( empty( $all_settings[ $active_tab ]['main'] ) ) {
		$has_main_settings = false;
	}

	// Check for old non-sectioned settings (see #4211 and #5171)
	if ( ! $has_main_settings ) {
		foreach( $all_settings[ $active_tab ] as $sid => $stitle ) {
			if ( is_string( $sid ) && ! empty( $sections) && array_key_exists( $sid, $sections ) ) {
				continue;
			} else {
				$has_main_settings = true;
				break;
			}
		}
	}

	$override = false;
	// Unset 'main' if it's empty and default to the first non-empty if it's the chosen section
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

	ob_start();
	?>
	<div class="wrap <?php echo 'wrap-' . $active_tab; ?>">
		<h1><?php esc_html_e( 'Settings', 'easy-digital-downloads' ); ?></h1>
		<h2 class="nav-tab-wrapper">
			<?php
			foreach ( $settings_tabs as $tab_id => $tab_name ) {
				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab'              => $tab_id,
				) );

				// Remove the section from the tabs so we always end up at the main section
				$tab_url = remove_query_arg( 'section', $tab_url );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>
		<?php

		$number_of_sections = count( $sections );
		$number = 0;
		if ( $number_of_sections > 1 ) {
			echo '<div class="wp-clearfix"><ul class="subsubsub edd-settings-sub-nav">';
			foreach( $sections as $section_id => $section_name ) {
				echo '<li>';
				$number++;
				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $active_tab,
					'section' => $section_id
				) );
				$class = '';
				if ( $section == $section_id ) {
					$class = 'current';
				}
				echo '<a class="' . $class . '" href="' . esc_url( $tab_url ) . '">' . $section_name . '</a>';

				echo '</li>';
			}
			echo '</ul></div>';
		}

		// Find out if we're displaying a sidebar.
		$is_promo_active = edd_is_promo_active();
		$wrapper_class   = ( true === $is_promo_active )
			? array( ' edd-has-sidebar' )
			: array();
		?>
		<div id="tab_container" class="<?php echo esc_attr( $active_tab . '-tab' ); ?>">
			<div class="edd-settings-wrap<?php echo esc_attr( implode( ' ', $wrapper_class ) ); ?> wp-clearfix">
				<div class="edd-settings-content">
					<form method="post" action="options.php">
						<table class="form-table">
							<?php

							settings_fields( 'edd_settings' );

							if ( 'main' === $section ) {
								do_action( 'edd_settings_tab_top', $active_tab );
							}

							do_action( 'edd_settings_tab_top_' . $active_tab . '_' . $section );

							do_settings_sections( 'edd_settings_' . $active_tab . '_' . $section );

							do_action( 'edd_settings_tab_bottom_' . $active_tab . '_' . $section );

							// For backwards compatibility.
							if ( 'main' === $section ) {
								do_action( 'edd_settings_tab_bottom', $active_tab );
							}

							// If the main section was empty and we overrode the view with the next subsection, prepare the section for saving.
							if ( true === $override ) {
								?>
								<input type="hidden" name="edd_section_override" value="<?php echo esc_attr( $section ); ?>" />
								<?php
							}
							?>
						</table>
						<?php submit_button(); ?>
					</form>
				</div>
				<?php
				if ( true === $is_promo_active ) {
					edd_options_sidebar();
				}
				?>
			</div>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
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
				<input id="edd-coupon-code" type="text" value="<?php echo esc_attr( $coupon_code ); ?>" readonly>
				<p class="edd-coupon-note"><?php _e( 'Sale ends 23:59 PM December 6th CST. Save 25% on <a href="https://sandhillsdev.com/projects/" target="_blank">our other plugins</a>.', 'easy-digital-downloads' ); ?></p>
			</div>
			<div class="edd-sidebar-footer-section">
				<a class="edd-cta-button" href="<?php echo esc_url( $url ); ?>" target="_blank"><?php _e( 'Shop Now!', 'easy-digital-downloads' ); ?></a>
			</div>
		</div>
	</div>
	<?php
}

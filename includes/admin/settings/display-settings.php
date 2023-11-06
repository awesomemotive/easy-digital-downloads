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
 * Adds the EDD branded header to the EDD settings pages.
 *
 * @since 2.11.3
 */
function edd_admin_header() {
	if ( ! edd_is_admin_page( '', '', false ) ) {
		return;
	}
	$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
	if ( $screen && $screen->is_block_editor() ) {
		return;
	}
	$numberNotifications = EDD()->notifications->countActiveNotifications();
	$current_page        = ! empty( $_GET['page'] ) ? $_GET['page'] : '';
	$is_single_view      = (bool) apply_filters( 'edd_admin_is_single_view', ! empty( $_GET['view'] ) );

	$page_title = __( 'Downloads', 'easy-digital-downloads' );
	switch( $current_page ) {
		case 'edd-settings':
			$page_title = __( 'Settings', 'easy-digital-downloads' );
			break;
		case 'edd-reports':
			$page_title = __( 'Reports', 'easy-digital-downloads' );
			break;
		case 'edd-payment-history':
			$page_title = __( 'Orders', 'easy-digital-downloads' );
			break;
		case 'edd-discounts':
			$page_title = __( 'Discounts', 'easy-digital-downloads' );
			break;
		case 'edd-customers':
			$page_title = __( 'Customers', 'easy-digital-downloads' );
			break;
		case 'edd-tools':
			$page_title = __( 'Tools', 'easy-digital-downloads' );
			break;
		case 'edd-addons':
			$page_title = __( 'View Extensions', 'easy-digital-downloads' );
			if ( edd_is_pro() ) {
				$page_title = __( 'Manage Extensions', 'easy-digital-downloads' );
			}
			break;
		default:
			if ( ! empty( $_GET['page'] ) ) {
				$page_title = ucfirst( str_replace( array( 'edd-', 'fes-' ), '', $current_page ) );
			} elseif ( ! empty( $_GET['post_type'] ) ) {
				$post_type  = get_post_type_object( $_GET['post_type'] );
				$page_title = $post_type->labels->name;
			}
			break;
	}

	$page_title = apply_filters( 'edd_settings_page_title', $page_title, $current_page, $is_single_view );
	if ( ! empty( $page_title ) && empty( $is_single_view ) ) {
		?>
		<style>
			.wrap > h1,
			.wrap h1.wp-heading-inline {
				display: none;
			}
			.page-title-action {
				visibility: hidden;
			}
		</style>
		<script>
		jQuery(document).ready(function($){
			const coreAddNew = $( '.page-title-action:visible' );
			const eddAddNew  = $( '.add-new-h2:visible' );

			if ( coreAddNew.length ) {
				coreAddNew.appendTo( '.edd-header-page-title-wrap' ).addClass( 'button' ).css( 'visibility', 'unset' );
			}

			if ( eddAddNew.length ) {
				eddAddNew.appendTo('.edd-header-page-title-wrap').addClass('button');
			}
		});
		</script>
		<?php
	}

	?>

	<div id="edd-header" class="edd-header">
		<div id="edd-header-wrapper">
			<span id="edd-header-branding">
				<img class="edd-header-logo" alt="" src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/logo-edd-dark.svg' ); ?>" />
			</span>

			<?php if ( ! empty( $page_title ) ) : ?>
			<span class="edd-header-page-title-wrap">
				<span class="edd-header-separator">/</span>
				<?php $element = true === $is_single_view ? 'span' : 'h1'; ?>
				<<?php echo esc_attr( $element ); ?> class="edd-header-page-title"><?php echo esc_html( $page_title ); ?></<?php echo esc_attr( $element ); ?>>
			</span>
			<?php endif; ?>

			<div id="edd-header-actions">
				<button
					id="edd-notification-button"
					class="edd-round edd-hidden"
					x-data
					x-init="function() {
						if ( 'undefined' !== typeof $store.eddNotifications ) {
							$el.classList.remove( 'edd-hidden' );
							$store.eddNotifications.numberActiveNotifications = <?php echo esc_js( $numberNotifications ); ?>
						}
					}"
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
 * Output the primary options page navigation
 *
 * @since 3.0
 *
 * @param array  $tabs       All available tabs.
 * @param string $active_tab Current active tab.
 */
function edd_options_page_primary_nav( $tabs, $active_tab = '' ) {
	?>
	<nav class="nav-tab-wrapper edd-nav-tab-wrapper edd-settings-nav" aria-label="<?php esc_attr_e( 'Secondary menu', 'easy-digital-downloads' ); ?>">
		<?php

		foreach ( $tabs as $tab_id => $tab_name ) {
			$tab_url = edd_get_admin_url(
				array(
					'settings-updated' => false,
					'page'             => 'edd-settings',
					'tab'              => sanitize_key( $tab_id ),
				)
			);

			// Remove the section from the tabs so we always end up at the main section
			$tab_url = remove_query_arg( 'section', $tab_url );
			$class   = 'nav-tab';
			if ( $active_tab === $tab_id ) {
				$class .= ' nav-tab-active';
			}

			// Link
			echo '<a href="' . esc_url( $tab_url ) . '" class="' . esc_attr( $class ) . '">';
			echo esc_html( $tab_name );
			echo '</a>';
		}
		?>
	</nav>
	<?php
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
		$tab_url = add_query_arg(
			array(
				'post_type' => 'download',
				'page'      => 'edd-settings',
				'tab'       => $active_tab,
				'section'   => $section_id,
			),
			edd_get_admin_base_url()
		);

		// Settings not updated
		$tab_url = remove_query_arg( 'settings-updated', $tab_url );

		// Class for link
		$class = ( $section === $section_id )
			? 'current'
			: '';

		// Add to links array
		$links[ $section_id ] = '<li class="' . esc_attr( $class ) . '"><a class="' . esc_attr( $class ) . '" href="' . esc_url( $tab_url ) . '">' . esc_html( $section_name ) . '</a><li>';
	}
	if ( count( $links ) < 2 ) {
		return;
	}
	?>

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
	?>

	<div class="edd-settings-wrap wp-clearfix">
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
	$active_tab     = array_key_exists( $active_tab, $settings_tabs ) && array_key_exists( $active_tab, $all_settings ) ? $active_tab : 'general';
	$sections       = edd_get_settings_tab_sections( $active_tab );
	$section        = ! empty( $_GET['section'] ) && ! empty( $sections[ $_GET['section'] ] ) ? sanitize_text_field( $_GET['section'] ) : 'main';

	// Default values
	$has_main_settings = true;
	$override          = false;

	// Remove tabs that don't have settings fields.
	foreach ( array_keys( $settings_tabs ) as $settings_tab ) {
		if ( empty( $all_settings[ $settings_tab ] ) ) {
			unset( $settings_tabs[ $settings_tab ] );
		}
	}

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

	// Unset 'main' if it's empty and default to the first non-empty if it's the chosen section.
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
		<h1><?php esc_html_e( 'Settings', 'easy-digital-downloads' ); ?></h1>

		<?php
		// Primary nav
		edd_options_page_primary_nav( $settings_tabs, $active_tab );

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
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

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

/**
 * Display help text at the top of the Licenses tab.
 *
 * @since 3.1.1.4
 * @return void
 */
function edd_license_settings_help_text() {
	if ( ! current_user_can( 'manage_shop_settings' ) ) {
		return;
	}

	?>
	<div class="edd-licenses__description">
		<p>
			<?php esc_html_e( 'Manage extensions for Easy Digital Downloads which are not included with a pass. Having an active license for your extensions gives you access to updates when they\'re available.', 'easy-digital-downloads' ); ?>
		</p>
		<?php
		$pass_manager = new \EDD\Admin\Pass_Manager();
		if ( ! $pass_manager->highest_license_key ) :
			?>
			<p>
				<?php
				$url = edd_get_admin_url(
					array(
						'page' => 'edd-settings',
						'tab'  => 'general',
					)
				);
				printf(
					wp_kses_post(
						/* translators: 1. opening anchor tag; 2. closing anchor tag */
						__( 'Have a pass? You\'re ready to set up EDD (Pro). %1$sActivate Your Pass%2$s' )
					),
					'<a href="' . esc_url( $url ) . '" class="button button-primary">',
					'</a>'
				);
				?>
			</p>
		<?php endif; ?>
	</div>
	<?php
}
add_action( 'edd_settings_tab_top_licenses_main', 'edd_license_settings_help_text' );

<?php

namespace EDD\Admin\Tools;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Menu\SecondaryNavigation;

/**
 * Class Screen
 *
 * @since 3.3.0
 */
class Screen {

	/**
	 * The tab shown when no tab is requested.
	 *
	 * @since 3.7.1
	 * @var string
	 */
	const DEFAULT_TAB = 'general';

	/**
	 * The tabs.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	private static $tabs;

	/**
	 * Registers the tools screen.
	 *
	 * @since 3.3.0
	 */
	public static function render() {

		wp_enqueue_script( 'edd-admin-tools' );

		$active_tab = self::get_active_tab();
		if ( 'import_export' === $active_tab ) {
			wp_enqueue_script( 'edd-admin-tools-import' );
			wp_enqueue_script( 'edd-admin-tools-export' );
		}

		$navigation = new SecondaryNavigation(
			self::get_tabs(),
			'edd-tools'
		);
		$navigation->render();
		?>

		<div class="wrap">
			<hr class="wp-header-end">
			<div class="metabox-holder">
				<?php do_action( 'edd_tools_tab_' . esc_attr( $active_tab ) ); ?>
			</div>
		</div>
		<?php
	}

	/**
	 * Retrieve tools tabs.
	 *
	 * @since 2.0
	 *
	 * @return array Tabs for the 'Tools' page.
	 */
	public static function get_tabs() {

		if ( is_null( self::$tabs ) ) {

			// Define all tabs.
			$tabs = array(
				'general'           => __( 'General', 'easy-digital-downloads' ),
				'api_keys'          => __( 'API Keys', 'easy-digital-downloads' ),
				'logs'              => __( 'Event Logging', 'easy-digital-downloads' ),
				'scheduled_actions' => __( 'Scheduled Actions', 'easy-digital-downloads' ),
				'system_info'       => __( 'System Info', 'easy-digital-downloads' ),
				'debug_log'         => __( 'Debug Log', 'easy-digital-downloads' ),
				'import_export'     => __( 'Import/Export', 'easy-digital-downloads' ),
			);

			// The AI tab has nothing to configure until the Abilities API exists.
			if ( function_exists( 'wp_register_ability' ) ) {
				$tabs['ai'] = __( 'AI', 'easy-digital-downloads' );
			}

			$tabs['labs'] = __( 'Labs', 'easy-digital-downloads' );

			self::$tabs = apply_filters( 'edd_tools_tabs', $tabs );

			self::$tabs['system_info'] = array(
				'name' => self::$tabs['system_info'],
				'url'  => self::get_system_info_link(),
			);

			// Labs is the last tab, including after an add-on has registered its own.
			if ( isset( self::$tabs['labs'] ) ) {
				$labs = self::$tabs['labs'];
				unset( self::$tabs['labs'] );
				self::$tabs['labs'] = $labs;
			}
		}

		return self::$tabs;
	}

	/**
	 * Gets the active tab.
	 *
	 * Falls back to `DEFAULT_TAB` when the requested tab is not registered, so an
	 * unknown `tab` value can never reach `do_action()` in `render()`.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private static function get_active_tab() {
		$active_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );

		// Fallback to $_GET if filter_input() returns null (for test environments).
		if ( ! $active_tab && isset( $_GET['tab'] ) && is_string( $_GET['tab'] ) ) {
			$active_tab = sanitize_text_field( $_GET['tab'] );
		}

		return array_key_exists( (string) $active_tab, self::get_tabs() ) ? $active_tab : self::DEFAULT_TAB;
	}

	/**
	 * Gets the system info link.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private static function get_system_info_link() {
		return add_query_arg(
			array(
				'tab' => 'debug',
				'edd' => 'filter',
			),
			admin_url( 'site-health.php' )
		);
	}
}

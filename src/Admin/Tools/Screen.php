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
				'general'       => __( 'General', 'easy-digital-downloads' ),
				'api_keys'      => __( 'API Keys', 'easy-digital-downloads' ),
				'betas'         => __( 'Beta Versions', 'easy-digital-downloads' ),
				'logs'          => __( 'Logs', 'easy-digital-downloads' ),
				'system_info'   => __( 'System Info', 'easy-digital-downloads' ),
				'debug_log'     => __( 'Debug Log', 'easy-digital-downloads' ),
				'import_export' => __( 'Import/Export', 'easy-digital-downloads' ),
			);

			// Unset the betas tab if not allowed.
			if ( count( edd_get_beta_enabled_extensions() ) <= 0 ) {
				unset( $tabs['betas'] );
			}

			self::$tabs = apply_filters( 'edd_tools_tabs', $tabs );

			self::$tabs['system_info'] = array(
				'name' => self::$tabs['system_info'],
				'url'  => self::get_system_info_link(),
			);
		}

		return self::$tabs;
	}

	/**
	 * Gets the active tab.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private static function get_active_tab() {
		$active_tab = filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS );

		return $active_tab ?? 'general';
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

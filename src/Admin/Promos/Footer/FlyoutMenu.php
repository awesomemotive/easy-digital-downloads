<?php
/**
 * Adds our Edd Flyout menu to EDD admin pages.
 *
 * @package     EDD
 * @subpackage  Admin/Footer
 * @since       3.2.4
 */

namespace EDD\Admin\Promos\Footer;

/**
 * Class FlyoutMenu
 *
 * @since 3.2.4
 */
class FlyoutMenu {

	/**
	 * Output menu.
	 *
	 * @since 3.2.4
	 */
	public static function output() {
		if ( ! apply_filters( 'edd_admin_flyoutmenu', true ) ) {
			return;
		}

		self::enqueue();

		printf(
			'<div id="edd-flyout">
				<div id="edd-flyout-items">
					%1$s
				</div>
				<button id="edd-flyout-button" class="%2$s">
					<span class="edd-flyout-label">%3$s</span>
					<img src="%4$s" alt="%3$s" data-active="%5$s" />
				</button>
			</div>',
			self::get_items_html(), // phpcs:ignore
			edd_is_inactive_pro() ? 'has-alert' : '',
			esc_attr__( 'See Quick Links', 'easy-digital-downloads' ),
			esc_url( EDD_PLUGIN_URL . 'assets/images/admin-flyout-menu/edd-default.svg' ),
			esc_url( EDD_PLUGIN_URL . 'assets/images/admin-flyout-menu/edd-active.svg' ),
		);
	}

	/**
	 * Generate menu items HTML.
	 *
	 * @since 3.2.4
	 *
	 * @return string Menu items HTML.
	 */
	private static function get_items_html() {
		$items      = array_reverse( self::menu_items() );
		$items_html = '';

		foreach ( $items as $item_key => $item ) {
			$items_html .= sprintf(
				'<span class="edd-flyout-item edd-flyout-item-%3$s">
					<span class="edd-flyout-label"><a href="%1$s"%2$s>%5$s</a></span>
					<a class="edd-flyout-icon%4$s" href="%1$s"%2$s><span class="dashicons dashicons-%6$s" aria-hidden="true"></span></a>
				</span>',
				\esc_url( $item['url'] ),
				$item['is_external'] ? ' target="_blank" rel="noopener noreferrer" ' : ' ',
				\esc_attr( $item_key ),
				! empty( $item['class'] ) ? ' ' . \sanitize_html_class( $item['class'] ) : '',
				\esc_html( $item['title'] ),
				\sanitize_html_class( $item['icon'] )
			);
		}

		return $items_html;
	}

	/**
	 * Menu items data.
	 *
	 * @since 3.2.4
	 */
	private static function menu_items() {
		$items  = array();
		$screen = get_current_screen();
		$screen = ! empty( $screen ) ? $screen->id : 'unknown';
		$screen = str_replace( 'download_page_edd-', '', $screen );

		// Set the UTM parameters for this menu set.
		$utm_medium = 'admin-flyout';
		$utm_term   = str_replace( '_', '-', $screen );

		$pass_manager = new \EDD\Admin\Pass_Manager();
		if ( ! edd_is_pro() && ! $pass_manager->has_pass() ) {
			$items['upgrade'] = array(
				'title'       => esc_html__( 'Upgrade to EDD (Pro)', 'easy-digital-downloads' ),
				'url'         => edd_link_helper(
					'https://easydigitaldownloads.com/lite-upgrade/',
					array(
						'utm_medium'  => $utm_medium,
						'utm_content' => 'upgrade-to-pro',
						'utm_term'    => $utm_term,
					)
				),
				'icon'        => 'star-filled',
				'class'       => 'green',
				'is_external' => true,
			);
		}

		if ( edd_is_inactive_pro() ) {
			$items['activate'] = array(
				'title'       => esc_html__( 'Activate Your License', 'easy-digital-downloads' ),
				'url'         => edd_get_admin_url(
					array(
						'page' => 'edd-settings',
					)
				),
				'icon'        => 'unlock',
				'class'       => 'red',
				'is_external' => false,
			);

			$items['license'] = array(
				'title'         => esc_html__( 'Get a License', 'easy-digital-downloads' ),
				'url'           => edd_link_helper(
					'https://easydigitaldownloads.com/lite-upgrade/',
					array(
						'utm_medium'  => $utm_medium,
						'utm_content' => '',
						'utm_term'    => $utm_term,
					)
				),
				'icon'          => 'star-filled',
				'class'         => 'green',
				'hover_bgcolor' => '#199155',
				'is_external'   => true,
			);
		}

		$support_url = ! edd_is_pro() || edd_is_inactive_pro()
			? 'https://wordpress.org/support/plugin/easy-digital-downloads/'
			: edd_link_helper(
				'https://easydigitaldownloads.com/support/',
				array(
					'utm_medium'  => $utm_medium,
					'utm_content' => 'support',
					'utm_term'    => $utm_term,
				)
			);

		$items['support'] = array(
			'title'       => esc_html__( 'Contact Support', 'easy-digital-downloads' ),
			'url'         => $support_url,
			'icon'        => 'sos',
			'is_external' => true,
		);

		$items['docs'] = array(
			'title'       => esc_html__( 'View Documentation', 'easy-digital-downloads' ),
			'url'         => edd_link_helper(
				apply_filters( 'edd_flyout_docs_link', 'https://easydigitaldownloads.com/docs/' ),
				array(
					'utm_medium'  => $utm_medium,
					'utm_content' => 'docs',
					'utm_term'    => $utm_term,
				)
			),
			'icon'        => 'text-page',
			'is_external' => true,
		);

		return apply_filters( 'edd_admin_flyout_menu_items', $items );
	}

	/**
	 * Enqueue scripts.
	 *
	 * @since 3.2.4
	 */
	private static function enqueue() {
		wp_enqueue_script(
			'edd-flyout-menu',
			EDD_PLUGIN_URL . 'assets/js/edd-admin-flyout.js',
			array(),
			EDD_VERSION,
			true
		);
	}
}

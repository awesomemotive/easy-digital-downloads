<?php
/**
 * Sets up the class for checking extension licenses.
 *
 * @since 3.1.2
 */
namespace EDD\Admin\SiteHealth;

defined( 'ABSPATH' ) || exit;

class Licenses extends Test {

	/**
	 * Gets the test.
	 *
	 * @since 3.1.2
	 * @return false|array
	 */
	public function get() {
		if ( empty( $this->get_licensed_products() ) ) {
			return false;
		}

		return array(
			'label'     => __( 'Licensed Extensions', 'easy-digital-downloads' ),
			'test'      => array( $this, 'get_test_edd_licenses' ),
			'skip_cron' => true,
		);
	}

	/**
	 * Adds a test for whether EDD licenses are valid/missing/expired.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get_test_edd_licenses() {
		$result = array(
			'label'       => __( 'Your extensions are receiving updates', 'easy-digital-downloads' ),
			'status'      => 'good',
			'badge'       => $this->get_default_badge(),
			'description' => sprintf(
				'<p>%s</p>',
				__( 'Your EDD extensions are all licensed and receiving updates.', 'easy-digital-downloads' )
			),
			'actions'     => '',
			'test'        => 'edd_licenses',
		);
		if ( ! $this->has_missing_licenses() ) {
			return $result;
		}

		$result['label']          = __( 'You are not receiving updates for some extensions', 'easy-digital-downloads' );
		$result['status']         = 'critical';
		$result['badge']['color'] = 'red';
		$result['description']    = sprintf(
			'<p>%s</p>',
			__( 'At least one of your extensions is missing a license key, or the license is expired. Your site may be missing critical software updates.', 'easy-digital-downloads' )
		);
		$result['actions']        = $this->get_licensing_action_links();

		return $result;
	}

	/**
	 * Gets the licensed products global.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	private function get_licensed_products() {
		global $edd_licensed_products;

		return $edd_licensed_products;
	}

	/**
	 * Checks the licensed products global for unlicensed extensions.
	 *
	 * @since 3.1.2
	 * @return bool
	 */
	private function has_missing_licenses() {
		return in_array( 0, $this->get_licensed_products(), true );
	}

	/**
	 * Gets the licensing action links.
	 *
	 * @since 3.1.2
	 * @return string
	 */
	private function get_licensing_action_links() {
		$actions      = $this->get_licensing_actions();
		$action_links = array();
		foreach ( $actions as $action ) {
			$action_links[] = sprintf(
				'<a href="%s">%s</a>',
				esc_url( $action['url'] ),
				esc_html( $action['label'] )
			);
		}

		return ! empty( $action_links ) ? implode( ' | ', $action_links ) : '';
	}

	/**
	 * Gets the licensing actions.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	private function get_licensing_actions() {
		return array(
			array(
				'label' => __( 'Upgrade to EDD (Pro)', 'easy-digital-downloads' ),
				'url'   => edd_link_helper(
					'https://easydigitaldownloads.com/pricing/',
					array(
						'utm_medium'  => 'site-health',
						'utm_content' => 'upgrade-to-pro',
					),
					false
				),
			),
			array(
				'label' => __( 'Enter a license key for EDD (Pro)', 'easy-digital-downloads' ),
				'url'   => edd_get_admin_url(
					array(
						'page' => 'edd-settings',
						'tab'  => 'general',
					)
				),
			),
			array(
				'label' => __( 'Enter a license key for an extension', 'easy-digital-downloads' ),
				'url'   => edd_get_admin_url(
					array(
						'page' => 'edd-settings',
						'tab'  => 'licenses',
					)
				),
			),
		);
	}
}

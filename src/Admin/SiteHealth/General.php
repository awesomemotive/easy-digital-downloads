<?php
/**
 * Gets the general EDD information.
 *
 * @since 3.1.2
 * @package EDD\Admin\SiteHealth
 */

namespace EDD\Admin\SiteHealth;

use EDD\Admin\Pass_Manager;

/**
 * Loads general EDD information into Site Health
 *
 * @since 3.1.2
 */
class General {

	/**
	 * The pass manager.
	 *
	 * @since 3.1.2
	 * @var EDD\Admin\Pass_Manager
	 */
	private $pass_manager;

	/**
	 * General constructor.
	 */
	public function __construct() {
		$this->pass_manager = new Pass_Manager();
	}

	/**
	 * Gets the site health section.
	 *
	 * @since 3.1.2
	 * @return array
	 */
	public function get() {
		return array(
			'label'  => __( 'Easy Digital Downloads &mdash; General', 'easy-digital-downloads' ),
			'fields' => array(
				'version'                  => array(
					'label' => 'EDD Version',
					'value' => EDD_VERSION,
				),
				'edd_timezone'             => array(
					'label' => 'EDD Timezone',
					'value' => edd_get_timezone_abbr(),
				),
				'upgraded'                 => array(
					'label' => 'Upgraded From',
					'value' => get_option( 'edd_version_upgraded_from', 'None' ),
				),
				'edd_is_pro'               => array(
					'label' => 'EDD (Pro) Status',
					'value' => $this->get_pro_status(),
				),
				'edd_activated'            => array(
					'label' => 'EDD Activation Date',
					'value' => $this->get_date( 'edd_activation_date' ),
				),
				'edd_pro_activated'        => array(
					'label' => 'EDD (Pro) Activation Date',
					'value' => $this->get_date( 'edd_pro_activation_date' ),
				),
				'edd_pass'                 => array(
					'label' => 'EDD Pass Status',
					'value' => $this->pass_manager->highest_pass_id ? 'Valid Pass' : 'Missing',
				),
				'edd_test_mode'            => array(
					'label' => 'Test Mode',
					'value' => edd_is_test_mode() ? 'Enabled' : 'Disabled',
				),
				'edd_ajax'                 => array(
					'label' => 'AJAX',
					'value' => ! edd_is_ajax_disabled() ? 'Enabled' : 'Disabled',
				),
				'edd_guest_checkout'       => array(
					'label' => 'Guest Checkout',
					'value' => edd_no_guest_checkout() ? 'Disabled' : 'Enabled',
				),
				'symlinks'                 => array(
					'label' => 'Symlinks',
					'value' => apply_filters( 'edd_symlink_file_downloads', edd_get_option( 'symlink_file_downloads', false ) ) && function_exists( 'symlink' ) ? 'Enabled' : 'Disabled',
				),
				'download_method'          => array(
					'label' => 'Download Method',
					'value' => ucfirst( edd_get_file_download_method() ),
				),
				'currency_code'            => array(
					'label' => 'Currency Code',
					'value' => edd_get_currency(),
				),
				'currency_position'        => array(
					'label' => 'Currency Position',
					'value' => edd_get_option( 'currency_position', 'before' ),
				),
				'decimal_separator'        => array(
					'label' => 'Decimal Separator',
					'value' => edd_get_option( 'decimal_separator', '.' ),
				),
				'thousands_separator'      => array(
					'label' => 'Thousands Separator',
					'value' => edd_get_option( 'thousands_separator', '.' ),
				),
				'completed_upgrades'       => array(
					'label' => 'Upgrades Completed',
					'value' => implode( ', ', edd_get_completed_upgrades() ),
				),
				'download_link_expiration' => array(
					'label' => 'Download Link Expiration',
					'value' => edd_get_option( 'download_link_expiration' ) . ' hour(s)',
				),
				'rest_enabled'             => array(
					'label' => 'REST API',
					'value' => $this->is_rest_api_enabled( 'wp/v2/edd-downloads' ) ? 'Accessible' : 'Not Accessible',
				),
				'paypal_rest_available'    => array(
					'label' => 'PayPal REST Endpoints',
					'value' => $this->is_rest_api_enabled( 'edd/webhooks/v1/paypal/webhook-test' ) ? 'Accessible' : 'Not Accessible',
				),
			),
		);
	}

	/**
	 * Gets the date for an option.
	 *
	 * @since 3.1.2
	 * @param string $option The option name.
	 * @return string
	 */
	private function get_date( $option ) {
		$date = get_option( $option );

		return $date ? edd_date_i18n( $date, 'Y-m-d' ) : 'n/a';
	}

	/**
	 * Gets the pro license status for the site.
	 *
	 * @since 3.1.2
	 * @return string
	 */
	private function get_pro_status() {
		if ( ! edd_is_pro() ) {
			return 'Disabled';
		}

		return $this->pass_manager::isPro() ? 'Enabled' : 'Missing License';
	}

	/**
	 * Test if the REST API is accessible.
	 *
	 * The REST API might be inaccessible due to various security measures,
	 * or it might be completely disabled by a plugin.
	 *
	 * @since 3.2.0
	 * @return bool
	 */
	private function is_rest_api_enabled( $endpoint = '' ) {
		if ( empty( $endpoint ) ) {
			return;
		}

		$checker = new \EDD\Utils\RESTChecker( $endpoint );

		return $checker->is_enabled();
	}
}

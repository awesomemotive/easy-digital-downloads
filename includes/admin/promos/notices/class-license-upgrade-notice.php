<?php
/**
 * License Upgrade Notice
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.10.6
 */

namespace EDD\Admin\Promos\Notices;

use EDD\Admin\Pass_Manager;

class License_Upgrade_Notice extends Notice {

	const DISPLAY_HOOK = 'edd_settings_before_wrap';
	const DISMISS_DURATION = MINUTE_IN_SECONDS; // @todo

	/**
	 * Number of EDD license keys that have been entered.
	 * Not validated to make sure they're actually active; this is
	 * just an indicator if any licenses exist at all.
	 *
	 * @var array
	 */
	private $number_license_keys;

	/**
	 * @var Pass_Manager
	 */
	private $pass_manager;

	/**
	 * License_Upgrade_Notice constructor.
	 */
	public function __construct() {
		global $edd_licensed_products;

		$this->number_license_keys = is_array( $edd_licensed_products ) ? count( $edd_licensed_products ) : 0;
		$this->pass_manager        = new Pass_Manager();
	}

	/**
	 * @inheritDoc
	 *
	 * @return bool
	 */
	protected function _should_display() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return false;
		}

		// Someone with no license keys entered always sees a notice.
		if ( 0 === $this->number_license_keys ) {
			return true;
		}

		// If we have no pass data yet, don't show the notice because we don't yet know what it should say.
		if ( ! $this->pass_manager->has_pass_data ) {
			return false;
		}

		// If someone has an extended pass or higher, and has an active AffiliateWP license, don't show.
		try {
			if (
				$this->pass_manager->has_pass() &&
				Pass_Manager::pass_compare( $this->pass_manager->highest_pass_id, Pass_Manager::EXTENDED_PASS_ID, '>=' ) &&
				$this->has_affiliate_wp_license()
			) {
				return false;
			}
		} catch ( \Exception $e ) {
			return true;
		}

		return true;
	}

	/**
	 * Determines whether or not AffiliateWP is installed and has a license key.
	 *
	 * @since 2.10.6
	 *
	 * @return bool
	 */
	private function has_affiliate_wp_license() {
		if ( ! function_exists( 'affiliate_wp' ) ) {
			return false;
		}

		return (bool) affiliate_wp()->settings->get( 'license_key' );
	}

	/**
	 * @inheritDoc
	 */
	protected function _display() {
		try {
			if ( 0 === $this->number_license_keys ) {

				// No license keys active at all.
				printf(
				/* Translators: %s link URL */
					__( 'You are using the free version of Easy Digital Downloads. <a href="%s" target="_blank">Purchase a pass</a> to get email marketing tools and recurring payments.', 'easy-digital-downloads' ),
					'https://easydigitaldownloads.com/pricing/'
				);

			} elseif ( ! $this->pass_manager->highest_pass_id ) {

				// Individual product license active, but no pass.
				printf(
				/* Translators: %s link URL */
					__( 'For access to additional extensions to grow your store, consider <a href="%s" target="_blank">purchasing a pass</a>.', 'easy-digital-downloads' ),
					'https://easydigitaldownloads.com/pricing/'
				);

			} elseif ( Pass_Manager::pass_compare( $this->pass_manager->highest_pass_id, Pass_Manager::PERSONAL_PASS_ID, '=' ) ) {

				// Personal pass active.
				printf(
				/* Translators: %s link URL */
					__( 'You are using Easy Digital Downloads with a Personal pass. Consider <a href="%s" target="_blank">upgrading</a> to get recurring payments and more.', 'easy-digital-downloads' ),
					'https://easydigitaldownloads.com/your-account/license-keys/'
				);

			} elseif ( Pass_Manager::pass_compare( $this->pass_manager->highest_pass_id, Pass_Manager::EXTENDED_PASS_ID, '>=' ) ) {

				// Extended pass or higher.
				printf(
				/* Translators: %s link URL */
					__( 'Grow your business and make more money with affiliate marketing. <a href="%s" target="_blank">Get AffiliateWP.</a>', 'easy-digital-downloads' ),
					'http://affiliatewp.com/?ref=743'
				);
			}
		} catch ( \Exception $e ) {
			// If we're in here, that means we have an invalid pass ID... what should we do? :thinking:
		}
	}
}

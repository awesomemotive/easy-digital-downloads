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

	const DISPLAY_HOOK = 'in_admin_header';

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
	 * This notice lasts 90 days.
	 *
	 * @return int
	 */
	public static function dismiss_duration() {
		return 3 * MONTH_IN_SECONDS;
	}

	/**
	 * Determines if the current page is an EDD admin page.
	 *
	 * @return bool
	 */
	private function is_edd_admin_page() {
		if ( defined( 'EDD_DOING_TESTS' ) && EDD_DOING_TESTS ) {
			return true;
		}

		$screen = get_current_screen();

		if ( ! $screen instanceof \WP_Screen || 'dashboard' === $screen->id || ! edd_is_admin_page( '', '', false ) ) {
			return false;
		}

		return true;
	}

	/**
	 * @inheritDoc
	 *
	 * @return bool
	 */
	protected function _should_display() {

		if ( ! $this->is_edd_admin_page() ) {
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
				$this->has_affiliate_wp_license() &&
				$this->has_mi_license()
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
	 * Determines whether or not MonsterInsights is installed and has a license key.
	 *
	 * @since 2.11.6
	 *
	 * @return bool
	 */
	private function has_mi_license() {
		if ( ! class_exists( 'MonsterInsights' ) ) {
			return false;
		}

		$mi_license = \MonsterInsights::$instance->license->get_license_key();
		return ! empty( $mi_license );
	}

	/**
	 * @inheritDoc
	 */
	protected function _display() {

		try {
			if ( 0 === $this->number_license_keys ) {
				$utm_parameters = $this->query_args( 'core' );
				$link_url       = $this->build_url(
					'https://easydigitaldownloads.com/lite-upgrade/',
					$utm_parameters
				);

				$help_url = edd_link_helper(
					'https://easydigitaldownloads.com/what-is-an-edd-pass/',
					array(
						'utm_medium'  => 'top-promo',
						'utm_content' => 'what-is-a-pass',
					)
				);

				// No license keys active at all.
				printf(
				/* Translators: %1$s opening anchor tag; %2$s closing anchor tag */
					__( 'You are using the free version of Easy Digital Downloads. %1$sPurchase a pass%2$s to get email marketing tools and recurring payments. %3$sAlready have a Pass?%4$s', 'easy-digital-downloads' ),
					'<a href="' . $link_url . '" target="_blank">',
					'</a>',
					'<a href="' . $help_url . '" target="_blank">',
					'</a>'
				);

			} elseif ( ! $this->pass_manager->highest_pass_id ) {
				$utm_parameters = $this->query_args( 'extension-license' );
				$link_url       = $this->build_url(
					'https://easydigitaldownloads.com/your-account/',
					$utm_parameters
				);

				// Individual product license active, but no pass.
				printf(
				/* Translators: %1$s opening anchor tag; %2$s closing anchor tag */
					__( 'For access to additional Easy Digital Downloads extensions to grow your store, consider %1$spurchasing a pass%2$s.', 'easy-digital-downloads' ),
					'<a href="' . $link_url . '" target="_blank">',
					'</a>'
				);

			} elseif ( Pass_Manager::pass_compare( $this->pass_manager->highest_pass_id, Pass_Manager::PERSONAL_PASS_ID, '=' ) ) {
				$utm_parameters = $this->query_args( 'personal-pass' );
				$link_url       = $this->build_url(
					'https://easydigitaldownloads.com/your-account/',
					$utm_parameters
				);

				// Personal pass active.
				printf(
				/* Translators: %1$s opening anchor tag; %2$s closing anchor tag */
					__( 'You are using Easy Digital Downloads with a Personal Pass. Consider %1$supgrading%2$s to get recurring payments and more.', 'easy-digital-downloads' ),
					'<a href="' . $link_url . '" target="_blank">',
					'</a>'
				);

			} elseif ( Pass_Manager::pass_compare( $this->pass_manager->highest_pass_id, Pass_Manager::EXTENDED_PASS_ID, '>=' ) ) {
				if ( ! $this->has_affiliate_wp_license() ) {
					$link_url = edd_link_helper(
						'https://affiliatewp.com',
						array(
							'utm_medium'  => 'top-promo',
							'utm_content' => 'affiliate-wp',
						)
					);

					printf(
					/* Translators: %1$s opening anchor tag; %2$s closing anchor tag */
						__( 'Grow your business and make more money with affiliate marketing. %1$sGet AffiliateWP%2$s', 'easy-digital-downloads' ),
						'<a href="' . $link_url . '" target="_blank">',
						'</a>'
					);
				} elseif( ! $this->has_mi_license() ) {
					printf(
					/* Translators: %1$s opening anchor tag; %2$s closing anchor tag */
						__( 'Gain access to powerful insights to grow your traffic and revenue. %1$sGet MonsterInsights%2$s', 'easy-digital-downloads' ),
						'<a href="' . esc_url( 'https://monsterinsights.com?utm_campaign=xsell&utm_source=eddplugin&utm_content=top-promo' ) . '" target="_blank">',
						'</a>'
					);
				}
			}
		} catch ( \Exception $e ) {
			// If we're in here, that means we have an invalid pass ID... what should we do? :thinking:.
		}
	}

	/**
	 * Builds the UTM parameters for the URLs.
	 *
	 * @since 2.10.6
	 *
	 * @param string $upgrade_from License type upgraded from.
	 * @param string $source       Current page.
	 *
	 * @return string[]
	 */
	private function query_args( $upgrade_from, $source = '' ) {
		return array(
			'utm_medium'   => 'top-promo',
			'utm_content'  => 'upgrade-from-' . urlencode( $upgrade_from ),
		);
	}

	/**
	 * Build a link with UTM parameters
	 *
	 * @since 3.1
	 *
	 * @param string $url            The Base URL.
	 * @param array  $utm_parameters The UTM tags for the URL.
	 *
	 * @return string
	 */
	private function build_url( $url, $utm_parameters ) {
		return esc_url(
			edd_link_helper(
				$url,
				$utm_parameters
			)
		);
	}
}

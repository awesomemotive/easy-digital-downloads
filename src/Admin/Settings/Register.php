<?php
/**
 * Easy Digital Downloads Settings
 *
 * @package EDD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.1.4
 */
namespace EDD\Admin\Settings;

defined( 'ABSPATH' ) || exit;

class Register {

	/**
	 * The array of registered settings.
	 *
	 * @since 3.1.4
	 * @var array
	 */
	private $settings;

	/**
	 * Get the registered settings.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	public function get() {
		if ( is_null( $this->settings ) ) {
			$this->settings = $this->register();
		}

		return apply_filters( 'edd_registered_settings', $this->settings );
	}

	/**
	 * Register the settings.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function register() {
		$tabs = array(
			'general'    => new Tabs\General(),
			'gateways'   => new Tabs\Gateways(),
			'emails'     => new Tabs\Emails(),
			'marketing'  => new Tabs\Marketing(),
			'taxes'      => new Tabs\Taxes(),
			'extensions' => new Tabs\Extensions(),
			'licenses'   => new Tabs\Licenses(),
			'misc'       => new Tabs\Misc(),
			'privacy'    => new Tabs\Privacy(),
		);

		$settings = array();
		foreach ( $tabs as $key => $tab ) {
			$settings[ $key ] = $tab->get();
		}

		if ( has_filter( 'edd_settings_styles' ) ) {
			$settings['styles'] = $this->get_styles();
		}

		return $settings;
	}

	/**
	 * Allow registered settings to surface the deprecated "Styles" tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function get_styles() {
		return edd_apply_filters_deprecated(
			'edd_settings_styles',
			array(
				array(
					'main'    => array(),
					'buttons' => array(),
				),
			),
			'3.0',
			'edd_settings_misc'
		);
	}
}

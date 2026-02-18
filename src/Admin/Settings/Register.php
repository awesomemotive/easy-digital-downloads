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
	 * Add the settings fields to the settings page.
	 *
	 * @since 3.6.5
	 * @param array $edd_settings The EDD settings.
	 * @return void
	 */
	public static function add_settings_fields( array $edd_settings ): void {
		foreach ( $edd_settings as $tab => $sections ) {

			// Loop through sections.
			foreach ( $sections as $section => $settings ) {

				// Check for backwards compatibility.
				$section_tabs = edd_get_settings_tab_sections( $tab );
				if ( ! is_array( $section_tabs ) || ! array_key_exists( $section, $section_tabs ) ) {
					$section  = 'main';
					$settings = $sections;
				}

				// Current page.
				$page = "edd_settings_{$tab}_{$section}";

				// Add the settings section.
				add_settings_section(
					$page,
					__return_null(),
					'__return_false',
					$page
				);

				foreach ( $settings as $option ) {

					// For backwards compatibility.
					if ( empty( $option['id'] ) ) {
						continue;
					}
					$option['section'] = $section;
					$args              = wp_parse_args( $option, self::get_default_setting_args() );

					// Callback fallback.
					$func     = "edd_{$args['type']}_callback";
					$callback = ! function_exists( $func )
						? 'edd_missing_callback'
						: $func;

					// Link the label to the form field.
					if ( empty( $args['label_for'] ) ) {
						$args['label_for'] = "edd_settings[{$args['id']}]";
					}

					// Add the settings field.
					add_settings_field(
						'edd_settings[' . $args['id'] . ']',
						$args['name'],
						$callback,
						$page,
						$page,
						$args
					);
				}
			}
		}
	}

	/**
	 * Get the default settings.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	public static function get_default_setting_args(): array {
		return array(
			'section'       => 'main',
			'id'            => null,
			'desc'          => '',
			'name'          => '',
			'size'          => null,
			'options'       => '',
			'std'           => '',
			'min'           => null,
			'max'           => null,
			'step'          => null,
			'chosen'        => null,
			'multiple'      => null,
			'placeholder'   => null,
			'allow_blank'   => true,
			'readonly'      => false,
			'faux'          => false,
			'tooltip_title' => false,
			'tooltip_desc'  => false,
			'field_class'   => '',
			'label_for'     => false,
		);
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

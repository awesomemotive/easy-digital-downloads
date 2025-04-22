<?php
/**
 * Select HTML Element
 *
 * @package     EDD\HTML
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\HTML;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class CountrySelect
 *
 * @since 3.3.8
 */
class CountrySelect extends Select {

	/**
	 * Parses the arguments for the select.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function defaults(): array {
		return wp_parse_args(
			array(
				'name'              => 'edd_countries',
				'class'             => 'edd_countries_filter',
				'options'           => edd_get_country_list(),
				'chosen'            => true,
				'selected'          => '',
				'show_option_none'  => false,
				'placeholder'       => __( 'Choose a Country', 'easy-digital-downloads' ),
				'show_option_all'   => false,
				'show_option_none'  => false,
				'show_option_empty' => __( 'All Countries', 'easy-digital-downloads' ),
				'data'              => array(
					'nonce' => wp_create_nonce( 'edd-country-field-nonce' ),
				),
			),
			parent::defaults()
		);
	}

	/**
	 * Gets the base CSS classes for the select element.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function get_base_classes(): array {
		$base_classes   = parent::get_base_classes();
		$base_classes[] = 'edd_countries_filter';

		return $base_classes;
	}
}

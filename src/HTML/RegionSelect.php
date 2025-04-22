<?php
/**
 * Region Select HTML Element.
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
 * Class RegionSelect
 *
 * @since 3.3.8
 */
class RegionSelect extends Select {
	use Traits\ShopStates;

	/**
	 * Gets the HTML for the select element.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get(): string {
		$this->args['options'] = $this->get_options();

		return parent::get();
	}

	/**
	 * Parses the arguments for the select.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function defaults(): array {
		return wp_parse_args(
			array(
				'name'              => 'edd_regions',
				'class'             => 'edd_regions_filter',
				'chosen'            => true,
				'selected'          => '',
				'show_option_none'  => false,
				'placeholder'       => __( 'Choose a Region', 'easy-digital-downloads' ),
				'show_option_empty' => __( 'All Regions', 'easy-digital-downloads' ),
				'show_option_all'   => false,
				'country'           => edd_get_shop_country(),
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
		$base_classes[] = 'edd_regions_filter';

		return $base_classes;
	}
}

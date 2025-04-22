<?php
/**
 * Region Select Element.
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
 * Class Region
 *
 * @since 3.3.8
 */
class Region extends Base {
	use Traits\ShopStates;

	/**
	 * Gets the HTML for the select element.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get(): string {
		$options = $this->get_options();
		if ( ! empty( $options ) ) {
			$this->args['options'] = $options;
			$input                 = new RegionSelect( $this->args );
		} else {
			if ( ! empty( $this->args['selected'] ) ) {
				$this->args['value'] = $this->args['selected'];
				unset( $this->args['selected'] );
			}
			$input = new Text( $this->args );
		}

		return $input->get();
	}

	/**
	 * Parses the arguments for the select.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function defaults(): array {
		return array(
			'name'     => 'edd_regions',
			'class'    => 'edd_regions_filter',
			'selected' => '',
			'country'  => edd_get_shop_country(),
		);
	}
}

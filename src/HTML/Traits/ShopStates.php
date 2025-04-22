<?php
/**
 * Shop States Trait.
 *
 * @package     EDD\HTML\Traits
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\HTML\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Shop States Trait.
 *
 * @since 3.3.8
 */
trait ShopStates {

	/**
	 * Gets the options for the select element.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	private function get_options(): array {
		if ( ! empty( $this->args['options'] ) ) {
			return $this->args['options'];
		}

		$options = edd_get_shop_states( $this->args['country'] );
		if ( 'GB' === $this->args['country'] && ! empty( $this->args['selected'] ) && ! array_key_exists( $this->args['selected'], $options ) ) {
			$legacy_states = include EDD_PLUGIN_DIR . 'i18n/states-gb-legacy.php';
			if ( array_key_exists( $this->args['selected'], $legacy_states ) ) {
				$options[ $this->args['selected'] ] = $legacy_states[ $this->args['selected'] ];

				// Sort the states alphabetically.
				asort( $options );
			}
		}

		return $options;
	}
}

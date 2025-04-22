<?php
/**
 * Coutry Class.
 *
 * @package     EDD\Forms\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Forms\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * The country field.
 *
 * @since 3.3.8
 */
class Country extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_id(): string {
		return 'billing_country';
	}

	/** Get the field label.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Billing Country', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.8
	 */
	public function do_input(): void {
		$country_select = new \EDD\HTML\CountrySelect(
			wp_parse_args(
				array(
					'selected'          => $this->get_selected_country(),
					'autocomplete'      => 'billing country',
					'show_option_all'   => false,
					'show_option_none'  => false,
					'show_option_empty' => __( 'Select a Country', 'easy-digital-downloads' ),
				),
				$this->get_defaults()
			)
		);
		$country_select->output();
	}

	/**
	 * Get the description.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_description(): string {
		return __( 'The country for your billing address.', 'easy-digital-downloads' );
	}

	/** Get the field key.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_key(): string {
		return 'country';
	}
}

<?php
/**
 * City Class.
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
 * The city field.
 *
 * @since 3.3.8
 */
class City extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_id(): string {
		return 'card_city';
	}

	/** Get the field label.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Billing City', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.8
	 */
	public function do_input(): void {
		$input = new \EDD\HTML\Text(
			wp_parse_args(
				array(
					'placeholder'  => esc_html__( 'City', 'easy-digital-downloads' ),
					'value'        => $this->data['address']['city'],
					'autocomplete' => 'billing locality',
					'include_span' => false,
				),
				$this->get_defaults()
			)
		);
		$input->output();
	}

	/**
	 * Get the description.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_description(): string {
		return __( 'The city for your billing address.', 'easy-digital-downloads' );
	}

	/** Get the field key.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_key(): string {
		return 'city';
	}
}

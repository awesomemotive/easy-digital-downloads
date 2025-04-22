<?php
/**
 * Address 2 Class.
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
 * The address 2 field.
 *
 * @since 3.3.8
 */
class Address2 extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_id(): string {
		return 'card_address_2';
	}

	/** Get the field label.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Billing Address Line 2 (optional)', 'easy-digital-downloads' );
	}

	/**
	 * Display the address field (line 1).
	 *
	 * @since 3.3.8
	 */
	public function do_input(): void {
		$input = new \EDD\HTML\Text(
			wp_parse_args(
				array(
					'placeholder'  => esc_html__( 'Address line 2', 'easy-digital-downloads' ),
					'value'        => $this->data['address']['line2'],
					'autocomplete' => 'billing address-line2',
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
		return __( 'The suite, apt no, PO box, etc, associated with your billing address.', 'easy-digital-downloads' );
	}

	/**
	 * Get the field key.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_key(): string {
		return 'address-2';
	}
}

<?php
/**
 * Phone Class.
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
 * The phone field.
 *
 * @since 3.3.8
 */
class Phone extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_id(): string {
		return 'card_phone';
	}

	/** Get the field label.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Phone Number', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.8
	 */
	public function do_input(): void {
		$input = new \EDD\HTML\Phone(
			wp_parse_args(
				array(
					'placeholder' => __( '(001) 555-0123', 'easy-digital-downloads' ),
					'value'       => $this->data['address']['phone'],
					'data'        => array(
						'country' => strtolower( $this->get_selected_country() ),
					),
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
		return __( 'The phone number for your order.', 'easy-digital-downloads' );
	}

	/**
	 * Get the field key.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_key(): string {
		return 'phone';
	}
}

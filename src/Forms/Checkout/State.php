<?php
/**
 * State Class.
 *
 * @package     EDD\Forms\Checkout
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.8
 */

namespace EDD\Forms\Checkout;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\HTML\Region;

/**
 * The state field.
 *
 * @since 3.3.8
 */
class State extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_id(): string {
		return 'card_state';
	}

	/** Get the field label.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	public function get_label(): string {
		return esc_html__( 'Billing State / Province', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.8
	 */
	public function do_input(): void {
		$selected_state = $this->get_selected_state();
		$input          = new Region(
			wp_parse_args(
				array(
					'placeholder'       => esc_html__( 'State / Province', 'easy-digital-downloads' ),
					'value'             => $selected_state,
					'country'           => $this->get_selected_country(),
					'selected'          => $selected_state,
					'chosen'            => false,
					'show_option_empty' => __( 'Select a State / Province', 'easy-digital-downloads' ),
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
		return __( 'The state or province for your billing address.', 'easy-digital-downloads' );
	}

	/**
	 * Get the field key.
	 *
	 * @since 3.3.8
	 * @return string
	 */
	protected function get_key(): string {
		return 'state';
	}

	/**
	 * Get the classes for the field.
	 *
	 * @since 3.3.8
	 * @return array
	 */
	protected function get_field_classes(): array {
		$classes   = parent::get_field_classes();
		$classes[] = 'card_state';

		return $classes;
	}
}

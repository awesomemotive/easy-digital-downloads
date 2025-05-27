<?php
/**
 * Discount editor minimum field.
 *
 * @package     EDD\Admin\Discounts\Editor
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Admin\Discounts\Editor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Forms\Field;

/**
 * Discount editor minimum field.
 *
 * @since 3.3.9
 */
class Minimum extends Field {

	/**
	 * Get the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'edd-min-cart-amount';
	}

	/**
	 * Get the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Minimum Amount', 'easy-digital-downloads' );
	}

	/**
	 * Get the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return __( 'The minimum subtotal of item prices in a cart before this discount may be applied.', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$minimum = $this->data->min_charge_amount ? edd_format_amount( $this->data->min_charge_amount ) : '';
		$text    = new \EDD\HTML\Text(
			wp_parse_args(
				array(
					'name'        => 'min_charge_amount',
					'id'          => 'edd-min-cart-amount',
					'value'       => $minimum,
					'placeholder' => __( 'No minimum', 'easy-digital-downloads' ),
				),
				$this->get_defaults()
			)
		);
		$text->output();
	}

	/**
	 * Get the action hooks.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_action_hooks(): array {
		return array(
			'before' => 'edd_edit_discount_form_before_min_cart_amount',
		);
	}
}

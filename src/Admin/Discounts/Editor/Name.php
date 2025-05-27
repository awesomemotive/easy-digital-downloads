<?php
/**
 * Discount editor name field.
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
 * Discount editor name field.
 *
 * @since 3.3.9
 */
class Name extends Field {

	/**
	 * Gets the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'name';
	}

	/**
	 * Get the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Name', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$input = new \EDD\HTML\Text(
			wp_parse_args(
				array(
					'placeholder' => __( 'Summer Sale', 'easy-digital-downloads' ),
					'value'       => stripslashes( $this->data->name ),
				),
				$this->get_defaults()
			)
		);
		$input->output();
	}

	/**
	 * Get the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return __( 'The name of this discount. Customers will see this on checkout.', 'easy-digital-downloads' );
	}

	/**
	 * Get the key.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	protected function get_key(): string {
		return 'edd-name';
	}

	/**
	 * Checks if the field is required.
	 *
	 * @since 3.3.9
	 * @return bool
	 */
	protected function is_required(): bool {
		return true;
	}

	/**
	 * Get the action hooks.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_action_hooks(): array {
		return array(
			'before' => 'edd_edit_discount_form_before_name',
		);
	}
}

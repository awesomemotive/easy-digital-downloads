<?php
/**
 * Discount editor max uses field.
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
 * Discount editor max uses field.
 *
 * @since 3.3.9
 */
class UseOnce extends Field {

	/**
	 * Get the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'once_per_customer';
	}

	/**
	 * Get the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Use Once Per Customer', 'easy-digital-downloads' );
	}

	/**
	 * Get the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$single_use = $this->data->get_once_per_customer();
		$toggle     = new \EDD\HTML\CheckboxToggle(
			array(
				'name'    => 'once_per_customer',
				'current' => $single_use,
				'label'   => __( 'Prevent customers from using this discount more than once.', 'easy-digital-downloads' ),
			)
		);
		$toggle->output();
	}

	/**
	 * Gets the action hooks.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_action_hooks(): array {
		return array(
			'before' => 'edd_edit_discount_form_before_use_once',
		);
	}
}

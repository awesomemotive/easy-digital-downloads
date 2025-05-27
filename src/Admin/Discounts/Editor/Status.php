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
class Status extends Field {

	/**
	 * Gets the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'status';
	}

	/**
	 * Gets the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Status', 'easy-digital-downloads' );
	}

	/**
	 * Renders the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$input = new \EDD\HTML\Select(
			wp_parse_args(
				array(
					'selected'          => $this->data->status,
					'options'           => array(
						'active'   => __( 'Active', 'easy-digital-downloads' ),
						'inactive' => __( 'Inactive', 'easy-digital-downloads' ),
						'archived' => __( 'Archived', 'easy-digital-downloads' ),
					),
					'show_option_none'  => false,
					'show_option_all'   => false,
					'show_option_empty' => false,
				),
				$this->get_defaults()
			)
		);
		$input->output();
	}

	/**
	 * Gets the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return __( 'The status of this discount code.', 'easy-digital-downloads' );
	}

	/**
	 * Gets the key.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	protected function get_key(): string {
		return 'edd-status';
	}

	/**
	 * Gets the action hooks.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_action_hooks(): array {
		return array(
			'before' => 'edd_edit_discount_form_before_status',
		);
	}
}

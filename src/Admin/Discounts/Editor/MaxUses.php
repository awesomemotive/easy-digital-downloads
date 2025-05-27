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
class MaxUses extends Field {

	/**
	 * Get the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'edd-max-uses';
	}

	/**
	 * Get the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Max Uses', 'easy-digital-downloads' );
	}

	/**
	 * Get the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return __( 'The maximum number of times this discount can be used.', 'easy-digital-downloads' );
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$max_uses = $this->data->max_uses ? absint( $this->data->max_uses ) : '';
		$text     = new \EDD\HTML\Text(
			wp_parse_args(
				array(
					'name'        => 'max_uses',
					'value'       => $max_uses,
					'placeholder' => __( 'Unlimited', 'easy-digital-downloads' ),
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
			'before' => 'edd_edit_discount_form_before_max_uses',
		);
	}
}

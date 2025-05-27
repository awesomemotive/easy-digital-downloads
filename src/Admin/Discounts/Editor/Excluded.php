<?php
/**
 * Discount editor excluded products field.
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
 * Discount editor excluded products field.
 *
 * @since 3.3.9
 */
class Excluded extends Field {

	/**
	 * Gets the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'edd-excluded-products';
	}

	/**
	 * Gets the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		/* translators: %s: Downloads plural label */
		return sprintf( __( '%s Excluded', 'easy-digital-downloads' ), edd_get_label_plural() );
	}

	/**
	 * Gets the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		/* translators: %s: Downloads plural label */
		return sprintf( __( '%s this discount cannot be applied to. Leave blank for none.', 'easy-digital-downloads' ), edd_get_label_plural() );
	}

	/**
	 * Gets the key.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_key(): string {
		return 'edd-excluded-products';
	}

	/**
	 * Renders the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$excluded_products = $this->data->get_excluded_products();
		$products          = new \EDD\HTML\ProductSelect(
			array(
				'name'        => 'excluded_products[]',
				'id'          => 'excluded_products',
				'selected'    => $excluded_products,
				'multiple'    => true,
				'chosen'      => true,
				/* translators: %s: Downloads plural label */
				'placeholder' => sprintf( _x( 'Select %s', 'Noun: The plural label for the download post type as a placeholder for a dropdown', 'easy-digital-downloads' ), edd_get_label_plural() ),
				'variations'  => true,
			)
		);
		$products->output();
	}

	/**
	 * Gets the action hooks.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	protected function get_action_hooks(): array {
		return array(
			'before' => 'edd_edit_discount_form_before_excluded_products',
		);
	}
}

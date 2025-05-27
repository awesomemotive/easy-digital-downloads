<?php
/**
 * Discount editor categories field.
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
 * Discount editor categories field.
 *
 * @since 3.3.9
 */
class Categories extends Field {

	/**
	 * Gets the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'edd-categories';
	}

	/**
	 * Get the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Categories', 'easy-digital-downloads' );
	}

	public function get_description(): string {
		return __( 'Optionally include/exclude products from this discount by category. Leave blank for any.', 'easy-digital-downloads' );
	}

	public function do_input(): void {
		$categories     = edd_get_adjustment_meta( $this->data->id, 'categories', true );
		$term_condition = edd_get_adjustment_meta( $this->data->id, 'term_condition', true ) ?? '';
		$dropdown       = new \EDD\HTML\CategorySelect(
			array(
				'name'             => 'categories[]',
				'id'               => 'edd-categories',
				'selected'         => $categories ?: array(), // phpcs:ignore Universal.Operators.DisallowShortTernary.Found
				'multiple'         => true,
				'chosen'           => true,
				'show_option_all'  => false,
				'show_option_none' => false,
				'number'           => 30,
			)
		);
		echo $dropdown->get(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		?>
		<p id="edd-discount-category-conditions" style="<?php echo esc_attr( $categories ? '' : 'display:none;' ); ?>">
			<select id="edd-term-condition" name="term_condition">
				<option value=""<?php selected( '', $term_condition ); ?>><?php esc_html_e( 'Only discount products in these categories', 'easy-digital-downloads' ); ?></option>
				<option value="exclude"<?php selected( 'exclude', $term_condition ); ?>><?php esc_html_e( 'Do not discount products in these categories', 'easy-digital-downloads' ); ?></option>
			</select>
		</p>
		<?php
	}
}

<?php
/**
 * Discount editor products field.
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
 * Discount editor products field.
 *
 * @since 3.3.9
 */
class Products extends Field {

	/**
	 * Get the field ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'edd_products';
	}

	/**
	 * Get the field label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		/* translators: %s: Downloads singular label */
		return sprintf( __( '%s Requirements', 'easy-digital-downloads' ), edd_get_label_singular() );
	}

	/**
	 * Get the field description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		/* translators: %s: Downloads plural label */
		return sprintf( __( '%s this discount can only be applied to. Leave blank for any.', 'easy-digital-downloads' ), edd_get_label_plural() );
	}

	/**
	 * Get the field key.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_key(): string {
		return 'edd-supports';
	}

	/**
	 * Get the action hooks.
	 *
	 * @since 3.3.9
	 * @return array
	 */
	public function get_action_hooks(): array {
		return array(
			'before' => 'edd_edit_discount_form_before_products',
			'after'  => 'edd_edit_discount_form_after_products',
		);
	}

	/**
	 * Render the code field.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$product_requirements = $this->data->get_product_reqs();
		$condition            = $this->data->get_product_condition();
		$condition_classes    = array( 'edd-product-conditions' );
		if ( empty( $product_requirements ) ) {
			$condition_classes[] = 'edd-hidden';
		}

		$products = new \EDD\HTML\ProductSelect(
			array(
				'name'        => 'product_reqs[]',
				'id'          => 'edd_products',
				'selected'    => $product_requirements,
				'multiple'    => true,
				'chosen'      => true,
				/* translators: %s: Downloads plural label */
				'placeholder' => sprintf( _x( 'Select %s', 'Noun: The plural label for the download post type as a placeholder for a dropdown', 'easy-digital-downloads' ), edd_get_label_plural() ),
				'variations'  => true,
				'class'       => 'edd-form-group__input edd-supports',
				'data'        => array(
					'edd-supported' => 'product_reqs',
					'search-type'   => 'download',
				),
			)
		);
		$products->output();
		?>
		<div id="edd-discount-product-conditions" class="<?php echo implode( ' ', $condition_classes ); ?>" data-edd-supports-product_reqs="any">
			<p>
				<select id="edd-product-condition" name="product_condition">
					<option value="all"<?php selected( 'all', $condition ); ?>>
						<?php
						/* translators: %s: Downloads plural label */
						printf( __( 'Cart must contain all selected %s', 'easy-digital-downloads' ), edd_get_label_plural() );
						?>
					</option>
					<option value="any"<?php selected( 'any', $condition ); ?>>
						<?php
						/* translators: %s: Downloads plural label */
						printf( __( 'Cart needs one or more of the selected %s', 'easy-digital-downloads' ), edd_get_label_plural() );
						?>
					</option>
				</select>
			</p>
			<p>
				<label>
					<input type="radio" class="tog" name="scope" value="global"<?php checked( 'global', $this->data->scope ); ?>/>
					<?php esc_html_e( 'Apply discount to entire purchase.', 'easy-digital-downloads' ); ?>
				</label><br/>
				<label>
					<input type="radio" class="tog" name="scope" value="not_global"<?php checked( 'not_global', $this->data->scope ); ?>/>
					<?php
					/* translators: %s: Downloads plural label */
					printf( __( 'Apply discount only to selected %s.', 'easy-digital-downloads' ), edd_get_label_plural() );
					?>
				</label>
			</p>
		</div>
		<?php
	}
}

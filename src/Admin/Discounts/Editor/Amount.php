<?php
/**
 * Discount editor amount field.
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
 * Discount editor amount field.
 *
 * @since 3.3.9
 */
class Amount extends Field {

	/**
	 * Gets the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'edd-amount';
	}

	/**
	 * Gets the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Amount', 'easy-digital-downloads' );
	}

	/**
	 * Gets the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return __( 'The amount as a percentage or flat rate. Cannot be left blank.', 'easy-digital-downloads' );
	}

	/**
	 * Render the amount field.
	 *
	 * @since 3.3.9
	 */
	public function render(): void {
		?>
		<div class="<?php echo esc_attr( $this->get_css_class_string( $this->get_form_group_classes() ) ); ?>">
			<?php
			do_action( 'edd_edit_discount_form_before_type', $this->data->id, $this->data );
			do_action( 'edd_edit_discount_form_before_amount', $this->data->id, $this->data );
			$this->do_label();
			$this->do_input();
			$this->do_description();
			?>
		</div>
		<?php
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		$type   = $this->data->get_type();
		$amount = empty( $this->data->amount ) ? '' : edd_format_amount( $this->data->amount );
		?>
		<div class="edd-form-group__control edd-amount-type-wrapper">
			<input type="text" required class="edd-price-field edd__input edd__input--left" id="edd-amount" name="amount" value="<?php echo esc_attr( $amount ); ?>" placeholder="<?php esc_html_e( '10.00', 'easy-digital-downloads' ); ?>" />
			<label for="edd-amount-type" class="screen-reader-text"><?php esc_html_e( 'Amount Type', 'easy-digital-downloads' ); ?></label>
			<select name="amount_type" id="edd-amount-type" class="edd__input edd__input--right">
				<option value="percent" <?php selected( $type, 'percent' ); ?>>%</option>
				<option value="flat"<?php selected( $type, 'flat' ); ?>><?php echo esc_html( edd_currency_symbol() ); ?></option>
			</select>
		</div>
		<?php
	}
}

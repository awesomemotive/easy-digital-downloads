<?php
/**
 * Discount editor code field.
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
 * Discount editor code field.
 *
 * @since 3.3.9
 */
class Code extends Field {

	/**
	 * Gets the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'code';
	}

	/**
	 * Gets the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Code', 'easy-digital-downloads' );
	}

	/**
	 * Gets the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return __( 'The code customers will enter to apply this discount. Only alphanumeric characters are allowed.', 'easy-digital-downloads' );
	}

	/**
	 * Renders the field.
	 *
	 * @since 3.3.9
	 */
	public function render(): void {
		?>
		<div class="<?php echo esc_attr( $this->get_css_class_string( $this->get_form_group_classes() ) ); ?>">
			<?php
			do_action( 'edd_edit_discount_form_before_code', $this->data->id, $this->data );
			$this->do_label();
			?>
			<div class="edd-form-group__control edd-code-wrapper">
				<?php
				$this->do_input();
				if ( empty( $this->data->code ) ) {
					do_action( 'edd_add_discount_form_after_code_field' );
				}
				?>
			</div>
			<?php
			if ( empty( $this->data->code ) ) {
				do_action( 'edd_add_discount_form_after_code_field_wrapper' );
			}
			?>
			<?php $this->do_description(); ?>
		</div>
		<?php
	}

	/**
	 * Renders the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		?>
		<input type="text" required="required" id="edd-code" name="code" class="code" value="<?php echo esc_attr( $this->data->code ); ?>" pattern="[a-zA-Z0-9_\-]+" maxlength="50" placeholder="<?php esc_html_e( '10PERCENT', 'easy-digital-downloads' ); ?>" />
		<?php
	}

	/**
	 * Gets the key.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	protected function get_key(): string {
		return 'edd-code';
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
}

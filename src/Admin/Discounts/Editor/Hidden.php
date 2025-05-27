<?php
/**
 * Discount editor hidden fields.
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
 * Discount editor hidden fields.
 *
 * @since 3.3.9
 */
class Hidden extends Field {

	/**
	 * Gets the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'hidden';
	}

	/**
	 * Gets the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return '';
	}

	/**
	 * Gets the description.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_description(): string {
		return '';
	}

	/**
	 * Render the hidden fields.
	 *
	 * @since 3.3.9
	 */
	public function render(): void {
		?>
		<div class="edd-form-group hidden">
			<?php $this->do_input(); ?>
		</div>
		<?php
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		?>
		<input type="hidden" name="type" value="discount" />
		<?php if ( ! empty( $this->data->id ) ) : ?>
			<input type="hidden" name="edd-action" value="edit_discount" />
			<input type="hidden" name="discount-id" value="<?php echo esc_attr( $this->data->id ); ?>" />
		<?php else : ?>
			<input type="hidden" name="edd-action" value="add_discount" />
		<?php endif; ?>
		<input type="hidden" name="edd-discount-nonce" value="<?php echo esc_attr( wp_create_nonce( 'edd_discount_nonce' ) ); ?>" />
		<?php
	}
}

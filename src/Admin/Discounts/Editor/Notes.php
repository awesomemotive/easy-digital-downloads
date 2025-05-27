<?php
/**
 * Discount editor notes field.
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
 * Discount editor notes field.
 *
 * @since 3.3.9
 */
class Notes extends Field {

	/**
	 * Gets the ID.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_id(): string {
		return 'notes';
	}

	/**
	 * Gets the label.
	 *
	 * @since 3.3.9
	 * @return string
	 */
	public function get_label(): string {
		return __( 'Discount Notes', 'easy-digital-downloads' );
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
	 * Render the notes field.
	 *
	 * @since 3.3.9
	 */
	public function render(): void {
		if ( empty( $this->data->id ) ) {
			return;
		}
		?>

		<div class="<?php echo esc_attr( $this->get_css_class_string( $this->get_form_group_classes() ) ); ?>">
			<?php do_action( 'edd_edit_discount_form_before_notes', $this->data->id, $this->data ); ?>
			<label for="notes"><?php esc_html_e( 'Discount Notes', 'easy-digital-downloads' ); ?></label>
			<div class="edd-form-group__control">
				<div class="edd-notes-wrapper">
					<?php $this->do_input(); ?>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the input.
	 *
	 * @since 3.3.9
	 */
	public function do_input(): void {
		echo edd_admin_get_notes_html( edd_get_discount_notes( $this->data->id ) );
		echo edd_admin_get_new_note_form( $this->data->id, 'discount' );
	}

}

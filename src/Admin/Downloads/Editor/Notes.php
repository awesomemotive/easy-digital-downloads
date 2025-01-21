<?php
/**
 * General notes section.
 *
 * @package   EDD\Admin\Downloads\Editor
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.6
 */

namespace EDD\Admin\Downloads\Editor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * General notes section.
 *
 * @since 3.3.6
 */
class Notes extends Section {

	/**
	 * Section ID.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $id = 'notes';

	/**
	 * Section priority.
	 *
	 * @since 3.3.6
	 * @var int
	 */
	protected $priority = 50;

	/**
	 * Section icon.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $icon = 'edit-large';

	/**
	 * Get the section label.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_label() {
		return __( 'Notes', 'easy-digital-downloads' );
	}

	/**
	 * Render the section.
	 *
	 * @since 3.3.6
	 * @return void
	 */
	public function render() {
		$download = $this->item;
		// Check if the user can edit this specific download ID (post ID).
		if ( ! $download || ! current_user_can( 'edit_product', $download->ID ) ) {
			return;
		}

		$product_notes = edd_get_product_notes( $download->ID );
		?>
		<div class="edd-form-group">
			<div class="edd-form-group__control">
				<label for="edd_product_notes_field" class="edd-form-group__label screen-reader-text">]
					<?php esc_html_e( 'Download Instructions', 'easy-digital-downloads' ); ?>
				</label>
				<textarea rows="5" cols="40" class="edd-form-group__input large-textarea" name="edd_product_notes" id="edd_product_notes_field"><?php echo esc_textarea( $product_notes ); ?></textarea>
			</div>
			<p class="edd-form-group__help description">
				<?php
				printf(
					/* translators: %s: singular label. */
					esc_html__( 'Special instructions for this %s. These will be added to the purchase receipt, and may be used by some extensions or themes.', 'easy-digital-downloads' ),
					edd_get_label_singular()
				);
				?>
			</p>
		</div>
		<?php
	}
}

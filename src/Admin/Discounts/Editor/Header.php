<?php
/**
 * Discount editor header.
 *
 * @package     EDD\Admin\Discounts\Editor
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.3.9
 */

namespace EDD\Admin\Discounts\Editor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Discount editor header.
 *
 * @since 3.3.9
 */
class Header {

	/**
	 * Render the header.
	 *
	 * @param \EDD_Discount $discount The discount object.
	 */
	public static function render( $discount ) {
		?>
		<div class="edd-editor__header edd-editor__header--discount">
			<div class="edd-editor__header--actions">
				<div class="edd-editor__actions">
					<?php
					$text = ! empty( $discount->id ) ? __( 'Update Discount', 'easy-digital-downloads' ) : __( 'Create Discount', 'easy-digital-downloads' );
					submit_button( $text, 'primary', 'submit', false );
					?>
				</div>
				<?php
				$loading = new \EDD\Utils\StatusBadge(
					array(
						'label'    => __( 'Saving Changes', 'easy-digital-downloads' ),
						'status'   => 'info',
						'icon'     => 'info',
						'class'    => array( 'edd-discount-status-badge', 'edd-hidden' ),
						'position' => 'before',
					)
				);
				echo $loading->get();
				?>
			</div>
		</div>
		<hr class="wp-header-end">
		<?php
	}
}

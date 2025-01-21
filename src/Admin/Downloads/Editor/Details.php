<?php
/**
 * General download details section.
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
 * General pricing section.
 *
 * @since 3.3.6
 */
class Details extends Section {

	/**
	 * Section ID.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $id = 'details';

	/**
	 * Section priority.
	 *
	 * @since 3.3.6
	 * @var int
	 */
	protected $priority = 1;

	/**
	 * Section icon.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $icon = 'download';

	/**
	 * Get the section label.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_label() {
		return __( 'Details', 'easy-digital-downloads' );
	}

	/**
	 * Render the section.
	 *
	 * @since 3.3.6
	 * @return void
	 */
	public function render() {
		$download = $this->item;
		if ( ! current_user_can( 'edit_product', $download->ID ) ) {
			return;
		}

		$types = edd_get_download_types();
		$type  = edd_get_download_type( $download->ID );
		ksort( $types );
		?>
		<div class="edd-form-group">
			<label for="_edd_product_type" class="edd-form-group__label">
				<?php
				echo esc_html(
					apply_filters( 'edd_product_type_options_heading', __( 'Product Type Options:', 'easy-digital-downloads' ) )
				);
				?>
			</label>
			<div class="edd-form-group__control">
				<?php
				$select = new \EDD\HTML\Select(
					array(
						'options'          => $types,
						'name'             => '_edd_product_type',
						'id'               => '_edd_product_type',
						'selected'         => $type,
						'show_option_all'  => false,
						'show_option_none' => false,
						'class'            => 'edd-form-group__input edd-supports',
						'data'             => array(
							'edd-supported' => 'product-type',
						),
					)
				);
				$select->output();
				?>
			</div>
			<p class="edd-form-group__help description">
				<?php esc_html_e( 'Sell this item as a single product with download files, or select a custom product type with different options, which may not necessarily include download files.', 'easy-digital-downloads' ); ?>
			</p>
		</div>

		<?php
		/**
		 * Hook to add additional fields to the meta box.
		 *
		 * @param int           $download_id The download ID.
		 * @param \EDD_Download $download    The download object.
		 */
		do_action( 'edd_meta_box_fields', $download->ID, $download );
	}
}

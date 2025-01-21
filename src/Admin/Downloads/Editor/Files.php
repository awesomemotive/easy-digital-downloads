<?php
/**
 * General files section.
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
 * General files section.
 *
 * @since 3.3.6
 */
class Files extends Section {

	/**
	 * Section ID.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $id = 'files';

	/**
	 * Section priority.
	 *
	 * @since 3.3.6
	 * @var int
	 */
	protected $priority = 8;

	/**
	 * Section icon.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $icon = 'list-view';

	/**
	 * What the section supports.
	 *
	 * @since 3.3.6
	 * @var array
	 */
	protected $supports = array(
		'product-type' => array( 'false', 'bundle' ),
	);

	/**
	 * Get the section label.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_label() {
		return $this->item && 'bundle' === $this->item->type ? edd_get_label_plural() : __( 'Files', 'easy-digital-downloads' );
	}

	/**
	 * Render the section.
	 *
	 * @since 3.3.6
	 * @return void
	 */
	public function render() {
		?>
		<div id="edd_product_files" class="inside">
			<?php
			/**
			 * Output the files fields.
			 *
			 * @since 1.9
			 */
			do_action( 'edd_meta_box_files_fields', $this->item->ID, '' );
			?>
		</div>
		<?php
	}

	/**
	 * Overrides the parent configuration if the download already has files attached.
	 *
	 * @since 3.3.6
	 * @return array
	 */
	public function get_config() {
		$config = parent::get_config();
		if ( edd_get_download_files( $this->item->ID ) ) {
			$config['supports'] = null;
		}

		return $config;
	}
}

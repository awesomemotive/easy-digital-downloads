<?php
/**
 * General settings section.
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
 * General settings section.
 *
 * @since 3.3.6
 */
class Settings extends Section {

	/**
	 * Section ID.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $id = 'settings';

	/**
	 * Section priority.
	 *
	 * @since 3.3.6
	 * @var int
	 */
	protected $priority = 80;

	/**
	 * Section icon.
	 *
	 * @since 3.3.6
	 * @var string
	 */
	protected $icon = 'admin-generic';

	/**
	 * Get the section label.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	public function get_label() {
		return __( 'Settings', 'easy-digital-downloads' );
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

		/**
		 * Output the settings fields.
		 *
		 * @since 1.9
		 * @param int           $download_id The post ID.
		 * @param \EDD_Download $download    The download object.
		 */
		do_action( 'edd_meta_box_settings_fields', $download->ID, $this->item );
	}
}

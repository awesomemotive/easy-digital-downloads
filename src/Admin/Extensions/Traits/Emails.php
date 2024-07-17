<?php

namespace EDD\Admin\Extensions\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Extensions\ProductData;

/**
 * Trait Emails
 *
 * @since 3.3.0
 * @package EDD\Admin\Extensions
 */
trait Emails {

	/**
	 * Overrides the body array sent to the Products API.
	 * Download category 1592 is "extensions".
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_api_body() {
		return array( 'tag' => 2340 );
	}

	/**
	 * Updates the card configuration.
	 *
	 * @since 3.3.0
	 * @param ProductData $product_data The extension data returned from the Products API.
	 * @return array
	 */
	protected function get_configuration( ProductData $product_data ) {
		return array(
			'style' => 'overlay',
		);
	}

	/**
	 * Update the button parameters.
	 *
	 * @since 3.3.0
	 * @param ProductData $product_data The extension data returned from the Products API.
	 * @param bool|int    $item_id      The item ID.
	 * @return array
	 */
	protected function get_button_parameters( ProductData $product_data, $item_id = false ) {
		$button = parent::get_button_parameters( $product_data, $item_id );

		// If the extension is not active, return the button as is.
		if ( ! $this->manager->is_plugin_active( $product_data->basename ) ) {
			return $button;
		}

		// If the extension is active and an update is available, link to the update screen.
		if ( version_compare( $this->manager->get_plugin_version( $product_data->basename ), $product_data->version, '<' ) ) {
			$button['button_text'] = __( 'Update Now', 'easy-digital-downloads' );
			$button['href']        = admin_url( 'update-core.php' );
		}

		return $button;
	}
}

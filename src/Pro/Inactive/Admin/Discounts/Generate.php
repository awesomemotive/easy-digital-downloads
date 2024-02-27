<?php
/**
 * Handles discount code generation via the admin area via AJAX.
 *
 * @since 3.2.0
 * @package EDD
 * @subpackage Admin
 * @category Discounts
 */

namespace EDD\Pro\Inactive\Admin\Discounts;

defined( 'ABSPATH' ) || exit;

class Generate extends \EDD\Admin\Discounts\Generate {

	/**
	 * Gets the data for the control.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	protected function get_control_data() {
		return array(
			'title'       => __( 'License Key Inactive', 'easy-digital-downloads' ),
			'message'     => __( 'Effortlessly generate unique discount codes by activating your license key.', 'easy-digital-downloads' ),
			'button_url'  => edd_get_admin_url(
				array(
					'page' => 'edd-settings',
				)
				),
			'button_text' => __( 'Activate License', 'easy-digital-downloads' ),
			'target'      => '_self',
		);
	}
}

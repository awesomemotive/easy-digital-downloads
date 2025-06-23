<?php
/**
 * Compatibility helper for the Square gateway.
 *
 * @package     EDD\Gateways\Square\Helpers
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.4.0
 */

namespace EDD\Gateways\Square\Helpers;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Compatibility helper for the Square gateway.
 *
 * @since 3.4.0
 */
class Compat {

	/**
	 * Map and merge serialized `form_data` to $_POST so it's accessible to other functions.
	 *
	 * @since 3.4.0
	 * @param array $post_data $_POST data containing serialized form data.
	 * @return void
	 */
	public static function map_form_data_to_request( $post_data ) {
		if ( ! isset( $post_data['form_data'] ) ) {
			return;
		}

		// It shouldn't be a string, but just in case.
		if ( is_string( $post_data['form_data'] ) ) {
			parse_str( $post_data['form_data'], $form_data );
		} else {
			$form_data = $post_data['form_data'];
		}

		$_POST    = array_merge( $_POST, $form_data );
		$_REQUEST = array_merge( $_REQUEST, $_POST );
	}

	/**
	 * Simulate being in an `edd_process_purchase_form()` request.
	 *
	 * @since 3.4.0
	 * @return void
	 */
	public static function mock_process_purchase_step() {
		// Save current errors.
		$errors = edd_get_errors();

		// Clear any errors that might be used as a reason to attempt a redirect in the following action.
		edd_clear_errors();

		// Don't run any attached actions twice.
		remove_all_actions( 'edd_pre_process_purchase' );

		// Pretend we are about to process a purchase.
		do_action( 'edd_pre_process_purchase' );

		// Clear any errors that may have been set in the previous action.
		edd_clear_errors();

		// Restore original errors.
		if ( ! empty( $errors ) ) {
			foreach ( $errors as $error_id => $error_message ) {
				edd_set_error( $error_id, $error_message );
			}
		}
	}
}

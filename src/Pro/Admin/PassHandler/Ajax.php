<?php
/**
 * Ajax functionality for EDD passes.
 *
 * @package EDD
 * @subpackage EDD/PassHandler
 */

namespace EDD\Pro\Admin\PassHandler;

use EDD\EventManagement\SubscriberInterface;
use EDD\Admin\Pass_Manager;

class Ajax extends \EDD\Admin\PassHandler\Ajax {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_ajax_edd_activate_pass' => 'activate',
		);
	}

	/**
	 * Attempt to activate a pass license.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function activate() {
		if ( ! $this->can_manage_pass() ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'You do not have permission to manage this pass.', 'easy-digital-downloads' ) ),
				)
			);
		}

		$license_key = ! empty( $_POST['license'] ) ? sanitize_text_field( $_POST['license'] ) : false;
		if ( ! $license_key ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'No key provided.', 'easy-digital-downloads' ) ),
				)
			);
		}

		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license_key,
			'item_id'    => $this->pass_manager::PERSONAL_PASS_ID,
			'pass_id'    => $this->pass_manager::PERSONAL_PASS_ID,
		);

		$license_data = $this->handler->remote_request( $api_params );

		if ( empty( $license_data->success ) ) {
			wp_send_json_error(
				array(
					'error'   => $license_data->error,
					'message' => $this->get_activation_error( $license_data, $license_key ),
				)
			);
		}

		update_site_option( 'edd_pro_license_key', $license_key );
		$this->pass_manager->maybe_set_pass_flag( $license_key, $license_data );
		$this->handler->update_pro_license( $license_data );

		if ( ! get_option( 'edd_pro_activation_date', false ) ) {
			update_option( 'edd_pro_activation_date', time() );
		}

		wp_send_json_success(
			array(
				'message'  => wpautop( __( 'Congratulations; your pass was successfully activated!', 'easy-digital-downloads' ) ),
				'actions'  => $this->handler->get_pass_actions( 'active', $license_key ),
				'url'      => false,
				'back_url' => false,
			)
		);
	}

	/**
	 * Gets the correct activation error message.
	 * Returns standard license error unless the response includes the extension flag.
	 *
	 * @since 3.1.1
	 * @param object $license_data
	 * @param string $license_key
	 * @return string
	 */
	private function get_activation_error( $license_data, $license_key ) {
		if ( empty( $license_data->extension ) ) {
			$messages = new \EDD\Licensing\Messages(
				array(
					'status'       => $license_data->error,
					'license_key'  => $license_key,
					'expires'      => $license_data->expires,
					'name'         => __( 'EDD (Pro)', 'easy-digital-downloads' ),
					'subscription' => ! empty( $license_data->subscription ) ? $license_data->subscription : null,
				)
			);
			return wpautop( $messages->get_message() );
		}

		// Unlikely result--extension license but not activated.
		if ( empty( $license_data->license ) || 'valid' !== $license_data->license ) {
			return '<p>' . sprintf(
				/* translators: the name of a licensed extension. */
				__( 'You have attempted to activate a license for %s instead of a pass. You will need to download the extension from your account.', 'easy-digital-downloads' ),
				'<strong>' . $license_data->item_name . '</strong>'
			) . '</p>';
		}

		// Update the EDD license options.
		$item_shortname        = preg_replace( '/[^a-zA-Z0-9_\s]/', '', str_replace( ' ', '_', strtolower( $license_data->item_name ) ) );
		$license_data->success = true;
		edd_update_option( "edd_{$item_shortname}_license_key", $license_key );
		update_option( "edd_{$item_shortname}_license_active", $license_data );

		$message = '<p>' . sprintf(
			/* translators: the name of a licensed extension. */
			__( 'You have attempted to activate a license for an extension instead of a pass. We have activated the license for %s, but you will need to download it and install it manually.', 'easy-digital-downloads' ),
			'<strong>' . $license_data->item_name . '</strong>'
		) . '</p>';
		if ( ! empty( $license_data->package ) ) {
			$message .= sprintf(
				'<p><a href="%s" class="button button-primary">%s</a></p>',
				esc_url( $license_data->package ),
				/* translators: the name of a licensed extension. */
				sprintf( __( 'Download %s', 'easy-digital-downloads' ), $license_data->item_name )
			);
		}

		return $message;
	}
}

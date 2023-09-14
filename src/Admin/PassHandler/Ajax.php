<?php
/**
 * Ajax functionality for EDD passes.
 *
 * @package EDD
 * @subpackage EDD/PassHandler
 */

namespace EDD\Admin\PassHandler;

use EDD\EventManagement\SubscriberInterface;
use EDD\Admin\Pass_Manager;

class Ajax implements SubscriberInterface {

	/**
	 * The EDD Pass Manager class.
	 *
	 * @var \EDD\Admin\Pass_Manager
	 */
	protected $pass_manager;

	/**
	 * The pass handler.
	 *
	 * @var \EDD\Admin|PassHandler\Handler;
	 */
	protected $handler;

	public function __construct( Handler $handler ) {
		$this->handler      = $handler;
		$this->pass_manager = new Pass_Manager();
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_ajax_edd_verify_pass'     => 'verify',
			'wp_ajax_edd_deactivate_pass' => 'deactivate',
			'wp_ajax_edd_delete_pass'     => 'delete',
		);
	}

	/**
	 * Attempt to verify a pass license.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function verify() {
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
					'message' => wpautop( __( 'Please enter a license key.', 'easy-digital-downloads' ) ),
				)
			);
		}

		wp_send_json_success( $this->get_verification_response( $license_key ) );
	}

	/**
	 * Gets the array of response parameters for a successful license key activation.
	 *
	 * @since 3.1.1
	 * @param string $license_key
	 * @return array
	 */
	private function get_verification_response( $license_key ) {

		$oth        = hash( 'sha512', wp_rand() );
		$hashed_oth = hash_hmac( 'sha512', $oth, wp_salt() );
		$endpoint   = admin_url( 'admin-ajax.php' );
		$redirect   = edd_get_admin_url( array( 'page' => 'edd-settings' ) );

		update_option( 'edd_connect_token', $oth );

		$url = add_query_arg(
			array(
				'key'      => $license_key,
				'oth'      => $hashed_oth,
				'endpoint' => $endpoint,
				'version'  => EDD_VERSION,
				'siteurl'  => admin_url(),
				'homeurl'  => network_home_url(),
				'redirect' => rawurldecode( base64_encode( $redirect ) ), // phpcs:ignore
			),
			'https://upgrade.easydigitaldownloads.com'
		);

		return array(
			'message'  => false,
			'actions'  => '',
			'url'      => $url,
			'back_url' => add_query_arg(
				array(
					'action' => 'edd_connect',
					'oth'    => $hashed_oth,
				),
				$endpoint
			),
		);
	}

	/**
	 * Attempt to deactivate a pass license.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function deactivate() {
		if ( ! $this->can_manage_pass() ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'You do not have permission to manage this pass.', 'easy-digital-downloads' ) ),
				)
			);
		}

		$pass_data    = $this->handler->get_pro_license();
		$api_params   = array(
			'edd_action' => 'deactivate_license',
			'license'    => $pass_data->key,
			'item_id'    => urlencode( $pass_data->pass_id ),
		);
		$license_data = $this->handler->remote_request( $api_params );

		$this->handler->update_pro_license( $license_data );
		$this->pass_manager->maybe_remove_pass_flag( $pass_data->key );

		wp_send_json_success(
			array(
				'message' => wpautop( __( 'Your pass was successfully deactivated.', 'easy-digital-downloads' ) ),
				'actions' => $this->handler->get_pass_actions( 'inactive', $pass_data->key ),
			)
		);
	}

	/**
	 * Deletes a pass key and the related option.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function delete() {
		if ( ! $this->can_manage_pass( 'edd_passhandler-delete' ) ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'You do not have permission to manage this pass.', 'easy-digital-downloads' ) ),
				)
			);
		}

		$license = $this->handler->get_pro_license();
		$license->delete();

		wp_send_json_success(
			array(
				'message' => wpautop( __( 'Pass key deleted.', 'easy-digital-downloads' ) ),
			)
		);
	}

	/**
	 * Whether the current user can manage the pass.
	 * Checks the user capabilities, tokenizer, and nonce.
	 *
	 * @since 3.1.1
	 * @param string $nonce The name of the specific nonce to validate.
	 * @return bool
	 */
	protected function can_manage_pass( $nonce = 'edd_passhandler' ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		$token     = isset( $_POST['token'] ) ? sanitize_text_field( $_POST['token'] ) : '';
		$timestamp = isset( $_POST['timestamp'] ) ? sanitize_text_field( $_POST['timestamp'] ) : '';

		if ( empty( $timestamp ) || empty( $token ) ) {
			return false;
		}

		return \EDD\Utils\Tokenizer::is_token_valid( $token, $timestamp ) && wp_verify_nonce( $_POST['nonce'], $nonce );
	}
}

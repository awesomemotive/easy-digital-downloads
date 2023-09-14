<?php
/**
 * Handle ajax initiated licensing actions.
 */
namespace EDD\Licensing;

use EDD\EventManagement\SubscriberInterface;

class Ajax implements SubscriberInterface {
	use Traits\Controls;

	/**
	 * The license object.
	 *
	 * @var \EDD\Licensing\License
	 */
	private $license;

	/**
	 * The license key.
	 *
	 * @var string
	 */
	private $license_key;

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.1.1
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_ajax_edd_activate_extension_license'   => 'activate',
			'wp_ajax_edd_deactivate_extension_license' => 'deactivate',
			'wp_ajax_edd_delete_extension_license'     => 'delete',
		);
	}

	/**
	 * Attempt to activate an extension license.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function activate() {
		if ( ! $this->can_manage() ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'You do not have permission to manage this extension.', 'easy-digital-downloads' ) ),
				)
			);
		}

		if ( ! empty( $_POST['license'] ) ) {
			$this->license_key = sanitize_text_field( $_POST['license'] );
		}
		if ( ! $this->license_key ) {
			wp_send_json_error(
				array(
					'message' => __( 'No key provided.', 'easy-digital-downloads' ),
				)
			);
		}

		$this->name   = filter_input( INPUT_POST, 'item_name', FILTER_SANITIZE_SPECIAL_CHARS );
		$api_params   = array(
			'edd_action'  => 'activate_license',
			'license'     => $this->license_key,
			'item_name'   => $this->name,
			'item_id'     => filter_input( INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT ),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);
		$custom_api   = filter_input( INPUT_POST, 'api', FILTER_SANITIZE_URL );
		$api          = new API( $custom_api );
		$license_data = $api->make_request( $api_params );

		if ( empty( $license_data->success ) ) {
			if ( ! empty( $license_data ) ) {
				$messages = new \EDD\Licensing\Messages(
					array(
						'status'       => $license_data->error,
						'license_key'  => $this->license_key,
						'expires'      => ! empty( $license_data->expires ) ? $license_data->expires : false,
						'name'         => $this->name,
						'subscription' => ! empty( $license_data->subscription ) ? $license_data->subscription : null,
						'api_url'      => $custom_api,
					)
				);
				$message  = $messages->get_message();
			} else {
				$message = __( 'Your license key could not be activated.', 'easy-digital-downloads' );
			}
			wp_send_json_error(
				array(
					'message' => wpautop( $message ),
				)
			);
		}

		set_site_transient( 'update_plugins', null );

		$pass_manager = new \EDD\Admin\Pass_Manager();
		$pass_manager->maybe_set_pass_flag( $this->license_key, $license_data );

		// Clear the option for licensed extensions to force regeneration.
		if ( ! empty( $license_data->license ) && 'valid' === $license_data->license ) {
			delete_option( 'edd_licensed_extensions' );
		}

		edd_update_option( filter_input( INPUT_POST, 'key', FILTER_SANITIZE_SPECIAL_CHARS ), $this->license_key );
		$license = new License( $this->name );
		$license->save( $license_data );
		// Get the license again.
		$this->license = new License( $this->name );
		$this->set_up_license_data();

		wp_send_json_success(
			array(
				'message' => $this->do_message( false ),
				'actions' => $this->get_actions( 'valid' ),
			)
		);
	}

	/**
	 * Attempt to deactivate an extension license.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function deactivate() {
		if ( ! $this->can_manage() ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'You do not have permission to manage this extension.', 'easy-digital-downloads' ) ),
				)
			);
		}

		$this->name        = filter_input( INPUT_POST, 'item_name', FILTER_SANITIZE_SPECIAL_CHARS );
		$item_id           = filter_input( INPUT_POST, 'item_id', FILTER_SANITIZE_NUMBER_INT );
		$this->license     = new License( $this->name );
		$this->license_key = $this->license->key;
		$api_params        = array(
			'edd_action'  => 'deactivate_license',
			'license'     => $this->license_key,
			'item_id'     => urlencode( $item_id ),
			'environment' => function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production',
		);
		$custom_api        = filter_input( INPUT_POST, 'api', FILTER_SANITIZE_URL );
		$api               = new API( $custom_api );
		$license_data      = $api->make_request( $api_params );

		$this->license->save( $license_data );

		$pass_manager = new \EDD\Admin\Pass_Manager();
		$pass_manager->maybe_remove_pass_flag( $this->license_key );

		wp_send_json_success(
			array(
				'message' => wpautop( __( 'Your license key has been deactivated.', 'easy-digital-downloads' ) ),
				'actions' => $this->get_actions( $license_data->license ),
			)
		);
	}

	/**
	 * Deletes an extension key and the related option.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function delete() {
		if ( ! $this->can_manage( 'edd_licensehandler-delete' ) ) {
			wp_send_json_error(
				array(
					'message' => wpautop( __( 'You do not have permission to manage this extension.', 'easy-digital-downloads' ) ),
				)
			);
		}

		$this->name    = filter_input( INPUT_POST, 'item_name', FILTER_SANITIZE_SPECIAL_CHARS );
		$this->license = new License( $this->name );
		$this->license->delete();
		edd_delete_option( filter_input( INPUT_POST, 'key', FILTER_SANITIZE_SPECIAL_CHARS ) );

		wp_send_json_success(
			array(
				'message' => wpautop( __( 'License key deleted.', 'easy-digital-downloads' ) ),
			)
		);
	}

	/**
	 * Whether the current user can manage the extension.
	 * Checks the user capabilities, tokenizer, and nonce.
	 *
	 * @since 3.1.1
	 * @param string $nonce The name of the specific nonce to validate.
	 * @return bool
	 */
	protected function can_manage( $nonce = 'edd_licensehandler' ) {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
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

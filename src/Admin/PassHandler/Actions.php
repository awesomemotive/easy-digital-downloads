<?php
/**
 * Actions functionality for EDD passes.
 *
 * @package EDD
 * @subpackage EDD/PassHandler
 */

namespace EDD\Admin\PassHandler;

use EDD\EventManagement\SubscriberInterface;

class Actions implements SubscriberInterface {

	/**
	 * The pass handler.
	 *
	 * @var \EDD\Admin|PassHandler\Handler;
	 */
	protected $handler;

	public function __construct( Handler $handler ) {
		$this->handler = $handler;
	}

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_refresh_pass_status' => 'refresh',
		);
	}

	/**
	 * When the "refresh" button is clicked, performs a remote license request to check the pass status.
	 * Sets a ten minute transient to avoid a double check (this is running twice for some reason) and excessive clicking.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function refresh() {
		if ( ! $this->can_refresh() ) {
			edd_redirect( $this->handler->get_extensions_url() );
		}

		$pass_data = $this->handler->get_pro_license();
		if ( empty( $pass_data->key ) ) {
			edd_redirect(
				edd_get_admin_url(
					array(
						'page'        => 'edd-settings',
						'edd-message' => 'missing-pass-key',
					)
				)
			);
		}

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $pass_data->key,
			'item_id'    => $pass_data->pass_id,
		);

		$license_data = $this->handler->remote_request( $api_params );
		if ( empty( $license_data->success ) ) {
			edd_redirect( $this->handler->get_extensions_url() );
		}

		$pass_manager = new \EDD\Admin\Pass_Manager();
		$pass_manager->maybe_set_pass_flag( $pass_data->key, $license_data );
		$this->handler->update_pro_license( $license_data );
		set_transient( 'edd_pass_refreshed', true, 10 * MINUTE_IN_SECONDS );

		edd_redirect( $this->handler->get_extensions_url() );
	}

	/**
	 * Check if the current user can refresh the pass status.
	 *
	 * @return bool
	 */
	private function can_refresh() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return false;
		}
		if ( get_transient( 'edd_pass_refreshed' ) ) {
			return false;
		}

		return true;
	}
}

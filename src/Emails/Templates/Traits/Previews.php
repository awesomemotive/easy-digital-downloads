<?php

namespace EDD\Emails\Templates\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Emails\Templates\Previews\Data;

trait Previews {

	/**
	 * Gets the email preview data.
	 *
	 * @since 3.3.0
	 * @return array|false
	 */
	final public function set_preview_data() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return false;
		}
		if ( is_null( $this->preview_data ) ) {
			$this->preview_data = $this->get_preview_data();
		}

		return $this->preview_data;
	}

	/**
	 * Check if we are doing a preview or test.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	final public static function doing_preview() {
		$edd_action = filter_input( INPUT_GET, 'edd_action', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( empty( $edd_action ) ) {
			$edd_action = filter_input( INPUT_GET, 'edd-action', FILTER_SANITIZE_SPECIAL_CHARS );
		}

		return ! empty( $edd_action ) && in_array( $edd_action, array( 'preview_email', 'send_test_email' ), true );
	}

	/**
	 * Generates the preview data for this email.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	protected function get_preview_data() {
		if ( 'order' === $this->context ) {
			return array(
				edd_get_order( Data::get_complete_order_id() ),
			);
		}

		if ( 'user' === $this->context ) {
				return Data::get_user_id_and_data();
		}

		if ( 'refund' === $this->context ) {
			$refund_id = Data::get_refund_id();

			return array(
				edd_get_order( $refund_id->parent ),
				$refund_id,
			);
		}

		return array( false );
	}

	/**
	 * Whether the email can be previewed.
	 * This requires valid preview data.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	protected function can_preview() {
		if ( ! $this->can_preview || ! \EDD\Emails\Registry::is_registered( $this->email_id ) ) {
			return false;
		}

		return $this->has_preview_data();
	}

	/**
	 * Whether a test email can be sent.
	 * This requires valid preview data.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	protected function can_test() {

		if ( ! $this->can_test || ! \EDD\Emails\Registry::is_registered( $this->email_id ) ) {
			return false;
		}

		return $this->has_preview_data();
	}

	/**
	 * Whether the email has valid preview data.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private function has_preview_data(): bool {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return false;
		}

		if ( is_null( $this->preview_data ) ) {
			$this->preview_data = $this->get_preview_data();
		}

		if ( is_array( $this->preview_data ) ) {
			return ! empty( array_filter( $this->preview_data ) );
		}

		return ! empty( $this->preview_data );
	}
}

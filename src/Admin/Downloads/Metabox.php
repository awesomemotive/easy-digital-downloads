<?php

namespace EDD\Admin\Downloads;

defined( 'ABSPATH' ) || exit;

use EDD\EventManagement\SubscriberInterface;

class Metabox implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'wp_ajax_edd_swap_download_type' => 'swap_download_type',
		);
	}

	/**
	 * Potentially swaps out the download files HTML when the product type changes.
	 *
	 * @since 3.2.0
	 */
	public function swap_download_type() {
		if ( ! current_user_can( 'edit_products' ) || ! edd_doing_ajax() ) {
			wp_send_json_error();
		}

		$download_id = ! empty( $_POST['post_id'] ) ? absint( $_POST['post_id'] ) : null;
		if ( is_null( $download_id ) || ! current_user_can( 'edit_product', $download_id ) ) {
			wp_send_json_error();
		}

		ob_start();
		do_action( 'edd_meta_box_files_fields', $download_id, $this->get_download_type() );

		wp_send_json_success(
			array(
				'html' => ob_get_clean(),
			)
		);
	}

	/**
	 * Gets the download type.
	 *
	 * @since 3.2.3
	 * @return string
	 */
	private function get_download_type() {
		return ( ! empty( $_POST['product_type'] ) && 'bundle' === $_POST['product_type'] ) ? 'bundle' : 'default';
	}
}

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
			'wp_ajax_edd_swap_download_type'=> 'swap_download_type',
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

		$new_type_is_bundle = (bool) ( ! empty( $_POST['product_type'] ) && 'bundle' === $_POST['product_type'] );
		ob_start();
		if ( $new_type_is_bundle ) {
			edd_render_products_field( $download_id, 'bundle' );
		} else {
			edd_render_files_field( $download_id, 'default' );
		}

		wp_send_json_success(
			array(
				'html' => ob_get_clean(),
			)
		);
	}
}

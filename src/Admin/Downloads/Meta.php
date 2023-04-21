<?php
/**
 * Handles meta related filters/actions for downloads.
 *
 * @since 3.1.0.5
 */
namespace EDD\Admin\Downloads;

use EDD\EventManagement\SubscriberInterface;

class Meta implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_metabox_save_edd_variable_prices' => 'variable_prices_value',
			'edd_metabox_save_edd_download_files'  => 'download_files_value',
			'edd_save_download'                    => array( 'bundled_conditions', 10, 2 ),
		);
	}

	/**
	 * Checks the variable prices array to weed out empty prices.
	 *
	 * @since 3.1.0.5
	 * @param array $prices
	 * @return array
	 */
	public function variable_prices_value( $prices ) {
		if ( empty( $prices ) ) {
			return false;
		}
		foreach ( $prices as $id => $price ) {
			if ( empty( $price['amount'] ) && empty( $price['name'] ) ) {
				unset( $prices[ $id ] );
				continue;
			}
		}

		return $prices;
	}


	/**
	 * Checks the download files array to weed out empty file options.
	 *
	 * @since 3.1.0.5
	 * @param array $files
	 * @return array
	 */
	public function download_files_value( $files ) {
		if ( empty( $files ) ) {
			return false;
		}
		foreach ( $files as $id => $file ) {
			if ( empty( $file['name'] ) && empty( $file['file'] ) ) {
				unset( $files[ $id ] );
				continue;
			}
		}

		return $files;
	}

	/**
	 * Once the download is saved, if it's not a bundle, delete the product conditions meta.
	 *
	 * @since 3.1.0.5
	 * @param int     $post_id
	 * @param WP_Post $post
	 * @return void
	 */
	public function bundled_conditions( $post_id, $post ) {
		if ( ! get_post_meta( $post_id, '_edd_product_type', true ) ) {
			delete_post_meta( $post_id, '_edd_bundled_products_conditions' );
		}
	}
}

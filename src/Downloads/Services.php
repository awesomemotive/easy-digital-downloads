<?php
/**
 * Downloads as Services
 * @package     EDD
 * @subpackage  Services
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.2.0
 */
namespace EDD\Downloads;

use EDD\EventManagement\SubscriberInterface;

class Services implements SubscriberInterface {

	/**
	 * Returns an array of events that this subscriber wants to listen to.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_order_receipt_show_download_files'     => array( 'modify_receipt', 10, 4 ),
			'edd_receipt_no_files_found_text'           => array( 'no_files_found_text', 10, 2 ),
			'edd_get_download_type'                     => array( 'get_download_type', 20, 2 ),
			'edd_settings_marketing'                    => 'settings',
			'edd_get_option_edd_das_service_categories' => 'maybe_update_option',
		);
	}

	/**
	 * Modifies the receipt to hide the download files for services.
	 *
	 * @since 3.2.0
	 * @param bool   $show
	 * @param int    $item_id
	 * @param array  $receipt_args
	 * @param object $order_item
	 * @return bool
	 */
	public function modify_receipt( $show, $item_id, $receipt_args, $order_item ) {
		return $this->is_download_service( $item_id, $order_item->price_id ) ? false : $show;
	}

	/**
	 * Modifies the "No Files Found" text to be empty for services.
	 *
	 * @since 3.2.0
	 * @param string $text
	 * @param int    $download_id
	 * @return string
	 */
	public function no_files_found_text( $text, $download_id ) {
		return $this->is_download_service( $download_id ) ? '' : $text;
	}

	/**
	 * Returns the download type for services.
	 *
	 * @since 3.2.0
	 * @param string $type
	 * @param int    $download_id
	 * @return string
	 */
	public function get_download_type( $type, $download_id ) {
		if ( 'service' === $type ) {
			return $type;
		}

		// If the download doesn't yet have a type, but does have AA settings, it's probably an AA download.
		if ( ( empty( $type ) || 'default' === $type ) && get_post_meta( $download_id, '_edd_das_enabled', true ) ) {
			update_post_meta( $download_id, '_edd_product_type', 'service' );
			delete_post_meta( $download_id, '_edd_das_enabled' );

			return 'service';
		}

		return $type;
	}

	/**
	 * Adds the settings field for selecting service categories.
	 *
	 * @since 3.2.0
	 * @param array $settings
	 * @return array
	 */
	public function settings( $settings ) {
		$settings['main']['edd_das_service_categories'] = array(
			'id'          => 'edd_das_service_categories',
			'name'        => __( 'Downloads as Services', 'easy-digital-downloads' ),
			'desc'        => __( 'Select the categories that contain services, or products with no downloadable files.', 'easy-digital-downloads' ),
			'type'        => 'select',
			'options'     => $this->get_terms_options(),
			'multiple'    => true,
			'chosen'      => true,
			'placeholder' => __( 'Select categories', 'easy-digital-downloads' ),
			'std'         => array(),
		);

		return $settings;
	}

	/**
	 * Updates the service categories option to be an array of term IDs.
	 *
	 * @since 3.2.0
	 * @param array $value
	 * @return array
	 */
	public function maybe_update_option( $value ) {
		if ( ! is_array( $value ) ) {
			return $value;
		}

		$terms = array_filter( array_keys( $value ) );
		if ( ! $terms ) {
			return $value;
		}

		$terms = array_map( 'absint', $terms );
		$terms = array_unique( $terms );

		return $terms;
	}

	/**
	 * Determines if the download is a service.
	 *
	 * @since 3.2.0
	 * @param int $download_id
	 * @param int|null $price_id
	 * @return bool
	 */
	private function is_download_service( $download_id, $price_id = null ) {
		$download = new Service( $download_id );

		return $download->is_service( $price_id );
	}

	/**
	 * Returns an array of terms for the settings field.
	 *
	 * @since 3.2.0
	 * @return array
	 */
	private function get_terms_options() {
		$args = array(
			'hide_empty'   => false,
			'hierarchical' => false,
		);

		$options = array();
		$terms   = get_terms( 'download_category', apply_filters( 'edd_das_get_terms', $args ) );
		foreach ( $terms as $term ) {
			$options[ $term->term_id ] = $term->name;
		}

		return $options;
	}
}

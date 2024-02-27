<?php
namespace EDD\Tests\Helpers;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Class EDD_Helper_Download.
 *
 * Helper class to create and delete a downlaod easily.
 */
class EDD_Helper_Download extends EDD_UnitTestCase {

	/**
	 * Delete a download.
	 *
	 * @since 2.3
	 *
	 * @param int $download_id ID of the download to delete.
	 */
	public static function delete_download( $download_id ) {
		// Delete the post
		wp_delete_post( $download_id, true );
	}

	public static function delete_all_downloads() {
		$query = new \WP_Query(
			array(
				'post_type'      => 'download',
				'posts_per_page' => -1,
			)
		);

		foreach ( $query->posts as $post ) {
			wp_delete_post( $post->ID, true );
		}
	}

	/**
	 * Create a simple download.
	 *
	 * @since 2.3
	 */
	public static function create_simple_download() {

		$post_id = wp_insert_post( array(
			'post_title'    => 'Test Download Product',
			'post_name'     => 'test-download-product',
			'post_type'     => 'download',
			'post_status'   => 'publish'
		) );

		$_download_files = array(
			array(
				'name'      => 'Simple File 1',
				'file'      => 'http://localhost/simple-file1.jpg',
				'condition' => 0
			),
		);

		$meta = array(
			'edd_price'                         => '20.00',
			'_variable_pricing'                 => 0,
			'edd_variable_prices'               => false,
			'edd_download_files'                => array_values( $_download_files ),
			'_edd_download_limit'               => 20,
			'_edd_hide_purchase_link'           => 1,
			'edd_product_notes'                 => 'Purchase Notes',
			'_edd_product_type'                 => 'default',
			'_edd_download_earnings'            => 40,
			'_edd_download_sales'               => 2,
			'_edd_download_limit_override_1'    => 1,
			'edd_sku'                           => 'sku_0012'
		);

		foreach( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return get_post( $post_id );

	}

	/**
	 * Create a variable priced download.
	 *
	 * @since 2.3
	 */
	public static function create_variable_download() {

		$post_id = wp_insert_post( array(
			'post_title'    => 'Variable Test Download Product',
			'post_name'     => 'variable-test-download-product',
			'post_type'     => 'download',
			'post_status'   => 'publish'
		) );

		$_variable_pricing = array(
			array(
				'name'   => 'Simple',
				'amount' => 20
			),
			array(
				'name'   => 'Advanced',
				'amount' => 100
			)
		);

		$_download_files = array(
			array(
				'name'      => 'File 1',
				'file'      => 'http://localhost/file1.jpg',
				'condition' => 0,
			),
			array(
				'name'      => 'File 2',
				'file'      => 'http://localhost/file2.jpg',
				'condition' => 'all',
			),
		);

		$meta = array(
			'edd_price'                         => '0.00',
			'_variable_pricing'                 => 1,
			'_edd_price_options_mode'           => 'on',
			'edd_variable_prices'               => array_values( $_variable_pricing ),
			'edd_download_files'                => array_values( $_download_files ),
			'_edd_download_limit'               => 20,
			'_edd_hide_purchase_link'           => 1,
			'edd_product_notes'                 => 'Purchase Notes',
			'_edd_product_type'                 => 'default',
			'_edd_download_earnings'            => 120,
			'_edd_download_sales'               => 6,
			'_edd_download_limit_override_1'    => 1,
			'edd_sku'                          => 'sku_0012',
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return get_post( $post_id );

	}

	/**
	 * Create a variable priced download.
	 *
	 * @since 2.3
	 */
	public static function create_variable_download_with_multi_price_purchase() {

		$post_id = wp_insert_post( array(
			'post_title'    => 'Variable Multi Test Download Product',
			'post_name'     => 'variable-multi-test-download-product',
			'post_type'     => 'download',
			'post_status'   => 'publish'
		) );

		$_variable_pricing = array(
			array(
				'name'   => 'Simple',
				'amount' => 20
			),
			array(
				'name'   => 'Advanced',
				'amount' => 100
			),
			array(
				'name'   => 'Enterprise',
				'amount' => 200,
			),
			array(
				'name'   => 'Corporate',
				'amount' => 300,
			),
		);

		$_download_files = array(
			array(
				'name'      => 'File 1',
				'file'      => 'http://localhost/file1.jpg',
				'condition' => 0,
			),
			array(
				'name'      => 'File 2',
				'file'      => 'http://localhost/file2.jpg',
				'condition' => 'all',
			),
		);

		$meta = array(
			'edd_price'                         => '0.00',
			'_variable_pricing'                 => 1,
			'_edd_price_options_mode'           => 'on',
			'edd_variable_prices'               => array_values( $_variable_pricing ),
			'edd_download_files'                => array_values( $_download_files ),
			'_edd_download_limit'               => 20,
			'_edd_hide_purchase_link'           => 1,
			'edd_product_notes'                 => 'Purchase Notes',
			'_edd_product_type'                 => 'default',
			'_edd_download_earnings'            => 120,
			'_edd_download_sales'               => 6,
			'_edd_download_limit_override_1'    => 1,
			'edd_sku'                          => 'sku_0013',
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return new \EDD_Download( $post_id );

	}

	/**
	 * Create a bundled download.
	 *
	 * @since 2.3
	 */
	public static function create_bundled_download() {

		$post_id = wp_insert_post( array(
			'post_title'    => 'Bundled Test Download Product',
			'post_name'     => 'bundled-test-download-product',
			'post_type'     => 'download',
			'post_status'   => 'publish'
		) );

		$simple_download 	= EDD_Helper_Download::create_simple_download();
		$variable_download 	= EDD_Helper_Download::create_variable_download();

		$meta = array(
			'edd_price'                 => '9.99',
			'_variable_pricing'         => 1,
			'edd_variable_prices'       => false,
			'edd_download_files'        => array(),
			'_edd_bundled_products'     => array( $simple_download->ID, $variable_download->ID ),
			'_edd_download_limit'       => 20,
			'edd_product_notes'         => 'Bundled Purchase Notes',
			'_edd_product_type'         => 'bundle',
			'_edd_download_earnings'    => 120,
			'_edd_download_sales'       => 12,
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return get_post( $post_id );

	}

	public static function create_variable_bundled_download() {

		$post_id = wp_insert_post(
			array(
				'post_title'  => 'Variable Bundled Test Download Product',
				'post_name'   => 'variable-bundled-test-download-product',
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		$simple_download   = self::create_simple_download();
		$variable_download = self::create_variable_download();
		$_variable_pricing = array(
			array(
				'name'   => 'Advanced',
				'amount' => 100,
			),
			array(
				'name'   => 'Enterprise',
				'amount' => 200,
			),
			array(
				'name'   => 'Corporate',
				'amount' => 300,
			),
		);

		$meta = array(
			'edd_price'                        => 9.99,
			'_variable_pricing'                => 1,
			'edd_variable_prices'              => array_values( $_variable_pricing ),
			'edd_download_files'               => array(),
			'_edd_bundled_products'            => array(
				1 => $simple_download->ID,
				2 => $variable_download->ID,
			),
			'_edd_bundled_products_conditions' => array(
				1 => 1,
				2 => 2,
			),
			'_edd_download_limit'              => 20,
			'edd_product_notes'                => 'Bundled Purchase Notes',
			'_edd_product_type'                => 'bundle',
			'_edd_download_earnings'           => 120,
			'_edd_download_sales'              => 12,
		);
		foreach ( $meta as $key => $value ) {
			update_post_meta( $post_id, $key, $value );
		}

		return get_post( $post_id );
	}

		/**
	 * Add a filter for the edd_download_files hook.
	 *
	 * This allows us to 'fake' the download files for a download.
	 */
	public static function add_download_files() {
		add_filter( 'edd_download_files', array( __CLASS__, 'helper_filter_download_files' ), 10, 3 );
	}

	/**
	 * Remove the filter for the edd_download_files hook.
	 */
	public static function remove_download_files() {
		remove_filter( 'edd_download_files', array( __CLASS__, 'helper_filter_download_files' ), 10, 3 );
	}

	/**
	 * Filter the download files for a download, by adding fake files, for testing.
	 *
	 * @param array $files      The download files.
	 * @param int   $download_id The download ID.
	 * @param int   $payment_id  The payment ID.
	 *
	 * @return array The filtered download files.
	 */
	public function helper_filter_download_files( $files, $download_id, $payment_id ) {
		$files = array(
			array (
				'index'          => '0',
				'attachment_id'  => '0',
				'thumbnail_size' => '',
				'name'           => 'Test File',
				'file'           => 'https://example.org/wp-content/uploads/edd/2019/05/test-file.zip',
				'condition'      => 'all',
			),
			array (
				'index'          => '1',
				'attachment_id'  => '0',
				'thumbnail_size' => '',
				'name'           => 'Test File',
				'file'           => 'https://example.org/wp-content/uploads/edd/2019/05/test-file.zip',
				'condition'      => '0',
			),
		);

		return $files;
	}
}

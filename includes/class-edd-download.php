<?php
/**
 * Download Object
 *
 * @package     EDD
 * @subpackage  Classes/Download
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.2
*/

/**
 * EDD_Download Class
 *
 * @since 2.2
 */
class EDD_Download {

	public $price;

	public $prices;

	public $is_variable;

	public $files;

	public $type;

	public $bundled_downloads;

	public $sales;

	public $earnings;

	public $notes;

	public $sku;

	/**
	 * Get things going
	 *
	 * @since 2.2
	 */
	public function __construct( $_id = false, $_args = array() ) {

		if( empty( $_id ) ) {

			$defaults = array(
				'post_type'   => 'download',
				'post_status' => 'draft',
				'post_title'  => __( 'New Download Product', 'edd' )
			);

			$args = wp_parse_args( $_args, $defaults );

			$_id  = wp_insert_post( $args, true );

		}

		$download = WP_Post::get_instance( $_id );

		foreach ( $download as $key => $value ) {

			$this->$key = $value;

		}

		foreach ( get_object_vars( $this ) as $key => $value ) {

			if( method_exists( $this, 'get_' . $key ) ) {

				$value = call_user_func( array( $this, 'get_' . $key ) );

			}

			$this->$key = $value;

		}

	}

	public function get_price() {

		$price = get_post_meta( $this->ID, 'edd_price', true );

		if ( $price ) {

			$price = edd_sanitize_amount( $price );
		
		} else {
		
			$price = 0;
		
		}

		return apply_filters( 'edd_get_download_price', $price, $this->ID );
	}

	public function get_prices() {
		$prices = get_post_meta( $this->ID, 'edd_variable_prices', true );
		return apply_filters( 'edd_get_variable_prices', $prices, $this->ID );
	}

	public function get_price_mode() {

	}

	public function has_variable_prices() {

	}

	public function get_files() {

	}

	public function get_file_price_condition() {

	}

	public function get_type() {

	}

	public function get_bundled_downloads() {

	}

	public function get_sales() {

	}

	public function  get_earnings() {

	}

	public function get_notes() {

	}

	public function get_sku() {

	}

	public function increase_earnings() {

	}

	public function decrease_earnings() {

	}

	public function increase_sales() {

	}

	public function decrease_sales() {

	}

	public function is_free() {

	}

}
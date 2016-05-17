<?php
/**
 * Easy Digital Downloads API V1
 *
 * @package     EDD
 * @subpackage  Classes/API
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_API_V2 Class
 *
 * The base version API class
 *
 * @since  2.6
 */
class EDD_API_V2 extends EDD_API {

	/**
	 * Process Get Products API Request
	 *
	 * @access public
	 * @since 2.0
	 * @param array $args Query arguments
	 * @return array $customers Multidimensional array of the products
	 */
	public function get_products( $args = array() ) {

		$products = array();
		$error = array();

		if ( empty( $args['product'] ) ) {

			$products['products'] = array();

			$query_args = array(
				'post_type'        => 'download',
				'posts_per_page'   => $this->per_page(),
				'suppress_filters' => true,
				'paged'            => $this->get_paged()
			);

			if( ! empty( $args['s'] ) ) {
				$query_args['s'] = sanitize_text_field( $args['s'] );
			}

			if( ! empty( $args['category'] ) ) {
				$query_args['download_category'] = $args['category'];
			}

			if( ! empty( $args['tag'] ) ) {
				$query_args['download_tag'] = $args['tag'];
			}

			$product_list = get_posts( $query_args );

			if ( $product_list ) {
				$i = 0;
				foreach ( $product_list as $product_info ) {
					$products['products'][$i] = $this->get_product_data( $product_info );
					$i++;
				}
			}

		} else {

			if ( get_post_type( $args['product'] ) == 'download' ) {
				$product_info = get_post( $args['product'] );

				$products['products'][0] = $this->get_product_data( $product_info );

			} else {
				$error['error'] = sprintf( __( 'Product %s not found!', 'easy-digital-downloads' ), $args['product'] );
				return $error;
			}
		}

		return $products;
	}

}

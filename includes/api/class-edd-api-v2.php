<?php
/**
 * Easy Digital Downloads API V1
 *
 * @package     EDD
 * @subpackage  Classes/API
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6
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
class EDD_API_V2 extends EDD_API_V1 {

	/**
	 * Process Get Products API Request
	 *
	 * @access public
	 * @since 2.6
	 * @param array $args Query arguments
	 * @return array $customers Multidimensional array of the products
	 */
	public function get_products( $args = array() ) {

		$products = array();
		$error    = array();

		if ( empty( $args['product'] ) ) {

			$products['products'] = array();

			$query_args = array(
				'post_type'        => 'download',
				'posts_per_page'   => $this->per_page(),
				'suppress_filters' => true,
				'paged'            => $this->get_paged(),
			);

			if( ! empty( $args['s'] ) ) {
				$query_args['s'] = sanitize_text_field( $args['s'] );
			}

			if( ! empty( $args['category'] ) ) {
				if ( strpos( $args['category'], ',' ) ) {
					$args['category'] = explode( ',', $args['category'] );
				}

				if ( is_numeric( $args['category'] ) ) {
					$query_args['tax_query'] = array(
						array(
							'taxonomy' => 'download_category',
							'field'    => 'ID',
							'terms'    => (int) $args['category']
						),
					);
				} else if ( is_array( $args['category'] ) ) {

					foreach ( $args['category'] as $category ) {


						$field = is_numeric( $category ) ? 'ID': 'slug';

						$query_args['tax_query'][] = array(
							'taxonomy' => 'download_category',
							'field'    => $field,
							'terms'    => $category,
						);

					}

				} else {
					$query_args['download_category'] = $args['category'];
				}
			}

			if( ! empty( $args['tag'] ) ) {
				if ( strpos( $args['tag'], ',' ) ) {
					$args['tag'] = explode( ',', $args['tag'] );
				}

				if ( is_numeric( $args['tag'] ) ) {
					$query_args['tax_query'] = array(
						array(
							'taxonomy' => 'download_tag',
							'field'    => 'ID',
							'terms'    => (int) $args['tag']
						),
					);
				} else if ( is_array( $args['tag'] ) ) {

					foreach ( $args['tag'] as $tag ) {


						$field = is_numeric( $tag ) ? 'ID': 'slug';

						$query_args['tax_query'][] = array(
							'taxonomy' => 'download_tag',
							'field'    => $field,
							'terms'    => $tag,
						);

					}

				} else {
					$query_args['download_tag'] = $args['tag'];
				}
			}

			if ( ! empty( $query_args['tax_query'] ) ) {

				$relation = ! empty( $args['term_relation'] ) ? sanitize_text_field( $args['term_relation'] ) : 'OR';
				$query_args['tax_query']['relation'] = $relation;

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

<?php
/**
 * Batch Downloads Import Class
 *
 * This class handles importing download products
 *
 * @package     EDD
 * @subpackage  Admin/Import
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.6
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Batch_Downloads_Import Class
 *
 * @since 2.6
 */
class EDD_Batch_Downloads_Import extends EDD_Batch_Import {


	public function init() {

		// Set up default field map values
		$this->field_mapping = array(
			'post_title'   => '',
			'post_name'    => '',
			'post_status'  => 'draft',
			'post_author'  => '',
			'post_date'    => '',
			'post_content' => '',
			'post_excerpt' => '',
			'price'        => '',
			'files'        => '',
			'categories'   => '',
			'tags'         => '',
			'notes'        => ''
		);
	}

	/**
	 * Process a step
	 *
	 * @since 2.6
	 * @return bool
	 */
	public function process_step() {

		$more = false;

		if ( ! $this->can_import() ) {
			wp_die( __( 'You do not have permission to import data.', 'edd' ), __( 'Error', 'edd' ), array( 'response' => 403 ) );
		}

		$i      = 1;
		$offset = $this->step > 1 ? ( $this->per_step * $this->step ) : 0;

		if( $offset > $this->total ) {
			$this->done = true;
		}

		if( ! $this->done && $this->csv->data ) {

			$more = true;

			foreach( $this->csv->data as $key => $row ) {

				// Skip all rows until we reach pass our offset
				if( $key + 1 < $offset ) {
					continue;
				}

				// Done with this batch
				if( $i >= $this->per_step ) {
					break;
				}

				// Import Download
				$args = array(
					'post_type'    => 'download',
					'post_title'   => '',
					'post_name'    => '',
					'post_status'  => '',
					'post_author'  => '',
					'post_date'    => '',
					'post_content' => '',
					'post_excerpt' => ''
				);

				foreach( $args as $key => $field ) {
					if( ! empty( $this->field_mapping[ $key ] ) && ! empty( $row[ $this->field_mapping[ $key ] ] ) ) {
						$args[ $key ] = $row[ $this->field_mapping[ $key ] ];
					}
				}

				$download_id = wp_insert_post( $args );

				// setup categories
				if( ! empty( $row[ $this->field_mapping['categories'] ] ) ) {

					$categories = $this->str_to_array( $row[ $this->field_mapping['categories'] ] );

					$categories = $this->maybe_create_terms( $categories, 'download_category' );

					if( ! empty( $categories ) ) {

						wp_set_object_terms( $download_id, $categories, 'download_category' );

					}

				}

				// setup tags
				if( ! empty( $row[ $this->field_mapping['tags'] ] ) ) {

					$tags = $this->str_to_array( $row[ $this->field_mapping['tags'] ] );

					$tags = $this->maybe_create_terms( $tags, 'download_tag' );

					if( ! empty( $tags ) ) {

						wp_set_object_terms( $download_id, $tags, 'download_tag' );

					}

				}

				// setup price(s)
				if( ! empty( $row[ $this->field_mapping['price'] ] ) ) {

					$price = $row[ $this->field_mapping['price'] ];

					if( is_numeric( $price ) ) {

						update_post_meta( $download_id, 'edd_price', edd_sanitize_amount( $price ) );

					} else {

						$prices = $this->str_to_array( $price );

						if( ! empty( $prices ) ) {

							$variable_prices = array();
							foreach( $prices as $price ) {

								// See if this matches the EDD Download export for variable prices
								if( false !== strpos( $price, ':' ) ) {

									$price = array_map( 'trim', explode( ':', $price ) );

									$variable_prices[] = array( 'name' => $price[0], 'amount' => $price[1] );

								}

							}

							update_post_meta( $download_id, '_variable_pricing', 1 );
							update_post_meta( $download_id, 'edd_variable_prices', $variable_prices );

						}

					}

				}

				// setup files
				if( ! empty( $row[ $this->field_mapping['files'] ] ) ) {

					$files = $this->str_to_array( $row[ $this->field_mapping['files'] ] );

					if( ! empty( $files ) ) {

						$download_files = array();
						foreach( $files as $file ) {

							$download_files[] = array( 'file' => $file, 'name' => basename( $file ) );

						}

						update_post_meta( $download_id, 'edd_download_files', $download_files );

					}

				}

				// Product Image
				if( ! empty( $row[ $this->field_mapping['featured_image'] ] ) ) {

					// Set up image here
					$image_id = 0;

					update_post_meta( $download_id, '_thumbnail_id', $image_id );
				}

				// File download limit
				if( ! empty( $row[ $this->field_mapping['download_limit'] ] ) ) {

					update_post_meta( $download_id, '_edd_download_limit', absint( $row[ $this->field_mapping['download_limit'] ] ) );
				}

				// Sale count
				if( ! empty( $row[ $this->field_mapping['sales'] ] ) ) {

					update_post_meta( $download_id, '_edd_download_sales', absint( $row[ $this->field_mapping['sales'] ] ) );
				}

				// Earnings
				if( ! empty( $row[ $this->field_mapping['earnings'] ] ) ) {

					update_post_meta( $download_id, '_edd_download_earnings', edd_sanitize_amount( $row[ $this->field_mapping['earnings'] ] ) );
				}

				// Notes
				if( ! empty( $row[ $this->field_mapping['notes'] ] ) ) {

					update_post_meta( $download_id, 'edd_product_notes', sanitize_text_field( $row[ $this->field_mapping['notes'] ] ) );
				}

				// SKU
				if( ! empty( $row[ $this->field_mapping['sku'] ] ) ) {

					update_post_meta( $download_id, 'edd_sku', sanitize_text_field( $row[ $this->field_mapping['sku'] ] ) );
				}

				// Custom fields


				$i++;
			}

		}

		return $more;
	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.6
	 * @return int
	 */
	public function get_percentage_complete() {

		if( $this->total > 0 ) {
			$percentage = ( $this->step / $this->total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	private function str_to_array( $str = '' ) {

		// Look for standard delimiters
		if( false !== strpos( $str, '|' ) ) {

			$delimiter = '|';

		} elseif( false !== strpos( $str, ',' ) ) {

			$delimiter = ',';

		} elseif( false !== strpos( $str, ';' ) ) {

			$delimiter = ';';

		}

		if( ! empty( $delimiter ) ) {

			$array = (array) explode( $delimiter, $str );

			return array_map( 'trim', $array );

		}

		return array();

	}

	private function maybe_create_terms( $terms = array(), $taxonomy = 'download_category' ) {

		// Return of term IDs
		$term_ids = array();

		foreach( $terms as $term ) {

			if( is_numeric( $term ) && 0 === (int) $term ) {

				$term = get_term( $term, $taxonomy );

			} else {

				$term = get_term_by( 'name', $term, $taxonomy );

				if( ! $term ) {

					$term = get_term_by( 'slug', $term, $taxonomy );

				}

			}

			if( ! empty( $term ) ) {

				$term_ids[] = $term->term_id;

			} else {

				$term_ids[] = wp_insert_term( $term, $taxonomy );

			}

		}

		return $term_ids;
	}

}
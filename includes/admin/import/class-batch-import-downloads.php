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

	public $field_mapping = array();

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

		$csv = new parseCSV();
		$csv->auto( $this->file );

		if( $csv->data ) {

			$i    = 0;
			$more = true;

			foreach( $csv->data as $key => $row ) {

				// Done with this batch
				if( $i >= 19 ) {
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

					if( ! empty( $categories ) ) {

						wp_set_object_terms( $download_id, $terms, 'download_category' );

					}

				}


				// setup tags
				if( ! empty( $row[ $this->field_mapping['tags'] ] ) ) {

					$tags = $this->str_to_array( $row[ $this->field_mapping['tags'] ] );

					if( ! empty( $tags ) ) {

						wp_set_object_terms( $download_id, $terms, 'download_tag' );

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

							update_post_meta( $download_id, 'edd_variable_prices', $variable_prices );

						}

					}

				}

				// setup files


				// setup other metadata

				// Once download is imported, remove row
				unset( $csv->data[ $key ] );
				$i++;
			}

			$csv->save();

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

		$total = 20;

		if( $total > 0 ) {
			$percentage = ( $this->step / $total ) * 100;
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
}
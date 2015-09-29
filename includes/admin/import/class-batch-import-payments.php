<?php
/**
 * Batch Import Class
 *
 * This is the base class for all batch import methods. Each data import type (customers, payments, etc) extend this class
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
 * EDD_Batch_Import Class
 *
 * @since 2.6
 */
class EDD_Batch_Payments_Import extends EDD_Batch_Import {

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

				// Import payment

				// Once payment is imported, remove row
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
}
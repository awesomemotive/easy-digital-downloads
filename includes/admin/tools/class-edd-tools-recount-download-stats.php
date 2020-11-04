<?php
/**
 * Recount download earnings and stats
 *
 * This class handles batch processing of recounting earnings and stats
 *
 * @subpackage  Admin/Tools/EDD_Tools_Recount_Stats
 * @copyright   Copyright (c) 2015, Chris Klosowski
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Tools_Recount_Stats Class
 *
 * @since 2.5
 */
class EDD_Tools_Recount_Download_Stats extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 2.5
	 */
	public $export_type = '';

	/**
	 * Allows for a non-download batch processing to be run.
	 * @since  2.5
	 * @var boolean
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step
	 * @since  2.5
	 * @var integer
	 */
	public $per_step = 30;

	/**
	 * ID of the download we're recounting stats for
	 * @var int|false
	 */
	protected $download_id = false;

	/**
	 * Array or order IDs found from the query
	 * @var array
	 */
	protected $_log_ids_debug = array();

	/**
	 * Get the Export Data
	 *
	 * @since 2.5
	 * @global object $wpdb Used to query the database using the WordPress
	 *   Database API
	 * @return bool
	 */
	public function get_data() {

		$accepted_statuses  = apply_filters( 'edd_recount_accepted_statuses', array( 'complete', 'revoked' ) );

		if ( $this->step == 1 ) {
			$this->delete_data( 'edd_temp_recount_download_stats' );
		}

		$totals = $this->get_stored_data( 'edd_temp_recount_download_stats' );

		if ( false === $totals ) {
			$totals = array(
				'earnings' => (float) 0,
				'sales'    => 0,
			);
			$this->store_data( 'edd_temp_recount_download_stats', $totals );
		}

		$deprecated_args = edd_apply_filters_deprecated( 'edd_recount_download_stats_args', array(
			array(
				'post_parent'    => $this->download_id,
				'post_type'      => 'edd_log',
				'posts_per_page' => $this->per_step,
				'post_status'    => 'publish',
				'paged'          => $this->step,
				'log_type'       => 'sale',
				'fields'         => 'ids',
			)
		), '3.0' );

		$new_args = array(
			'status__in' => $accepted_statuses,
			'number'     => $deprecated_args['posts_per_page'],
			'offset'     => ( $deprecated_args['paged'] * $deprecated_args['posts_per_page'] ) - $deprecated_args['posts_per_page']
		);

		if ( ! empty( $this->download_id ) && is_numeric( $this->download_id ) ) {
			$new_args['product_id'] = absint( $this->download_id );
		}

		$order_items = edd_get_order_items( $new_args );

		$this->_log_ids_debug = array();

		if ( $order_items ) {
			foreach ( $order_items as $order_item ) {

				$this->_log_ids_debug[] = $order_item->order_id;

				}

					$totals['sales']++;
					$totals['earnings'] += $order_item->total;

			}

			$this->store_data( 'edd_temp_recount_download_stats', $totals );

			return true;
		}


		update_post_meta( $this->download_id, '_edd_download_sales'   , $totals['sales'] );
		update_post_meta( $this->download_id, '_edd_download_earnings', $totals['earnings'] );

		return false;

	}

	/**
	 * Return the calculated completion percentage
	 *
	 * @since 2.5
	 * @return int
	 */
	public function get_percentage_complete() {

		if ( $this->step == 1 ) {
			$this->delete_data( 'edd_recount_total_' . $this->download_id );
		}

		$accepted_statuses  = apply_filters( 'edd_recount_accepted_statuses', array( 'complete', 'revoked' ) );
		$total   = $this->get_stored_data( 'edd_recount_total_' . $this->download_id );

		if ( false === $total ) {
			$deprecated_args = edd_apply_filters_deprecated( 'edd_recount_download_stats_total_args', array(
				array(
					'post_parent'    => $this->download_id,
					'post_type'      => 'edd_log',
					'post_status'    => 'publish',
					'log_type'       => 'sale',
					'fields'         => 'ids',
					'nopaging'       => true,
				)
			), '3.0' );

			$new_args = array(
				'status__in' => $accepted_statuses,
			);

			if ( ! empty( $deprecated_args['post_parent'] ) && is_numeric( $deprecated_args['post_parent'] ) ) {
				$new_args['product_id'] = absint( $deprecated_args['post_parent'] );
			}

			$total = edd_count_order_items( $new_args );

			$this->store_data( 'edd_recount_total_' . $this->download_id, $total );
		}

		$percentage = 100;

		if( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the payments export
	 *
	 * @since 2.5
	 * @param array $request The Form Data passed into the batch processing
	 */
	public function set_properties( $request ) {
		$this->download_id = isset( $request['download_id'] ) ? sanitize_text_field( $request['download_id'] ) : false;
	}

	/**
	 * Process a step
	 *
	 * @since 2.5
	 * @return bool
	 */
	public function process_step() {

		if ( ! $this->can_export() ) {
			wp_die( __( 'You do not have permission to export data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if( $had_data ) {
			$this->done = false;
			return true;
		} else {
			$this->delete_data( 'edd_recount_total_' . $this->download_id );
			$this->delete_data( 'edd_temp_recount_download_stats' );
			$this->done    = true;
			$this->message = sprintf( __( 'Earnings and sales stats successfully recounted for %s.', 'easy-digital-downloads' ), get_the_title( $this->download_id ) );
			return false;
		}
	}

	public function headers() {
		edd_set_time_limit();
	}

	/**
	 * Perform the export
	 *
	 * @since 2.5
	 * @return void
	 */
	public function export() {

		// Set headers
		$this->headers();

		edd_die();
	}

	/**
	 * Given a key, get the information from the Database Directly
	 *
	 * @since  2.5
	 * @param  string $key The option_name
	 * @return mixed       Returns the data from the database
	 */
	private function get_stored_data( $key ) {
		global $wpdb;
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM $wpdb->options WHERE option_name = '%s'", $key ) );

		if ( empty( $value ) ) {
			return false;
		}

		$maybe_json = json_decode( $value );
		if ( ! is_null( $maybe_json ) ) {
			$value = json_decode( $value, true );
		}

		return $value;
	}

	/**
	 * Give a key, store the value
	 *
	 * @since  2.5
	 * @param  string $key   The option_name
	 * @param  mixed  $value  The value to store
	 * @return void
	 */
	private function store_data( $key, $value ) {
		global $wpdb;

		$value = is_array( $value ) ? wp_json_encode( $value ) : esc_attr( $value );

		$data = array(
			'option_name'  => $key,
			'option_value' => $value,
			'autoload'     => 'no',
		);

		$formats = array(
			'%s', '%s', '%s',
		);

		$wpdb->replace( $wpdb->options, $data, $formats );
	}

	/**
	 * Delete an option
	 *
	 * @since  2.5
	 * @param  string $key The option_name to delete
	 * @return void
	 */
	private function delete_data( $key ) {
		global $wpdb;
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}

}

<?php
/**
 * Reset store earnings and stats.
 *
 * This class handles batch processing of resetting store and download sales and earnings stats.
 *
 * @package     EDD
 * @subpackage  Admin\Tools
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * EDD_Tools_Reset_Stats class.
 *
 * @since 2.5
 * @since 3.0 Updated to work with new query methods.
 */
class EDD_Tools_Reset_Stats extends EDD_Batch_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions.
	 *
	 * @since 2.5
	 * @var string
	 */
	public $export_type = '';

	/**
	 * Allows for a non-download batch processing to be run.
	 *
	 * @since 2.5
	 * @var bool
	 */
	public $is_void = true;

	/**
	 * Sets the number of items to pull on each step.
	 *
	 * @since 2.5
	 * @var int
	 */
	public $per_step = 1;

	/**
	 * Sets the message to use when returning a response to the customer.
	 *
	 * @var string
	 */
	public $message = '';

	/**
	 * Retrieve the export data.
	 *
	 * @since 2.5
	 *
	 * @return array|false $data The data for the CSV file, false otherwise.
	 */
	public function get_data() {
		global $wpdb;

		$tables = $this->get_stored_data( 'edd_reset_tables_to_truncate' );

		if ( ! is_array( $tables ) ) {
			return false;
		}

		$offset     = ( $this->step - 1 ) * $this->per_step;
		$step_items = array_slice( $tables, $offset, $this->per_step );

		if ( $step_items ) {
			$query = "TRUNCATE TABLE {$step_items[0]}";
			edd_debug_log( var_export($query, true), true );
			$wpdb->query( $query );

			return true;
		}

		return false;
	}

	/**
	 * Return the percentage completed.
	 *
	 * @since 2.5
	 *
	 * @return float Percentage complete.
	 */
	public function get_percentage_complete() {
		$items = $this->get_stored_data( 'edd_reset_tables_to_truncate' );
		$total = count( $items );

		$percentage = 100;

		if ( $total > 0 ) {
			$percentage = ( ( $this->per_step * $this->step ) / $total ) * 100;
		}

		if ( $percentage > 100 ) {
			$percentage = 100;
		}

		return $percentage;
	}

	/**
	 * Set the properties specific to the export.
	 *
	 * @since 2.5
	 *
	 * @param array $request Form data passed into the batch processor.
	 */
	public function set_properties( $request ) {}

	/**
	 * Process a step.
	 *
	 * @since 2.5
	 *
	 * @return bool True if more data exists, false otherwise.
	 */
	public function process_step() {
		if ( ! $this->can_export() ) {
			wp_die( esc_html__( 'You do not have permission to export data.', 'easy-digital-downloads' ), esc_html__( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
		}

		$had_data = $this->get_data();

		if ( $had_data ) {
			$this->done = false;
			return true;
		} else {
			update_option( 'edd_earnings_total', 0, false );
			update_option( 'edd_earnings_total_without_tax', 0, false );
			delete_transient( 'edd_earnings_total' );
			delete_transient( 'edd_earnings_total_without_tax' );
			delete_transient( 'edd_estimated_monthly_stats' . true );
			delete_transient( 'edd_estimated_monthly_stats' . false );
			$this->delete_data( 'edd_reset_tables_to_truncate' );

			// Reset the sequential order numbers
			if ( edd_get_option( 'enable_sequential' ) ) {
				delete_option( 'edd_last_payment_number' );
				delete_option( 'edd_next_order_number' );
			}

			$this->done    = true;
			$this->message = __( 'Your store has been successfully reset.', 'easy-digital-downloads' );
			return false;
		}
	}

	/**
	 * Export headers.
	 *
	 * @since 2.5
	 */
	public function headers() {
		edd_set_time_limit();
	}

	/**
	 * Perform the export.
	 *
	 * @since 2.5
	 */
	public function export() {

		// Set headers.
		$this->headers();

		edd_die();
	}

	/**
	 * Fetch data prior to batch processing starting.
	 *
	 * @since 2.5
	 */
	public function pre_fetch() {
		if ( 1 === $this->step ) {
			$this->delete_data( 'edd_reset_tables_to_truncate' );
		}

		$tables = get_option( 'edd_reset_tables_to_truncate', false );

		if ( false === $tables ) {
			$tables = array();

			foreach ( EDD()->components as $component ) {
				/** @var $component EDD\Component */

				// Objects
				$object = $component->get_interface( 'table' );
				if ( $object instanceof \EDD\Database\Table && $object->exists() ) {
					if ( 'adjustments' === $object->name ) {
						continue;
					}
					$tables[] = $object->table_name;
				}

				// Meta
				$meta = $component->get_interface( 'meta' );
				if ( $meta instanceof \EDD\Database\Table && $meta->exists() ) {
					if ( 'adjustmentmeta' === $meta->name ) {
						continue;
					}
					$tables[] = $meta->table_name;
				}
			}

			$tables = apply_filters( 'edd_reset_tables_to_truncate', $tables );

			$this->store_data( 'edd_reset_tables_to_truncate', $tables );
		}
	}

	/**
	 * Given a key, get the information from the database directly.
	 *
	 * @since 2.5
	 *
	 * @param string $key Option name.
	 * @return mixed Returns the data from the database.
	 */
	private function get_stored_data( $key ) {
		global $wpdb;

		$value = $wpdb->get_var( $wpdb->prepare( "SELECT option_value FROM {$wpdb->options} WHERE option_name = %s", $key ) );

		if ( empty( $value ) ) {
			return array();
		}

		$maybe_json = json_decode( $value );
		if ( ! is_null( $maybe_json ) ) {
			$value = json_decode( $value, true );
		}

		return (array) $value;
	}

	/**
	 * Store a value in the wp_options table.
	 *
	 * @since 2.5
	 *
	 * @param string $key   Option name.
	 * @param mixed  $value Option value.
	 */
	private function store_data( $key = '', $value = '' ) {
		global $wpdb;

		// Bail if no key was passed.
		if ( empty( $key ) ) {
			return;
		}

		// Parse value.
		$value = is_array( $value )
			? wp_json_encode( $value )
			: esc_attr( $value );

		// Prepare data.
		$data = array(
			'option_name'  => $key,
			'option_value' => $value,
			'autoload'     => 'no',
		);

		$formats = array( '%s', '%s', '%s' );

		// Update database.
		$wpdb->replace( $wpdb->options, $data, $formats );
	}

	/**
	 * Delete an option.
	 *
	 * @since 2.5
	 *
	 * @param string $key Option name.
	 */
	private function delete_data( $key = '' ) {
		global $wpdb;

		// Bail if no key was passed.
		if ( empty( $key ) ) {
			return;
		}

		// Delete from the database.
		$wpdb->delete( $wpdb->options, array( 'option_name' => $key ) );
	}
}

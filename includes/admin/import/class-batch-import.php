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
class EDD_Batch_Import {

	/**
	 * The file being imported
	 *
	 * @since 2.6
	 */
	public $file;

	/**
	 * The parsed CSV file being imported
	 *
	 * @since 2.6
	 */
	public $csv;

	/**
	 * Total rows in the CSV file
	 *
	 * @since 2.6
	 */
	public $total;

	/**
	 * The current step being processed
	 *
	 * @since 2.6
	 */
	public $step;

	/**
	 * The number of items to process per step
	 *
	 * @since 2.6
	 */
	public $per_step = 20;

	/**
	 * The capability required to import data
	 *
	 * @since 2.6
	 */
	public $capability_type = 'manage_shop_settings';

	/**
	 * Is the import file empty
	 *
	 * @since 2.6
	 */
	public $is_empty = false;

	public $field_mapping = array();

	/**
	 * Get things started
	 *
	 * @param $_step int The step to process
	 * @since 2.6
	 */
	public function __construct( $_file = '', $_step = 1 ) {

		if( ! class_exists( 'parseCSV' ) ) {
			require_once EDD_PLUGIN_DIR . 'includes/libraries/parsecsv.lib.php';
		}

		$this->step  = $_step;
		$this->file  = $_file;
		$this->done  = false;
		$this->csv   = new parseCSV();
		$this->csv->auto( $this->file );
		$this->total = count( $this->csv->data );
		$this->init();

	}

	public function init() {}

	/**
	 * Can we import?
	 *
	 * @access public
	 * @since 2.6
	 * @return bool Whether we can iport or not
	 */
	public function can_import() {
		return (bool) apply_filters( 'edd_import_capability', current_user_can( $this->capability_type ) );
	}

	/**
	 * Get the CSV columns
	 *
	 * @access public
	 * @since 2.6
	 * @return array The columns in the CSV
	 */
	public function get_columns() {

		return $this->csv->titles;
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
			wp_die( __( 'You do not have permission to import data.', 'easy-digital-downloads' ), __( 'Error', 'easy-digital-downloads' ), array( 'response' => 403 ) );
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
		return 100;
	}

	public function map_fields( $csv_columns = array(), $import_fields = array() ) {

		foreach( $csv_columns as $key => $column ) {

			if( ! empty( $import_fields[ $key ] ) ) {

				$this->field_mapping[ $import_fields[ $key ] ] = $column;

			}

		}
	}

	public function get_list_table_url() {}
	public function get_import_type_label() {}
}
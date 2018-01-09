<?php
/**
 * Reports API - Report object
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Admin\Reports;

use EDD\Utils;

/**
 * Represents an encapsulated report for the Reports API.
 *
 * @since 3.0
 */
class Report {

	/**
	 * Report ID.
	 *
	 * Will only be set if built using a reports registry entry.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $report_id;

	/**
	 * Endpoint label.
	 *
	 * @since 3.0
	 * @var   string
	 */
	private $label;

	/**
	 * Represents filters available to the report.
	 *
	 * @since 3.0
	 */
	public $filters = array();

	/**
	 * Holds errors related to instantiating the report object.
	 *
	 * @since 3.0
	 * @var   \WP_Error
	 */
	private $errors;

	/**
	 * Constructs the report object.
	 *
	 * @since 3.0
	 *
	 * @param array  $report Optional. Report record from the registry. Default empty array.
	 */
	public function __construct( $report = array() ) {
		$this->errors = new \WP_Error();

		if ( ! empty( $report ) ) {

			if ( ! empty( $report['id'] ) ) {
				$this->report_id = $report['id'];
			} else {
				$this->errors->add( 'missing_report_id', 'The report_id is missing.' );
			}

			if ( ! empty( $report['label'] ) ) {
				$this->label = $report['label'];
			} else {
				$this->errors->add( 'missing_label', 'The report label is missing.' );
			}

			if ( ! empty( $report['filters'] ) ) {
				$this->filters = $report['filters'];
			}
		}
	}

	/**
	 * Determines whether the endpoint has generated errors during instantiation.
	 *
	 * @since 3.0
	 *
	 * @return bool True if errors have been logged, otherwise false.
	 */
	public function has_errors() {
		$errors = $this->errors->get_error_codes();

		return empty( $errors ) ? false : true;
	}

	/**
	 * Retrieves any logged errors for the endpoint.
	 *
	 * @since 3.0
	 */
	public function get_errors() {
		return $this->errors;
	}

	/**
	 * Retrieves the report ID.
	 *
	 * @since 3.0
	 *
	 * @return string Report ID.
	 */
	public function get_id() {
		return $this->report_id;
	}

	/**
	 * Retrieves the global label for the report.
	 *
	 * @since 3.0
	 *
	 * @return string Report label.
	 */
	public function get_label() {
		return $this->label;
	}

}

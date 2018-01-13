<?php
/**
 * Structured Data
 *
 * @package     EDD
 * @subpackage  StructuredData
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Structured_Data Class.
 *
 * @since 3.0
 */
class EDD_Structured_Data {

	/**
	 * Structured data.
	 *
	 * @var array
	 */
	private $data = array();

	/**
	 * Constructor.
	 *
	 * @since 3.0
	 */
	public function __construct() {

	}

	/**
	 * Get raw data. This data is not formatted in any way.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @return array Raw data.
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Set structured data. This is then output in `wp_footer`.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $data JSON-LD structured data.
	 *
	 * @return bool True if data was set, false otherwise.
	 */
	public function set_data( $data = null ) {
		if ( is_null( $data ) || empty( $data ) || ! is_array( $data ) ) {
			return false;
		}

		// Ensure the type exists and matches the format expected.
		if ( ! isset( $data['@type'] ) || ! preg_match( '|^[a-zA-Z]{1,20}$|', $data['@type'] ) ) {
			return false;
		}

		$this->data[] = $data;

		return true;
	}
}
<?php
/**
 * Graphs
 *
 * This class handles building pretty report graphs
 *
 * @package     EDD
 * @subpackage  Admin/Reports
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Graph Class
 *
 * @since 1.9
 */
class EDD_Graph {

	/*

	Should look something like this:

	$data = array(

		// X axis
		array(
			1,
			5,
			10
		),

		// Y axis
		array(
			20,
			56,
			72
		)
	);

	$graph = new EDD_Graph( $data );

	// Include optional methods for setting colors, sizes, etc

	$graph->display();

	*/


	private $data;

	/**
	 * Get things started
	 *
	 * @since 1.9
	 */
	public function __construct() {
		
	}

	public function set( $key, $value ) {
		// For setting optional graph settings
	}

	public function scripts() {

	}

	public function styles() {

	}

	public function display() {

	}
	

}
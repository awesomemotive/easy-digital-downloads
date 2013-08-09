<?php
/**
 * Payments Query
 *
 * @package     EDD
 * @subpackage  Classes/Stats
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
*/


/**
 * EDD_Payments_Query Class
 *
 * This class is for retrieving payments data
 *
 * Payments can be retrieved for date ranges and pre-defined periods
 *
 * @since 1.8
 */
class EDD_Payments_Query extends EDD_Stats_Base {


	/**
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function __construct() { /* nothing here. Call get_sales() and get_earnings() directly */ }


	/**
	 * Retrieve sale stats
	 *
	 * @access public
	 * @since 1.8
	 * @param $download_id INT The download product to retrieve stats for. If false, gets stats for all products
	 * @param $status string The sale status to count. Only valid when retrieving global stats
	 * @return float|int
	 */
	public function get_sales( $download_id = 0, $start_date = false, $end_date = false, $status = 'publish' ) {

		$this->setup_dates( $start_date, $end_date );

		// Make sure start date is valid
		if( is_wp_error( $this->start_date ) )
			return $this->start_date;

		// Make sure end date is valid
		if( is_wp_error( $this->end_date ) )
			return $this->end_date;


		add_filter( 'posts_where', array( $this, 'payments_where' ) );



		remove_filter( 'posts_where', array( $this, 'payments_where' ) );


		return $count;

	}

}
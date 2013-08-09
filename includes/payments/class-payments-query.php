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
	 * The args to pass to the edd_get_payments() query
	 *
	 * @var array
	 * @access public
	 * @since 1.8
	 */
	public $args = array();


	/**
	 *
	 * @access public
	 * @since 1.8
	 * @return void
	 */
	public function __construct( $_args = array() ) {

		$this->args = $_args;

	}


	/**
	 * Retrieve payments
	 *
	 * @access public
	 * @since 1.8
	 * @param $start_date string|int The starting date to get payments for
	 * @param $start_date string|int The ending date to get payments for
	 * @param $status string The sale status to count. Only valid when retrieving global stats
	 * @return float|int
	 */
	public function get_payments( $start_date = false, $end_date = false ) {

		if( $start_date || $end_date ) {
			$this->setup_dates( $start_date, $end_date );
		}

		// Make sure start date is valid
		if( is_wp_error( $this->start_date ) )
			return $this->start_date;

		// Make sure end date is valid
		if( is_wp_error( $this->end_date ) )
			return $this->end_date;

		// Only filter the posts_where if a start or end date is specified
		if( $start_date || $end_date ) {
			add_filter( 'posts_where', array( $this, 'payments_where' ) );
		}

		$payments = array();
		$query    = edd_get_payments( $this->args );

		if( $query ) {
			foreach( $query as $payment ) {
				$details = new stdClass;

				$details->ID           = $payment->ID;
				$details->date         = $payment->post_date;
				$details->post_status  = edd_get_payment_status( $payment );
				$details->total        = edd_get_payment_amount( $payment->ID );
				$details->subtotal     = edd_get_payment_subtotal( $payment->ID );
				$details->tax          = edd_get_payment_tax( $payment->ID );
				$details->fees         = edd_get_payment_fees( $payment->ID );
				$details->key          = edd_get_payment_key( $payment->ID );
				$details->gateway      = edd_get_payment_gateway( $payment->ID );
				$details->user_info    = edd_get_payment_meta_user_info( $payment->ID );
				$details->cart_details = edd_get_payment_meta_cart_details( $payment->ID, true );

				$payments[] = $details;
			}
		}

		// Remove the posts_where filter, if it was added
		if( $start_date || $end_date ) {
			remove_filter( 'posts_where', array( $this, 'payments_where' ) );
		}

		return $payments;

	}

}
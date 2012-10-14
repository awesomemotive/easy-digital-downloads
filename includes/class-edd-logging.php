<?php

/**
 * Class for logging events and errors
 *
 * @package     Easy Digital Downloads
 * @subpackage  Logging
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       x.x.x
*/




/**
 * A general use class for logging events and errors.
 *
 * This class is extended to log each time someone downloads a file,
 * anytime there is an error processing payment, and anything else we may need to log
 *
 * @access      private
 * @since       x.x.x
 * @return      void
*/

class EDD_Logging {


	function __construct() {

		// let's run this puppy!

	}


	/**
	 * Create new log entry
	 *
	 * This is just a simple and fast way to log something. Use $this->insert_log()
	 * if you need to store custom meta data
	 *
	 * @access      private
	 * @since       x.x.x
	 *
	 * @uses 		$this->insert_log()
	 *
	 * @return      int The ID of the new log entry
	*/

	function log( $message = '', $parent = 0, $type = null ) {

		$log_data = array(
			'post_content'	=> $message,
			'post_parent'	=> $parent
		);

		$log_meta = array(
			'type'	=> $type
		);

		return $this->insert_log( $log_data, $log_meta );

	}


	/**
	 * Retrieves log items
	 *
	 * @access      private
	 * @since       x.x.x
	 *
	 * @uses 		$this->get_connected_logs()
	 *
	 * @return      array
	*/

	function get_logs( $args = array() ) {

		return $this->get_connected_logs( $args );

	}


	/**
	 * Stores a log entry
	 *
	 * @access      private
	 * @since       x.x.x
	 *
	 * @uses 		wp_parse_args()
	 * @uses 		wp_insert_post()
	 * @uses 		update_post_meta()
	 *
	 * @return      int The ID of the newly created log item
	*/

	function insert_log( $log_data = array(), $log_meta = array() ) {

		$defaults = array(
			'post_type' 	=> 'edd_log',
			'post_status'	=> 'publish',
			'post_parent'	=> 0,
			'post_content'	=> ''
		);

		$args = wp_parse_args( $log_data, $defaults );

		do_action( 'edd_pre_insert_log' );

		// store the log entry
		$log_id = wp_insert_post( $args );

		if( $log_id && ! empty( $log_meta ) ) {
			foreach( (array) $log_meta as $key => $meta ) {
				if( ! empty( $meta ) )
					update_post_meta( $log_id, '_edd_log_' . sanitize_key( $key ), $meta );
			}
		}

		do_action( 'edd_post_insert_log', $log_id );

		return $log_id;

	}


	/**
	 * Update and existing log item
	 *
	 * @access      private
	 * @since       x.x.x
	 *
	 * @uses 		wp_update_post()
	 *
	 * @return      bool True if successful, false otherwise
	*/
	function update_log( $log_data = array(), $log_meta = array() ) {

		do_action( 'edd_pre_update_log', $log_id );

		$defaults = array(
			'post_type' 	=> 'edd_log',
			'post_status'	=> 'publish',
			'post_parent'	=> 0
		);

		$args = wp_parse_args( $log_data, $defaults );

		// store the log entry
		$log_id = wp_update_post( $args );

		if( $log_id && ! empty( $log_meta ) ) {
			foreach( (array) $log_meta as $key => $meta ) {
				if( ! empty( $meta ) )
					update_post_meta( $log_id, '_edd_log_' . sanitize_key( $key ), $meta );
			}
		}

		do_action( 'edd_post_update_log', $log_id );

	}


	/**
	 * Retrieve all connected logs
	 *
	 * Used for retrieving logs related to particular items, such as a specific purchase.
	 *
	 * @access  private
	 * @since 	x.x.x
	 *
	 * @uses 	wp_parse_args()
	 * @uses 	get_posts()
	 *
	 * @return  array / false
	*/

	function get_connected_logs( $args = array(), $type = null ) {

		$defaults = array(
			'post_parent' 	=> 0,
			'post_type'		=> 'edd_log',
			'posts_per_page'=> 10,
			'post_status'	=> 'publish'
		);

		$query_args = wp_parse_args( $args, $defaults );

		if( ! empty( $type ) ) {

			$query_args['meta_key'] 	= '_edd_log_type';
			$query_args['meta_value'] 	= $type;

		}

		$logs = get_posts( $query_args );

		if( $logs )
			return $logs;

		// no logs found
		return false;

	}

}
<?php

/**
 * Class for logging events and errors
 *
 * @package     Easy Digital Downloads
 * @subpackage  Logging
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;


/**
 * A general use class for logging events and errors.
 *
 * @access      private
 * @since       1.3.1
 * @return      void
*/

class EDD_Logging {


	function __construct() {

		// create the log post type
		add_action( 'init', array( $this, 'register_post_type' ), -1 );

		// create types taxonomy and default types
		add_action( 'init', array( $this, 'register_taxonomy' ), -1 );

	}


	/**
	 * Registers the edd_log Post Type
	 *
	 * @access      private
	 * @since       1.3.1
	 *
	 * @uses 		register_post_type()
	 *
	 * @return     void
	*/

	function register_post_type() {

		/* logs post type */

		$log_args = array(
			'labels'			=> array( 'name' => __( 'Logs', 'edd' ) ),
			'public'			=> false,
			'query_var'			=> false,
			'rewrite'			=> false,
			'capability_type'	=> 'post',
			'supports'			=> array( 'title', 'editor' ),
			'can_export'		=> false
		);
		register_post_type( 'edd_log', $log_args );

	}


	/**
	 * Registers the Type Taxonomy
	 *
	 * The Type taxonomy is used to determine the type of log entry
	 *
	 * @access      private
	 * @since       1.3.1
	 *
	 * @uses 		register_taxonomy()
	 * @uses 		term_exists()
	 * @uses 		wp_insert_term()
	 *
	 * @return     void
	*/

	function register_taxonomy() {

		register_taxonomy( 'edd_log_type', 'edd_log', array( 'public' => false ) );

		$types = $this->log_types();

		foreach ( $types as $type ) {
			if( ! term_exists( $type, 'edd_log_type' ) ) {
				wp_insert_term( $type, 'edd_log_type' );
			}
		}
	}


	/**
	 * Log types
	 *
	 * Sets up the default log types and allows for new ones to be created
	 *
	 * @access      private
	 * @since       1.3.1
	 *
	 *
	 * @return     array
	*/

	function log_types() {
		$terms = array(
			'sale', 'file_download', 'gateway_error'
		);

		return apply_filters( 'edd_log_types', $terms );
	}


	/**
	 * Check if a log type is valid
	 *
	 * Checks to see if the specified type is in the registered list of types
	 *
	 * @access      private
	 * @since       1.3.1
	 *
	 *
	 * @return     array
	*/

	function valid_type( $type ) {
		return in_array( $type, $this->log_types() );
	}


	/**
	 * Create new log entry
	 *
	 * This is just a simple and fast way to log something. Use $this->insert_log()
	 * if you need to store custom meta data
	 *
	 * @access      private
	 * @since       1.3.1
	 *
	 * @uses 		$this->insert_log()
	 *
	 * @return      int The ID of the new log entry
	*/

	function add( $title = '', $message = '', $parent = 0, $type = null ) {

		$log_data = array(
			'post_title' 	=> $title,
			'post_content'	=> $message,
			'post_parent'	=> $parent,
			'log_type'		=> $type
		);

		return $this->insert_log( $log_data );

	}


	/**
	 * Easily retrieves log items for a particular object ID
	 *
	 * @access      private
	 * @since       1.3.1
	 *
	 * @uses 		$this->get_connected_logs()
	 *
	 * @return      array
	*/

	function get_logs( $object_id = 0, $type = null, $paged = null ) {
		return $this->get_connected_logs( array( 'post_parent' => $object_id, 'paged' => $paged, 'log_type' => $type ) );

	}


	/**
	 * Stores a log entry
	 *
	 * @access      private
	 * @since       1.3.1
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
			'post_content'	=> '',
			'log_type'		=> false
		);

		$args = wp_parse_args( $log_data, $defaults );

		do_action( 'edd_pre_insert_log' );

		// store the log entry
		$log_id = wp_insert_post( $args );

		// set the log type, if any
		if( $log_data['log_type'] && $this->valid_type( $log_data['log_type'] ) ) {
			wp_set_object_terms( $log_id, $log_data['log_type'], 'edd_log_type', false );
		}


		// set log meta, if any
		if( $log_id && ! empty( $log_meta ) ) {
			foreach( (array) $log_meta as $key => $meta ) {
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
	 * @since       1.3.1
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
	 * @since 	1.3.1
	 *
	 * @uses 	wp_parse_args()
	 * @uses 	get_posts()
	 *
	 * @return  array / false
	*/

	function get_connected_logs( $args = array() ) {

		$defaults = array(
			'post_parent' 	=> 0,
			'post_type'		=> 'edd_log',
			'posts_per_page'=> 10,
			'post_status'	=> 'publish',
			'paged'			=> get_query_var( 'paged' ),
			'log_type'		=> false
		);

		$query_args = wp_parse_args( $args, $defaults );

		if( $query_args['log_type'] && $this->valid_type( $query_args['log_type'] ) ) {

			$query_args['tax_query'] = array(
				array(
					'taxonomy' 	=> 'edd_log_type',
					'field'		=> 'slug',
					'terms'		=> $query_args['log_type']
				)
			);

		}

		$logs = get_posts( $query_args );

		if( $logs )
			return $logs;

		// no logs found
		return false;

	}


	/**
	 * Retrieves number of log entries connected to particular object ID
	 *
	 * @access  private
	 * @since 	1.3.1
	 *
	 * @uses 	WP_Query()
	 *
	 * @return  int
	*/

	function get_log_count( $object_id = 0, $type = null, $meta_query = null ) {

		$query_args = array(
			'post_parent' 	=> $object_id,
			'post_type'		=> 'edd_log',
			'posts_per_page'=> -1,
			'post_status'	=> 'publish'
		);

		if( ! empty( $type ) && $this->valid_type( $type ) ) {

			$query_args['tax_query'] = array(
				array(
					'taxonomy' 	=> 'edd_log_type',
					'field'		=> 'slug',
					'terms'		=> $type
				)
			);

		}

		if( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$logs = new WP_Query( $query_args );

		return (int) $logs->post_count;

	}


	function delete_logs( $object_id = 0, $type = null, $meta_query = null  ) {

		$query_args = array(
			'post_parent' 	=> $object_id,
			'post_type'		=> 'edd_log',
			'posts_per_page'=> -1,
			'post_status'	=> 'publish',
			'fields'        => 'ids'
		);

		if( ! empty( $type ) && $this->valid_type( $type ) ) {

			$query_args['tax_query'] = array(
				array(
					'taxonomy' 	=> 'edd_log_type',
					'field'		=> 'slug',
					'terms'		=> $type,
				)
			);

		}

		if( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$logs = get_posts( $query_args );

		if( $logs ) {
			foreach( $logs as $log ) {
				wp_delete_post( $log, true );
			}

		}

	}

}

// initiate the logging system
$GLOBALS['edd_logs'] = new EDD_Logging();


/**
 * Record a log entry
 *
 * This is just a simple wrapper function for the log class add() function
 *
 * @access      public
 * @since       1.3.3
 *
 * @uses 		$this->add()
 *
 * @return      int ID of the new log entry
*/

function edd_record_log( $title = '', $message = '', $parent = 0, $type = null ) {
	global $edd_logs;
	$log = $edd_logs->add( $title, $message, $parent, $type );
	return $log;
}
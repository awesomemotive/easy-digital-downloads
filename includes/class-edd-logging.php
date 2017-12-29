<?php
/**
 * Class for logging events and errors
 *
 * @package     EDD
 * @subpackage  Logging
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * EDD_Logging Class
 *
 * A general use class for logging events and errors.
 *
 * @since 1.3.1
 */
class EDD_Logging {

	public $is_writable = true;
	private $filename   = '';
	private $file       = '';

	/**
	 * Set up the EDD Logging Class
	 *
	 * @since 1.3.1
	 */
	public function __construct() {
		// Create the log post type
		add_action( 'init', array( $this, 'register_post_type' ), 1 );

		// Create types taxonomy and default types
		add_action( 'init', array( $this, 'register_taxonomy' ), 1 );

		add_action( 'plugins_loaded', array( $this, 'setup_log_file' ), 0 );

		// Backwards compatibility for API request logs
		add_filter( 'get_post_metadata', array( $this, '_api_request_log_get_meta_backcompat' ), 99, 4 );
		add_filter( 'update_post_metadata', array( $this, '_api_request_log_update_meta_backcompat' ), 99, 5 );
		add_filter( 'add_post_metadata', array( $this, '_api_request_log_update_meta_backcompat' ), 99, 5 );

		// Backwards compatibility for file download logs
		add_filter( 'get_post_metadata', array( $this, '_file_download_log_get_meta_backcompat' ), 99, 4 );
		add_filter( 'update_post_metadata', array( $this, '_file_download_log_update_meta_backcompat' ), 99, 5 );
		add_filter( 'add_post_metadata', array( $this, '_file_download_log_update_meta_backcompat' ), 99, 5 );
	}

	/**
	 * Sets up the log file if it is writable
	 *
	 * @since 2.8.7
	 * @return void
	 */
	public function setup_log_file() {

		$upload_dir       = wp_upload_dir();
		$this->filename   = wp_hash( home_url( '/' ) ) . '-edd-debug.log';
		$this->file       = trailingslashit( $upload_dir['basedir'] ) . $this->filename;

		if ( ! is_writeable( $upload_dir['basedir'] ) ) {
			$this->is_writable = false;
		}

	}

	/**
	 * Registers the edd_log Post Type
	 *
	 * @access public
	 * @since 1.3.1
	 * @return void
	 */
	public function register_post_type() {
		/* Logs post type */
		$log_args = array(
			'labels'              => array( 'name' => __( 'Logs', 'easy-digital-downloads' ) ),
			'public'              => false,
			'exclude_from_search' => true,
			'publicly_queryable'  => false,
			'show_ui'             => false,
			'query_var'           => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
			'supports'            => array( 'title', 'editor' ),
			'can_export'          => true,
		);

		register_post_type( 'edd_log', $log_args );
	}

	/**
	 * Registers the Type Taxonomy
	 *
	 * The "Type" taxonomy is used to determine the type of log entry
	 *
	 * @access public
	 * @since 1.3.1
	 * @return void
	*/
	public function register_taxonomy() {
		register_taxonomy( 'edd_log_type', 'edd_log', array( 'public' => false ) );
	}

	/**
	 * Log types
	 *
	 * Sets up the default log types and allows for new ones to be created
	 *
	 * @access public
	 * @since 1.3.1
	 * @return  array $terms
	 */
	public function log_types() {
		$terms = array(
			'sale', 'file_download', 'gateway_error', 'api_request'
		);

		return apply_filters( 'edd_log_types', $terms );
	}

	/**
	 * Check if a log type is valid
	 *
	 * Checks to see if the specified type is in the registered list of types
	 *
	 * @access public
	 * @since 1.3.1
	 * @uses EDD_Logging::log_types()
	 * @param string $type Log type
	 * @return bool Whether log type is valid
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
	 * @access public
	 * @since 1.3.1
	 * @uses EDD_Logging::insert_log()
	 * @param string $title Log entry title
	 * @param string $message Log entry message
	 * @param int $parent Log entry parent
	 * @param string $type Log type (default: null)
	 * @return int Log ID
	 */
	public function add( $title = '', $message = '', $parent = 0, $type = null ) {
		$log_data = array(
			'post_title'   => $title,
			'post_content' => $message,
			'post_parent'  => $parent,
			'log_type'     => $type,
		);

		return $this->insert_log( $log_data );
	}

	/**
	 * Easily retrieves log items for a particular object ID
	 *
	 * @access public
	 * @since 1.3.1
	 * @uses EDD_Logging::get_connected_logs()
	 * @param int $object_id (default: 0)
	 * @param string $type Log type (default: null)
	 * @param int $paged Page number (default: null)
	 * @return array Array of the connected logs
	*/
	public function get_logs( $object_id = 0, $type = null, $paged = null ) {
		return $this->get_connected_logs( array( 'post_parent' => $object_id, 'paged' => $paged, 'log_type' => $type ) );
	}

	/**
	 * Stores a log entry
	 *
	 * @access public
	 * @since 1.3.1
	 * @uses EDD_Logging::valid_type()
	 * @param array $log_data Log entry data
	 * @param array $log_meta Log entry meta
	 * @return int The ID of the newly created log item
	 */
	function insert_log( $log_data = array(), $log_meta = array() ) {
		$defaults = array(
			'post_type'    => 'edd_log',
			'post_status'  => 'publish',
			'post_parent'  => 0,
			'post_content' => '',
			'log_type'     => false,
		);

		$args = wp_parse_args( $log_data, $defaults );

		do_action( 'edd_pre_insert_log', $log_data, $log_meta );

		if ( 'api_request' === $log_data['log_type'] ) {
			$data = array(
				'user_id' => $log_meta['user'],
				'api_key' => $log_meta['key'],
				'token'   => null === $log_meta['token'] ? 'public' : $log_meta['token'],
				'version' => $log_meta['version'],
				'request' => $log_data['post_excerpt'],
				'error'   => $log_data['post_content'],
				'ip'      => $log_meta['request_ip'],
				'time'    => $log_meta['time'],
			);

			$log_id = EDD()->api_request_logs->insert( $data );

			do_action( 'edd_post_insert_log', $log_id, $log_data, $log_meta );

			return $log_id;
		}

		// Store the log entry
		$log_id = wp_insert_post( $args );

		// Set the log type, if any
		if ( $log_data['log_type'] && $this->valid_type( $log_data['log_type'] ) ) {
			wp_set_object_terms( $log_id, $log_data['log_type'], 'edd_log_type', false );
		}

		// Set log meta, if any
		if ( $log_id && ! empty( $log_meta ) ) {
			foreach ( (array) $log_meta as $key => $meta ) {
				update_post_meta( $log_id, '_edd_log_' . sanitize_key( $key ), $meta );
			}
		}

		do_action( 'edd_post_insert_log', $log_id, $log_data, $log_meta );

		return $log_id;
	}

	/**
	 * Update and existing log item
	 *
	 * @access public
	 * @since 1.3.1
	 * @param array $log_data Log entry data
	 * @param array $log_meta Log entry meta
	 * @return bool True if successful, false otherwise
	 */
	public function update_log( $log_data = array(), $log_meta = array() ) {

		do_action( 'edd_pre_update_log', $log_data, $log_meta );

		$defaults = array(
			'post_type'   => 'edd_log',
			'post_status' => 'publish',
			'post_parent' => 0,
		);

		$args = wp_parse_args( $log_data, $defaults );

		// Store the log entry
		$log_id = wp_update_post( $args );

		if ( $log_id && ! empty( $log_meta ) ) {
			foreach ( (array) $log_meta as $key => $meta ) {
				if ( ! empty( $meta ) )
					update_post_meta( $log_id, '_edd_log_' . sanitize_key( $key ), $meta );
			}
		}

		do_action( 'edd_post_update_log', $log_id, $log_data, $log_meta );
	}

	/**
	 * Retrieve all connected logs
	 *
	 * Used for retrieving logs related to particular items, such as a specific purchase.
	 *
	 * @access private
	 * @since 1.3.1
	 * @param array $args Query arguments
	 * @return mixed array if logs were found, false otherwise
	 */
	public function get_connected_logs( $args = array() ) {
		$defaults = array(
			'post_type'      => 'edd_log',
			'posts_per_page' => 20,
			'post_status'    => 'publish',
			'paged'          => get_query_var( 'paged' ),
			'log_type'       => false,
		);

		$query_args = wp_parse_args( $args, $defaults );

		if ( $query_args['log_type'] && $this->valid_type( $query_args['log_type'] ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy'  => 'edd_log_type',
					'field'     => 'slug',
					'terms'     => $query_args['log_type'],
				)
			);
		}

		if ( 'api_request' === $query_args['log_type'] ) {
			$args = array(
				'number' => $query_args['posts_per_page'],
				'paged'  => $query_args['paged'],
			);

			if ( isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) ) {
				$args['meta_query'] = $query_args['meta_query'];
			}

			$logs = EDD()->api_request_logs->get_logs( $args );

			if ( $logs ) {
				return $logs;
			} else {
				return false;
			}
		}

		if ( 'file_download' === $query_args['log_type'] ) {
			$args = array(
				'number' => $query_args['posts_per_page'],
				'paged'  => $query_args['paged'],
			);

			if ( isset( $query_args['meta_query'] ) && is_array( $query_args['meta_query'] ) ) {
				$args['meta_query'] = $query_args['meta_query'];
			}

			$logs = EDD()->file_download_logs->get_logs( $args );

			if ( $logs ) {
				return $logs;
			} else {
				return false;
			}
		}

		$logs = get_posts( $query_args );

		if ( $logs )
			return $logs;

		// No logs found
		return false;
	}

	/**
	 * Retrieves number of log entries connected to particular object ID
	 *
	 * @access public
	 * @since 1.3.1
	 * @param int $object_id (default: 0)
	 * @param string $type Log type (default: null)
	 * @param array $meta_query Log meta query (default: null)
	 * @param array $date_query Log data query (default: null) (since 1.9)
	 * @return int Log count
	 */
	public function get_log_count( $object_id = 0, $type = null, $meta_query = null, $date_query = null ) {

		$query_args = array(
			'post_parent'      => $object_id,
			'post_type'        => 'edd_log',
			'posts_per_page'   => -1,
			'post_status'      => 'publish',
			'fields'           => 'ids',
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy'  => 'edd_log_type',
					'field'     => 'slug',
					'terms'     => $type,
				)
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		if ( ! empty( $date_query ) ) {
			$query_args['date_query'] = $date_query;
		}

		$logs = new WP_Query( $query_args );

		return (int) $logs->post_count;
	}

	/**
	 * Delete a log
	 *
	 * @access public
	 * @since 1.3.1
	 * @uses EDD_Logging::valid_type
	 * @param int $object_id (default: 0)
	 * @param string $type Log type (default: null)
	 * @param array $meta_query Log meta query (default: null)
	 * @return void
	 */
	public function delete_logs( $object_id = 0, $type = null, $meta_query = null  ) {
		$query_args = array(
			'post_parent'    => $object_id,
			'post_type'      => 'edd_log',
			'posts_per_page' => -1,
			'post_status'    => 'publish',
			'fields'         => 'ids',
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$query_args['tax_query'] = array(
				array(
					'taxonomy'  => 'edd_log_type',
					'field'     => 'slug',
					'terms'     => $type,
				)
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		$logs = get_posts( $query_args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				wp_delete_post( $log, true );
			}
		}
	}

	/**
	 * Retrieve the log data
	 *
	 * @since 2.8.7
	 * @return string
	 */
	public function get_file_contents() {
		return $this->get_file();
	}

	/**
	 * Log message to file
	 *
	 * @since 2.8.7
	 * @return void
	 */
	public function log_to_file( $message = '' ) {
		$message = date( 'Y-n-d H:i:s' ) . ' - ' . $message . "\r\n";
		$this->write_to_log( $message );

	}

	/**
	 * Retrieve the file data is written to
	 *
	 * @since 2.8.7
	 * @return string
	 */
	protected function get_file() {

		$file = '';

		if ( @file_exists( $this->file ) ) {

			if ( ! is_writeable( $this->file ) ) {
				$this->is_writable = false;
			}

			$file = @file_get_contents( $this->file );

		} else {

			@file_put_contents( $this->file, '' );
			@chmod( $this->file, 0664 );

		}

		return $file;
	}

	/**
	 * Write the log message
	 *
	 * @since 2.8.7
	 * @return void
	 */
	protected function write_to_log( $message = '' ) {
		$file = $this->get_file();
		$file .= $message;
		@file_put_contents( $this->file, $file );
	}

	/**
	 * Delete the log file or removes all contents in the log file if we cannot delete it
	 *
	 * @since 2.8.7
	 * @return void
	 */
	public function clear_log_file() {
		@unlink( $this->file );

		if ( file_exists( $this->file ) ) {

			// it's still there, so maybe server doesn't have delete rights
			chmod( $this->file, 0664 ); // Try to give the server delete rights
			@unlink( $this->file );

			// See if it's still there
			if ( @file_exists( $this->file ) ) {

				/*
				 * Remove all contents of the log file if we cannot delete it
				 */
				if ( is_writeable( $this->file ) ) {

					file_put_contents( $this->file, '' );

				} else {

					return false;

				}

			}

		}

		$this->file = '';
		return true;

	}

	/**
	 * Backwards compatibility filters for get_post_meta() calls on API request logs.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param  mixed  $value       The value get_post_meta would return if we don't filter.
	 * @param  int    $object_id   The object ID post meta was requested for.
	 * @param  string $meta_key    The meta key requested.
	 * @param  bool   $single      If the person wants the single value or an array of the value
	 * @return mixed               The value to return
	 */
	public function _api_request_log_get_meta_backcompat( $value, $object_id, $meta_key, $single ) {
		global $wpdb;

		$meta_keys = apply_filters( 'edd_post_meta_api_request_log_backwards_compat_keys', array(
			'_edd_log_request_ip',
			'_edd_log_user',
			'_edd_log_key',
			'_edd_log_token',
			'_edd_log_time',
			'_edd_log_version',
		) );

		if ( ! in_array( $meta_key, $meta_keys ) ) {
			return $value;
		}

		$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
		$show_notice     = apply_filters( 'edd_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) && ! defined( 'EDD_DOING_TESTS' ) );

		$api_request_log = new EDD_API_Request_Log( $object_id );

		if ( ! $api_request_log || ! $api_request_log->id > 0 ) {
			// We didn't find a API request log record with this ID... so let's check and see if it was a migrated one
			$object_id = $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_edd_log_migrated_id'" );

			if ( ! empty( $object_id ) ) {
				$api_request_log = new EDD_API_Request_Log( $object_id );
			} else {
				return $value;
			}
		}

		switch ( $meta_key ) {
			case '_edd_log_request_ip':
			case '_edd_log_user':
			case '_edd_log_key':
			case '_edd_log_token':
			case '_edd_log_time':
			case '_edd_log_version':
				$key   = str_replace( '_edd_log_', '', $meta_key );

				if ( 'request_ip' === $key ) {
					$key = 'ip';
				}

				if ( 'key' === $key ) {
					$key = 'api_key';
				}

				if ( 'user' === $key ) {
					$key = 'user_id';
				}

				$value = $api_request_log->$key;

				if ( $show_notice ) {
					// Throw deprecated notice if WP_DEBUG is defined and on
					trigger_error( __( 'The EDD API request log postmeta is <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use the EDD_API_Request_Log object to get the relevant data, instead.', 'easy-digital-downloadsd' ) );
					$backtrace = debug_backtrace();
					trigger_error( print_r( $backtrace, 1 ) );
				}
				break;

			default:
				/*
				 * Developers can hook in here with add_filter( 'edd_get_post_meta_api_request_log_backwards_compat-meta_key... in order to
				 * Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_API_Request_Log::get_meta
				 */
				$value = apply_filters( 'edd_get_post_meta_api_request_log_backwards_compat-' . $meta_key, $value, $object_id );
				break;
		}

		return $value;
	}

	/**
	 * Listen for calls to update_post_meta and see if we need to filter them.
	 *
	 * @since 3.0
	 *
	 * @param mixed   $check     Comes in 'null' but if returned not null, WordPress Core will not interact with the postmeta table
	 * @param int    $object_id  The object ID post meta was requested for.
	 * @param string $meta_key   The meta key requested.
	 * @param mixed  $meta_value The value get_post_meta would return if we don't filter.
	 * @param mixed  $prev_value The previous value of the meta
	 *
	 * @return mixed Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid postmeta
	 */
	function _api_request_log_update_meta_backcompat( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		global $wpdb;

		$meta_keys = apply_filters( 'edd_post_meta_api_request_log_backwards_compat_keys', array(
			'_edd_log_request_ip',
			'_edd_log_user',
			'_edd_log_key',
			'_edd_log_token',
			'_edd_log_time',
			'_edd_log_version',
		) );

		if ( ! in_array( $meta_key, $meta_keys ) ) {
			return $check;
		}

		$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
		$show_notice     = apply_filters( 'edd_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) && ! defined( 'EDD_DOING_TESTS' ) );

		$api_request_log = new EDD_File_Download_Log( $object_id );

		if ( ! $api_request_log || ! $api_request_log->id > 0 ) {
			// We didn't find an API request log record with this ID... so let's check and see if it was a migrated one
			$object_id = $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_edd_log_migrated_id'" );

			if ( ! empty( $object_id ) ) {
				$api_request_log = new EDD_File_Download_Log( $object_id );
			} else {
				return $check;
			}
		}

		switch ( $meta_key ) {
			case '_edd_log_request_ip':
			case '_edd_log_user':
			case '_edd_log_key':
			case '_edd_log_token':
			case '_edd_log_time':
			case '_edd_log_version':
				$key   = str_replace( '_edd_log_', '', $meta_key );

				if ( 'request_ip' === $key ) {
					$key = 'ip';
				}

				if ( 'key' === $key ) {
					$key = 'api_key';
				}

				if ( 'user' === $key ) {
					$key = 'user_id';
				}

				$api_request_log->{$key} = $meta_value;

				$api_request_log->update( array(
					$key => $meta_value,
				) );

				// Since the old API request logs data was simply stored in a single post meta entry, just don't let it be added.
				if ( $show_notice ) {
					// Throw deprecated notice if WP_DEBUG is defined and on
					trigger_error( __( 'API request log data is no longer stored in post meta. Please use the new custom database tables to insert a API request log record.', 'easy-digital-downloads' ) );
					$backtrace = debug_backtrace();
					trigger_error( print_r( $backtrace, 1 ) );
				}
				break;

			default:
				/*
				 * Developers can hook in here with add_filter( 'edd_get_post_meta_discount_backwards_compat-meta_key... in order to
				 * Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Discount::get_meta
				 */
				$check = apply_filters( 'edd_update_post_meta_api_request_log_backwards_compat-' . $meta_key, $check, $object_id, $meta_value, $prev_value );
				break;
		}

		return $check;
	}

	/**
	 * Backwards compatibility filters for get_post_meta() calls on file download logs.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param  mixed  $value       The value get_post_meta would return if we don't filter.
	 * @param  int    $object_id   The object ID post meta was requested for.
	 * @param  string $meta_key    The meta key requested.
	 * @param  bool   $single      If the person wants the single value or an array of the value
	 * @return mixed               The value to return
	 */
	public function _file_download_log_get_meta_backcompat( $value, $object_id, $meta_key, $single ) {
		global $wpdb;

		$meta_keys = apply_filters( 'edd_post_meta_file_download_log_backwards_compat_keys', array(
			'_edd_log_user_info',
			'_edd_log_user_id',
			'_edd_log_file_id',
			'_edd_key_ip',
			'_edd_log_payment_id',
			'_edd_log_price_id',
		) );

		if ( ! in_array( $meta_key, $meta_keys ) ) {
			return $value;
		}

		$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
		$show_notice     = apply_filters( 'edd_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) && ! defined( 'EDD_DOING_TESTS' ) );

		$file_download_log = new EDD_File_Download_Log( $object_id );

		if ( ! $file_download_log || ! $file_download_log->id > 0 ) {
			// We didn't find a API request log record with this ID... so let's check and see if it was a migrated one
			$object_id = $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_edd_log_migrated_id'" );

			if ( ! empty( $object_id ) ) {
				$file_download_log = new EDD_File_Download_Log( $object_id );
			} else {
				return $value;
			}
		}

		switch ( $meta_key ) {
			case '_edd_log_user_id':
			case '_edd_log_file_id':
			case '_edd_key_ip':
			case '_edd_log_payment_id':
			case '_edd_log_price_id':
				$key   = str_replace( '_edd_log_', '', $meta_key );

				$value = $file_download_log->$key;

				if ( $show_notice ) {
					// Throw deprecated notice if WP_DEBUG is defined and on
					trigger_error( __( 'The EDD file download log postmeta is <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use the EDD_API_Request_Log object to get the relevant data, instead.', 'easy-digital-downloadsd' ) );
					$backtrace = debug_backtrace();
					trigger_error( print_r( $backtrace, 1 ) );
				}
				break;
			case '_edd_log_user_info':
				$user = get_userdata( $file_download_log->user_id );

				$value = array(
					'id'    => $user->ID,
					'email' => $user->user_email,
					'name'  => $user->display_name,
				);

				break;
			default:
				/*
				 * Developers can hook in here with add_filter( 'edd_get_post_meta_file_download_log_backwards_compat-meta_key... in order to
				 * Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_API_Request_Log::get_meta
				 */
				$value = apply_filters( 'edd_get_post_meta_file_download_log_backwards_compat-' . $meta_key, $value, $object_id );
				break;
		}

		return $value;
	}

	/**
	 * Listen for calls to update_post_meta and see if we need to filter them.
	 *
	 * @since 3.0
	 *
	 * @param mixed   $check     Comes in 'null' but if returned not null, WordPress Core will not interact with the postmeta table
	 * @param int    $object_id  The object ID post meta was requested for.
	 * @param string $meta_key   The meta key requested.
	 * @param mixed  $meta_value The value get_post_meta would return if we don't filter.
	 * @param mixed  $prev_value The previous value of the meta
	 *
	 * @return mixed Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid postmeta
	 */
	function _file_download_log_update_meta_backcompat( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		global $wpdb;

		$meta_keys = apply_filters( 'edd_post_meta_file_download_log_backwards_compat_keys', array(
			'_edd_log_user_info',
			'_edd_log_user_id',
			'_edd_log_file_id',
			'_edd_key_ip',
			'_edd_log_payment_id',
			'_edd_log_price_id',
		) );

		if ( ! in_array( $meta_key, $meta_keys ) ) {
			return $check;
		}

		$edd_is_checkout = function_exists( 'edd_is_checkout' ) ? edd_is_checkout() : false;
		$show_notice     = apply_filters( 'edd_show_deprecated_notices', ( defined( 'WP_DEBUG' ) && WP_DEBUG && ! $edd_is_checkout ) && ! defined( 'EDD_DOING_TESTS' ) );

		$file_download_log = new EDD_File_Download_Log( $object_id );

		if ( ! $file_download_log || ! $file_download_log->id > 0 ) {
			// We didn't find an API request log record with this ID... so let's check and see if it was a migrated one
			$object_id = $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_edd_log_migrated_id'" );

			if ( ! empty( $object_id ) ) {
				$file_download_log = new EDD_File_Download_Log( $object_id );
			} else {
				return $check;
			}
		}

		switch ( $meta_key ) {
			case '_edd_log_user_id':
			case '_edd_log_file_id':
			case '_edd_key_ip':
			case '_edd_log_payment_id':
			case '_edd_log_price_id':
				$key = str_replace( '_edd_log_', '', $meta_key );

				$file_download_log->{$key} = $meta_value;

				$file_download_log->update( array( $key => $meta_value, ) );

				// Since the old API request logs data was simply stored in a single post meta entry, just don't let it be added.
				if ( $show_notice ) {
					// Throw deprecated notice if WP_DEBUG is defined and on
					trigger_error( __( 'File download log data is no longer stored in post meta. Please use the new custom database tables to insert a API request log record.', 'easy-digital-downloads' ) );
					$backtrace = debug_backtrace();
					trigger_error( print_r( $backtrace, 1 ) );
				}
				break;
			case '_edd_log_user_info':
				break;
			default:
				/*
				 * Developers can hook in here with add_filter( 'edd_get_post_meta_discount_backwards_compat-meta_key... in order to
				 * Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD_Discount::get_meta
				 */
				$check = apply_filters( 'edd_update_post_meta_file_download_log_backwards_compat-' . $meta_key, $check, $object_id, $meta_value, $prev_value );
				break;
		}

		return $check;
	}
}

// Initiate the logging system
$GLOBALS['edd_logs'] = new EDD_Logging();

/**
 * Record a log entry
 *
 * This is just a simple wrapper function for the log class add() function
 *
 * @since 1.3.3
 *
 * @param string $title
 * @param string $message
 * @param int    $parent
 * @param null   $type
 *
 * @global $edd_logs EDD Logs Object
 *
 * @uses EDD_Logging::add()
 *
 * @return mixed ID of the new log entry
 */
function edd_record_log( $title = '', $message = '', $parent = 0, $type = null ) {
	global $edd_logs;
	$log = $edd_logs->add( $title, $message, $parent, $type );
	return $log;
}


/**
 * Logs a message to the debug log file
 *
 * @since 2.8.7
 *
 * @param string $message
 * @global $edd_logs EDD Logs Object
 * @return void
 */
function edd_debug_log( $message = '' ) {
	global $edd_logs;

	if( edd_is_debug_mode() ) {

		$edd_logs->log_to_file( $message );

	}
}

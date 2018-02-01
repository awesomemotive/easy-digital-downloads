<?php
/**
 * Class for logging events and errors
 *
 * @package     EDD
 * @subpackage  Logging
 * @copyright   Copyright (c) 2018, Pippin Williamson
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
 * @since 3.0 - Updated to work with the new tables and classes as part of the migration to custom tables.
 */
class EDD_Logging {

	/**
	 * Whether the debug log file is writable or not.
	 *
	 * @var bool
	 */
	public $is_writable = true;

	/**
	 * Filename of the debug log.
	 *
	 * @var string
	 */
	private $filename = '';

	/**
	 * File path to the debug log.
	 *
	 * @var string
	 */
	private $file = '';

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
		$upload_dir     = wp_upload_dir();
		$this->filename = wp_hash( home_url( '/' ) ) . '-edd-debug.log';
		$this->file     = trailingslashit( $upload_dir['basedir'] ) . $this->filename;

		if ( ! is_writeable( $upload_dir['basedir'] ) ) {
			$this->is_writable = false;
		}
	}

	/**
	 * Registers the edd_log Post Type
	 *
	 * @since 1.3.1
	 * @since 3.0.0 Deprecated due to migration to custom tables.
	 */
	public function register_post_type() {
		_edd_deprecated_function( __FUNCTION__, '3.0.0' );
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
		_edd_deprecated_function( __FUNCTION__, '3.0.0' );
	}

	/**
	 * Get log types.
	 *
	 * @access public
	 * @since 1.3.1
	 *
	 * @return array $terms Log types.
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
	 *
	 * @param string $type Log type.
	 * @return bool True if valid log type, false otherwise.
	 */
	public function valid_type( $type ) {
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
	 *
	 * @param string $title   Log entry title.
	 * @param string $message Log entry message.
	 * @param int    $parent  Download ID.
	 * @param string $type    Log type (default: null).
	 *
	 * @return int ID of the newly created log item.
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
	 * Easily retrieves log items for a particular object ID.
	 *
	 * @access public
	 * @since 1.3.1
	 *
	 * @param int    $object_id Object ID (default: 0).
	 * @param string $type      Log type (default: null).
	 * @param int    $paged     Page number (default: null).
	 *
	 * @return array Array of the connected logs.
	*/
	public function get_logs( $object_id = 0, $type = null, $paged = null ) {
		return $this->get_connected_logs( array( 'post_parent' => $object_id, 'paged' => $paged, 'log_type' => $type ) );
	}

	/**
	 * Stores a log entry.
	 *
	 * @access public
	 * @since 1.3.1
	 *
	 * @param array $log_data Log entry data.
	 * @param array $log_meta Log entry meta.
	 *
	 * @return int The ID of the newly created log item.
	 */
	public function insert_log( $log_data = array(), $log_meta = array() ) {
		$defaults = array(
			'post_type'    => 'edd_log',
			'post_status'  => 'publish',
			'post_parent'  => 0,
			'post_content' => '',
			'log_type'     => false,
		);

		$args = wp_parse_args( $log_data, $defaults );

		do_action( 'edd_pre_insert_log', $args, $log_meta );

		// Set up variables to hold data to go into the logs table by default
		$db_object = 'logs';
		$data = array (
			'message'     => $args['post_content'],
			'object_id'   => $args['post_parent'],
			'object_type' => isset( $args['object_type'] ) ? $args['object_type'] : 'download',
		);

		if ( $type = $args['log_type'] ) {
			$data['type'] = $type;
		}

		if ( array_key_exists( 'post_title', $args ) ) {
			$data['title'] = $args['post_title'];
		}

		// Override $data and $db_object based on the log type.
		if ( 'api_request' === $args['log_type'] ) {
			$db_object = 'api_request_logs';

			$data = array(
				'user_id' => $log_meta['user'],
				'api_key' => $log_meta['key'],
				'token'   => null === $log_meta['token'] ? 'public' : $log_meta['token'],
				'version' => $log_meta['version'],
				'request' => $args['post_excerpt'],
				'error'   => $args['post_content'],
				'ip'      => $log_meta['request_ip'],
				'time'    => $log_meta['time'],
			);
		} else if ( 'file_download' === $args['log_type'] ) {
			$db_object = 'file_download_logs';

			$data = array(
				'download_id' => $args['post_parent'],
				'file_id'     => $log_meta['file_id'],
				'payment_id'  => $log_meta['payment_id'],
				'price_id'    => $log_meta['price_id'],
				'user_id'     => $log_meta['user_id'],
				'email'       => $log_meta['user_info']['email'],
				'ip'          => $log_meta['ip'],
			);
		}

		$log_id = EDD()->{$db_object}->insert( $data );

		// Set log meta, if any
		if ( $log_id && 'logs' === $db_object && ! empty( $log_meta ) ) {
			foreach ( (array) $log_meta as $key => $meta ) {
				$log = new EDD\Logs\Log( $log_id );
				$log->add_meta( sanitize_key( $key ), $meta );
			}
		}

		do_action( 'edd_post_insert_log', $log_id, $args, $log_meta );

		return $log_id;
	}

	/**
	 * Update and existing log item
	 *
	 * @access public
	 * @since 1.3.1
	 * @since 3.0 - Added $log_id parameter and boolean return type.
	 *
	 * @param array $log_data Log entry data.
	 * @param array $log_meta Log entry meta.
	 * @param int   $log_id   Log ID.
	 *
	 * @return bool True on success, false otherwise.
	 */
	public function update_log( $log_data = array(), $log_meta = array(), $log_id = 0 ) {
		// $log_id is at the end because it was introduced in 3.0
		do_action( 'edd_pre_update_log', $log_data, $log_meta, $log_id );

		$defaults = array(
			'post_content' => '',
			'post_title'   => '',
			'object_id'    => 0,
			'object_type'  => '',
		);

		$args = wp_parse_args( $log_data, $defaults );

		if ( isset( $log_data['ID'] ) && empty( $log_id ) ) {
			$log_id = $log_data['ID'];
		}

		// Bail if the log ID is still empty.
		if ( empty ( $log_id ) ) {
			return false;
		}

		// Set up variables to hold data to go into the logs table by default
		$db_object = 'logs';
		$data = array (
			'type'        => $args['type'],
			'object_id'   => $args['object_id'],
			'object_type' => $args['object_type'],
			'title'       => $args['title'],
			'message'     => $args['message'],
		);

		$result = EDD()->{$db_object}->update( $log_id, $data );

		// Set log meta, if any
		if ( $log_id && 'logs' === $db_object && ! empty( $log_meta ) ) {
			foreach ( (array) $log_meta as $key => $meta ) {
				$log = new EDD\Logs\Log( $log_id );
				$log->update_meta( sanitize_key( $key ), $meta );
			}
		}

		do_action( 'edd_post_update_log', $log_id, $log_data, $log_meta );
	}

	/**
	 * Retrieve all connected logs.
	 *
	 * Used for retrieving logs related to particular items, such as a specific purchase.
	 *
	 * @access public
	 * @since 1.3.1
	 *
	 * @param array $args Query arguments.
	 * @return mixed array Logs fetched, false otherwise.
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

		// Used to dynamically dispatch the call to the correct class.
		$db_object = 'logs';

		if ( 'api_request' === $query_args['log_type'] ) {
			$db_object = 'api_request_logs';
		} else if ( 'file_download' === $query_args['log_type'] ) {
			$db_object = 'file_download_logs';
		}

		$query_args['number'] = $query_args['posts_per_page'];

		$logs = EDD()->{$db_object}->get_logs( $query_args );

		if ( $logs ) {
			return $logs;
		} else {
			return false;
		}
	}

	/**
	 * Retrieves number of log entries connected to particular object ID.
	 *
	 * @access public
	 * @since 1.3.1
	 * @since 1.9 - Added date query support.
	 *
	 * @param int    $object_id  Object ID (default: 0).
	 * @param string $type       Log type (default: null).
	 * @param array  $meta_query Log meta query (default: null).
	 * @param array  $date_query Log date query (default: null) [since 1.9].
	 *
	 * @return int Log count.
	 */
	public function get_log_count( $object_id = 0, $type = null, $meta_query = null, $date_query = null ) {
		$query_args = array(
			'object_id' => $object_id,
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$query_args['type'] = $type;
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		if ( ! empty( $date_query ) ) {
			$query_args['date_query'] = $date_query;
		}

		// Used to dynamically dispatch the call to the correct class.
		$db_object = 'logs';

		if ( 'api_request' === $type ) {
			$db_object = 'api_request_logs';
		} else if ( 'file_download' === $type ) {
			$db_object = 'file_download_logs';
		}

		$count = EDD()->{$db_object}->count( $query_args );

		return $count;
	}

	/**
	 * Delete logs based on parameters passed.
	 *
	 * @access public
	 * @since 1.3.1
	 *
	 * @param int    $object_id  Object ID (default: 0).
	 * @param string $type       Log type (default: null).
	 * @param array  $meta_query Log meta query (default: null).
	 */
	public function delete_logs( $object_id = 0, $type = null, $meta_query = null  ) {
		$query_args = array(
			'post_parent' => $object_id,
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$query_args['type'] = $type;
		}

		if ( ! empty( $meta_query ) ) {
			$query_args['meta_query'] = $meta_query;
		}

		// Used to dynamically dispatch the call to the correct class.
		$db_object = 'logs';

		if ( 'api_request' === $type ) {
			$db_object = 'api_request_logs';
		} else if ( 'file_download' === $type ) {
			$db_object = 'file_download_logs';
		}

		$logs = EDD()->{$db_object}->get_logs( $query_args );

		if ( $logs ) {
			foreach ( $logs as $log ) {
				EDD()->{$db_object}->delete( $log->ID );
			}
		}
	}

	/**
	 * Retrieve the log data.
	 *
	 * @access public
	 * @since 2.8.7
	 *
	 * @return string Log data.
	 */
	public function get_file_contents() {
		return $this->get_file();
	}

	/**
	 * Log message to file.
	 *
	 * @access public
	 * @since 2.8.7
	 *
	 * @param string $message Message to insert in the log.
	 */
	public function log_to_file( $message = '' ) {
		$message = date( 'Y-n-d H:i:s' ) . ' - ' . $message . "\r\n";
		$this->write_to_log( $message );
	}

	/**
	 * Retrieve the file data is written to
	 *
	 * @access protected
	 * @since 2.8.7
	 *
	 * @return string File data.
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
	 * Write the log message.
	 *
	 * @access protected
	 * @since 2.8.7
	 */
	protected function write_to_log( $message = '' ) {
		$file = $this->get_file();
		$file .= $message;
		@file_put_contents( $this->file, $file );
	}

	/**
	 * Delete the log file or removes all contents in the log file if we cannot delete it.
	 *
	 * @access public
	 * @since 2.8.7
	 *
	 * @return bool True if the log was cleared, false otherwise.
	 */
	public function clear_log_file() {
		@unlink( $this->file );

		if ( file_exists( $this->file ) ) {

			// It's still there, so maybe server doesn't have delete rights
			chmod( $this->file, 0664 ); // Try to give the server delete rights
			@unlink( $this->file );

			// See if it's still there...
			if ( @file_exists( $this->file ) ) {

				// Remove all contents of the log file if we cannot delete it
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
	 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
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

		$api_request_log = new EDD\Logs\API_Request_Log( $object_id );

		if ( ! $api_request_log || ! $api_request_log->id > 0 ) {
			// We didn't find a API request log record with this ID... so let's check and see if it was a migrated one
			$object_id = $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_edd_log_migrated_id'" );

			if ( ! empty( $object_id ) ) {
				$api_request_log = new EDD\Logs\API_Request_Log( $object_id );
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
					trigger_error( __( 'The EDD API request log postmeta is <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use the EDD\Logs\API_Request_Log object to get the relevant data, instead.', 'easy-digital-downloads' ) );
					$backtrace = debug_backtrace();
					trigger_error( print_r( $backtrace, 1 ) );
				}
				break;

			default:
				/*
				 * Developers can hook in here with add_filter( 'edd_get_post_meta_api_request_log_backwards_compat-meta_key... in order to
				 * Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD\Logs\API_Request_Log::get_meta
				 */
				$value = apply_filters( 'edd_get_post_meta_api_request_log_backwards_compat-' . $meta_key, $value, $object_id );
				break;
		}

		return $value;
	}

	/**
	 * Listen for calls to update_post_meta for API request logs and see if we need to filter them.
	 *
	 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
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
	public function _api_request_log_update_meta_backcompat( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
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

		$api_request_log = new EDD\Logs\API_Request_Log( $object_id );

		if ( ! $api_request_log || ! $api_request_log->id > 0 ) {
			// We didn't find an API request log record with this ID... so let's check and see if it was a migrated one
			$object_id = $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_edd_log_migrated_id'" );

			if ( ! empty( $object_id ) ) {
				$api_request_log = new EDD\Logs\API_Request_Log( $object_id );
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
	 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
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

		$meta_keys = apply_filters( 'edd_post_meta_log_backwards_compat_keys', array(
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

		$file_download_log = new EDD\Logs\File_Download_Log( $object_id );

		if ( ! $file_download_log || ! $file_download_log->id > 0 ) {
			// We didn't find a API request log record with this ID... so let's check and see if it was a migrated one
			$object_id = $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_edd_log_migrated_id'" );

			if ( ! empty( $object_id ) ) {
				$file_download_log = new EDD\Logs\File_Download_Log( $object_id );
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
				$key = str_replace( '_edd_log_', '', $meta_key );

				$value = $file_download_log->$key;

				if ( $show_notice ) {
					// Throw deprecated notice if WP_DEBUG is defined and on
					trigger_error( __( 'The EDD file download log postmeta is <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use the EDD\Logs\File_Download_Log object to get the relevant data, instead.', 'easy-digital-downloadsd' ) );
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
				 * Filter their own meta values for backwards compatibility calls to get_post_meta instead of EDD\Logs\File_Download_Log::get_meta
				 */
				$value = apply_filters( 'edd_get_post_meta_file_download_log_backwards_compat-' . $meta_key, $value, $object_id );
				break;
		}

		return $value;
	}

	/**
	 * Listen for calls to update_post_meta for file download logs and see if we need to filter them.
	 *
	 * This is here for backwards compatibility purposes with the migration to custom tables in EDD 3.0.
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
	public function _file_download_log_update_meta_backcompat( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		global $wpdb;

		$meta_keys = apply_filters( 'edd_post_meta_log_backwards_compat_keys', array(
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

		$file_download_log = new EDD\Logs\File_Download_Log( $object_id );

		if ( ! $file_download_log || ! $file_download_log->id > 0 ) {
			// We didn't find an API request log record with this ID... so let's check and see if it was a migrated one
			$object_id = $wpdb->get_var( "SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_edd_log_migrated_id'" );

			if ( ! empty( $object_id ) ) {
				$file_download_log = new EDD\Logs\File_Download_Log( $object_id );
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
 * Helper method to insert a new log into the database.
 *
 * @since 1.3.3
 *
 * @see EDD_Logging::add()
 *
 * @param string $title   Log title.
 * @param string $message Log message.
 * @param int    $parent  Download ID.
 * @param null   $type    Log type.
 *
 * @return int ID of the new log.
 */
function edd_record_log( $title = '', $message = '', $parent = 0, $type = null ) {
	/** @var EDD_Logging $edd_logs */
	global $edd_logs;

	$log = $edd_logs->add( $title, $message, $parent, $type );

	return $log;
}


/**
 * Logs a message to the debug log file.
 *
 * @since 2.8.7
 *
 * @param string $message Log message.
 */
function edd_debug_log( $message = '' ) {
	/** @var EDD_Logging $edd_logs */
	global $edd_logs;

	if ( edd_is_debug_mode() ) {
		$edd_logs->log_to_file( $message );
	}
}

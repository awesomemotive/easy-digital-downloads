<?php
/**
 * Class for logging events and errors
 *
 * @package     EDD
 * @subpackage  Logging
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.3.1
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
		add_action( 'plugins_loaded', array( $this, 'setup_log_file' ), 8 );
	}

	/**
	 * Get log types.
	 *
	 * @since 1.3.1
	 *
	 * @return array $terms Log types.
	 */
	public function log_types() {
		return apply_filters( 'edd_log_types', array(
			'sale',
			'file_download',
			'gateway_error',
			'api_request'
		) );
	}

	/**
	 * Check if a log type is valid
	 *
	 * Checks to see if the specified type is in the registered list of types
	 *
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
		return $this->insert_log( array(
			'post_title'   => $title,
			'post_content' => $message,
			'post_parent'  => $parent,
			'log_type'     => $type
		) );
	}

	/**
	 * Easily retrieves log items for a particular object ID.
	 *
	 * @since 1.3.1
	 *
	 * @param int    $object_id Object ID (default: 0).
	 * @param string $type      Log type (default: null).
	 * @param int    $paged     Page number (default: null).
	 *
	 * @return array Array of the connected logs.
	*/
	public function get_logs( $object_id = 0, $type = null, $paged = null ) {
		return $this->get_connected_logs( array(
			'post_parent' => $object_id,
			'paged'       => $paged,
			'log_type'    => $type
		) );
	}

	/**
	 * Stores a log entry.
	 *
	 * @since 1.3.1
	 * @since 3.0 Updated to use the new database classes as part of the migration to custom tables.
	 *
	 * @param array $log_data Log entry data.
	 * @param array $log_meta Log entry meta.
	 * @return int The ID of the newly created log item.
	 */
	public function insert_log( $log_data = array(), $log_meta = array() ) {

		// Parse args
		$args = wp_parse_args( $log_data, array(
			'post_type'    => 'edd_log',
			'post_status'  => 'publish',
			'post_parent'  => 0,
			'post_content' => '',
			'log_type'     => false,
		) );

		/**
		 * Triggers just before a log is inserted.
		 *
		 * @param array $args     Log entry data.
		 * @param array $log_meta Log meta data.
		 */
		do_action( 'edd_pre_insert_log', $args, $log_meta );

		// Used to dynamically dispatch the method call to insert() to the correct class.
		$insert_method = 'edd_add_log';

		// Set up variables to hold data to go into the logs table by default.
		$data = array(
			'content'     => $args['post_content'],
			'object_id'   => isset( $args['post_parent'] )
				? $args['post_parent']
				: 0,
			'object_type' => isset( $args['log_type'] )
				? $args['log_type']
				: null,
			/*
			 * Fallback user ID is the current user, due to it previously being set to that by WordPress
			 * core when setting post_author on the CPT.
			 */
			'user_id'     => ! empty( $log_meta['user'] ) ? $log_meta['user'] : get_current_user_id()
		);

		$type = $args['log_type'];
		if ( ! empty( $type ) ) {
			$data['type'] = $type;
		}

		if ( array_key_exists( 'post_title', $args ) ) {
			$data['title'] = $args['post_title'];
		}

		$meta_to_unset = array( 'user' );

		// Override $data and $insert_method based on the log type.
		if ( 'api_request' === $args['log_type'] ) {
			$insert_method = 'edd_add_api_request_log';

			$data = array(
				'user_id' => ! empty( $log_meta['user'] ) ? $log_meta['user'] : 0,
				'api_key' => ! empty( $log_meta['key'] ) ? $log_meta['key'] : 'public',
				'token'   => ! empty( $log_meta['token'] ) ? $log_meta['token'] : 'public',
				'version' => ! empty( $log_meta['version'] ) ? $log_meta['version'] : '',
				'request' => ! empty( $args['post_excerpt'] ) ? $args['post_excerpt'] : '',
				'error'   => ! empty( $args['post_content'] ) ? $args['post_content'] : '',
				'ip'      => ! empty( $log_meta['request_ip'] ) ? $log_meta['request_ip'] : '',
				'time'    => ! empty( $log_meta['time'] ) ? $log_meta['time'] : '',
			);

			$meta_to_unset = array( 'user', 'key', 'token', 'version', 'request_ip', 'time' );
		} elseif ( 'file_download' === $args['log_type'] ) {
			$insert_method = 'edd_add_file_download_log';

			if ( ! class_exists( 'Browser' ) ) {
				require_once EDD_PLUGIN_DIR . 'includes/libraries/browser.php';
			}

			$browser = new Browser();

			$user_agent = $browser->getBrowser() . ' ' . $browser->getVersion() . '/' . $browser->getPlatform();

			$data = array(
				'product_id'  => $args['post_parent'],
				'file_id'     => ! empty( $log_meta['file_id'] ) ? $log_meta['file_id'] : 0,
				'order_id'    => ! empty( $log_meta['payment_id'] ) ? $log_meta['payment_id'] : 0,
				'price_id'    => ! empty( $log_meta['price_id'] ) ? $log_meta['price_id'] : 0,
				'customer_id' => ! empty( $log_meta['customer_id'] ) ? $log_meta['customer_id'] : 0,
				'ip'          => ! empty( $log_meta['ip'] ) ? $log_meta['ip'] : '',
				'user_agent'  => $user_agent,
			);

			$meta_to_unset = array( 'file_id', 'payment_id', 'price_id', 'customer_id', 'ip', 'user_id' );
		}

		// Now unset the meta we've used up in the main data array.
		foreach ( $meta_to_unset as $meta_key ) {
			unset( $log_meta[ $meta_key ] );
		}

		// Get the log ID if method is callable
		$log_id = is_callable( $insert_method )
			? call_user_func( $insert_method, $data )
			: false;

		// Set log meta, if any
		if ( $log_id && ! empty( $log_meta ) ) {

			// Use the right log fetching function based on the type of log this is.
			if ( 'edd_add_api_request_log' === $insert_method ) {
				$add_meta_function = 'edd_add_api_request_log_meta';
			} elseif ( 'edd_add_file_download_log' === $insert_method ) {
				$add_meta_function = 'edd_add_file_download_log_meta';
			} else {
				$add_meta_function = 'edd_add_log_meta';
			}

			if ( is_callable( $add_meta_function ) ) {
				foreach ( (array) $log_meta as $key => $meta ) {
					$add_meta_function( $log_id, sanitize_key( $key ), $meta );
				}
			}
		}

		/**
		 * Triggers after a log has been inserted.
		 *
		 * @param int   $log_id   ID of the new log.
		 * @param array $args     Log data.
		 * @param array $log_meta Log meta data.
		 */
		do_action( 'edd_post_insert_log', $log_id, $args, $log_meta );

		return $log_id;
	}

	/**
	 * Update and existing log item
	 *
	 * @since 1.3.1
	 * @since 3.0 - Added $log_id parameter and boolean return type.
	 *
	 * @param array $log_data Log entry data.
	 * @param array $log_meta Log entry meta.
	 * @param int   $log_id   Log ID.
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

		if ( isset( $args['ID'] ) && empty( $log_id ) ) {
			$log_id = $args['ID'];
		}

		// Bail if the log ID is still empty.
		if ( empty( $log_id ) ) {
			return false;
		}

		// Used to dynamically dispatch the method call to insert() to the correct class.
		$update_method        = 'edd_update_log';
		$update_meta_function = 'edd_update_log_meta';

		$type = $args['log_type'];
		if ( ! empty( $type ) ) {
			$data['type'] = $args['log_type'];
		}

		$data = array(
			'object_id'   => $args['object_id'],
			'object_type' => $args['object_type'],
			'title'       => $args['title'],
			'message'     => $args['message'],
		);

		if ( 'api_request' === $data['type'] ) {
			$update_meta_function = 'edd_update_api_request_log_meta';
			$legacy               = array(
				'user'         => 'user_id',
				'key'          => 'api_key',
				'token'        => 'token',
				'version'      => 'version',
				'post_excerpt' => 'request',
				'post_content' => 'error',
				'request_ip'   => 'ip',
				'time'         => 'time',
			);

			foreach ( $legacy as $old_key => $new_key ) {
				if ( isset( $log_meta[ $old_key ] ) ) {
					$data[ $new_key ] = $log_meta[ $old_key ];

					unset( $log_meta[ $old_key ] );
				}
			}
		} elseif ( 'file_download' === $data['type'] ) {
			$update_meta_function = 'edd_update_file_download_log_meta';
			$legacy               = array(
				'file_id'    => 'file_id',
				'payment_id' => 'payment_id',
				'price_id'   => 'price_id',
				'user_id'    => 'user_id',
				'ip'         => 'ip',
			);

			foreach ( $legacy as $old_key => $new_key ) {
				if ( isset( $log_meta[ $old_key ] ) ) {
					$data[ $new_key ] = $log_meta[ $old_key ];

					unset( $log_meta[ $old_key ] );
				}
			}

			if ( isset( $args['post_parent'] ) ) {
				$data['download_id'] = $args['post_parent'];
			}
		}

		unset( $data['type'] );

		// Bail if not callable
		if ( ! is_callable( $update_method ) ) {
			return false;
		}

		call_user_func( $update_method, $data );

		// Set log meta, if any
		if ( is_callable( $update_meta_function ) ) {
			if ( 'edd_update_log' === $update_method && ! empty( $log_meta ) ) {
				foreach ( (array) $log_meta as $key => $meta ) {
					$update_meta_function( $log_id, sanitize_key( $key ), $meta );
				}
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

		$log_type = isset( $args['log_type'] )
			? $args['log_type']
			: false;

		// Parse arguments
		$r = $this->parse_args( $args );

		// Used to dynamically dispatch the call to the correct class.
		$log_type = $this->get_log_table( $log_type );
		$func     = "edd_get_{$log_type}";
		$logs     = is_callable( $func )
			? call_user_func( $func, $r )
			: false;

		// Return the logs (or false)
		return $logs;
	}

	/**
	 * Retrieves number of log entries connected to particular object ID.
	 *
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
		$r = array(
			$this->get_object_id_column_name_for_type( $type ) => $object_id,
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$r['type'] = $type;
		}

		if ( ! empty( $meta_query ) ) {
			$r['meta_query'] = $meta_query;
		}

		if ( ! empty( $date_query ) ) {
			$r['date_query'] = $date_query;
		}

		// Used to dynamically dispatch the call to the correct class.
		$log_type = $this->get_log_table( $type );

		// Call the func, or not
		$func  = "edd_count_{$log_type}";
		$count = is_callable( $func )
			? call_user_func( $func, $r )
			: 0;

		return $count;
	}

	/**
	 * Delete logs based on parameters passed.
	 *
	 * @since 1.3.1
	 *
	 * @param int    $object_id  Object ID (default: 0).
	 * @param string $type       Log type (default: null).
	 * @param array  $meta_query Log meta query (default: null).
	 */
	public function delete_logs( $object_id = 0, $type = null, $meta_query = null ) {
		$r = array(
			$this->get_object_id_column_name_for_type( $type ) => $object_id,
		);

		if ( ! empty( $type ) && $this->valid_type( $type ) ) {
			$r['type'] = $type;
		}

		if ( ! empty( $meta_query ) ) {
			$r['meta_query'] = $meta_query;
		}

		// Used to dynamically dispatch the call to the correct class.
		$log_type = $this->get_log_table( $type );

		// Call the func, or not.
		$func = "edd_get_{$log_type}";
		$logs = is_callable( $func )
			? call_user_func( $func, $r )
			: array();

		// Bail if no logs.
		if ( empty( $logs ) ) {
			return;
		}

		// Maybe bail if delete function does not exist.
		$func = rtrim( "edd_delete_{$log_type}", 's' );
		if ( ! is_callable( $func ) ) {
			return;
		}

		// Loop through and delete logs.
		foreach ( $logs as $log ) {
			call_user_func( $func, $log->id );
		}
	}

	/**
	 * Get the new log type from the old type.
	 *
	 * @since 3.0
	 *
	 * @param string $type
	 *
	 * @return string
	 */
	private function get_log_table( $type = '' ) {
		$retval = 'logs';

		if ( 'api_request' === $type ) {
			$retval = 'api_request_logs';
		} elseif ( 'file_download' === $type ) {
			$retval = 'file_download_logs';
		}

		return $retval;
	}

	/**
	 * Parse arguments. Contains back-compat argument aliasing.
	 *
	 * @since 3.0
	 *
	 * @param array $args
	 * @return array
	 */
	private function parse_args( $args = array() ) {

		// Parse args
		$r = wp_parse_args( $args, array(
			'log_type'       => false,
			'post_type'      => 'edd_log',
			'post_status'    => 'publish',
			'post_parent'    => 0,
			'posts_per_page' => 20,
			'paged'          => get_query_var( 'paged' ),
			'orderby'        => 'id',
		) );

		// Back-compat for ID ordering
		if ( 'ID' === $r['orderby'] ) {
			$r['orderby'] = 'id';
		}

		// Back-compat for log_type
		if ( ! empty( $r['log_type'] ) ) {
			$r['type'] = $r['log_type'];
		}

		// Back-compat for post_parent.
		if ( ! empty( $r['post_parent'] ) ) {
			$type                                        = ! empty( $r['log_type'] ) ? $r['log_type'] : '';
			$r[ $this->get_object_id_column_name_for_type( $type ) ] = $r['post_parent'];
		}

		// Back compat for posts_per_page
		$r['number'] = $r['posts_per_page'];

		// Unset old keys
		unset(
			$r['posts_per_page'],
			$r['post_parent'],
			$r['post_status'],
			$r['post_type'],
			$r['log_type']
		);

		if ( ! isset( $r['offset'] ) ) {
			$r['offset'] = $r['paged'] > 1
				? ( ( $r['paged'] - 1 ) * $r['number'] )
				: 0;
			unset( $r['paged'] );
		}

		// Return parsed args
		return $r;
	}

	/**
	 * Gets the object ID column name based on the log type.
	 *
	 * @since 3.1
	 * @param string $type The log type.
	 * @return string      The column name to query for the object ID.
	 */
	private function get_object_id_column_name_for_type( $type = '' ) {

		switch ( $type ) {
			case 'file_download':
				$object_id = 'product_id';
				break;

			case 'api_request':
				$object_id = 'user_id';
				break;

			default:
				$object_id = 'object_id';
				break;
		}

		return $object_id;
	}

	/** File System ***********************************************************/

	/**
	 * Sets up the log file if it is writable
	 *
	 * @since 2.8.7
	 * @return void
	 */
	public function setup_log_file() {
		$this->init_fs();

		$upload_dir     = wp_upload_dir();
		$this->filename = wp_hash( home_url( '/' ) ) . '-edd-debug.log';
		$this->file     = trailingslashit( $upload_dir['basedir'] ) . $this->filename;

		if ( ! $this->get_fs()->is_writable( $upload_dir['basedir'] ) ) {
			$this->is_writable = false;
		}
	}

	/**
	 * Initialize the WordPress file system
	 *
	 * @since 3.0
	 *
	 * @global WP_Filesystem_Base $wp_filesystem
	 */
	private function init_fs() {
		global $wp_filesystem;

		if ( ! empty( $wp_filesystem ) ) {
			return;
		}

		// Include the file-system
		require_once ABSPATH . 'wp-admin/includes/file.php';

		// Initialize the file system
		WP_Filesystem();
	}

	/**
	 * Get the WordPress file-system
	 *
	 * @since 3.0
	 *
	 * @return WP_Filesystem_Base
	 */
	private function get_fs() {
		return ! empty( $GLOBALS['wp_filesystem'] )
			? $GLOBALS['wp_filesystem']
			: false;
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
	 * Return the location of the log file that EDD_Logging will use.
	 *
	 * @since 2.9.1
	 *
	 * @return string
	 */
	public function get_log_file_path() {
		return $this->file;
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
	 * Retrieve the file data is written to
	 *
	 * @access protected
	 * @since 2.8.7
	 *
	 * @return string File data.
	 */
	protected function get_file() {
		$file = '';

		if ( $this->get_fs()->exists( $this->file ) ) {
			if ( ! $this->get_fs()->is_writable( $this->file ) ) {
				$this->is_writable = false;
			}

			$file = $this->get_fs()->get_contents( $this->file );
		} else {
			$this->get_fs()->put_contents( $this->file, '' );
			$this->get_fs()->chmod( $this->file, 0664 );
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
		file_put_contents( $this->file, $message, FILE_APPEND );
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
		$this->get_fs()->delete( $this->file );

		if ( $this->get_fs()->exists( $this->file ) ) {

			// It's still there, so maybe server doesn't have delete rights
			$this->get_fs()->chmod( $this->file, 0664 );
			$this->get_fs()->delete( $this->file );

			// See if it's still there...
			if ( $this->get_fs()->exists( $this->file ) ) {
				$this->get_fs()->put_contents( $this->file, '' );
			}
		}

		$this->file = '';
		return true;
	}

	/** Deprecated ************************************************************/

	/**
	 * Registers the edd_log post type.
	 *
	 * @since 1.3.1
	 * @deprecated 3.0 Due to migration to custom tables.
	 */
	public function register_post_type() {
		_edd_deprecated_function( __FUNCTION__, '3.0.0' );
	}

	/**
	 * Register the log type taxonomy.
	 *
	 * @since 1.3.1
	 * @deprecated 3.0 Due to migration to custom tables.
	*/
	public function register_taxonomy() {
		_edd_deprecated_function( __FUNCTION__, '3.0.0' );
	}
}

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
	$edd_logs = EDD()->debug_log;

	return $edd_logs->add( $title, $message, $parent, $type );
}

/**
 * Logs a message to the debug log file.
 *
 * @since 2.8.7
 * @since 2.9.4 Added the 'force' option.
 *
 * @param string $message Log message.
 * @param bool   $force   Whether to force a log entry to be added. Default false.
 */
function edd_debug_log( $message = '', $force = false ) {
	$edd_logs = EDD()->debug_log;

	if ( edd_is_debug_mode() || $force ) {

		if ( function_exists( 'mb_convert_encoding' ) ) {

			$message = mb_convert_encoding( $message, 'UTF-8' );

		}

		$edd_logs->log_to_file( $message );
	}
}

/**
 * Logs an exception to the debug log file.
 *
 * @since 3.0
 *
 * @param \Exception $exception Exception object.
 */
function edd_debug_log_exception( $exception ) {

	$label = get_class( $exception );

	if ( $exception->getCode() ) {

		$message = sprintf( '%1$s: %2$s - %3$s',
			$label,
			$exception->getCode(),
			$exception->getMessage()
		);

	} else {

		$message = sprintf( '%1$s: %2$s',
			$label,
			$exception->getMessage()
		);

	}

	edd_debug_log( $message );
}

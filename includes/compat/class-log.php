<?php
/**
 * Backwards Compatibility Handler for Logs.
 *
 * @package     EDD
 * @subpackage  Compat
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Compat;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Log Class.
 *
 * @since 3.0
 */
class Log extends Base {

	/**
	 * Holds the component for which we are handling back-compat. There is a chance that two methods have the same name
	 * and need to be dispatched to completely other methods. When a new instance of Back_Compat is created, a component
	 * can be passed to the constructor which will allow __call() to dispatch to the correct methods.
	 *
	 * @since 3.0
	 * @access protected
	 * @var string
	 */
	protected $component = 'payment';

	/**
	 * Backwards compatibility hooks for logs.
	 *
	 * @since 3.0
	 * @access protected
	 */
	protected function hooks() {

		/* Filters ************************************************************/

		add_filter( 'get_post_metadata',    array( $this, 'api_request_log_get_post_meta'    ), 99, 4 );
		add_filter( 'update_post_metadata', array( $this, 'api_request_log_update_post_meta' ), 99, 5 );
		add_filter( 'add_post_metadata',    array( $this, 'api_request_log_update_post_meta' ), 99, 5 );

		add_filter( 'get_post_metadata',    array( $this, 'file_download_log_get_post_meta'    ), 99, 4 );
		add_filter( 'update_post_metadata', array( $this, 'file_download_log_update_post_meta' ), 99, 5 );
		add_filter( 'add_post_metadata',    array( $this, 'file_download_log_update_post_meta' ), 99, 5 );
	}

	/**
	 * Backwards compatibility filters for get_post_meta() calls on API request logs.
	 *
	 * @since 3.0
	 *
	 * @param  mixed  $value       The value get_post_meta would return if we don't filter.
	 * @param  int    $object_id   The object ID post meta was requested for.
	 * @param  string $meta_key    The meta key requested.
	 * @param  bool   $single      If a single value or an array of the value is requested.
	 *
	 * @return mixed The value to return.
	 */
	public function api_request_log_get_post_meta( $value, $object_id, $meta_key, $single ) {
		if ( 'get_post_metadata' !== current_filter() ) {
			$message = __( 'This function is not meant to be called directly. It is only here for backwards compatibility purposes.', 'easy-digital-downloads' );
			_doing_it_wrong( __FUNCTION__, $message, 'EDD 3.0' );
		}

		$meta_keys = array(
			'_edd_log_request_ip',
			'_edd_log_user',
			'_edd_log_key',
			'_edd_log_token',
			'_edd_log_time',
			'_edd_log_version',
		);

		if ( ! in_array( $meta_key, $meta_keys, true ) ) {
			return $value;
		}

		$api_request_log = edd_get_api_request_log( $object_id );

		if ( ! $api_request_log ) {
			return $value;
		}

		switch ( $meta_key ) {
			case '_edd_log_request_ip':
			case '_edd_log_user':
			case '_edd_log_key':
			case '_edd_log_token':
			case '_edd_log_time':
			case '_edd_log_version':
				$key = str_replace( '_edd_log_', '', $meta_key );

				switch ( $key ) {
					case 'request_ip':
						$key = 'ip';
						break;
					case 'key':
						$key = 'api_key';
						break;
					case 'user':
						$key = 'user_id';
						break;
				}

				$value = $api_request_log->{$key};
				break;
		}

		if ( $this->show_notices ) {
			_doing_it_wrong( 'get_post_meta()', 'All log postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_get_api_request_log()</code> instead.', 'EDD 3.0' );

			if ( $this->show_backtrace ) {
				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}
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
	 * @param mixed  $check      Comes in 'null' but if returned not null, WordPress Core will not interact with the
	 *                           postmeta table.
	 * @param int    $object_id  The object ID post meta was requested for.
	 * @param string $meta_key   The meta key requested.
	 * @param mixed  $meta_value The value get_post_meta would return if we don't filter.
	 * @param mixed  $prev_value The previous value of the meta.
	 * @return mixed Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid postmeta.
	 */
	public function api_request_log_update_post_meta( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		$meta_keys = array(
			'_edd_log_request_ip',
			'_edd_log_user',
			'_edd_log_key',
			'_edd_log_token',
			'_edd_log_time',
			'_edd_log_version',
		);

		if ( ! in_array( $meta_key, $meta_keys, true ) ) {
			return $check;
		}

		$api_request_log = edd_get_api_request_log( $object_id );

		if ( ! $api_request_log ) {
			return $check;
		}

		switch ( $meta_key ) {
			case '_edd_log_request_ip':
			case '_edd_log_user':
			case '_edd_log_key':
			case '_edd_log_token':
			case '_edd_log_time':
			case '_edd_log_version':
				$key = str_replace( '_edd_log_', '', $meta_key );

				switch ( $key ) {
					case 'request_ip':
						$key = 'ip';
						break;
					case 'key':
						$key = 'api_key';
						break;
					case 'user':
						$key = 'user_id';
						break;
				}

				$check = edd_update_api_request_log( $object_id, array(
					$key => $meta_value,
				) );
				break;
		}

		if ( $this->show_notices ) {
			_doing_it_wrong( 'add_post_meta()/update_post_meta()', 'All log postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_add_order_meta()/edd_update_order_meta()()</code> instead.', 'EDD 3.0' );

			if ( $this->show_backtrace ) {
				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}
		}

		return $check;
	}

	/**
	 * Backwards compatibility filters for get_post_meta() calls on file download logs.
	 *
	 * @since 3.0
	 *
	 * @param  mixed  $value       The value get_post_meta would return if we don't filter.
	 * @param  int    $object_id   The object ID post meta was requested for.
	 * @param  string $meta_key    The meta key requested.
	 * @param  bool   $single      If a single value or an array of the value is requested.
	 *
	 * @return mixed The value to return.
	 */
	public function file_download_log_get_post_meta( $value, $object_id, $meta_key, $single ) {
		if ( 'get_post_metadata' !== current_filter() ) {
			$message = __( 'This function is not meant to be called directly. It is only here for backwards compatibility purposes.', 'easy-digital-downloads' );
			_doing_it_wrong( __FUNCTION__, $message, 'EDD 3.0' );
		}

		$meta_keys = array(
			'_edd_log_user_info',
			'_edd_log_user_id',
			'_edd_log_file_id',
			'_edd_log_ip',
			'_edd_log_payment_id',
			'_edd_log_price_id',
			'_edd_log_customer_id',
		);

		if ( ! in_array( $meta_key, $meta_keys, true ) ) {
			return $value;
		}

		$file_download_log = edd_get_file_download_log( $object_id );

		if ( ! $file_download_log ) {
			return $value;
		}

		switch ( $meta_key ) {
			case '_edd_log_user_id':
			case '_edd_log_file_id':
			case '_edd_log_ip':
			case '_edd_log_payment_id':
			case '_edd_log_price_id':
			case '_edd_log_customer_id':
				$key = str_replace( '_edd_log_', '', $meta_key );

				switch ( $key ) {
					case 'request_ip':
						$key = 'ip';
						break;
					case 'key':
						$key = 'api_key';
						break;
					case 'user':
						$key = 'user_id';
						break;
					case 'payment_id':
						$key = 'order_id';
						break;
				}

				if ( isset( $file_download_log->{$key} ) ) {
					$value = $file_download_log->{$key};
				}

				if ( 'user_id' === $key ) {
					$customer = new \EDD_Customer( $file_download_log->customer_id );
					$value    = ! empty( $customer->user_id ) ? $customer->user_id : 0;
				}
				break;
		}

		if ( $this->show_notices ) {
			_doing_it_wrong( 'get_post_meta()', __( 'All log postmeta has been <strong>deprecated</strong> since Easy Digital Downloads 3.0! Use <code>edd_get_api_request_log()</code> instead.', 'easy-digital-downloads' ), 'EDD 3.0' );

			if ( $this->show_backtrace ) {
				$backtrace = debug_backtrace();
				trigger_error( print_r( $backtrace, 1 ) );
			}
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
	 * @param mixed  $check      Comes in 'null' but if returned not null, WordPress Core will not interact with
	 *                           the postmeta table.
	 * @param int    $object_id  The object ID post meta was requested for.
	 * @param string $meta_key   The meta key requested.
	 * @param mixed  $meta_value The value get_post_meta would return if we don't filter.
	 * @param mixed  $prev_value The previous value of the meta
	 *
	 * @return mixed Returns 'null' if no action should be taken and WordPress core can continue, or non-null to avoid postmeta
	 */
	public function file_download_log_update_post_meta( $check, $object_id, $meta_key, $meta_value, $prev_value ) {
		$meta_keys = array(
			'_edd_log_user_info',
			'_edd_log_user_id',
			'_edd_log_file_id',
			'_edd_log_ip',
			'_edd_log_payment_id',
			'_edd_log_price_id',
			'_edd_log_customer_id',
		);

		if ( ! in_array( $meta_key, $meta_keys, true ) ) {
			return $check;
		}

		$file_download_log = edd_get_file_download_log( $object_id );

		if ( ! $file_download_log ) {
			return $check;
		}

		switch ( $meta_key ) {
			case '_edd_log_user_id':
			case '_edd_log_file_id':
			case '_edd_key_ip':
			case '_edd_log_payment_id':
			case '_edd_log_price_id':
			case '_edd_log_customer_id':
				$key = str_replace( '_edd_log_', '', $meta_key );

				if ( 'payment_id' === $key ) {
					$key = 'order_id';
				}

				$check = edd_update_file_download_log( $object_id, array(
					$key => $meta_value,
				) );
				break;
		}

		return $check;
	}
}

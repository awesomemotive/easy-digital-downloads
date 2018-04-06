<?php
/**
 * Logs API - API Request Log Object.
 *
 * @package     EDD
 * @subpackage  Logs
 * @copyright   Copyright (c) 2018, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Logs;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Api_Request_Log Class.
 *
 * @since 3.0
 */
class Api_Request_Log {

	/**
	 * API request log ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $id;

	/**
	 * User ID of the user making the API request.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $user_id;

	/**
	 * API key.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $api_key;

	/**
	 * API token.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $token;

	/**
	 * API version.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $version;

	/**
	 * API request.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $request;

	/**
	 * IP address of the client making the API request.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $ip;

	/**
	 * Speed of the API request.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    float
	 */
	protected $time;

	/**
	 * Date log was created.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $date_created;

	/**
	 * Constructor.
	 *
	 * @since 3.0.0
	 *
	 * @param \object $log API request log data from the database.
	 */
	public function __construct( $log = null ) {
		if ( is_object( $log ) ) {
			foreach ( get_object_vars( $log ) as $key => $value ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Sanitize the data for update/create.
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $data The data to sanitize.
	 *
	 * @return array $data The sanitized data, based off column defaults.
	 */
	private function sanitize_columns( $data ) {
		$default_values = array();

		foreach ( $data as $key => $type ) {
			// Only sanitize data that we were provided
			if ( ! array_key_exists( $key, $data ) ) {
				continue;
			}

			switch ( $type ) {
				case '%s':
					$data[ $key ] = sanitize_text_field( $data[ $key ] );
					break;

				case '%d':
					if ( ! is_numeric( $data[ $key ] ) || absint( $data[ $key ] ) !== (int) $data[ $key ] ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = absint( $data[ $key ] );
					}
					break;

				case '%f':
					$value = floatval( $data[ $key ] );

					if ( ! is_float( $value ) ) {
						$data[ $key ] = $default_values[ $key ];
					} else {
						$data[ $key ] = $value;
					}
					break;

				default:
					$data[ $key ] = sanitize_text_field( $data[ $key ] );
					break;
			}
		}

		return $data;
	}
}

<?php
/**
 * Logs API - File Download Log Object.
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
 * EDD_File_Download_Log Class.
 *
 * @since 3.0
 */
class File_Download_Log {

	/**
	 * File download log ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $id;

	/**
	 * Download ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $download_id;

	/**
	 * File ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $file_id;

	/**
	 * Payment ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $payment_id;

	/**
	 * Price ID.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $price_id;

	/**
	 * User ID of the user who downloaded the file.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    int
	 */
	protected $user_id;

	/**
	 * Email address of the user who downloaded the file.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $email;

	/**
	 * IP address of the client that downloaded the file.
	 *
	 * @since  3.0
	 * @access protected
	 * @var    string
	 */
	protected $ip;

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
	 * @param \object $log File download log data from the database.
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
		$columns        = $this->db->get_columns();
		$default_values = $this->db->get_column_defaults();

		foreach ( $columns as $key => $type ) {
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

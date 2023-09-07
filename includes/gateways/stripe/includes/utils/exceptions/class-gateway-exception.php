<?php
/**
 * Exception: Gateway
 *
 * @package EDD_Stripe
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 2.8.1
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * EDD_Stripe_Gateway_Exception class.
 */
class EDD_Stripe_Gateway_Exception extends \Exception {

	/**
	 * Log message, not displayed to the user.
	 *
	 * @since 2.8.1
	 * @var string
	 */
	public $log_message;

	/**
	 * Constructs the exception.
	 *
	 * @since 2.8.1
	 *
	 * @param string $display_message User-facing exception message.
	 * @param string $log_message Optional. Admin-facing exception message.
	 * @param int    $http_status_code Optional. HTTP status code to respond with, e.g. 400.
	 */
	public function __construct(
		$display_message,
		$log_message = '',
		$http_status_code = 400
	) {
		parent::__construct( $display_message, $http_status_code );

		if ( ! empty( $log_message ) ) {
			$this->log_message = $log_message;
		}
	}

	/**
	 * Determines if an admin-facing message is set.
	 *
	 * @since 2.8.1
	 *
	 * @return bool
	 */
	public function hasLogMessage() {
		return ! empty( $this->log_message );
	}

	/**
	 * Returns the admin-facing log message.
	 *
	 * @since 2.8.1
	 *
	 * @return string
	 */
	public function getLogMessage() {
		if ( false === $this->hasLogMessage() ) {
			return '';
		}

		return $this->log_message;
	}

}

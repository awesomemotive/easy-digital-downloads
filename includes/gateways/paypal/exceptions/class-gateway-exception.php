<?php
/**
 * PayPal Gateway Exception
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.11
 */

namespace EDD\Gateways\PayPal\Exceptions;

class Gateway_Exception extends \Exception {

	/**
	 * More specific message, used for recording gateway errors.
	 *
	 * @var string
	 */
	private $debug_message;

	/**
	 * Gateway_Exception constructor.
	 *
	 * @param string $message       Exception message. This might be vague, as it's usually presented to the end user.
	 * @param int    $code          Error code.
	 * @param string $debug_message More detailed debug message, used when recording gateway errors.
	 *
	 * @since 2.11
	 */
	public function __construct( $message = '', $code = 0, $debug_message = '' ) {
		$this->debug_message = $debug_message;

		parent::__construct( $message, $code );
	}

	/**
	 * Records a gateway error based off this exception.
	 *
	 * @param int $payment_id
	 *
	 * @since 2.11
	 */
	public function record_gateway_error( $payment_id = 0 ) {
		$message = ! empty( $this->debug_message ) ? $this->debug_message : $this->getMessage();

		edd_record_gateway_error(
			__( 'PayPal Gateway Error', 'easy-digital-downloads' ),
			sprintf(
			/* Translators: %d - HTTP response code; %s - Error message */
				__( 'Response Code: %d; Message: %s', 'easy-digital-downloads' ),
				$this->getCode(),
				$message
			),
			$payment_id
		);
	}

}

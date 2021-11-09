<?php
/**
 * CheckoutProcessor.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Checkout;

use EDD\Checkout\Errors\ErrorCollection;
use EDD\Checkout\Exceptions\ValidationException;
use EDD\Checkout\Traits\CollectsAccountInformation;
use EDD\Checkout\Traits\CollectsAddressInformation;
use EDD\ValueObjects\Address;

class CheckoutProcessor {

	use CollectsAccountInformation, CollectsAddressInformation;

	/**
	 * @var Config
	 */
	protected $config;

	/**
	 * @var array
	 */
	protected $data = [];

	/**
	 * @var Validator
	 */
	protected $validator;

	/**
	 * @var ErrorCollection
	 */
	protected $errorCollection;

	public function __construct( Validator $validator, ErrorCollection $errorCollection ) {
		$this->validator       = $validator;
		$this->errorCollection = $errorCollection;
	}

	/**
	 * Sets the form data.
	 *
	 * @param array $data
	 *
	 * @return $this
	 */
	public function setData( $data ) {
		$this->data = $data;

		return $this;
	}

	/**
	 * @throws ValidationException
	 */
	public function process( Config $config ) {
		$this->config = $config;

		$this->validator->validate( $this->config, $this->data );

		$user    = $this->getOrCreateUser();
		$address = $this->getUserAddress( $this->data );

		$order = $this->getOrderFromSession();
	}

	public function getOrderFromSession() {
		return Order::getFromSession( $this->getUserAddress( $this->data ) );
	}

	private function getOrCreateUser() {
		$user = [];

		if ( is_user_logged_in() ) {
			return (array) wp_get_current_user();
		}

		// @todo create new user slash guest checkout

		return $user;
	}

}

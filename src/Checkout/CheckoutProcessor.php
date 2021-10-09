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
use EDD\ValueObjects\Address;

class CheckoutProcessor {

	use CollectsAccountInformation;

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var Validator
	 */
	private $validator;

	/**
	 * @var ErrorCollection
	 */
	private $errorCollection;

	public function __construct( Validator $validator, ErrorCollection $errorCollection ) {
		$this->validator       = $validator;
		$this->errorCollection = $errorCollection;
	}

	/**
	 * @throws ValidationException
	 */
	public function process( Config $config, $data ) {
		$this->config = $config;
		$this->data   = $data;

		$this->validator->validate( $this->config, $this->data );

		$user    = $this->getOrCreateUser();
		$address = $this->getUserAddress();

		$order = Order::getFromSession( $address );
	}

	private function getOrCreateUser() {
		$user = [];

		if ( is_user_logged_in() ) {
			return (array) wp_get_current_user();
		}

		// @todo create new user slash guest checkout

		return $user;
	}

	/**
	 * @return Address
	 */
	private function getUserAddress() {
		/*
		 * This is a map of the final key we want (e.g. `line1`) to the array key
		 * it's saved under in the form data (e.g. `card_address`).
		 */
		$map = [
			'line1'       => 'card_address',
			'line2'       => 'card_address_2',
			'city'        => 'card_city',
			'region'      => 'card_state',
			'postal_code' => 'card_zip',
			'country'     => 'billing_country',
		];

		$address = [];

		foreach ( $map as $param => $dataField ) {
			$address[ $param ] = ! empty( $this->data[ $dataField ] ) ? sanitize_text_field( $this->data[ $dataField ] ) : '';
		}

		return Address::fromArray( $address );
	}

}

<?php
/**
 * CheckoutProcessor.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Checekout;

use EDD\Checkout\Config;
use EDD\Checkout\Errors\ErrorCollection;
use EDD\Checkout\Errors\FormError;
use EDD\Checkout\Exceptions\ValidationException;
use EDD\Checkout\Order;
use EDD\Checkout\Validator;

class CheckoutProcessor {

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
	private $errors;

	public function __construct( Config $config, $data ) {
		$this->config    = $config;
		$this->data      = $data;
		$this->validator = new Validator( $this->config, $this->data );
		$this->errors    = new ErrorCollection();
	}

	/**
	 * @throws ValidationException
	 */
	public function process() {
		$this->validator->validate();

		if ( isset( $this->data['edd_login_submit'] ) ) {
			// process login
		}

		$user            = $this->getOrCreateUser();
		$user['address'] = $this->getUserAddress();

		$order = Order::getFromSession( $user['address'] );
	}

	private function getOrCreateUser() {
		$user = [];

		if ( is_user_logged_in() ) {
			return (array) wp_get_current_user();
		}

		// @todo create new user slash guest checkout

		return $user;
	}

	private function getUserAddress() {
		/*
		 * This is a map of the final key we want (e.g. `line1`) to the array key
		 * it's saved under in the form data (e.g. `card_address`).
		 */
		$map = [
			'line1'   => 'card_address',
			'line2'   => 'card_address_2',
			'city'    => 'card_city',
			'state'   => 'card_state',
			'country' => 'billing_country',
			'zip'     => 'card_zip',
		];

		$address = [];

		foreach ( $map as $param => $dataField ) {
			$address[ $param ] = ! empty( $this->data[ $dataField ] ) ? sanitize_text_field( $this->data[ $dataField ] ) : '';
		}

		return $address;
	}

}

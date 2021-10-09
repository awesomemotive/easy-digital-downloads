<?php
/**
 * Config.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Checkout;

class Config {

	/**
	 * @var bool
	 */
	public $allowGuestCheckout;

	public function __construct() {
		$this->allowGuestCheckout = ! edd_no_guest_checkout();
	}

}

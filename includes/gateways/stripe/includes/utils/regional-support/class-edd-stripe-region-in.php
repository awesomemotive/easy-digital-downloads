<?php

/**
 * India Regionality functionality class for EDD Stripe.
 */
class EDD_Stripe_Region_IN extends EDD_Stripe_Region_Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->country_code          = 'IN';
		$this->requires_card_name    = true;
		$this->requires_card_address = true;

		parent::__construct();
	}
}

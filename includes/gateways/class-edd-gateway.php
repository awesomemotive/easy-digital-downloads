<?php

abstract class EDD_Gateway {
	
	public $id;
	public $checkout_label;
	public $admin_label;
	public $purchase_data;
	public $customer_id;
	public $customer;
	public $supports;

	public function __construct() {}

	public function init() {}

	public function process_webhooks() {}

	public function scripts() {}

	public function fields() {}

	public function validate_fields() {}

	public function supports( $item = '' ) {
		return in_array( $item, $this->supports );
	}

	public function add_error( $code = '', $message = '' ) {
		edd_set_error( $code, $message );
	}

	private function process_checkout() {}


}
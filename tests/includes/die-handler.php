<?php
/**
 * Override the die handlers.
 *
 * Easy Digital Downloads uses the edd_die() function to die/exit the script
 * which is a simple wrapper for calling the wp_die() function, this class simply
 * overrides that die calls by returning false allowing us to make assertions when
 * edd_die() is called, for example in the AJAX functions or the API functions.
 *
 * @package EDD_Unit_Tests
 * @author Sunny Ratilal
 */
class EDD_Die_Handler {
	public $die = false;

	public function __construct() {
		$this->die_handler();
	}

	public function die_handler() {
		define( 'EDD_UNIT_TESTS', true );
		$this->die = true;
	}

	public function died() {
		return $this->die;
	}

	public function reset() {
		$this->die = false;
	}
}
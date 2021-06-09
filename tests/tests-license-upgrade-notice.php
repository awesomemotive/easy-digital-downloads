<?php
/**
 * Promotional Notice Tests
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Sandhills Development, LLC
 * @license   GPL2+
 * @since     2.10.6
 */

namespace EDD\Tests;

use EDD\Admin\Promos\Notices\License_Upgrade_Notice;

class Tests_Promo_Notices extends \EDD_UnitTestCase {

	/**
	 * Runs once before each test.
	 *
	 * Deletes the pass licenses option so we can customize this per test.
	 */
	public function setUp() {
		parent::setUp();

		// Always start with no option.
		delete_option( 'edd_pass_licenses' );
	}

	public function test_individual_license_activated() {
		// We have pass data, but no passes.
		update_option( 'edd_pass_licenses', json_encode( array() ) );

		// Simulate that we have a license key though.
		global $edd_licensed_products;
		$edd_licensed_products = array( 'license_key' );

		$notice = new License_Upgrade_Notice();
	}

}

<?php

class EDD_UnitTestCase extends WP_UnitTestCase {

	public static function wpSetUpBeforeClass() {
		edd_install();
	}

	public static function tearDownAfterClass() {
		return parent::tearDownAfterClass();
	}
}

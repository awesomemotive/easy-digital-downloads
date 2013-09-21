<?php
namespace EDD_Unit_Tests;

/**
 * @group edd_emails
 */
class Tests_Emails extends EDD_UnitTestCase {
	public function setUp() {
		parent::setUp();
	}

	public function tearDown() {
		parent::tearDown();
	}

	/**
     * Test that each of the actions are added and each hooked in with the right priority
     */
	public function test_email_actions() {
		global $wp_filter;
		$this->assertarrayHasKey( 'edd_admin_email_notice',       $wp_filter['edd_admin_sale_notice'][10]  );
		$this->assertarrayHasKey( 'edd_trigger_purchase_receipt', $wp_filter['edd_complete_purchase'][999] );
		$this->assertarrayHasKey( 'edd_resend_purchase_receipt',  $wp_filter['edd_email_links'][10]        );
		$this->assertarrayHasKey( 'edd_send_test_email',          $wp_filter['edd_send_test_email'][10]    );
	}


}

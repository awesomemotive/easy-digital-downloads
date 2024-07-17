<?php
namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Payment;

/**
 * @group edd_emails
 */
class Registry extends EDD_UnitTestCase {

	/**
	 * Test that the email registry is properly registered.
	 */
	public function test_email_registry_is_registered() {
		$this->assertTrue( class_exists( 'EDD\Emails\Registry' ) );
	}

	/** Test the is_registerd method */
	/**
	 * Test that the order_receipt email is properly registered.
	 */
	public function test_order_receipt_email_is_registered() {
		$this->assertTrue( \EDD\Emails\Registry::is_registered( 'order_receipt' ) );
	}

	/**
	 * Test that the admin_order_notice email is properly registered.
	 */
	public function test_admin_order_notice_email_is_registered() {
		$this->assertTrue( \EDD\Emails\Registry::is_registered( 'admin_order_notice' ) );
	}

	/**
	 * Test that using is_registered on an unregistered ID returns false.
	 */
	public function test_is_registered_returns_false_for_unregistered_email() {
		$this->assertFalse( \EDD\Emails\Registry::is_registered( 'unregistered_email' ) );
	}

	/** Test the register method */

	/**
	 * Test that we can register a valid email class.
	*/
	public function test_register_valid_email() {
		\EDD\Emails\Registry::register( 'fake_email', 'EDD\Tests\Emails\ValidFakeEmail' );
		$this->assertTrue( \EDD\Emails\Registry::is_registered( 'fake_email' ) );

		$fake_email = new \EDD\Tests\Emails\ValidFakeEmail();
		$this->assertInstanceOf( '\EDD\Emails\Email', $fake_email->email );
		$this->assertEquals( 'fake_email', $fake_email->email->email_id );
		$this->assertEmpty( $fake_email->email->is_enabled() );
	}

	/**
	 * Test that trying to register a duplicate ID is handeled properly.
	 * @expectException WPDieException
	 */
	public function test_register_duplicate_id_throws_exception() {
		$this->setExpectedException( 'WPDieException', 'The email ID provided is already registered.' );
		\EDD\Emails\Registry::register( 'order_receipt', 'EDD\Emails\Types\OrderReceipt' );
	}

	/**
	 * Test that trying to register an email with no ID is handled properly.
	 * @expectException WPDieException
	 */
	public function test_register_no_id_throws_exception() {
		$this->setExpectedException( 'WPDieException', 'An email ID and class must be provided.' );
		\EDD\Emails\Registry::register( '', 'EDD\Emails\Types\OrderReceipt' );
	}

	/**
	 * Test that trying to register an email with no class is handled properly.
	 * @expectException WPDieException
	 */
	public function test_register_no_class_throws_exception() {
		$this->setExpectedException( 'WPDieException', 'An email ID and class must be provided.' );
		\EDD\Emails\Registry::register( 'missing_class', '' );
	}

	/**
	 * Test that trying to register an email with a class that does not exist is handled properly.
	 * @expectException WPDieException
	 */
	public function test_register_non_existent_class_throws_exception() {
		$this->setExpectedException( 'WPDieException', 'The email class must exist and extend the EDD\Emails\Types\Email class.' );
		\EDD\Emails\Registry::register( 'non_existent_class', 'EDD\Tests\Emails\NonExistentClass' );
	}

	/**
	 * Test that trying to register an email with a class that does not extend the EDD\Emails\Types\Email class is handled properly.
	 * @expectException WPDieException
	 */
	public function test_register_non_email_class_throws_exception() {
		$this->setExpectedException( 'WPDieException', 'The email class must exist and extend the EDD\Emails\Types\Email class.' );
		\EDD\Emails\Registry::register( 'non_email_class', 'EDD\Tests\Emails\InvalidFakeEmail' );
	}

	/** Test the get method */

	/**
	 * Test that we can get a registered email class that has arguments in __construct().
	 */
	public function test_get_registered_email_with_arguments() {
		$email = \EDD\Emails\Registry::get( 'order_receipt', array( false ) );
		$this->assertInstanceOf( 'EDD\Emails\Types\OrderReceipt', $email );
	}

	public function test_order_receipt_magic_getter_gets_order() {
		$order_id = EDD_Helper_Payment::create_simple_payment();
		$email    = \EDD\Emails\Registry::get( 'order_receipt', array( edd_get_order( $order_id ) ) );

		$this->assertInstanceOf( 'EDD\Orders\Order', $email->order );
		$this->assertEquals( $order_id, $email->order->id );
	}

	/**
	 * Test that we can get a registered email class that has no arguments in __construct().
	 */
	public function test_get_registered_email_without_arguments() {
		$email = \EDD\Emails\Registry::get( 'fake_email' );
		$this->assertInstanceOf( 'EDD\Tests\Emails\ValidFakeEmail', $email );
	}

	/**
	 * Test that getting a class for an unregistered email is handled properly.
	 * @expectException WPDieException
	 */
	public function test_get_unregistered_email_throws_exception() {
		$this->setExpectedException( 'WPDieException', 'The email ID provided is not registered.' );
		$email = \EDD\Emails\Registry::get( 'unregistered_email' );
	}

	/**
	 * Test that getting a registered email class without the proper number of arguments throws an exception.
	 * @expectException WPDieException
	 */
	public function test_get_registered_email_with_incorrect_number_of_arguments_throws_exception() {
		$this->setExpectedException( 'WPDieException', 'The number of arguments provided (0) does not match the number of arguments required (1) for EDD\Emails\Types\OrderReceipt.' );
		$email = \EDD\Emails\Registry::get( 'order_receipt' );
	}
}

/**
 * Setup a fake class that extends the EDD\Emails\Types\Email class.
 */
class ValidFakeEmail extends \EDD\Emails\Types\Email {
	protected $id = 'fake_email';
	protected $context = 'unit_test';
	protected $recipient_type = 'fake';

	protected function set_email_body_content() {
		return 'I\'m {secret_identity}.';
	}

	protected function set_from_name() {
		return 'Bruce Wayne';
	}

	protected function set_from_email() {
		return 'bruce@waynefoundation.org';
	}

	protected function set_to_email() {
		return 'jgordon@gothampolice.gov';
	}

	protected function set_headers() {
		return array(
			'X-Batman' => 'I\'m Batman.',
		);
	}

	protected function set_subject() {
		return 'Important information';
	}

	protected function set_heading() {
		return 'Important information';
	}

	protected function set_message() {
		return 'I\'m Batman.';
	}

	protected function set_attachments() {
		return array(
			'batmobile.jpg',
		);
	}

	public function get_default_body_content() {
		return '';
	}
}

/** Create a class that does not extend the \EDD\Emails\Types\Email class */
class InvalidFakeEmail {}

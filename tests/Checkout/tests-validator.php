<?php
/**
 * tests-validator.php
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, easy-digital-downloads
 * @license   GPL2+
 */

namespace EDD\Tests\Checkout;

use EDD\Checkout\Config;
use EDD\Checkout\Exceptions\ValidationException;
use EDD\Checkout\Validator;

/**
 * @coversDefaultClass \EDD\Checkout\Validator
 */
class ValidatorTests extends \EDD_UnitTestCase {

	/**
	 * @var Validator
	 */
	protected $validator;

	public function setUp() {
		parent::setUp();

		$this->validator = EDD( Validator::class );
	}

	/**
	 * @covers \EDD\Checkout\Validator::validateGuestUser
	 * @throws \Exception
	 */
	public function test_guest_checkout_throws_exception_if_not_allowed() {
		global $current_user;
		$current_user = false;

		$config                     = new Config();
		$config->allowGuestCheckout = false;

		try {
			$this->validator->validate(
				$config,
				[
					'edd_email' => 'janedoe@example.com',
					'edd_first' => 'Jane',
					'edd_last'  => 'Doe',
				]
			);

			/*
			 * Throwing a new exception here, which will not be caught. We do not
			 * actually expect to end up here, and if we do, this uncaught exception
			 * will fail the test.
			 */
			throw new \Exception( 'Validator unexpectedly passed.' );
		} catch ( ValidationException $e ) {
			$this->assertTrue( $e->getErrorCollection()->hasErrorCode( 'registration_required' ) );
		}
	}

	/**
	 * @covers \EDD\Checkout\Validator::validateGuestUser
	 * @throws \Exception
	 */
	public function test_guest_checkout_passes_if_allowed() {
		global $current_user;
		$current_user = false;

		$config                     = new Config();
		$config->allowGuestCheckout = true;

		$this->validator->validate(
			$config,
			[
				'edd_email' => 'janedoe@example.com',
				'edd_first' => 'Jane',
				'edd_last'  => 'Doe',
			]
		);

		$this->assertFalse( $this->validator->getErrors()->hasErrors() );
	}

	/**
	 * @covers \EDD\Checkout\Validator::validateFormFields
	 * @throws \Exception
	 */
	public function test_not_agreeing_to_terms_when_required_throws_exception() {
		edd_update_option( 'show_agree_to_privacy_policy', '1' );

		try {
			$this->validator->validate(
				new Config(),
				[
					'edd_email' => 'janedoe@example.com',
					'edd_first' => 'Jane',
					'edd_last'  => 'Doe',
				]
			);

			/*
			 * Throwing a new exception here, which will not be caught. We do not
			 * actually expect to end up here, and if we do, this uncaught exception
			 * will fail the test.
			 */
			throw new \Exception( 'Validator unexpectedly passed.' );
		} catch ( ValidationException $e ) {
			$this->assertTrue( $e->getErrorCollection()->hasErrorCode( 'agree_to_privacy_policy' ) );
		}
	}

	/**
	 * @covers \EDD\Checkout\Validator::validateFormFields
	 * @throws \Exception
	 */
	public function test_missing_email_throws_exception() {
		try {
			$this->validator->validate(
				new Config(),
				[
					'edd_first' => 'Jane',
					'edd_last'  => 'Doe',
				]
			);

			/*
			 * Throwing a new exception here, which will not be caught. We do not
			 * actually expect to end up here, and if we do, this uncaught exception
			 * will fail the test.
			 */
			throw new \Exception( 'Validator unexpectedly passed.' );
		} catch ( ValidationException $e ) {
			$this->assertTrue( $e->getErrorCollection()->hasErrorCode( 'invalid_email' ) );
		}
	}

}

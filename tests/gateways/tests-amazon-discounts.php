<?php
/**
 * Amazon checkout discount tests.
 *
 * @package     EDD\Tests\Gateways
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Gateways;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Tests\Discounts\DiscountFixtures;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests that Amazon claims a discount use before it charges the buyer.
 *
 * @group edd_discounts
 * @group edd_gateways
 */
class AmazonDiscounts extends EDD_UnitTestCase {

	use DiscountFixtures;

	/**
	 * The buyer's email address.
	 *
	 * @var string
	 */
	const EMAIL = 'amazon-discounts@edd.test';

	/**
	 * The posted gateway before the test.
	 *
	 * @var string|null
	 */
	private $original_posted_gateway;

	/**
	 * The session's resume order before the test.
	 *
	 * @var mixed
	 */
	private $original_resume_order;

	/**
	 * Captures the posted gateway and the session's resume order.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->forget_cached_claims();

		$this->original_posted_gateway = $_POST['edd-gateway'] ?? null;
		$this->original_resume_order   = EDD()->session->get( 'edd_resume_payment' );
		EDD()->session->set( 'edd_resume_payment', null );
	}

	/**
	 * Restores the posted gateway and the session's resume order.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		if ( is_null( $this->original_posted_gateway ) ) {
			unset( $_POST['edd-gateway'] );
		} else {
			$_POST['edd-gateway'] = $this->original_posted_gateway;
		}

		EDD()->session->set( 'edd_resume_payment', $this->original_resume_order );
		edd_empty_cart();
		edd_clear_errors();

		parent::tear_down();
	}

	public function test_checkout_fails_an_order_whose_discount_is_used_up_before_charging() {
		$amazon_charges = $this->check_out_with_amazon( $this->create_used_up_discount() );

		$this->assertSame( array(), $amazon_charges );
		$this->assertSame( 'failed', $this->get_latest_order_for( self::EMAIL )->status );
	}

	public function test_checkout_holds_the_discount_use_while_charging() {
		$discount = $this->create_discount( array( 'max_uses' => 5 ) );

		$amazon_charges = $this->check_out_with_amazon( $discount );

		$this->assertCount( 1, $amazon_charges );
		$this->assertTrue( $amazon_charges[0]['order_holds_use'] );
	}

	public function test_checkout_gives_back_the_discount_use_when_the_charge_fails() {
		$discount = $this->create_discount( array( 'max_uses' => 5 ) );

		$this->check_out_with_amazon( $discount );

		$this->assertSame( 'failed', $this->get_latest_order_for( self::EMAIL )->status );
		$this->assertSame( 0, (int) edd_get_discount( $discount->id )->use_count );
	}

	/**
	 * Runs Amazon's checkout handler against a client that answers every charge with an error.
	 *
	 * @param \EDD_Discount $discount The discount in the cart.
	 * @return array[] The charges requested, each noting whether the order being charged held its discount use.
	 */
	private function check_out_with_amazon( $discount ) {
		require_once EDD_PLUGIN_DIR . 'includes/gateways/amazon-payments.php';

		// The constructor defines constants and registers hooks, so only the handler is exercised.
		$amazon         = ( new \ReflectionClass( \PayWithAmazon\EDD_Amazon_Payments::class ) )->newInstanceWithoutConstructor();
		$amazon->client = new class( $discount ) {
			public $charges = array();
			private $discount;

			public function __construct( $discount ) {
				$this->discount = $discount;
			}

			public function charge( $args ) {
				$order = edd_get_order_by( 'payment_key', $args['charge_order_id'] );

				$this->charges[] = array(
					'order_holds_use' => $order && \EDD\Discounts\Claims::holds( $order->id, $this->discount->id ),
				);

				return (object) array( 'response' => array( 'Status' => 500 ) );
			}
		};

		$_POST['edd-gateway'] = 'amazon';

		$purchase_data                  = $this->get_checkout_order_data( $discount, self::EMAIL, 'amazon' );
		$purchase_data['date']          = current_time( 'mysql' );
		$purchase_data['gateway_nonce'] = wp_create_nonce( 'edd-gateway' );
		$purchase_data['post_data']     = array(
			'edd-gateway'             => 'amazon',
			'edd_amazon_reference_id' => 'AMAZON-REFERENCE-1',
		);

		$amazon->process_purchase( $purchase_data );

		return $amazon->client->charges;
	}
}

<?php
/**
 * Tests for the receipt shortcode's handling of a request-supplied order reference.
 *
 * @package     EDD\Tests
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Payment;

/**
 * @group edd_shortcode
 */
class ReceiptShortcode extends EDD_UnitTestCase {

	/**
	 * An order owned by a registered user.
	 *
	 * @var \EDD\Orders\Order
	 */
	protected $order;

	/**
	 * A user who does not own the order and is not viewing while logged in.
	 *
	 * @var int
	 */
	protected static $buyer_id;

	public static function wpSetUpBeforeClass() {
		self::$buyer_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
	}

	public function setUp(): void {
		parent::setUp();

		$order_id = EDD_Helper_Payment::create_simple_payment();
		edd_update_order( $order_id, array( 'user_id' => self::$buyer_id ) );
		$this->order = edd_get_order( $order_id );

		wp_set_current_user( 0 );
		EDD()->session->set( 'edd_purchase', null );
	}

	public function tearDown(): void {
		EDD()->session->set( 'edd_purchase', null );
		$_GET = array();
		edd_clear_errors();

		parent::tearDown();
	}

	/**
	 * The order under test belongs to a registered user, so the logged-out
	 * branch that renders the login form is the one exercised.
	 */
	public function test_fixture_order_belongs_to_a_registered_user() {
		$this->assertGreaterThan( 0, (int) $this->order->user_id );
		$this->assertFalse( edd_is_guest_payment( $this->order ) );
	}

	/**
	 * A request that pairs a real order id with an order reference that is not
	 * that order's receipt hash must not put a valid receipt hash into the output.
	 */
	public function test_mismatched_order_reference_yields_no_valid_hash_in_the_output() {
		$_GET = array( 'id' => (string) $this->order->id, 'order' => str_repeat( 'a', 64 ) );

		$output = edd_receipt_shortcode( array() );

		$this->assertStringNotContainsString(
			'You must be logged in to view this payment receipt.',
			$output,
			'A mismatched order reference must not resolve the order.'
		);

		foreach ( $this->hashes_in( $output ) as $candidate ) {
			$this->assertFalse(
				$this->order->is_receipt_hash_valid( $candidate ),
				'The output must not carry a valid receipt hash for a request that did not present one.'
			);
		}
	}

	/**
	 * The receipt link EDD issues still resolves its order: a caller presenting
	 * the correct hash while logged out reaches the sanctioned login prompt.
	 */
	public function test_real_receipt_link_still_resolves_the_order() {
		$_GET = array( 'id' => (string) $this->order->id, 'order' => $this->order->get_receipt_hash() );

		$output = edd_receipt_shortcode( array() );

		$this->assertStringContainsString( 'You must be logged in to view this payment receipt.', $output );
	}

	/**
	 * Receipt links in emails sent before 3.7.1 carry the legacy md5 form.
	 *
	 * @since x.x
	 */
	public function test_legacy_md5_receipt_link_still_resolves_the_order() {
		$legacy_hash = md5( $this->order->id . $this->order->payment_key . $this->order->email );
		$_GET        = array( 'id' => (string) $this->order->id, 'order' => $legacy_hash );

		$output = edd_receipt_shortcode( array() );

		$this->assertStringContainsString( 'You must be logged in to view this payment receipt.', $output );
	}

	/**
	 * Returns every 64-character hex token in a string.
	 *
	 * @param string $output
	 * @return string[]
	 */
	private function hashes_in( $output ) {
		preg_match_all( '/[0-9a-f]{64}/', (string) $output, $matches );

		return $matches[0];
	}
}

<?php
/**
 * Tests for the EDD order abilities.
 *
 * @package     EDD\Tests\Abilities
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests\Abilities;

use EDD\Abilities\Orders\Create;
use EDD\Abilities\Orders\NoteAdd;
use EDD\Abilities\Orders\NoteRead;
use EDD\Abilities\Orders\Read;
use EDD\Abilities\Orders\ResendReceipt;
use EDD\Abilities\Orders\UpdateStatus;
use EDD\Tests\Helpers\EDD_Helper_Download;
use EDD\Tests\Helpers\EDD_Helper_Email;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Order ability tests.
 *
 * @since 3.7.1
 */
class Orders extends EDD_UnitTestCase {

	/**
	 * The order_receipt email status captured before a test enabled it.
	 *
	 * @var int|null
	 */
	private $receipt_email_status = null;

	/**
	 * Set the current user to the site administrator, who holds all shop capabilities.
	 */
	public function set_up() {
		parent::set_up();

		wp_set_current_user( 1 );
	}

	/**
	 * Undo what the receipt tests set, so a failure mid-test does not leak.
	 */
	public function tear_down() {
		remove_filter( 'edd_use_after_payment_actions', '__return_false' );

		if ( ! is_null( $this->receipt_email_status ) ) {
			$email = edd_get_email_by( 'email_id', 'order_receipt' );
			if ( $email ) {
				edd_update_email( $email->id, array( 'status' => $this->receipt_email_status ) );
			}

			$this->receipt_email_status = null;
		}

		parent::tear_down();
	}

	public function test_order_read_single_includes_items() {
		$order = parent::edd()->order->create_and_get();

		$ability = new Read();
		$result  = $ability->execute( array( 'id' => $order->id ) );

		$this->assertIsArray( $result );
		$this->assertSame( 1, $result['total'] );
		$this->assertCount( 1, $result['orders'] );
		$this->assertSame( (int) $order->id, $result['orders'][0]['id'] );
		$this->assertArrayHasKey( 'items', $result['orders'][0] );
		$this->assertNotEmpty( $result['orders'][0]['items'] );
	}

	public function test_order_read_missing_order_returns_not_found() {
		$ability = new Read();
		$result  = $ability->execute( array( 'id' => 999999 ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_order_not_found', $result->get_error_code() );
	}

	public function test_order_read_list_paginates() {
		parent::edd()->order->create_many( 5 );

		$ability = new Read();
		$result  = $ability->execute( array( 'limit' => 2, 'offset' => 0 ) );

		$this->assertCount( 2, $result['orders'] );
		$this->assertSame( 2, $result['limit'] );
		$this->assertGreaterThanOrEqual( 5, $result['total'] );
		$this->assertTrue( $result['has_more'] );

		// List results do not include line items.
		$this->assertArrayNotHasKey( 'items', $result['orders'][0] );
	}

	public function test_order_read_filters_by_status() {
		parent::edd()->order->create( array( 'status' => 'pending' ) );
		parent::edd()->order->create( array( 'status' => 'complete' ) );

		$ability = new Read();
		$result  = $ability->execute( array( 'status' => 'pending' ) );

		$this->assertGreaterThanOrEqual( 1, $result['total'] );
		foreach ( $result['orders'] as $order ) {
			$this->assertSame( 'pending', $order['status'] );
		}
	}

	public function test_order_read_permission_requires_edit_shop_payments() {
		$ability = new Read();

		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );
		$this->assertFalse( $ability->check_permissions( array() ) );

		// Grant the capability on the live user object so the check re-evaluates.
		wp_get_current_user()->add_cap( 'edit_shop_payments' );
		$this->assertTrue( $ability->check_permissions( array() ) );
	}

	public function test_update_status_transitions_order() {
		$order_id = parent::edd()->order->create( array( 'status' => 'pending' ) );

		$ability = new UpdateStatus();
		$result  = $ability->execute(
			array(
				'order_id' => $order_id,
				'status'   => 'complete',
			)
		);

		$this->assertTrue( $result['updated'] );
		$this->assertSame( 'pending', $result['previous_status'] );
		$this->assertSame( 'complete', $result['new_status'] );
		$this->assertSame( 'complete', edd_get_order( $order_id )->status );
	}

	public function test_update_status_records_who_made_the_change() {
		$order_id = parent::edd()->order->create( array( 'status' => 'pending' ) );

		// A factory order already carries its own note, so count only the ability's.
		$this->assertCount( 0, $this->get_ability_notes( $order_id ) );

		( new UpdateStatus() )->execute(
			array(
				'order_id' => $order_id,
				'status'   => 'complete',
			)
		);

		$notes = $this->get_ability_notes( $order_id );

		$this->assertCount( 1, $notes );
		$this->assertSame( 1, (int) $notes[0]->user_id );
	}

	public function test_update_status_noop_records_no_note() {
		$order_id = parent::edd()->order->create( array( 'status' => 'complete' ) );

		( new UpdateStatus() )->execute(
			array(
				'order_id' => $order_id,
				'status'   => 'complete',
			)
		);

		$this->assertCount( 0, $this->get_ability_notes( $order_id ) );
	}

	public function test_update_status_same_status_is_noop() {
		$order_id = parent::edd()->order->create( array( 'status' => 'pending' ) );

		$ability = new UpdateStatus();
		$result  = $ability->execute(
			array(
				'order_id' => $order_id,
				'status'   => 'pending',
			)
		);

		$this->assertFalse( $result['updated'] );
	}

	public function test_update_status_missing_order_returns_not_found() {
		$ability = new UpdateStatus();
		$result  = $ability->execute(
			array(
				'order_id' => 999999,
				'status'   => 'complete',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_order_not_found', $result->get_error_code() );
	}

	public function test_refunded_orders_are_excluded_from_sale_listing_totals() {
		$order_id = parent::edd()->order->create( array( 'status' => 'complete' ) );

		$refund_id = edd_refund_order( $order_id );
		$this->assertGreaterThan( 0, $refund_id );

		$read   = new Read();
		$result = $read->execute( array() );

		// The refund record (type=refund) is never listed; the original sale still is.
		$found = wp_list_pluck( $result['orders'], 'id' );
		$this->assertNotContains( (int) $refund_id, $found );
		$this->assertContains( $order_id, $found );

		foreach ( $result['orders'] as $order ) {
			$this->assertSame( 'sale', $order['type'] );
		}
	}

	public function test_order_read_single_lookup_returns_refund_record_with_type() {
		$order_id = parent::edd()->order->create( array( 'status' => 'complete' ) );

		$refund_id = edd_refund_order( $order_id );
		$this->assertGreaterThan( 0, $refund_id );

		$read   = new Read();
		$result = $read->execute( array( 'id' => $refund_id ) );

		$this->assertSame( 'refund', $result['orders'][0]['type'] );
	}

	public function test_resend_receipt_sends_to_order_email() {
		$order = parent::edd()->order->create_and_get();

		// Seed the order receipt email record, which a fresh test site lacks.
		edd_add_email(
			array(
				'email_id'  => 'order_receipt',
				'subject'   => 'Purchase Receipt',
				'heading'   => 'Purchase Receipt',
				'content'   => 'Thank you for your purchase. {download_list}',
				'status'    => 1,
				'context'   => 'order',
				'sender'    => 'edd',
				'recipient' => 'customer',
			)
		);

		$ability = new ResendReceipt();
		$result  = $ability->execute( array( 'order_id' => $order->id ) );

		$this->assertIsArray( $result );
		$this->assertSame( (int) $order->id, $result['order_id'] );
		$this->assertSame( $order->email, $result['email'] );
		$this->assertIsBool( $result['sent'] );
	}

	public function test_resend_receipt_without_receipt_email_fails_cleanly() {
		$order = parent::edd()->order->create_and_get();

		$email = edd_get_email( 'order_receipt' );
		if ( $email ) {
			edd_delete_email( $email->id );
		}

		$ability = new ResendReceipt();
		$result  = $ability->execute( array( 'order_id' => $order->id ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_receipt_disabled', $result->get_error_code() );
	}

	public function test_resend_receipt_missing_order_returns_not_found() {
		$ability = new ResendReceipt();
		$result  = $ability->execute( array( 'order_id' => 999999 ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_order_not_found', $result->get_error_code() );
	}

	public function test_note_add_and_read_round_trip() {
		$order_id = parent::edd()->order->create();

		$add    = new NoteAdd();
		$result = $add->execute(
			array(
				'order_id' => $order_id,
				'note'     => 'Checked with the customer. <script>alert(1)</script>',
			)
		);

		$this->assertIsArray( $result );
		$this->assertStringNotContainsString( '<script>', $result['note']['content'] );
		$this->assertStringContainsString( 'Checked with the customer.', $result['note']['content'] );
		$this->assertSame( 1, $result['note']['user_id'] );

		$read  = new NoteRead();
		$notes = $read->execute( array( 'order_id' => $order_id ) );

		$this->assertGreaterThanOrEqual( 1, $notes['total'] );
		$contents = wp_list_pluck( $notes['notes'], 'content' );
		$this->assertContains( $result['note']['content'], $contents );
	}

	public function test_note_add_empty_after_sanitization_fails() {
		$order_id = parent::edd()->order->create();

		$add    = new NoteAdd();
		$result = $add->execute(
			array(
				'order_id' => $order_id,
				'note'     => '   ',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_note_empty', $result->get_error_code() );
	}

	public function test_create_order_with_new_customer_and_simple_product() {
		$download = EDD_Helper_Download::create_simple_download();

		$ability = new Create();
		$result  = $ability->execute(
			array(
				'email'  => 'ability-buyer@edd.test',
				'name'   => 'Ability Buyer',
				'items'  => array(
					array(
						'product_id' => $download->ID,
						'quantity'   => 2,
					),
				),
				'status' => 'complete',
			)
		);

		$this->assertIsArray( $result );
		$this->assertGreaterThan( 0, $result['order_id'] );
		$this->assertSame( 'complete', $result['status'] );

		$expected_subtotal = floatval( edd_get_download_price( $download->ID ) ) * 2;
		$this->assertEqualsWithDelta( $expected_subtotal, $result['subtotal'], 0.001 );
		$this->assertEqualsWithDelta( $expected_subtotal, $result['total'], 0.001 );

		// The customer record was created and linked.
		$customer = edd_get_customer_by( 'email', 'ability-buyer@edd.test' );
		$this->assertNotEmpty( $customer );
		$this->assertSame( (int) $customer->id, $result['customer_id'] );

		// Order items were written.
		$order = edd_get_order( $result['order_id'] );
		$items = $order->get_items();
		$this->assertCount( 1, $items );
		$this->assertSame( $download->ID, (int) $items[0]->product_id );
		$this->assertSame( 2, (int) $items[0]->quantity );
	}

	public function test_create_order_applies_percent_discount() {
		$download    = EDD_Helper_Download::create_simple_download();
		$discount_id = parent::edd()->discount->create(
			array(
				'code'        => 'ABILITY20',
				'name'        => 'Ability 20',
				'amount'      => 20,
				'amount_type' => 'percent',
				'status'      => 'active',
			)
		);

		$this->assertNotEmpty( $discount_id );

		$ability = new Create();
		$result  = $ability->execute(
			array(
				'email'         => 'discount-buyer@edd.test',
				'items'         => array(
					array( 'product_id' => $download->ID ),
				),
				'discount_code' => 'ABILITY20',
			)
		);

		$this->assertIsArray( $result );

		$price = floatval( edd_get_download_price( $download->ID ) );
		$this->assertEqualsWithDelta( $price * 0.2, $result['discount'], 0.001 );
		$this->assertEqualsWithDelta( $price * 0.8, $result['total'], 0.001 );

		// The discount was recorded as an order adjustment.
		$order       = edd_get_order( $result['order_id'] );
		$adjustments = $order->get_adjustments();
		$this->assertNotEmpty( $adjustments );
	}

	public function test_create_order_with_invalid_product_fails() {
		$ability = new Create();
		$result  = $ability->execute(
			array(
				'email' => 'nobody@edd.test',
				'items' => array(
					array( 'product_id' => 999999 ),
				),
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_product_not_found', $result->get_error_code() );
	}

	public function test_create_order_requires_customer_or_email() {
		$download = EDD_Helper_Download::create_simple_download();

		$ability = new Create();
		$result  = $ability->execute(
			array(
				'items' => array(
					array( 'product_id' => $download->ID ),
				),
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_customer_required', $result->get_error_code() );
	}

	public function test_create_order_with_inactive_discount_fails() {
		$download = EDD_Helper_Download::create_simple_download();
		parent::edd()->discount->create(
			array(
				'code'        => 'EXPIREDCODE',
				'name'        => 'Expired',
				'amount'      => 10,
				'amount_type' => 'percent',
				'status'      => 'inactive',
			)
		);

		$ability = new Create();
		$result  = $ability->execute(
			array(
				'email'         => 'nobody@edd.test',
				'items'         => array(
					array( 'product_id' => $download->ID ),
				),
				'discount_code' => 'EXPIREDCODE',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_discount', $result->get_error_code() );
	}

	public function test_create_order_queues_both_order_emails_by_default() {
		$download = EDD_Helper_Download::create_simple_download();

		$result = ( new Create() )->execute(
			array(
				'email'  => 'wants-emails@edd.test',
				'items'  => array(
					array( 'product_id' => $download->ID ),
				),
				'status' => 'complete',
			)
		);

		$this->assertIsArray( $result );

		// Each trigger records its own meta; without it the deferred send skips the order.
		$this->assertSame( '1', edd_get_order_meta( $result['order_id'], '_edd_should_send_order_receipt', true ), 'The receipt should be queued when send_emails is omitted.' );
		$this->assertSame( '1', edd_get_order_meta( $result['order_id'], '_edd_should_send_admin_order_notice', true ), 'The store notification should be queued when send_emails is omitted.' );
	}

	public function test_create_order_suppresses_both_order_emails_when_asked() {
		$download = EDD_Helper_Download::create_simple_download();

		$result = ( new Create() )->execute(
			array(
				'email'       => 'no-emails@edd.test',
				'items'       => array(
					array( 'product_id' => $download->ID ),
				),
				'status'      => 'complete',
				'send_emails' => false,
			)
		);

		$this->assertIsArray( $result );
		$this->assertEmpty( edd_get_order_meta( $result['order_id'], '_edd_should_send_order_receipt', true ), 'A receipt was queued for an order which asked for no emails.' );
		$this->assertEmpty( edd_get_order_meta( $result['order_id'], '_edd_should_send_admin_order_notice', true ), 'A store notification was queued for an order which asked for no emails.' );
	}

	public function test_create_order_with_variable_price_id() {
		$download = EDD_Helper_Download::create_variable_download();
		$prices   = edd_get_variable_prices( $download->ID );
		$price_id = (int) array_key_first( $prices );

		$ability = new Create();
		$result  = $ability->execute(
			array(
				'email' => 'variable-buyer@edd.test',
				'items' => array(
					array(
						'product_id' => $download->ID,
						'price_id'   => $price_id,
					),
				),
			)
		);

		$this->assertIsArray( $result );
		$expected = floatval( edd_get_price_option_amount( $download->ID, $price_id ) );
		$this->assertEqualsWithDelta( $expected, $result['subtotal'], 0.001 );

		$order = edd_get_order( $result['order_id'] );
		$items = $order->get_items();
		$this->assertSame( $price_id, (int) $items[0]->price_id );
	}

	public function test_create_order_with_invalid_price_id_fails() {
		$download = EDD_Helper_Download::create_variable_download();

		$ability = new Create();
		$result  = $ability->execute(
			array(
				'email' => 'nobody@edd.test',
				'items' => array(
					array(
						'product_id' => $download->ID,
						'price_id'   => 999,
					),
				),
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_price_id', $result->get_error_code() );
	}

	public function test_create_order_item_discounts_sum_to_order_discount() {
		$download_one = EDD_Helper_Download::create_simple_download();
		$download_two = EDD_Helper_Download::create_simple_download();
		parent::edd()->discount->create(
			array(
				'code'        => 'THIRTYOFF',
				'name'        => 'Thirty Off',
				'amount'      => 33.33,
				'amount_type' => 'percent',
				'status'      => 'active',
			)
		);

		$ability = new Create();
		$result  = $ability->execute(
			array(
				'email'         => 'rounding@edd.test',
				'items'         => array(
					array( 'product_id' => $download_one->ID ),
					array(
						'product_id' => $download_two->ID,
						'quantity'   => 3,
					),
				),
				'discount_code' => 'THIRTYOFF',
			)
		);

		$this->assertIsArray( $result );

		$order = edd_get_order( $result['order_id'] );
		$item_discount_sum = 0.0;
		$item_total_sum    = 0.0;
		foreach ( $order->get_items() as $item ) {
			$item_discount_sum += floatval( $item->discount );
			$item_total_sum    += floatval( $item->total );
		}

		$this->assertEqualsWithDelta( $result['discount'], $item_discount_sum, 0.0001, 'Item discounts must sum to the order discount.' );
		$this->assertEqualsWithDelta( $result['total'], $item_total_sum, 0.0001, 'Item totals must sum to the order total.' );
	}

	public function test_create_order_with_maxed_out_discount_fails() {
		$download    = EDD_Helper_Download::create_simple_download();
		$discount_id = parent::edd()->discount->create(
			array(
				'code'        => 'MAXEDOUT',
				'name'        => 'Maxed Out',
				'amount'      => 10,
				'amount_type' => 'percent',
				'status'      => 'active',
				'max_uses'    => 1,
			)
		);
		edd_update_discount( $discount_id, array( 'use_count' => 1 ) );

		$ability = new Create();
		$result  = $ability->execute(
			array(
				'email'         => 'nobody@edd.test',
				'items'         => array(
					array( 'product_id' => $download->ID ),
				),
				'discount_code' => 'MAXEDOUT',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_discount_maxed_out', $result->get_error_code() );
	}

	public function test_create_order_below_discount_minimum_fails() {
		$download = EDD_Helper_Download::create_simple_download();
		parent::edd()->discount->create(
			array(
				'code'              => 'BIGSPENDER',
				'name'              => 'Big Spender',
				'amount'            => 10,
				'amount_type'       => 'percent',
				'status'            => 'active',
				'min_charge_amount' => 99999,
			)
		);

		$ability = new Create();
		$result  = $ability->execute(
			array(
				'email'         => 'nobody@edd.test',
				'items'         => array(
					array( 'product_id' => $download->ID ),
				),
				'discount_code' => 'BIGSPENDER',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_discount_minimum_not_met', $result->get_error_code() );
	}

	public function test_order_email_is_withheld_without_sensitive_data_capability() {
		$accountant = self::factory()->user->create( array( 'role' => 'shop_accountant' ) );

		// The gate only means anything if the role really holds the ability's
		// capability and lacks the sensitive-data one.
		$this->assertTrue( user_can( $accountant, 'edit_shop_payments' ) );
		$this->assertFalse( user_can( $accountant, 'view_shop_sensitive_data' ) );

		$order_id = parent::edd()->order->create( array( 'status' => 'complete' ) );

		// A withheld field proves nothing if the order never carried one.
		$this->assertNotEmpty( edd_get_order( $order_id )->email );

		wp_set_current_user( $accountant );

		$result = ( new Read() )->execute( array( 'id' => $order_id ) );

		$this->assertArrayNotHasKey( 'email', $result['orders'][0] );
	}

	public function test_order_email_is_returned_with_sensitive_data_capability() {
		$order_id = parent::edd()->order->create( array( 'status' => 'complete' ) );
		$expected = edd_get_order( $order_id )->email;

		$this->assertNotEmpty( $expected );
		$this->assertTrue( current_user_can( 'view_shop_sensitive_data' ) );

		$result = ( new Read() )->execute( array( 'id' => $order_id ) );

		$this->assertSame( $expected, $result['orders'][0]['email'] );
	}

	/**
	 * The order write abilities are destructive and route over POST.
	 *
	 * Creating an order is destructive so a client confirms before recording one;
	 * reverting a completed order is destructive because it subtracts the order
	 * from store earnings. WriteAbility::get_annotations() says why neither is
	 * idempotent.
	 */
	public function test_order_write_abilities_are_destructive_and_not_idempotent() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'The WordPress Abilities API is not available.' );
		}

		$expected = array(
			'readonly'    => false,
			'destructive' => true,
			'idempotent'  => false,
		);

		foreach ( array( 'edd/order-update-status', 'edd/order-create' ) as $name ) {
			$this->assertTrue( wp_has_ability( $name ), "Ability {$name} is not registered." );
			$this->assertSame( $expected, wp_get_ability( $name )->get_meta()['annotations'], "Ability {$name} is annotated wrongly." );
		}
	}

	public function test_order_update_status_rejects_a_refund_record() {
		$order_id  = parent::edd()->order->create( array( 'status' => 'complete' ) );
		$refund_id = edd_refund_order( $order_id );

		// The guard means nothing if the fixture is not actually a refund row.
		$this->assertGreaterThan( 0, $refund_id );
		$this->assertSame( 'refund', edd_get_order( $refund_id )->type );

		$result = ( new UpdateStatus() )->execute(
			array(
				'order_id' => $refund_id,
				'status'   => 'pending',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_order_not_a_sale', $result->get_error_code() );
	}

	public function test_resend_receipt_rejects_a_refund_record() {
		$order_id  = parent::edd()->order->create( array( 'status' => 'complete' ) );
		$refund_id = edd_refund_order( $order_id );

		$this->assertGreaterThan( 0, $refund_id );
		$this->assertSame( 'refund', edd_get_order( $refund_id )->type );

		$result = ( new ResendReceipt() )->execute( array( 'order_id' => $refund_id ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_order_not_a_sale', $result->get_error_code() );
	}

	public function test_order_create_schema_bounds_its_collections() {
		$ability = new Create();
		$method  = new \ReflectionMethod( $ability, 'get_input_schema' );
		$method->setAccessible( true );
		$schema = $method->invoke( $ability );

		// The caller is a model, so an unbounded collection is a plausible
		// accident. The Abilities API enforces these once declared.
		$this->assertArrayHasKey( 'maxItems', $schema['properties']['items'] );
		$this->assertArrayHasKey( 'maximum', $schema['properties']['items']['items']['properties']['quantity'] );
		$this->assertArrayHasKey( 'maxLength', $schema['properties']['note'] );
	}

	/**
	 * The receipt stays suppressed when the after-order actions run inline.
	 *
	 * With edd_use_after_payment_actions filtered false, edd_after_order_actions
	 * fires from inside edd_update_order_status(), so the suppression meta has to
	 * already be in place when the ability transitions the order. The string
	 * 'false' is sanitized to false by require_input() before the check.
	 *
	 * @dataProvider send_emails_false_values
	 * @param bool|string $send_emails The value posted under send_emails.
	 */
	public function test_create_order_suppresses_receipt_when_actions_run_inline( $send_emails ) {
		$download = EDD_Helper_Download::create_simple_download();

		$existing                   = edd_get_email_by( 'email_id', 'order_receipt' );
		$this->receipt_email_status = $existing ? (int) $existing->status : 0;

		EDD_Helper_Email::enable( 'order_receipt' );
		add_filter( 'edd_use_after_payment_actions', '__return_false' );

		// The fixture: the same call with the flag left at its default logs a
		// receipt, so an empty log below means suppression and not an email which
		// never sends here.
		$wanted = ( new Create() )->execute(
			array(
				'email'  => 'inline-receipt@edd.test',
				'items'  => array( array( 'product_id' => $download->ID ) ),
				'status' => 'complete',
			)
		);

		$this->assertIsArray( $wanted );
		$this->assertCount(
			1,
			$this->get_receipt_logs( $wanted['order_id'] ),
			'The fixture is empty: the default logged no receipt, so the assertion below proves nothing.'
		);

		$suppressed = ( new Create() )->execute(
			array(
				'email'       => 'no-inline-receipt@edd.test',
				'items'       => array( array( 'product_id' => $download->ID ) ),
				'status'      => 'complete',
				'send_emails' => $send_emails,
			)
		);

		$this->assertIsArray( $suppressed );
		$this->assertEmpty(
			$this->get_receipt_logs( $suppressed['order_id'] ),
			'A receipt was sent for an order which asked for no emails.'
		);
	}

	public function send_emails_false_values() {
		return array(
			'boolean' => array( false ),
			'string'  => array( 'false' ),
		);
	}

	public function test_order_create_accepts_a_product_id_given_as_a_string() {
		$download = EDD_Helper_Download::create_simple_download();

		$result = ( new Create() )->execute(
			array(
				'email' => 'string-product-id@edd.test',
				'items' => array( array( 'product_id' => (string) $download->ID ) ),
			)
		);

		$this->assertIsArray( $result );

		$order = edd_get_order( $result['order_id'] );
		$this->assertCount( 1, $order->items );
		$this->assertSame( (int) $download->ID, (int) $order->items[0]->product_id );
	}

	public function test_order_create_rejects_items_that_are_not_a_list() {
		$before = edd_count_customers();

		$result = ( new Create() )->execute(
			array(
				'email' => 'bad-items@edd.test',
				'items' => 'not-a-list',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_input', $result->get_error_code() );

		// The message keeps the position within the list, since that is what tells
		// the caller which entry to fix.
		$this->assertStringContainsString( 'items][0]', $result->get_error_message() );

		$this->assertSame( $before, edd_count_customers() );
	}

	public function test_order_create_rejects_an_empty_items_list() {
		$before = edd_count_customers();

		$result = ( new Create() )->execute(
			array(
				'email' => 'empty-items@edd.test',
				'items' => array(),
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_input', $result->get_error_code() );
		$this->assertStringContainsString( 'items', $result->get_error_message() );
		$this->assertSame( $before, edd_count_customers() );
	}

	public function test_order_create_rejects_an_input_the_ability_does_not_accept() {
		$result = ( new Create() )->execute(
			array(
				'items'      => array( array( 'product_id' => 1 ) ),
				'first_name' => 'Jane',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_input', $result->get_error_code() );
		$this->assertStringContainsString( 'first_name', $result->get_error_message() );
	}

	/**
	 * A product ID which does not exist leaves no customer behind.
	 *
	 * The customer is resolved after every input has been validated, which is the
	 * only thing keeping the write out of a request that cannot produce an order.
	 */
	public function test_order_create_with_an_unknown_product_creates_no_customer() {
		$before = edd_count_customers();

		$result = ( new Create() )->execute(
			array(
				'email' => 'unknown-product@edd.test',
				'items' => array( array( 'product_id' => 999999 ) ),
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_product_not_found', $result->get_error_code() );
		$this->assertSame( $before, edd_count_customers() );
		$this->assertFalse( (bool) edd_get_customer_by( 'email', 'unknown-product@edd.test' ) );
	}

	/**
	 * A discount code which does not exist leaves no customer behind.
	 *
	 * The product is valid here, so the customer write is reached only if the
	 * discount is validated after it rather than before.
	 */
	public function test_order_create_with_an_invalid_discount_creates_no_customer() {
		$download = EDD_Helper_Download::create_simple_download();
		$email    = 'invalid-discount@edd.test';

		// The product resolves and the code does not, so the discount is the only refusal.
		$this->assertNotEmpty( edd_get_download( $download->ID ) );
		$this->assertFalse( (bool) edd_get_discount_by_code( 'NOSUCHCODE' ) );
		$this->assertFalse( (bool) edd_get_customer_by( 'email', $email ) );

		$before = edd_count_customers();

		$result = ( new Create() )->execute(
			array(
				'email'         => $email,
				'items'         => array( array( 'product_id' => $download->ID ) ),
				'discount_code' => 'NOSUCHCODE',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_discount', $result->get_error_code() );
		$this->assertSame( $before, edd_count_customers() );
		$this->assertFalse( (bool) edd_get_customer_by( 'email', $email ) );
	}

	/**
	 * A new customer which saves but cannot be read back does not warn against retrying.
	 *
	 * No order exists at that point, so the message has to differ from the
	 * saved-but-unreadable one, which tells the caller not to repeat the request.
	 */
	public function test_order_create_reports_a_new_customer_it_cannot_read_back() {
		$download = EDD_Helper_Download::create_simple_download();

		// Delete the row between the write and the read back.
		$delete_customer = function ( $customer_id ) {
			edd_delete_customer( $customer_id );
		};
		add_action( 'edd_customer_added', $delete_customer );

		$result = ( new Create() )->execute(
			array(
				'email' => 'vanishing-customer@edd.test',
				'items' => array( array( 'product_id' => $download->ID ) ),
			)
		);

		remove_action( 'edd_customer_added', $delete_customer );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_customer_unreadable', $result->get_error_code() );
		$this->assertStringContainsString( 'the order was not placed', $result->get_error_message() );
	}

	public function test_order_note_add_rejects_a_note_that_is_not_text() {
		$order = parent::edd()->order->create_and_get();

		$result = ( new NoteAdd() )->execute(
			array(
				'order_id' => $order->id,
				'note'     => array( 'not', 'text' ),
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_input', $result->get_error_code() );
		$this->assertStringContainsString( 'note', $result->get_error_message() );
	}

	/**
	 * A subclass whose lookup returns false, rather than an error, is refused.
	 *
	 * EDD's getters return false, so the base class cannot assume a non-error
	 * return is a usable record. No shipped leaf does this, so the guard is
	 * exercised through a stub.
	 */
	public function test_note_add_refuses_a_lookup_which_returns_false() {
		$ability = $this->getMockBuilder( \EDD\Abilities\NoteAddAbility::class )
			->onlyMethods( array( 'find_object', 'get_id_key', 'get_object_type' ) )
			->getMockForAbstractClass();

		$ability->method( 'find_object' )->willReturn( false );
		$ability->method( 'get_id_key' )->willReturn( 'order_id' );
		$ability->method( 'get_object_type' )->willReturn( 'order' );

		$result = $ability->execute(
			array(
				'order_id' => 999999,
				'note'     => 'A note against nothing.',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_object_not_found', $result->get_error_code() );

		// Nothing was written for the record which does not exist.
		$this->assertSame( 0, edd_count_notes( array( 'object_id' => 999999, 'object_type' => 'order' ) ) );
	}

	public function test_order_create_with_no_input_returns_an_error() {
		$result = ( new Create() )->execute( null );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_missing_input', $result->get_error_code() );
		$this->assertStringContainsString( 'items', $result->get_error_message() );
	}

	public function test_order_note_add_reports_a_note_it_cannot_read_back() {
		$order = parent::edd()->order->create_and_get();

		// Delete the row between the write and the read back, which is the state
		// the guard exists for: the note saved but cannot be reported on.
		$delete_note = function ( $note_id ) {
			edd_delete_note( $note_id );
		};
		add_action( 'edd_note_added', $delete_note );

		$result = ( new NoteAdd() )->execute(
			array(
				'order_id' => $order->id,
				'note'     => 'A note which will not survive to be read.',
			)
		);

		remove_action( 'edd_note_added', $delete_note );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_saved_not_readable', $result->get_error_code() );
	}

	/**
	 * An order whose row is gone before its totals are derived reports the save.
	 *
	 * The response is built from the order's own rows, so the ability has to
	 * load the order it just inserted. A caller told only that the request
	 * failed would place the order a second time, and the ability stops rather
	 * than carrying on against a record it cannot see.
	 */
	public function test_create_order_reports_a_save_it_cannot_read_back() {
		$download = EDD_Helper_Download::create_simple_download();

		// Delete the order row between the item insert and the read back, which
		// is the state the guard exists for: the order saved but cannot be
		// reported on.
		$order_id     = 0;
		$delete_order = function ( $order_item_id, $data ) use ( &$order_id ) {
			$order_id = (int) $data['order_id'];
			edd_delete_order( $order_id );
		};
		add_action( 'edd_order_item_added', $delete_order, 10, 2 );

		$result = ( new Create() )->execute(
			array(
				'email' => 'unreadable-order@edd.test',
				'items' => array( array( 'product_id' => $download->ID ) ),
			)
		);

		remove_action( 'edd_order_item_added', $delete_order, 10 );

		// The fixture: the order really is unreadable, so the error below is the
		// guard and not some other refusal.
		$this->assertGreaterThan( 0, $order_id );
		$this->assertEmpty( edd_get_order( $order_id ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_saved_not_readable', $result->get_error_code() );

		// Stopping there is what keeps the ability from recording who made a
		// change against an order which is no longer there.
		$this->assertCount( 0, $this->get_ability_notes( $order_id ) );
	}

	/**
	 * An order item ends up in the order's own status.
	 *
	 * The rows go in pending and the status transition carries them, so a
	 * pending order keeps pending items, a processing order gets processing
	 * ones, and a complete order gets complete ones. A complete item on an order which never completed is deliverable,
	 * per `edd_get_deliverable_order_item_statuses()`.
	 */
	public function test_create_order_items_are_created_in_the_order_status() {
		$download = EDD_Helper_Download::create_simple_download();

		foreach ( array( 'pending', 'processing', 'complete' ) as $status ) {
			$result = ( new Create() )->execute(
				array(
					'email'  => "items-{$status}@edd.test",
					'items'  => array( array( 'product_id' => $download->ID ) ),
					'status' => $status,
				)
			);

			$this->assertIsArray( $result );

			// The fixture: the order has to be in that status for the item
			// assertion to measure anything.
			$order = edd_get_order( $result['order_id'] );
			$this->assertNotEmpty( $order, "No order was created for status {$status}." );
			$this->assertSame( $status, $order->status );

			$items = $order->get_items();
			$this->assertNotEmpty( $items, "The order created as {$status} has no items." );

			foreach ( $items as $item ) {
				$this->assertSame( $status, $item->status, "An item on a {$status} order is {$item->status}." );
			}
		}
	}

	/**
	 * Creating an order on the default status counts the sale against the product.
	 *
	 * The order row is inserted pending and transitioned, and the transition is
	 * what moves the items, so the recalculation runs once the order is inside
	 * `edd_get_gross_order_statuses()`. An item inserted complete alongside a
	 * pending order row recalculates before that and counts nothing.
	 */
	public function test_create_order_on_the_default_status_counts_the_sale() {
		$download = EDD_Helper_Download::create_simple_download();

		// The fixture: the product helper seeds a sales figure of its own, so a
		// recalculated 1 below cannot be the value which was already stored.
		$this->assertNotSame( 1, (int) edd_get_download_sales_stats( $download->ID ) );

		$result = ( new Create() )->execute(
			array(
				'email' => 'default-status-order@edd.test',
				'items' => array( array( 'product_id' => $download->ID ) ),
			)
		);

		$this->assertIsArray( $result );

		// The fixture: the sales count only moves for a gross order status, so
		// the default really does have to have completed the order.
		$this->assertSame( 'complete', edd_get_order( $result['order_id'] )->status );

		$this->assertSame( 1, (int) edd_get_download_sales_stats( $download->ID ) );
	}

	/**
	 * Completing a created order recalculates the product it sold.
	 *
	 * `edd_recalculate_order_item_download` hangs off `edd_order_item_updated`,
	 * and the item query bails before that hook when the row it is handed is
	 * unchanged, so an item created complete on a pending order is never
	 * counted.
	 */
	public function test_completing_a_created_pending_order_recalculates_the_product() {
		$download = EDD_Helper_Download::create_simple_download();

		$result = ( new Create() )->execute(
			array(
				'email'  => 'completing-order@edd.test',
				'items'  => array( array( 'product_id' => $download->ID ) ),
				'status' => 'pending',
			)
		);

		$this->assertIsArray( $result );

		// The fixture: the product helper seeds a sales figure of its own, so a
		// recalculated 1 below cannot be the value which was already stored.
		$this->assertNotSame( 1, (int) edd_get_download_sales_stats( $download->ID ) );

		$updates = 0;
		$counter = function () use ( &$updates ) {
			++$updates;
		};

		add_action( 'edd_order_item_updated', $counter );

		( new UpdateStatus() )->execute(
			array(
				'order_id' => $result['order_id'],
				'status'   => 'complete',
			)
		);

		remove_action( 'edd_order_item_updated', $counter );

		$this->assertSame( 1, $updates, 'The order item was never updated, so the product was never recalculated.' );
		$this->assertSame( 1, (int) edd_get_download_sales_stats( $download->ID ) );
	}

	public function test_create_order_reports_that_tax_was_not_calculated() {
		$download = EDD_Helper_Download::create_simple_download();

		$result = ( new Create() )->execute(
			array(
				'email' => 'no-tax@edd.test',
				'items' => array( array( 'product_id' => $download->ID ) ),
			)
		);

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'tax_calculated', $result );
		$this->assertFalse( $result['tax_calculated'] );
		$this->assertEqualsWithDelta( 0.0, $result['tax'], 0.0001 );
	}

	/**
	 * The reported figures come from the order's own rows.
	 *
	 * An extension can add a fee while the order is being built, and a figure
	 * computed from the ability input alone would leave it out of both the
	 * stored total and the response.
	 */
	public function test_create_order_totals_are_derived_from_the_order_rows() {
		$download = EDD_Helper_Download::create_simple_download();
		$price    = floatval( edd_get_download_price( $download->ID ) );

		$add_fee = function ( $item_id, $data ) {
			edd_add_order_adjustment(
				array(
					'object_id'   => $data['order_id'],
					'object_type' => 'order',
					'type'        => 'fee',
					'description' => 'Handling',
					'subtotal'    => 5.00,
					'total'       => 5.00,
				)
			);
		};

		add_action( 'edd_order_item_added', $add_fee, 10, 2 );

		$result = ( new Create() )->execute(
			array(
				'email' => 'derived-totals@edd.test',
				'items' => array( array( 'product_id' => $download->ID ) ),
			)
		);

		remove_action( 'edd_order_item_added', $add_fee, 10 );

		$this->assertIsArray( $result );

		// The fixture: the fee row has to exist for the total to be able to
		// include it.
		$order = edd_get_order( $result['order_id'] );
		$this->assertNotEmpty( $order );
		$this->assertCount( 1, $order->get_fees() );

		$this->assertEqualsWithDelta( $price + 5.00, floatval( $order->total ), 0.0001 );
		$this->assertEqualsWithDelta( floatval( $order->total ), $result['total'], 0.0001 );
		$this->assertEqualsWithDelta( floatval( $order->subtotal ), $result['subtotal'], 0.0001 );
	}

	public function test_order_read_lists_refunds_only_when_the_type_asks_for_them() {
		$order_id  = parent::edd()->order->create( array( 'status' => 'complete' ) );
		$refund_id = edd_refund_order( $order_id );

		// The fixture: a refund record has to exist for either listing to mean anything.
		$this->assertGreaterThan( 0, $refund_id );
		$this->assertSame( 'refund', edd_get_order( $refund_id )->type );

		$sales = ( new Read() )->execute( array( 'limit' => 100 ) );
		$this->assertNotContains( (int) $refund_id, wp_list_pluck( $sales['orders'], 'id' ) );

		$refunds = ( new Read() )->execute(
			array(
				'type'  => 'refund',
				'limit' => 100,
			)
		);

		$this->assertIsArray( $refunds );
		$this->assertContains( (int) $refund_id, wp_list_pluck( $refunds['orders'], 'id' ) );
		foreach ( $refunds['orders'] as $order ) {
			$this->assertSame( 'refund', $order['type'] );
		}
	}

	/**
	 * Gets the receipt email log rows recorded for one order.
	 *
	 * @param int $order_id The order ID.
	 * @return array
	 */
	private function get_receipt_logs( int $order_id ): array {
		$logs = new \EDD\Database\Queries\LogEmail();

		return (array) $logs->query(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
				'email_id'    => 'order_receipt',
			)
		);
	}

	/**
	 * Gets only the notes an ability wrote on an order.
	 *
	 * Order creation records a note of its own, so the ability's audit note has to
	 * be picked out by name rather than counted.
	 *
	 * @param int $order_id The order ID.
	 * @return array
	 */
	private function get_ability_notes( int $order_id ): array {
		$notes = edd_get_notes(
			array(
				'object_id'   => $order_id,
				'object_type' => 'order',
			)
		);

		return array_values(
			array_filter(
				$notes,
				function ( $note ) {
					return false !== strpos( $note->content, 'edd/order-' );
				}
			)
		);
	}
}

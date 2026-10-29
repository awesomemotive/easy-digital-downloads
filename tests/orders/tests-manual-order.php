<?php
/**
 * Manual order product tests.
 */

namespace EDD\Tests\Orders;

use EDD\Tests\Helpers;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests which products the New Order screen accepts as order items.
 *
 * An order item may only name a product the caller is able to sell, which is the rule
 * EDD_Download::can_purchase() applies to the cart. A submitted product that fails it stops the
 * save outright rather than being dropped from it, so the handler never writes a record that
 * disagrees with what was submitted. These cases put product ids straight into the handler's
 * arguments rather than through the product dropdown, so each measures its own rule in isolation.
 *
 * @group edd_orders
 */
class ManualOrder extends EDD_UnitTestCase {

	/**
	 * The order id the manual order handler created, captured from its own action.
	 *
	 * @var int
	 */
	private $manual_order_id = 0;

	/**
	 * The user who authors the products, so no case is decided by the actor being able to
	 * edit a product of its own.
	 *
	 * @var int
	 */
	private $product_owner = 0;

	public function setUp(): void {
		parent::setUp();

		$this->manual_order_id = 0;

		$this->product_owner = $this->factory->user->create( array( 'role' => 'shop_manager' ) );

		add_action( 'edd_post_add_manual_order', array( $this, 'capture_manual_order_id' ) );
	}

	public function tearDown(): void {
		remove_action( 'edd_post_add_manual_order', array( $this, 'capture_manual_order_id' ) );

		unset( $_POST['edd_add_order_nonce'] );

		remove_filter( 'edd_can_purchase_download', '__return_false' );

		edd_clear_errors();
		wp_set_current_user( 0 );

		parent::tearDown();
	}

	/**
	 * Records the order id the manual order handler created.
	 *
	 * @param int $order_id The new order id.
	 */
	public function capture_manual_order_id( $order_id ) {
		$this->manual_order_id = (int) $order_id;
	}

	/**
	 * The premise the cases below rest on: a shop accountant may create an order, and cannot
	 * edit a product another user authored. Both have to hold or those cases prove nothing.
	 */
	public function test_shop_accountant_may_create_orders_but_cannot_edit_another_authors_products() {
		$accountant = $this->factory->user->create( array( 'role' => 'shop_accountant' ) );

		$this->assertTrue(
			user_can( $accountant, 'edit_shop_payments' ),
			'A shop accountant must be able to create an order, or these cases stop at the capability gate.'
		);
		$this->assertFalse(
			user_can( $accountant, 'edit_others_products' ),
			"A shop accountant must not be able to edit another author's product."
		);
		$this->assertFalse(
			user_can( $accountant, 'edit_private_products' ),
			'A shop accountant must not be able to edit a private product.'
		);
	}

	/**
	 * A draft product is not on sale, so a caller who cannot edit it may not record a sale of it.
	 */
	public function test_no_order_is_created_for_a_draft_product() {
		$download = $this->create_download_owned_by_another_author( 'draft' );
		$actor    = $this->sign_in_as( 'shop_accountant' );

		$this->assertFalse(
			user_can( $actor, 'edit_post', $download->ID ),
			'The actor must not be able to edit the product, or its status is not what decides this case.'
		);

		$this->assertNothingWasWritten( $download->ID );
	}

	/**
	 * The same for a private product, which is equally not on sale to a caller who cannot edit it.
	 */
	public function test_no_order_is_created_for_a_private_product() {
		$download = $this->create_download_owned_by_another_author( 'private' );
		$actor    = $this->sign_in_as( 'shop_accountant' );

		$this->assertFalse(
			user_can( $actor, 'edit_post', $download->ID ),
			'The actor must not be able to edit the product, or its status is not what decides this case.'
		);

		$this->assertNothingWasWritten( $download->ID );
	}

	/**
	 * A refused product stops the whole save. Dropping it instead would leave an order whose
	 * stored total, which is taken from the request, no longer describes its items.
	 */
	public function test_no_order_is_created_when_only_one_of_several_products_is_refused() {
		$published = $this->create_download_owned_by_another_author( 'publish' );
		$draft     = $this->create_download_owned_by_another_author( 'draft' );
		$this->sign_in_as( 'shop_accountant' );

		$orders_before    = edd_count_orders();
		$customers_before = edd_count_customers();

		$order_id = $this->submit_manual_order( array( $published->ID, $draft->ID ) );

		$this->assertEmpty( $order_id, 'The handler must not have created an order.' );
		$this->assertSame(
			$orders_before,
			edd_count_orders(),
			'A refused product must not leave a partial order holding the products that were allowed.'
		);
		$this->assertSame(
			$customers_before,
			edd_count_customers(),
			'A refused product must not leave a customer record behind.'
		);
	}

	/**
	 * The paired positive, so the rule cannot be tightened into refusing everything: a
	 * published product is on sale to anyone who may create an order.
	 */
	public function test_published_product_is_added_as_an_order_item() {
		$download = $this->create_download_owned_by_another_author( 'publish' );
		$this->sign_in_as( 'shop_accountant' );

		$order_id = $this->submit_manual_order( $download->ID );

		$this->assertNotEmpty( $order_id, 'The handler must have created the order.' );
		$this->assertSame(
			array( $download->ID ),
			$this->order_item_product_ids( $order_id ),
			'A published product must still be written as an order item.'
		);
	}

	/**
	 * Recording an offline sale of a product that is not publicly listed stays available to a
	 * caller who administers that product, so the rule tracks what the caller may sell rather
	 * than the product's status alone.
	 */
	public function test_private_product_is_added_for_a_caller_who_can_edit_it() {
		$download = $this->create_download_owned_by_another_author( 'private' );
		$actor    = $this->sign_in_as( 'shop_manager' );

		$this->assertTrue(
			user_can( $actor, 'edit_post', $download->ID ),
			'A shop manager must be able to edit the product, or this case measures the wrong thing.'
		);

		$order_id = $this->submit_manual_order( $download->ID );

		$this->assertNotEmpty( $order_id, 'The handler must have created the order.' );
		$this->assertSame(
			array( $download->ID ),
			$this->order_item_product_ids( $order_id ),
			'A caller who can edit a private product must still be able to record a sale of it.'
		);
	}

	/**
	 * An administrator may record a sale of any product, whatever its status, since it can
	 * edit every product on the store.
	 */
	public function test_administrator_can_add_a_draft_product() {
		$download = $this->create_download_owned_by_another_author( 'draft' );
		$actor    = $this->sign_in_as( 'administrator' );

		$this->assertTrue(
			user_can( $actor, 'edit_post', $download->ID ),
			'An administrator must be able to edit every product, or this case measures the wrong thing.'
		);

		$order_id = $this->submit_manual_order( $download->ID );

		$this->assertNotEmpty( $order_id, 'The handler must have created the order.' );
		$this->assertSame(
			array( $download->ID ),
			$this->order_item_product_ids( $order_id ),
			'An administrator must be able to record a sale of a product in any status.'
		);
	}

	/**
	 * Whether a product is on sale today is a separate question from whether a past sale of it
	 * can be recorded, so the availability filter does not decide this path.
	 */
	public function test_availability_filter_does_not_block_a_caller_who_can_edit_the_product() {
		$download = $this->create_download_owned_by_another_author( 'publish' );
		$this->sign_in_as( 'administrator' );

		add_filter( 'edd_can_purchase_download', '__return_false' );

		$this->assertFalse(
			edd_get_download( $download->ID )->can_purchase(),
			'The filter must be in force, or this case proves nothing.'
		);

		$order_id = $this->submit_manual_order( $download->ID );

		$this->assertNotEmpty( $order_id, 'The handler must have created the order.' );
		$this->assertSame(
			array( $download->ID ),
			$this->order_item_product_ids( $order_id ),
			'A caller who can edit the product must still be able to record a sale of it.'
		);
	}

	/**
	 * The rule turns on what the caller may edit, not on status alone, so a caller who authored
	 * a draft product may record a sale of it even without the others-products capabilities.
	 */
	public function test_own_authored_draft_is_added_by_its_author() {
		$actor = $this->sign_in_as( 'shop_accountant' );

		$download = Helpers\EDD_Helper_Download::create_simple_download();
		wp_update_post(
			array(
				'ID'          => $download->ID,
				'post_status' => 'draft',
				'post_author' => $actor,
			)
		);
		$download = get_post( $download->ID );

		$this->assertSame( 'draft', $download->post_status, 'The product fixture must be a draft.' );
		$this->assertSame( $actor, (int) $download->post_author, 'The actor must be the product author.' );
		$this->assertFalse(
			user_can( $actor, 'edit_others_products' ),
			'The actor must lack the others-products capability, or authorship is not what decides this case.'
		);
		$this->assertTrue(
			user_can( $actor, 'edit_post', $download->ID ),
			'A product author must be able to edit its own draft.'
		);

		$order_id = $this->submit_manual_order( $download->ID );

		$this->assertNotEmpty( $order_id, 'The handler must have created the order.' );
		$this->assertSame(
			array( $download->ID ),
			$this->order_item_product_ids( $order_id ),
			'A caller who authored a draft product must be able to record a sale of it.'
		);
	}

	/**
	 * A product id that no longer resolves has always been skipped rather than treated as a
	 * refusal, so a product deleted between opening the screen and submitting it must not stop
	 * the save.
	 */
	public function test_a_product_that_no_longer_exists_does_not_stop_the_save() {
		$published = $this->create_download_owned_by_another_author( 'publish' );
		$missing   = $published->ID + 100000;
		$this->sign_in_as( 'shop_manager' );

		$this->assertEmpty(
			edd_get_download( $missing ),
			'The missing product id must not resolve, or this case measures the wrong thing.'
		);

		$order_id = $this->submit_manual_order( array( $published->ID, $missing ) );

		$this->assertNotEmpty( $order_id, 'A product that no longer exists must not stop the save.' );
		$this->assertSame(
			array( $published->ID ),
			$this->order_item_product_ids( $order_id ),
			'The order must hold the product that does still exist, and nothing for the one that does not.'
		);
	}

	/**
	 * Creates a download with the given status, authored by someone other than the actor.
	 *
	 * @param string $post_status The status to give the product.
	 * @return \WP_Post
	 */
	private function create_download_owned_by_another_author( $post_status ) {
		$download = Helpers\EDD_Helper_Download::create_simple_download();

		wp_update_post(
			array(
				'ID'          => $download->ID,
				'post_status' => $post_status,
				'post_author' => $this->product_owner,
			)
		);

		$download = get_post( $download->ID );

		$this->assertSame(
			$post_status,
			$download->post_status,
			'The product fixture must carry the status under test.'
		);
		$this->assertSame(
			$this->product_owner,
			(int) $download->post_author,
			'The product fixture must belong to another author.'
		);
		$this->assertNotEmpty(
			edd_get_download_files( $download->ID ),
			'The product fixture must have a file, or it is not representative of a real product.'
		);

		return $download;
	}

	/**
	 * Signs in a fresh user holding the given role.
	 *
	 * @param string $role The role to give the user.
	 * @return int The user id.
	 */
	private function sign_in_as( $role ) {
		$user = $this->factory->user->create( array( 'role' => $role ) );

		wp_set_current_user( $user );

		return $user;
	}

	/**
	 * Runs the New Order handler with the given product ids in its arguments.
	 *
	 * @param int|int[] $download_ids The product id or ids to submit.
	 * @return int The order id the handler created, or 0 if it created none.
	 */
	private function submit_manual_order( $download_ids ) {
		$downloads = array();
		foreach ( (array) $download_ids as $download_id ) {
			$downloads[] = array(
				'id'       => $download_id,
				'quantity' => 1,
				'amount'   => 0.00,
			);
		}

		return $this->submit_manual_order_rows( $downloads );
	}

	/**
	 * Runs the New Order handler with the given product rows exactly as given.
	 *
	 * @param array[] $downloads The `downloads` rows to submit.
	 * @return int The order id the handler created, or 0 if it created none.
	 */
	private function submit_manual_order_rows( array $downloads ) {
		$_POST['edd_add_order_nonce'] = wp_create_nonce( 'edd_add_order_nonce' );

		edd_add_manual_order(
			array(
				'edd-payment-status'          => 'complete',
				'edd-new-customer'            => 1,
				'edd-new-customer-email'      => 'manual-order-' . md5( wp_json_encode( $downloads ) ) . '@example.test',
				'edd-new-customer-first-name' => 'Manual',
				'edd-new-customer-last-name'  => 'Order',
				'edd-payment-date'            => '2026-08-16',
				'edd-payment-time-hour'       => '11',
				'edd-payment-time-min'        => '00',
				'gateway'                     => 'manual',
				'ip'                          => '203.0.113.11',
				'downloads'                   => $downloads,
			)
		);

		return $this->manual_order_id;
	}

	/**
	 * Asserts that submitting the given product wrote nothing at all: no order, and no customer
	 * from the new-customer fields the request carries.
	 *
	 * @param int $download_id The product id to submit.
	 */
	private function assertNothingWasWritten( $download_id ) {
		$orders_before    = edd_count_orders();
		$customers_before = edd_count_customers();

		$order_id = $this->submit_manual_order( $download_id );

		$this->assertEmpty( $order_id, 'The handler must not have created an order.' );
		$this->assertSame( $orders_before, edd_count_orders(), 'No order row may be written.' );
		$this->assertSame( $customers_before, edd_count_customers(), 'No customer row may be written.' );
	}

	/**
	 * The product ids the order's items name.
	 *
	 * @param int $order_id The order to read.
	 * @return int[]
	 */
	private function order_item_product_ids( $order_id ) {
		$items = edd_get_order_items(
			array(
				'order_id' => $order_id,
				'number'   => 0, // Berlin's no-limit value.
			)
		);

		return array_map( 'intval', wp_list_pluck( $items, 'product_id' ) );
	}
}

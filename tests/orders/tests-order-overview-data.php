<?php
/**
 * Tests for the order overview's localized payload.
 *
 * The order details and refund details screens mount the same React components, so
 * both have to localize the same key set. The components read keys without fallbacks,
 * so a missing one renders as undefined.
 *
 * @package     EDD\Tests\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Tests\Orders;

use EDD\Admin\Assets\Scripts;
use EDD\Admin\Orders\Overview;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Order overview payload tests.
 *
 * @since 3.7.3
 */
class OrderOverviewData extends EDD_UnitTestCase {

	/**
	 * Order fixture.
	 *
	 * @var \EDD\Orders\Order
	 */
	protected static $order;

	/**
	 * The admin payment views are only loaded behind is_admin(), which the bootstrap never sets.
	 */
	public static function wpSetUpBeforeClass() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/orders.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/payments/refunds.php';

		self::$order = parent::edd()->order->create_and_get();
	}

	public function set_up() {
		parent::set_up();

		wp_deregister_script( 'edd-admin-orders' );
		Scripts::register();
	}

	public function tear_down() {
		wp_deregister_script( 'edd-admin-orders' );
		Scripts::register();

		parent::tear_down();
	}

	public function test_overview_localizes_the_global() {
		$this->assertNotEmpty( $this->get_order_payload() );
	}

	/**
	 * @dataProvider required_keys
	 *
	 * @param string $key Expected top-level key.
	 */
	public function test_overview_includes_required_keys( $key ) {
		$this->assertArrayHasKey( $key, $this->get_order_payload() );
	}

	/**
	 * Data provider of the top-level keys the order overview components consume.
	 *
	 * @return array
	 */
	public function required_keys() {
		$keys = array(
			'items',
			'adjustments',
			'refunds',
			'isAdding',
			'hasQuantity',
			'hasTax',
			'hasDiscounts',
			'isRefundable',
			'isRefundOverride',
			'isWindowPassed',
			'currencyPosition',
			'initialDiscounts',
			'initialDownloads',
			'order',
			'nonces',
			'urls',
			'i18n',
		);

		return array_map(
			function ( $key ) {
				return array( $key );
			},
			$keys
		);
	}

	public function test_edit_discount_url_targets_the_discount_editor() {
		$urls = $this->get_order_payload()['urls'];

		$this->assertStringContainsString( 'page=edd-discounts', $urls['editDiscount'] );
		$this->assertStringContainsString( 'view=edit_discount', $urls['editDiscount'] );
	}

	public function test_view_refund_url_targets_refund_details() {
		$urls = $this->get_order_payload()['urls'];

		$this->assertStringContainsString( 'view=view-refund-details', $urls['viewRefund'] );
	}

	public function test_order_includes_gateway() {
		$this->assertArrayHasKey( 'gateway', $this->get_order_payload()['order'] );
	}

	public function test_refund_details_localizes_i18n() {
		// Without this the refund screen's components have no strings to render.
		$this->assertNotEmpty( $this->get_refund_payload()['i18n'] );
	}

	public function test_refund_details_localizes_urls() {
		// OrderItem builds its product link from urls.editDownload; an empty one yields "&post=123".
		$urls = $this->get_refund_payload()['urls'];

		$this->assertNotEmpty( $urls['editDownload'] );
	}

	public function test_refund_details_and_order_details_share_i18n_keys() {
		$this->assertSame(
			array_keys( $this->get_order_payload()['i18n'] ),
			array_keys( $this->get_refund_payload()['i18n'] )
		);
	}

	public function test_order_net_total_subtracts_refunds() {
		$order = parent::edd()->order->create_and_get();
		edd_refund_order( $order->id );

		$payload = $this->get_payload(
			function () use ( $order ) {
				edd_order_details_overview( edd_get_order( $order->id ) );
			}
		);

		// The refund button is hidden on a fully refunded order, and only the net total says so.
		$this->assertGreaterThan( 0, (float) $payload['order']['total'] );
		$this->assertEquals( 0, $payload['order']['netTotal'] );
	}

	public function test_view_order_screen_skips_the_add_dialog_lists() {
		parent::edd()->discount->create(
			array(
				'code'   => 'VIEWSCREEN',
				'status' => 'active',
			)
		);
		self::factory()->post->create( array( 'post_type' => 'download' ) );

		$payload = $this->get_order_payload();

		$this->assertSame( array(), $payload['initialDiscounts'] );
		$this->assertSame( array(), $payload['initialDownloads'] );
	}

	public function test_initial_discounts_leave_out_an_active_discount_at_its_maximum_uses() {
		parent::edd()->discount->create(
			array(
				'code'        => 'STILLVALID',
				'status'      => 'active',
				'amount'      => 10,
				'amount_type' => 'percent',
			)
		);
		parent::edd()->discount->create(
			array(
				'code'        => 'USEDUP',
				'status'      => 'active',
				'amount'      => 10,
				'amount_type' => 'percent',
				'max_uses'    => 1,
				'use_count'   => 1,
			)
		);

		$codes = wp_list_pluck( Overview::get_initial_discounts(), 'code' );

		// A used-up discount keeps its active status, but the dialog's search leaves it out, so this list must too.
		$this->assertContains( 'STILLVALID', $codes );
		$this->assertNotContains( 'USEDUP', $codes );
	}

	public function test_initial_downloads_list_the_newest_first() {
		$older_download_id = self::factory()->post->create(
			array(
				'post_type' => 'download',
				'post_date' => gmdate( 'Y-m-d H:i:s', strtotime( '-1 year' ) ),
			)
		);
		$newer_download_id = self::factory()->post->create(
			array(
				'post_type' => 'download',
				'post_date' => gmdate( 'Y-m-d H:i:s', strtotime( '-1 hour' ) ),
			)
		);

		$ids = wp_list_pluck( Overview::get_initial_downloads(), 'id' );

		// The list is capped, so on a large store oldest-first would never show a new product.
		$this->assertLessThan(
			array_search( $older_download_id, $ids, true ),
			array_search( $newer_download_id, $ids, true )
		);
	}

	/**
	 * Render a callback and return the decoded eddAdminOrderOverview payload.
	 *
	 * @param callable $render Renders the overview for a given order or refund.
	 * @return array
	 */
	private function get_payload( callable $render ) {
		ob_start();
		$render();
		ob_get_clean();

		$data = wp_scripts()->get_data( 'edd-admin-orders', 'data' );

		preg_match( '/var eddAdminOrderOverview = (.*);/', $data, $matches );

		return json_decode( $matches[1], true );
	}

	/**
	 * Payload for the order details screen.
	 *
	 * @return array
	 */
	private function get_order_payload() {
		return $this->get_payload(
			function () {
				edd_order_details_overview( self::$order );
			}
		);
	}

	/**
	 * Payload for the refund details screen.
	 *
	 * @return array
	 */
	private function get_refund_payload() {
		$refund = edd_get_order( edd_refund_order( self::$order->id ) );

		return $this->get_payload(
			function () use ( $refund ) {
				edd_refund_details_items( $refund );
			}
		);
	}
}

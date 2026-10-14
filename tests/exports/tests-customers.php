<?php
/**
 * Tests for the Customers exporter.
 *
 * @package     EDD\Tests\Exports
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Exports;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Admin\Exports\Exporters\Customers;

/**
 * Customers exporter tests.
 *
 * @since 3.7.2
 *
 * @group exports
 * @group edd_customers
 */
class CustomersExport extends EDD_UnitTestCase {

	/**
	 * Exporter instance.
	 *
	 * @var Customers
	 */
	protected $exporter;

	/**
	 * Set up the exporter for each test.
	 */
	public function set_up() {
		parent::set_up();
		$this->exporter = new Customers();
	}

	/**
	 * Tear down after each test.
	 */
	public function tear_down() {
		$this->exporter = null;
		parent::tear_down();
	}

	/**
	 * Taxonomy-filtered rows carry every CSV column, in header order.
	 */
	public function test_taxonomy_export_rows_match_csv_columns() {
		$user_id     = self::factory()->user->create();
		$customer_id = parent::edd()->customer->create( array( 'user_id' => $user_id ) );
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$category_id = self::factory()->term->create( array( 'taxonomy' => 'download_category' ) );
		wp_set_object_terms( $download_id, $category_id, 'download_category' );

		$order_id = parent::edd()->order->create(
			array(
				'customer_id' => $customer_id,
				'user_id'     => $user_id,
			)
		);
		parent::edd()->order_item->create(
			array(
				'order_id'   => $order_id,
				'product_id' => $download_id,
				'status'     => 'complete',
			)
		);

		$this->exporter->set_properties( array( 'taxonomy' => $category_id ) );
		$data = $this->exporter->get_data();

		$this->assertCount( 1, $data );
		$this->assertSame( array_keys( $this->exporter->csv_cols() ), array_keys( $data[0] ) );
		$this->assertEquals( $customer_id, $data[0]['id'] );
		$this->assertEquals( $user_id, $data[0]['user_id'] );

		$customer = edd_get_customer( $customer_id );
		$this->assertSame( $customer->name, $data[0]['name'] );
		$this->assertSame( $customer->email, $data[0]['email'] );
		$this->assertNotEmpty( $data[0]['date_created'] );
	}
}

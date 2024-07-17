<?php

namespace EDD\Tests\Emails;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

class ListTable extends EDD_UnitTestCase {

	private static $list_table;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		self::$list_table = new \EDD\Admin\Emails\ListTable(
			array(
				'singular' => 'email_template',
				'plural'   => 'email_templates',
				'ajax'     => false,
			)
		);
		self::$list_table->prepare_items();
	}

	public function test_columns_has_name() {
		$this->assertArrayHasKey( 'name', self::$list_table->get_columns() );
		$this->assertEquals( 'Email', self::$list_table->get_columns()['name'] );
	}

	public function test_columns_has_recipient() {
		$this->assertArrayHasKey( 'recipient', self::$list_table->get_columns() );
		$this->assertEquals( 'Recipient', self::$list_table->get_columns()['recipient'] );
	}

	public function test_columns_has_sender() {
		$this->assertArrayHasKey( 'sender', self::$list_table->get_columns() );
		$this->assertEquals( 'Sender', self::$list_table->get_columns()['sender'] );
	}

	public function test_columns_has_context() {
		$this->assertArrayHasKey( 'context', self::$list_table->get_columns() );
		$this->assertEquals( 'Context', self::$list_table->get_columns()['context'] );
	}

	public function test_columns_has_subject() {
		$this->assertArrayHasKey( 'subject', self::$list_table->get_columns() );
		$this->assertEquals( 'Subject', self::$list_table->get_columns()['subject'] );
	}

	public function test_columns_has_status() {
		$this->assertArrayHasKey( 'status', self::$list_table->get_columns() );
		$this->assertEquals( 'Status', self::$list_table->get_columns()['status'] );
	}

	public function test_first_item_is_order_receipt() {
		$this->assertEquals( 'order_receipt', self::$list_table->items[0]->email_id );
	}

	public function test_single_row_has_data() {
		ob_start();
		self::$list_table->single_row( self::$list_table->items[0] );

		$row = ob_get_clean();

		$this->assertStringContainsString( 'Purchase Receipt', $row );
		$this->assertStringContainsString( 'Customer', $row );
		$this->assertStringContainsString( 'EDD Core', $row );
		$this->assertStringContainsString( 'Order', $row );
		$this->assertStringContainsString( 'data-id="order_receipt"', $row );
	}
}

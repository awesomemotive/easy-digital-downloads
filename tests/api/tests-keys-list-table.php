<?php
namespace EDD\Tests\API;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * API List Table tests.
 *
 * @group api
 * @group admin
 */
class List_Table extends EDD_UnitTestCase {

	/**
	 * @var int
	 */
	protected static $user_id;

	/**
	 * @var \EDD_API_Keys_Table
	 */
	protected static $list_table;

	/**
	 * @var array
	 */
	protected static $items;

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// Set the hook_suffix global so the list table can render.
		$GLOBALS['hook_suffix'] = 'download_page_edd-tools';

		// Create a user.
		self::$user_id = self::factory()->user->create( array(
			'role' => 'administrator',
		) );

		// Give the user the required caps.
		$user = new \WP_User( self::$user_id );
		$user->add_cap( 'view_shop_reports' );
		$user->add_cap( 'view_shop_sensitive_data' );
		$user->add_cap( 'manage_shop_discounts' );

		$roles = new \EDD_Roles;
		$roles->add_roles();
		$roles->add_caps();

		// Generate an API Key for the user.
		$_POST['edd_set_api_key'] = 1;
		EDD()->api->generate_api_key( self::$user_id );

		// Require the list table class.
		require_once EDD_PLUGIN_DIR . 'includes/admin/class-api-keys-table.php';

		self::$list_table = new \EDD_API_Keys_Table();
		self::$items      = self::$list_table->query();
	}

	/**
	 * Verify the plural, singular, and ajax properties.
	 */
	public function test_properties() {
		$this->assertSame( 'api-keys', self::$list_table->_args['plural'] );
		$this->assertSame( 'api-key', self::$list_table->_args['singular'] );
		$this->assertFalse( self::$list_table->_args['ajax'] );
	}

	/**
	 * @covers \EDD_API_Keys_Table::get_columns()
	 */
	public function test_get_columns() {
		$columns = self::$list_table->get_columns();

		$this->assertArrayHasKey( 'user', $columns );
		$this->assertArrayHasKey( 'key', $columns );
		$this->assertArrayHasKey( 'token', $columns );
		$this->assertArrayHasKey( 'secret', $columns );
		$this->assertArrayHasKey( 'last_used', $columns );
	}

	/**
	 * @covers \EDD_API_Keys_Table::query()
	 */
	public function test_query() {

		$results = self::$list_table->query();

		$this->assertSame( 1, count( $results ) );
	}

	/**
	 * @covers \EDD_API_Keys_Table::total_items()
	 */
	public function test_total_items() {
		$total_items = self::$list_table->total_items();

		$this->assertSame( 1, $total_items );
	}

	/**
	 * Verify the pagination args.
	 *
	 * @covers \EDD_API_Keys_Table::test_pagination_args()
	 */
	public function test_pagination_args() {
		self::$list_table->prepare_items();
		$this->assertSame( 1, self::$list_table->get_pagination_arg( 'total_items' ) );
		$this->assertSame( 1, self::$list_table->get_pagination_arg( 'total_pages' ) );
		$this->assertSame( 30, self::$list_table->get_pagination_arg( 'per_page' ) );
	}

	/**
	 * @covers \EDD_API_Keys_Table::column_key()
	 */
	public function test_column_key() {
		$key = self::$list_table->column_key( self::$items[ self::$user_id ] );

		$this->assertSame( '<input readonly="readonly" type="text" class="code" value="' . esc_attr( self::$items[ self::$user_id ]['key'] ) . '"/>', $key );
	}

	/**
	 * @covers \EDD_API_Keys_Table::column_token()
	 */
	public function test_column_token() {
		$token = self::$list_table->column_token( self::$items[ self::$user_id ] );

		$this->assertSame( '<input readonly="readonly" type="text" class="code" value="' . esc_attr( self::$items[ self::$user_id ]['token'] ) . '"/>', $token );
	}

	/**
	 * @covers \EDD_API_Keys_Table::column_secret()
	 */
	public function test_column_secret() {
		$secret = self::$list_table->column_secret( self::$items[ self::$user_id ] );

		$this->assertSame( '<input readonly="readonly" type="text" class="code" value="' . esc_attr( self::$items[ self::$user_id ]['secret'] ) . '"/>', $secret );
	}

	/**
	 * @covers \EDD_API_Keys_Table::column_last_used()
	 */
	public function test_column_last_used_never() {
		$last_used = self::$list_table->column_last_used( self::$items[ self::$user_id ] );

		$this->assertSame( 'Never Used', $last_used );
	}

	/**
	 * @covers \EDD_API_Keys_Table::column_last_used()
	 */
	public function test_column_last_used_is_used() {
		// Create a api access log entry.
		edd_add_api_request_log(
			array(
				'user_id' => self::$user_id,
				'api_key' => self::$items[ self::$user_id ]['key'],
				'token'   => self::$items[ self::$user_id ]['token'],
				'version' => 'v2',
				'error'   => '',
				'ip'      => '127.0.0.1',
				'time'    => 0.0001,
				'request' => http_build_query(
					array(
						'edd-api' => 'test',
						'key'     => self::$items[ self::$user_id ]['key'],
						'token'   => self::$items[ self::$user_id ]['token'],
						'query'   => null,
						'type'    => null,
					)
				)
			)
		);

		$items     = self::$list_table->query();
		$last_used = self::$list_table->column_last_used( $items[ self::$user_id ] );
		$this->assertStringContainsString( ' second', $last_used );
		$this->assertStringContainsString( ' ago', $last_used );
	}

	/**
	 * @covers \EDD_API_Keys_Table::column_user()
	 */
	public function test_column_user() {
		$user = self::$list_table->column_user( self::$items[ self::$user_id ] );

		$this->assertStringContainsString( '<a href="user-edit.php?user_id=' . self::$user_id . '"><strong>' . get_userdata( self::$user_id )->user_login . '</strong></a>', $user );
	}
}

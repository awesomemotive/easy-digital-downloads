<?php
namespace EDD\Tests;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use \EDD_Register_Meta;
/**
 * @group edd_meta
 */
class Tests_Register_Meta extends EDD_UnitTestCase {

	protected $payment_id;

	protected $download_id;

	/**
	 * Holds the EDD_Register_Meta instance
	 *
	 * @var \EDD_Register_Meta
	 */
	private $meta_handler;

	public function setUp(): void {
		parent::setUp();

		$this->meta_handler = EDD_Register_Meta::instance();

		$this->payment_id  = Helpers\EDD_Helper_Payment::create_simple_payment();
		$variable_download = Helpers\EDD_Helper_Download::create_variable_download();

		$this->download_id = $variable_download->ID;
	}

	public function tearDown(): void {
		Helpers\EDD_Helper_Payment::delete_payment( $this->payment_id );
		Helpers\EDD_Helper_Download::delete_download( $this->download_id );
		parent::tearDown();
	}

	public function test_intval_wrapper() {
		$this->setExpectedIncorrectUsage( 'add_post_meta()/update_post_meta()' );

		update_post_meta( $this->payment_id, '_edd_payment_customer_id', '90.4' );

		$this->assertEquals( '90', edd_get_payment_meta( $this->payment_id, '_edd_payment_customer_id', true ) );

		update_post_meta( $this->payment_id, '_edd_payment_customer_id', '-1.43' );
		$this->assertEquals( '0', edd_get_payment_meta( $this->payment_id, '_edd_payment_customer_id', true ) );
	}

	public function test_sanitize_price_positive_value() {
		$price = '9';

		$sanitized = $this->meta_handler->sanitize_price( $price );
		$this->assertEquals( 9, $sanitized );
	}

	public function test_sanitize_negative_value() {
		$price = -1;

		$sanitized = $this->meta_handler->sanitize_price( $price );
		$this->assertEquals( 0, $sanitized );
	}

	public function test_sanitize_zero_value() {
		// Test saving a zero value
		$price = 0;

		$sanitized = $this->meta_handler->sanitize_price( $price );
		$this->assertEquals( 0, $sanitized );
	}

	public function test_sanitize_allow_negative_values_value() {
		// Add our filter to allow negative prices.
		add_filter( 'edd_allow_negative_prices', '__return_true' );
		$price = -1;

		$sanitized = $this->meta_handler->sanitize_price( $price );
		$this->assertEquals( -1, $sanitized );

		// Remove our filter.
		remove_filter( 'edd_allow_negative_prices', '__return_true' );
	}

	public function test_sanitize_variable_prices() {
		$variable_prices = array(
			array( 'name'   => 'First Option' ),
			array( 'amount' => 5, 'name' => 'Second Option' ),
			array( 'foo'    => 'bar', 'bar' => 'baz' ),
		);

		$sanitized = $this->meta_handler->sanitize_variable_prices( $variable_prices );
		$this->assertEquals( 2, count( $sanitized ) );
		$this->assertEquals( 0, $sanitized[0]['amount'] );
	}

	public function test_sanitize_variable_prices_with_tags() {
		$variable_prices = array(
			array( 'name' => '<script>alert("hello");</script>First Option' ),
		);

		$sanitized = $this->meta_handler->sanitize_variable_prices( $variable_prices );
		$this->assertEquals( 'First Option', $sanitized[0]['name'] );
	}

	public function test_sanitize_files() {
		$files = array(
			array(
				'file' => '',
				'name' => '',
			),
			array(
				'file' => '  file2.zip  ',
				'name' => 'File 2',
			),
			array(
				'file' => 'file3.zip',
				'name' => '   File 3   ',
			),
		);

		$sanitized = $this->meta_handler->sanitize_files( $files );
		$this->assertEquals( 2, count( $sanitized ) );
		$this->assertEquals( 'file2.zip', $sanitized[1]['file'] );
		$this->assertEquals( 'File 3', $sanitized[2]['name'] );
	}

	/**
	 * An identifier which is not an attachment is dropped.
	 */
	public function test_sanitize_files_drops_an_id_which_is_not_an_attachment() {
		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$row = $this->save_file_row( $this->download_id );

		$this->assertArrayNotHasKey( 'attachment_id', $row );
	}

	/**
	 * A valid attachment ID is saved as an integer.
	 */
	public function test_sanitize_files_casts_a_valid_attachment_id_to_an_integer() {
		$vendor = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		wp_set_current_user( $vendor );

		$attachment_id = $this->create_attachment( $vendor );

		$this->assertSame( $attachment_id, $this->save_file_row( (string) $attachment_id )['attachment_id'] );
	}

	/**
	 * Stores one file row carrying an attachment ID and returns what was persisted.
	 *
	 * @param int $attachment_id The identifier to store.
	 * @return array
	 */
	private function save_file_row( $attachment_id ) {
		update_post_meta(
			$this->download_id,
			'edd_download_files',
			array(
				array(
					'index'         => 0,
					'attachment_id' => $attachment_id,
					'name'          => 'Product',
					'file'          => 'product.zip',
					'condition'     => 'all',
				),
			)
		);

		$files = get_post_meta( $this->download_id, 'edd_download_files', true );

		return reset( $files );
	}

	/**
	 * Creates an attachment owned by the given user.
	 *
	 * @param int $author The owner.
	 * @return int
	 */
	private function create_attachment( $author ) {
		return self::factory()->attachment->create_object(
			array(
				'file'           => 'register-meta-product.zip',
				'post_parent'    => 0,
				'post_author'    => $author,
				'post_status'    => 'inherit',
				'post_mime_type' => 'application/zip',
				'post_title'     => 'Product',
			)
		);
	}

	/**
	 * The two product meta keys the receipt renders are registered for downloads.
	 */
	public function test_receipt_meta_is_registered_for_downloads() {
		// The registrations run on init, before this test starts, so run them again to assert against them.
		$this->meta_handler->register_download_meta();

		$registered = get_registered_meta_keys( 'post', 'download' );

		$this->assertArrayHasKey( 'edd_sku', $registered );
		$this->assertArrayHasKey( 'edd_product_notes', $registered );
		$this->assertTrue( $registered['edd_sku']['show_in_rest'] );
		$this->assertTrue( $registered['edd_product_notes']['show_in_rest'] );

		// Each key gets its own registered callback: the notes keep their newline where the SKU is stripped.
		$this->assertSame( 'SKU-1', sanitize_meta( 'edd_sku', '<b>SKU</b>-1', 'post', 'download' ) );
		$this->assertSame( "First\nSecond", sanitize_meta( 'edd_product_notes', "First\n<b>Second</b>", 'post', 'download' ) );
	}

	/**
	 * A user who can only edit their own products cannot write a download's file list.
	 */
	public function test_a_vendor_cannot_write_the_download_files_meta() {
		// The registrations run on init, before this test starts, so run them again to assert against them.
		$this->meta_handler->register_download_meta();

		$registered = get_registered_meta_keys( 'post', 'download' );

		$this->assertArrayHasKey( 'edd_download_files', $registered, 'Fixture: the key must be registered for downloads.' );
		$this->assertSame(
			array( $this->meta_handler, 'can_write_download_files_meta' ),
			$registered['edd_download_files']['auth_callback'],
			'Fixture: the registration must carry the ownership callback, or the capability below answers from is_protected_meta().'
		);

		$vendor      = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_author' => $vendor,
			)
		);

		wp_set_current_user( $vendor );

		$this->assertTrue( current_user_can( 'edit_product', $download_id ), 'Fixture: the vendor must be able to edit their own download, or the post check refuses the write on its own.' );
		$this->assertFalse( current_user_can( 'edit_others_products' ), 'Fixture: the vendor must lack the broader capability.' );

		$this->assertFalse( current_user_can( 'edit_post_meta', $download_id, 'edd_download_files' ) );
		$this->assertFalse( current_user_can( 'add_post_meta', $download_id, 'edd_download_files' ) );
		$this->assertFalse( current_user_can( 'delete_post_meta', $download_id, 'edd_download_files' ) );
	}

	/**
	 * A user who can edit others' products may write a download's file list.
	 *
	 * The control for the vendor test: it passes with the callback absent as well, and pins that
	 * the callback refuses nobody it should admit.
	 */
	public function test_a_store_manager_can_write_the_download_files_meta() {
		$this->meta_handler->register_download_meta();

		$vendor      = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_author' => $vendor,
			)
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$this->assertTrue( current_user_can( 'edit_others_products' ), 'Fixture: the role must hold the broader capability.' );

		$this->assertTrue( current_user_can( 'edit_post_meta', $download_id, 'edd_download_files' ) );
		$this->assertTrue( current_user_can( 'add_post_meta', $download_id, 'edd_download_files' ) );
		$this->assertTrue( current_user_can( 'delete_post_meta', $download_id, 'edd_download_files' ) );
	}


	/**
	 * No class named in a submitted value may be constructed.
	 *
	 * @dataProvider class_naming_values
	 *
	 * @param string $value A serialized value naming a class.
	 */
	public function test_a_value_naming_a_class_is_refused( $value ) {
		$this->assertTrue( is_serialized( $value ), 'Fixture: the value must be serialized, or the sanitizer never inspects it.' );

		$this->assertFalse( $this->meta_handler->sanitize_array( $value ) );
	}

	public function class_naming_values() {
		return array(
			'object'        => array( 'a:1:{i:0;O:8:"stdClass":1:{s:4:"name";s:3:"set";}}' ),
			'unknown class' => array( 'a:1:{i:0;O:13:"stdClassOther":0:{}}' ),
			'serializable'  => array( 'a:1:{i:0;C:11:"ArrayObject":19:{x:i:0;a:0:{};m:a:0:{}}}' ),
		);
	}

	/**
	 * The same through the registered callback rather than the method, which is the
	 * path a refactor is most likely to break.
	 */
	public function test_bundled_products_meta_stores_no_object() {
		$value = 'a:1:{i:0;O:8:"stdClass":1:{s:4:"name";s:3:"set";}}';

		$this->assertFalse( $this->meta_handler->sanitize_array( $value ), 'Fixture: the sanitizer must refuse this value directly, or the registered callback path proves nothing beyond it.' );

		update_post_meta( $this->download_id, '_edd_bundled_products', $value );

		$stored = get_post_meta( $this->download_id, '_edd_bundled_products', true );

		$this->assertSame( '', $stored );
	}

	/**
	 * The behavior every caller of these two keys depends on.
	 */
	public function test_serialized_scalar_array_still_round_trips() {
		$this->assertSame(
			array( 5, 'abc' ),
			$this->meta_handler->sanitize_array( 'a:2:{i:0;i:5;i:1;s:3:"abc";}' )
		);
	}

	/**
	 * A value the walk cannot finish inspecting is refused rather than trusted, within a bounded amount of memory.
	 *
	 * @dataProvider self_referencing_values
	 *
	 * @param string $value A serialized array that refers back to itself.
	 */
	public function test_a_value_that_refers_to_itself_is_refused( $value ) {
		$this->assertTrue( is_serialized( $value ), 'Fixture: the value must be serialized, or the sanitizer never inspects it.' );
		$this->assertIsArray(
			@unserialize( $value, array( 'allowed_classes' => false ) ), // phpcs:ignore WordPress.PHP.NoSilencedErrors.Discouraged
			'Fixture: the value must unserialize to an array, so a refusal comes from the inspection and not from a parse failure.'
		);

		$usage_before = memory_get_usage();
		if ( function_exists( 'memory_reset_peak_usage' ) ) {
			memory_reset_peak_usage();
		}

		$this->assertFalse( $this->meta_handler->sanitize_array( $value ) );

		if ( function_exists( 'memory_reset_peak_usage' ) ) {
			$this->assertLessThan(
				64 * MB_IN_BYTES,
				memory_get_peak_usage() - $usage_before,
				'Inspecting the value must stay within a bounded amount of memory.'
			);
		}
	}

	public function self_referencing_values() {
		return array(
			'one back reference'          => array( 'a:1:{i:0;R:1;}' ),
			'two back references'         => array( 'a:2:{i:0;R:1;i:1;R:1;}' ),
			'two hundred back references' => array( 'a:200:{' . implode( '', array_map( static function ( $i ) { return "i:{$i};R:1;"; }, range( 0, 199 ) ) ) . '}' ),
		);
	}

	/**
	 * is_serialized() trims before testing, so the unserialize step has to as well.
	 */
	public function test_a_serialized_array_with_surrounding_whitespace_still_round_trips() {
		$this->assertSame(
			array( 5 ),
			$this->meta_handler->sanitize_array( " a:1:{i:0;i:5;} " )
		);
	}

	public function test_an_array_value_is_returned_untouched() {
		$this->assertSame( array( 1, 2 ), $this->meta_handler->sanitize_array( array( 1, 2 ) ) );
	}

	public function test_an_object_value_is_cast_to_an_array() {
		$object       = new \stdClass();
		$object->name = 'kept';

		$this->assertSame( array( 'name' => 'kept' ), $this->meta_handler->sanitize_array( $object ) );
	}

	/**
	 * A class named in a submitted value is never constructed, whichever serialized
	 * form names it.
	 *
	 * The stub implements Serializable without __serialize()/__unserialize(), which
	 * raises a deprecation at declaration time on PHP 8.1 and later that this suite
	 * converts to an exception, so it is declared behind a handler that swallows that level.
	 */
	public function test_no_class_is_constructed_from_a_serializable_value() {
		$stub = Helpers\Stubs\SerializableStub::class;

		if ( ! class_exists( $stub, false ) ) {
			set_error_handler( static function () { return true; }, E_DEPRECATED );
			try {
				require_once EDD_PLUGIN_DIR . 'tests/helpers/stubs/serializable-stub.php';
			} finally {
				restore_error_handler();
			}
		}

		$this->assertTrue( class_exists( $stub, false ), 'Fixture: the stub class must exist, or this test proves nothing.' );

		$value = 'a:1:{i:0;C:' . strlen( $stub ) . ':"' . $stub . '":0:{}}';

		$this->assertTrue( is_serialized( $value ), 'Fixture: the value must be serialized, or the sanitizer never inspects it.' );

		$stub::$ran = false;
		unserialize( $value ); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
		$this->assertTrue( $stub::$ran, 'Fixture: the value must be able to construct the class, or a refusal proves nothing.' );

		$stub::$ran = false;
		$this->meta_handler->sanitize_array( $value );

		$this->assertFalse( $stub::$ran, 'A class named in a submitted value must never be constructed.' );
	}

}

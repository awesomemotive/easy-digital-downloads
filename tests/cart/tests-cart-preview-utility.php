<?php
namespace EDD\Tests\Cart\Preview;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Cart\Preview\Utility;

/**
 * Cart Preview Utility Tests
 *
 * @group edd_cart
 * @group edd_cart_preview
 * @group edd_cart_preview_utility
 */
class PreviewUtility extends EDD_UnitTestCase {

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		// Reset settings.
		edd_update_option( 'enable_cart_preview', false );
	}

	/**
	 * Test is_enabled returns false when setting is false.
	 *
	 * @covers \EDD\Cart\Preview\Utility::is_enabled
	 */
	public function test_is_enabled_returns_false_when_disabled() {
		edd_update_option( 'enable_cart_preview', false );

		$this->assertFalse( Utility::is_enabled() );
	}

	/**
	 * Test is_enabled returns false when setting is not set.
	 *
	 * @covers \EDD\Cart\Preview\Utility::is_enabled
	 */
	public function test_is_enabled_returns_false_when_not_set() {
		edd_delete_option( 'enable_cart_preview' );

		$this->assertFalse( Utility::is_enabled() );
	}

	/**
	 * Test is_enabled returns true when setting is true.
	 *
	 * @covers \EDD\Cart\Preview\Utility::is_enabled
	 */
	public function test_is_enabled_returns_true_when_enabled() {
		edd_update_option( 'enable_cart_preview', true );

		$this->assertTrue( Utility::is_enabled() );
	}

	/**
	 * Test is_enabled returns correct boolean type.
	 *
	 * @covers \EDD\Cart\Preview\Utility::is_enabled
	 */
	public function test_is_enabled_returns_boolean() {
		edd_update_option( 'enable_cart_preview', '1' ); // String value.

		$result = Utility::is_enabled();

		$this->assertIsBool( $result );
	}

	/**
	 * Test load_template loads existing template.
	 *
	 * @covers \EDD\Cart\Preview\Utility::load_template
	 */
	public function test_load_template_loads_existing_template() {
		// Create a temporary test template.
		$template_dir = EDD_PLUGIN_DIR . 'src/Cart/Preview/templates/';

		// Ensure directory exists.
		if ( ! is_dir( $template_dir ) ) {
			$this->markTestSkipped( 'Template directory does not exist yet.' );
		}

		$test_template = $template_dir . 'test-template.php';
		$test_content  = '<?php echo "test content"; ?>';

		// Create test template.
		file_put_contents( $test_template, $test_content );

		// Test loading.
		ob_start();
		Utility::load_template( 'test-template.php' );
		$output = ob_get_clean();

		// Clean up.
		if ( file_exists( $test_template ) ) {
			unlink( $test_template );
		}

		$this->assertEquals( 'test content', $output );
	}

	/**
	 * Test load_template handles non-existent template.
	 *
	 * @covers \EDD\Cart\Preview\Utility::load_template
	 */
	public function test_load_template_handles_non_existent_template() {
		ob_start();
		Utility::load_template( 'non-existent-template.php' );
		$output = ob_get_clean();

		$this->assertEmpty( $output );
	}

	/**
	 * Test load_template with template that has variables.
	 *
	 * @covers \EDD\Cart\Preview\Utility::load_template
	 */
	public function test_load_template_with_variables() {
		$template_dir = EDD_PLUGIN_DIR . 'src/Cart/Preview/templates/';

		// Ensure directory exists.
		if ( ! is_dir( $template_dir ) ) {
			$this->markTestSkipped( 'Template directory does not exist yet.' );
		}

		$test_template = $template_dir . 'test-vars-template.php';
		$test_content  = '<?php $test_var = "hello"; echo $test_var; ?>';

		// Create test template.
		file_put_contents( $test_template, $test_content );

		// Test loading.
		ob_start();
		Utility::load_template( 'test-vars-template.php' );
		$output = ob_get_clean();

		// Clean up.
		if ( file_exists( $test_template ) ) {
			unlink( $test_template );
		}

		$this->assertEquals( 'hello', $output );
	}

	/**
	 * Test load_template with dialog template if it exists.
	 *
	 * @covers \EDD\Cart\Preview\Utility::load_template
	 */
	public function test_load_template_with_dialog_template() {
		$template_path = EDD_PLUGIN_DIR . 'src/Cart/Preview/templates/dialog.php';

		if ( ! file_exists( $template_path ) ) {
			$this->markTestSkipped( 'Dialog template does not exist yet.' );
		}

		ob_start();
		Utility::load_template( 'dialog.php' );
		$output = ob_get_clean();

		// The output should not be empty if the template loads.
		$this->assertNotEmpty( $output );
	}

	/**
	 * Test template path construction.
	 *
	 * @covers \EDD\Cart\Preview\Utility::load_template
	 */
	public function test_template_path_construction() {
		$template_dir = EDD_PLUGIN_DIR . 'src/Cart/Preview/templates/';

		// Ensure directory exists.
		if ( ! is_dir( $template_dir ) ) {
			$this->markTestSkipped( 'Template directory does not exist yet.' );
		}

		// Create a test template with a known name.
		$test_template = $template_dir . 'path-test.php';
		$test_content  = '<?php echo "path test"; ?>';

		file_put_contents( $test_template, $test_content );

		// Test loading.
		ob_start();
		Utility::load_template( 'path-test.php' );
		$output = ob_get_clean();

		// Clean up.
		if ( file_exists( $test_template ) ) {
			unlink( $test_template );
		}

		// Verify the template was found and loaded.
		$this->assertEquals( 'path test', $output );
	}

	/**
	 * Test load_template handles directory traversal attempts.
	 *
	 * @covers \EDD\Cart\Preview\Utility::load_template
	 */
	public function test_load_template_directory_traversal() {
		ob_start();
		Utility::load_template( '../../../wp-config.php' );
		$output = ob_get_clean();

		// Should not load files outside the template directory.
		// The template path will be invalid, so output should be empty.
		$this->assertEmpty( $output );
	}

	/**
	 * Test is_enabled with various truthy values.
	 *
	 * @covers \EDD\Cart\Preview\Utility::is_enabled
	 */
	public function test_is_enabled_with_truthy_values() {
		// Test with string '1'.
		edd_update_option( 'enable_cart_preview', '1' );
		$this->assertTrue( Utility::is_enabled() );

		// Test with integer 1.
		edd_update_option( 'enable_cart_preview', 1 );
		$this->assertTrue( Utility::is_enabled() );

		// Test with boolean true.
		edd_update_option( 'enable_cart_preview', true );
		$this->assertTrue( Utility::is_enabled() );
	}

	/**
	 * Test is_enabled with various falsy values.
	 *
	 * @covers \EDD\Cart\Preview\Utility::is_enabled
	 */
	public function test_is_enabled_with_falsy_values() {
		// Test with string '0'.
		edd_update_option( 'enable_cart_preview', '0' );
		$this->assertFalse( Utility::is_enabled() );

		// Test with integer 0.
		edd_update_option( 'enable_cart_preview', 0 );
		$this->assertFalse( Utility::is_enabled() );

		// Test with boolean false.
		edd_update_option( 'enable_cart_preview', false );
		$this->assertFalse( Utility::is_enabled() );

		// Test with empty string.
		edd_update_option( 'enable_cart_preview', '' );
		$this->assertFalse( Utility::is_enabled() );
	}

	/**
	 * Test load_template multiple times.
	 *
	 * @covers \EDD\Cart\Preview\Utility::load_template
	 */
	public function test_load_template_multiple_times() {
		$template_dir = EDD_PLUGIN_DIR . 'src/Cart/Preview/templates/';

		if ( ! is_dir( $template_dir ) ) {
			$this->markTestSkipped( 'Template directory does not exist yet.' );
		}

		$test_template = $template_dir . 'multi-test.php';
		$test_content  = '<?php echo "multi"; ?>';

		file_put_contents( $test_template, $test_content );

		// Load template multiple times.
		ob_start();
		Utility::load_template( 'multi-test.php' );
		$output1 = ob_get_clean();

		ob_start();
		Utility::load_template( 'multi-test.php' );
		$output2 = ob_get_clean();

		// Clean up.
		if ( file_exists( $test_template ) ) {
			unlink( $test_template );
		}

		$this->assertEquals( 'multi', $output1 );
		$this->assertEquals( 'multi', $output2 );
		$this->assertEquals( $output1, $output2 );
	}
}

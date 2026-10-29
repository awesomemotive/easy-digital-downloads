<?php
/**
 * Tests for EDD\Blocks\Admin\Scripts\localize().
 *
 * @package     EDD\Tests\Blocks
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

namespace EDD\Tests\Blocks;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Utils\Colors;

if ( ! function_exists( '\EDD\Blocks\Admin\Scripts\localize' ) ) {
	require_once EDD_PLUGIN_DIR . 'includes/blocks/includes/admin/scripts.php';
}

/**
 * Tests for the data the blocks package hands the editor script.
 *
 * @since 3.7.3
 *
 * @group blocks
 */
class LocalizedEditorData extends EDD_UnitTestCase {

	/**
	 * What the editor script had localized before the test.
	 *
	 * @var string|false
	 */
	private $original_editor_data;

	public function set_up(): void {
		parent::set_up();

		$this->original_editor_data = wp_scripts()->get_data( 'wp-block-editor', 'data' );
	}

	public function tear_down(): void {
		wp_scripts()->add_data( 'wp-block-editor', 'data', $this->original_editor_data );

		parent::tear_down();
	}

	public function test_the_editor_receives_the_server_list_of_css_value_functions() {
		$this->assertNotEmpty( Colors::CSS_VALUE_FUNCTIONS, 'Fixture: the server list must not be empty.' );

		\EDD\Blocks\Admin\Scripts\localize();

		$data = (string) wp_scripts()->get_data( 'wp-block-editor', 'data' );
		$this->assertSame( 1, preg_match( '/var EDDBlocks = (\{.*\});/s', $data, $matches ), 'Fixture: EDDBlocks must be localized.' );

		$localized = json_decode( $matches[1], true );

		$this->assertSame( Colors::CSS_VALUE_FUNCTIONS, $localized['css_value_functions'] ?? null );
	}
}

<?php
/**
 * Tests for EDD core block functions.
 */

if ( ! function_exists( 'register_block_type' ) ) {
	return;
}

class Tests_Blocks extends EDD_UnitTestCase {

	// EDD core blocks require WordPress 5.8 or higher.
	private $minimum_wp_version = '5.8';

	public function test_blocks_load_only_if_wp_version_met() {
		if ( ! $this->does_blocks_file_exist() ) {
			$this->markTestSkipped( 'This only runs if blocks are loaded.' );
		}

		$expected = $this->wp_version_supports_edd_blocks();

		$this->assertEquals( $expected, defined( 'EDD_BLOCKS_DIR' ) );
	}

	public function test_block_categories_includes_edd() {
		if ( ! class_exists( 'WP_Block_Editor_Context' ) ) {
			$this->markTestSkipped( 'The Block Editor Context is required.' );
		}
		if ( ! $this->does_blocks_file_exist() ) {
			$this->markTestSkipped( 'This only runs if blocks are loaded.' );
		}

		$block_editor_context = new WP_Block_Editor_Context( array( 'name' => 'core/edit-post' ) );
		$block_categories     = get_block_categories( $block_editor_context );
		$categories           = wp_list_pluck( $block_categories, 'slug' );
		$expected             = $this->wp_version_supports_edd_blocks();

		$this->assertEquals( $expected, in_array( 'easy-digital-downloads', $categories, true ) );
	}

	public function test_button_colors_update_button_class() {
		if ( ! $this->does_blocks_file_exist() ) {
			$this->markTestSkipped( 'This only runs if blocks are loaded.' );
		}
		if ( ! $this->wp_version_supports_edd_blocks() ) {
			$this->markTestSkipped( 'This only runs if the current WordPress version supports blocks.' );
		}
		$button_colors = edd_update_option( 'button_colors', array( 'background' => '#333', 'text' => '#fff' ) );

		$this->assertContains( 'has-edd-button-background-color', edd_get_button_color_class() );

		edd_delete_option( 'button_colors' );
	}

	/**
	 * Checks whether the blocks files exist to be tested.
	 *
	 * @return bool
	 */
	private function does_blocks_file_exist() {
		return file_exists( EDD_PLUGIN_DIR . 'includes/blocks/edd-blocks.php' );
	}

	/**
	 * Checks whether the current WordPress version supports EDD blocks in core.
	 *
	 * @return bool
	 */
	private function wp_version_supports_edd_blocks() {
		return version_compare( get_bloginfo( 'version' ), $this->minimum_wp_version, '>=' );
	}
}

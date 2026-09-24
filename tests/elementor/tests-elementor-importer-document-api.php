<?php
/**
 * Document-API save-path tests for the checkout-template Elementor importer, against REAL Elementor.
 *
 * Runs under --extra elementor: the RealElementorFixture restores the initialized
 * \Elementor\Plugin singleton so ElementorImporter::save_with_document_api() drives
 * Elementor's real Document::save() path. Exercises the page-settings whitelist strip,
 * the preservation of existing page settings on a template-only import, the
 * save-failure error surface, and element-ID regeneration.
 *
 * @package     EDD\Tests\Elementor
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.0
 */

namespace EDD\Tests\Elementor;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Checkout\Templates\Importer\ElementorImporter;
use EDD\Tests\Elementor\Support\RealElementorFixture;

/**
 * Save-path coverage for the Elementor importer's Document API integration.
 *
 * @covers \EDD\Pro\Checkout\Templates\Importer\ElementorImporter::save_with_document_api
 *
 * @group elementor
 *
 * @since 3.7.0
 */
class ElementorImporterDocumentApi extends EDD_UnitTestCase {

	use RealElementorFixture;

	/**
	 * The page the importer saves into.
	 *
	 * @var int
	 */
	private $page_id;

	/**
	 * Restore the real Elementor singleton and act as an administrator.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->edd_boot_real_elementor();

		// Guard against a silently-active stub: the Document class whose save() we
		// exercise must be the installed Elementor plugin's real class.
		$document_file = ( new \ReflectionClass( \Elementor\Core\Base\Document::class ) )->getFileName();
		$this->assertStringContainsString( 'plugins/elementor/', $document_file );

		// Reflection bypasses import()'s own permission gate, but Document::save()
		// returns false unless the current user can edit the page, which would land
		// the whitelist/preserve/ID cases in the elementor_save_failed branch.
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		wp_set_current_user( $admin_id );

		$this->page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * Restore the real singleton and drop the forced-failure filter after each test.
	 */
	public function tear_down() {
		remove_filter( 'elementor/document/save/data', array( $this, 'force_document_save_exception' ) );

		if ( ! empty( $GLOBALS['edd_elementor_real_plugin'] ) ) {
			\Elementor\Plugin::$instance = $GLOBALS['edd_elementor_real_plugin'];
		}

		parent::tear_down();
	}

	/**
	 * The page-settings whitelist strips disallowed keys and forces the canvas template.
	 *
	 * @since 3.7.0
	 */
	public function test_page_settings_whitelist_strips_disallowed_keys() {
		$result = $this->invoke_save(
			$this->minimal_content(),
			array(
				'hide_title'      => 'yes',
				'edd_evil_key'    => 'malicious',
				'background_color' => '#abcdef',
			)
		);

		$this->assertTrue( $result );

		$settings = get_post_meta( $this->page_id, '_elementor_page_settings', true );
		$this->assertIsArray( $settings );

		// Disallowed key is stripped by ALLOWED_PAGE_SETTINGS.
		$this->assertArrayNotHasKey( 'edd_evil_key', $settings );

		// Allowed key survives the whitelist.
		$this->assertArrayHasKey( 'hide_title', $settings );
		$this->assertSame( 'yes', $settings['hide_title'] );

		// The forced canvas template lands on the WordPress page template.
		$this->assertSame( 'elementor_canvas', get_post_meta( $this->page_id, '_wp_page_template', true ) );
	}

	/**
	 * Existing page settings survive a template-only import (no visual settings).
	 *
	 * @since 3.7.0
	 */
	public function test_existing_page_settings_survive_template_only_import() {
		$existing = array(
			'background_background' => 'classic',
			'background_color'      => '#0000ff',
		);
		update_post_meta( $this->page_id, '_elementor_page_settings', $existing );

		// No allowed visual settings, so has_visual_settings is false and the
		// importer never writes the settings key, preserving existing meta.
		$result = $this->invoke_save(
			$this->minimal_content(),
			array( 'edd_evil_key' => 'malicious' )
		);

		$this->assertTrue( $result );

		$this->assertSame(
			$existing,
			get_post_meta( $this->page_id, '_elementor_page_settings', true )
		);
		$this->assertSame( 'elementor_canvas', get_post_meta( $this->page_id, '_wp_page_template', true ) );
	}

	/**
	 * A throwing Document::save() surfaces as an elementor_save_failed error.
	 *
	 * The false === $result branch is unreachable from import(): import() short-circuits
	 * with insufficient_permissions before saving when the user cannot edit, and that is
	 * the only way Document::save() returns false. The reachable failure surface is the
	 * exception branch, forced here via the elementor/document/save/data filter, which
	 * fires before Document::save()'s editability check.
	 *
	 * @since 3.7.0
	 */
	public function test_save_failure_surfaces_elementor_save_failed() {
		add_filter( 'elementor/document/save/data', array( $this, 'force_document_save_exception' ) );

		$result = $this->invoke_save( $this->minimal_content(), array() );

		$this->assertInstanceOf( \WP_Error::class, $result );
		$this->assertSame( 'elementor_save_failed', $result->get_error_code() );
	}

	/**
	 * Imported element IDs are regenerated, never reusing the source IDs.
	 *
	 * @since 3.7.0
	 */
	public function test_element_ids_are_regenerated() {
		$source_ids = array( 'aaaa111', 'bbbb222' );
		$content    = array(
			array(
				'id'       => $source_ids[0],
				'elType'   => 'container',
				'settings' => array(),
				'elements' => array(
					array(
						'id'         => $source_ids[1],
						'elType'     => 'widget',
						'widgetType' => 'heading',
						'settings'   => array(),
						'elements'   => array(),
					),
				),
			),
		);

		$result = $this->invoke_save( $content, array() );
		$this->assertTrue( $result );

		$saved = json_decode( get_post_meta( $this->page_id, '_elementor_data', true ), true );
		$this->assertIsArray( $saved );

		$saved_ids = $this->collect_ids( $saved );
		$this->assertCount( 2, $saved_ids );

		foreach ( $saved_ids as $id ) {
			$this->assertNotContains( $id, $source_ids );
			$this->assertMatchesRegularExpression( '/^[a-f0-9]{6,}$/', $id );
		}

		// Regenerated IDs remain unique across the tree.
		$this->assertCount( count( $saved_ids ), array_unique( $saved_ids ) );
	}

	/**
	 * Filter callback that forces Document::save() to throw.
	 *
	 * @since 3.7.0
	 *
	 * @param array $data The save data (unused).
	 * @return array Never returned; always throws.
	 * @throws \Exception Always, to exercise the importer's exception branch.
	 */
	public function force_document_save_exception( $data ) {
		throw new \Exception( 'Forced Document::save() failure for coverage.' );
	}

	/**
	 * Invoke the private save_with_document_api() against the test page.
	 *
	 * @since 3.7.0
	 *
	 * @param array $content  The Elementor elements array.
	 * @param array $settings The template page settings.
	 * @return true|\WP_Error The importer's result.
	 */
	private function invoke_save( array $content, array $settings ) {
		$importer = new ElementorImporter();
		$method   = new \ReflectionMethod( $importer, 'save_with_document_api' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		return $method->invoke( $importer, $this->page_id, $content, $settings );
	}

	/**
	 * A minimal, valid single-element content tree.
	 *
	 * @since 3.7.0
	 *
	 * @return array The Elementor elements array.
	 */
	private function minimal_content(): array {
		return array(
			array(
				'id'       => 'seed001',
				'elType'   => 'container',
				'settings' => array(),
				'elements' => array(),
			),
		);
	}

	/**
	 * Recursively collect every element id in an Elementor content tree.
	 *
	 * @since 3.7.0
	 *
	 * @param array $elements The Elementor elements array.
	 * @return array The collected ids.
	 */
	private function collect_ids( array $elements ): array {
		$ids = array();

		foreach ( $elements as $element ) {
			if ( isset( $element['id'] ) ) {
				$ids[] = $element['id'];
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$ids = array_merge( $ids, $this->collect_ids( $element['elements'] ) );
			}
		}

		return $ids;
	}
}

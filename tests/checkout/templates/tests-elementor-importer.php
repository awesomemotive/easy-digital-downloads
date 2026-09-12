<?php
/**
 * Elementor Importer Tests
 *
 * @package EDD\Tests\Checkout\Templates
 * @since   3.7.0
 */

namespace EDD\Tests\Checkout\Templates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Checkout\Templates\Config\EditorRegistry;
use EDD\Pro\Checkout\Templates\Importer\ElementorImporter;

/**
 * ElementorImporter tests.
 *
 * Tests the functionality of importing checkout templates
 * for Elementor page builder.
 */
class ElementorImporterTest extends EDD_UnitTestCase {

	/**
	 * Test page ID.
	 *
	 * @var int
	 */
	protected static $test_page_id;

	/**
	 * Admin user ID.
	 *
	 * @var int
	 */
	protected static $admin_user_id;

	/**
	 * Set up test fixtures before class runs.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Create admin user.
		self::$admin_user_id = self::factory()->user->create(
			array( 'role' => 'administrator' )
		);

		// Create a test page.
		self::$test_page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Test Checkout Page',
				'post_status' => 'publish',
			)
		);
	}

	/**
	 * Test can_import returns false when Elementor is not installed.
	 */
	public function test_can_import_returns_false_without_elementor() {
		$importer = new ElementorImporter();

		// Elementor is not installed in test environment.
		$this->assertFalse( $importer->can_import() );
	}

	/**
	 * Test import returns error when Elementor is not available.
	 *
	 * Runs before the test that defines ELEMENTOR_VERSION, since
	 * PHP constants cannot be undefined once set.
	 */
	public function test_import_returns_error_without_elementor() {
		$admin_id = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$page_id  = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Elementor Unavailable Test Page',
				'post_status' => 'publish',
			)
		);
		wp_set_current_user( $admin_id );

		$importer = new ElementorImporter();
		$template = array(
			'id'      => 'test-template',
			'name'    => 'Test Template',
			'content' => array( 'test' => 'content' ),
		);

		$result = $importer->import( $template, $page_id );

		$this->assertWPError( $result );
		$this->assertSame( 'elementor_unavailable', $result->get_error_code() );
	}

	/**
	 * Test can_import returns true when Elementor version is sufficient.
	 *
	 * Defines ELEMENTOR_VERSION for the remainder of this test class.
	 * All "without Elementor" tests must be declared above this method.
	 */
	public function test_can_import_returns_true_with_elementor() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			define( 'ELEMENTOR_VERSION', '3.35.9' );
		}

		$importer = new ElementorImporter();

		$this->assertTrue( $importer->can_import() );
	}

	/**
	 * Test can_import skips the Elementor Flexbox Container feature check
	 * when the Elementor runtime is not loaded.
	 *
	 * The container check is fail-open: with a sufficient ELEMENTOR_VERSION
	 * defined but no Elementor classes loaded (the state of this test
	 * environment), can_import() must still return true rather than treating
	 * the missing runtime as the container feature being inactive.
	 */
	public function test_can_import_skips_container_check_without_elementor_runtime() {
		$importer = new ElementorImporter();

		$this->assertTrue( $importer->can_import() );
	}

	/**
	 * Test get_editor_slug returns correct value.
	 */
	public function test_get_editor_slug() {
		$importer = new ElementorImporter();

		$this->assertSame( 'elementor', $importer->get_editor_slug() );
	}

	/**
	 * Test get_min_version returns correct value.
	 */
	public function test_get_min_version() {
		$importer = new ElementorImporter();

		$this->assertSame( EditorRegistry::MIN_ELEMENTOR_VERSION, $importer->get_min_version() );
	}

	/**
	 * Test import returns error when content is empty.
	 */
	public function test_import_returns_error_with_empty_content() {
		wp_set_current_user( self::$admin_user_id );

		$importer = new ElementorImporter();
		$template = array(
			'id'      => 'test-template',
			'name'    => 'Test Template',
			'content' => '',
		);

		$result = $importer->import( $template, self::$test_page_id );

		$this->assertWPError( $result );
		$this->assertSame( 'empty_content', $result->get_error_code() );
	}

	/**
	 * Test import returns error when page ID is invalid.
	 *
	 * When a page doesn't exist, the capability check fails first (defense-in-depth),
	 * so we expect 'insufficient_permissions' rather than 'invalid_page'.
	 */
	public function test_import_returns_error_with_invalid_page() {
		wp_set_current_user( self::$admin_user_id );

		$importer = new ElementorImporter();
		$template = array(
			'id'      => 'test-template',
			'name'    => 'Test Template',
			'content' => array( 'test' => 'content' ),
		);

		$result = $importer->import( $template, 99999 );

		$this->assertWPError( $result );
		// Non-existent pages fail capability check first (can't edit what doesn't exist).
		$this->assertSame( 'insufficient_permissions', $result->get_error_code() );
	}

	/**
	 * Test import returns error when page is not a page post type.
	 */
	public function test_import_returns_error_with_non_page_post_type() {
		wp_set_current_user( self::$admin_user_id );

		// Create a post (not a page).
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'post',
				'post_title'  => 'Test Post',
				'post_status' => 'publish',
			)
		);

		$importer = new ElementorImporter();
		$template = array(
			'id'      => 'test-template',
			'name'    => 'Test Template',
			'content' => array( 'test' => 'content' ),
		);

		$result = $importer->import( $template, $post_id );

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_page', $result->get_error_code() );
	}

	/**
	 * Test import returns error with invalid JSON content.
	 */
	public function test_import_returns_error_with_invalid_json() {
		wp_set_current_user( self::$admin_user_id );

		$importer = new ElementorImporter();
		$template = array(
			'id'      => 'test-template',
			'name'    => 'Test Template',
			'content' => 'invalid json {{{',
		);

		$result = $importer->import( $template, self::$test_page_id );

		$this->assertWPError( $result );
		$this->assertSame( 'invalid_json', $result->get_error_code() );
	}

	/**
	 * Test get_edit_url returns correct format.
	 */
	public function test_get_edit_url_format() {
		$importer = new ElementorImporter();

		$edit_url = $importer->get_edit_url( self::$test_page_id );

		$this->assertStringContainsString( 'post=' . self::$test_page_id, $edit_url );
		$this->assertStringContainsString( 'action=elementor', $edit_url );
	}

	/**
	 * Test that import populates post_content with checkout block comment
	 * when Elementor Plugin class is not loaded (ELEMENTOR_VERSION defined
	 * but no runtime).
	 *
	 * When Elementor runtime is available, $document->save() populates
	 * post_content via save_plain_text(). Without it, the importer reconstructs
	 * the checkout marker set from the element tree and writes it directly.
	 */
	public function test_import_populates_post_content_without_elementor_runtime() {
		wp_set_current_user( self::$admin_user_id );

		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Plain Content Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Pre-existing content that should be cleared.',
			)
		);

		$importer = new ElementorImporter();
		$content  = array(
			array(
				'id'       => 'test_container',
				'elType'   => 'container',
				'isInner'  => false,
				'settings' => array( 'content_width' => 'full' ),
				'elements' => array(
					array(
						'id'         => 'checkout_widget',
						'elType'     => 'widget',
						'widgetType' => 'edd-checkout',
						'settings'   => array(),
						'elements'   => array(),
					),
				),
			),
		);

		$template = array(
			'template_id' => 'test-plain-content',
			'name'        => 'Test Plain Content',
			'version'     => '1.0.0',
			'content'     => $content,
			'settings'    => array(),
		);

		$result = $importer->import( $template, $page_id );

		// Import should succeed.
		$this->assertIsArray( $result );
		$this->assertTrue( $result['success'] );

		// Elementor data should be saved.
		$elementor_data = get_post_meta( $page_id, '_elementor_data', true );
		$this->assertNotEmpty( $elementor_data );
		$this->assertStringContainsString( 'edd-checkout', $elementor_data );

		// Without Elementor runtime, the importer reconstructs the checkout marker
		// set from the element tree so has_block() works. The legacy monolith
		// widget yields the shared outer marker with the reconstructed defaults.
		$post = get_post( $page_id );
		$this->assertStringContainsString( '<!-- wp:edd/checkout {"layout":"","show_discount_form":true,"thumbnail_width":25} /-->', $post->post_content );

		// Verify all Elementor meta is set correctly in fallback path.
		$this->assertSame( 'builder', get_post_meta( $page_id, '_elementor_edit_mode', true ) );
		$this->assertSame( ELEMENTOR_VERSION, get_post_meta( $page_id, '_elementor_version', true ) );
		$this->assertSame( 'elementor_canvas', get_post_meta( $page_id, '_wp_page_template', true ) );
	}

	/**
	 * Test that import produces empty post_content when template has no
	 * edd-checkout widget.
	 */
	public function test_import_empty_post_content_without_checkout_widget() {
		wp_set_current_user( self::$admin_user_id );

		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'No Checkout Widget Page',
				'post_status' => 'publish',
			)
		);

		$importer = new ElementorImporter();
		$content  = array(
			array(
				'id'       => 'test_container',
				'elType'   => 'container',
				'isInner'  => false,
				'settings' => array(),
				'elements' => array(
					array(
						'id'         => 'heading_widget',
						'elType'     => 'widget',
						'widgetType' => 'heading',
						'settings'   => array( 'title' => 'Hello' ),
						'elements'   => array(),
					),
				),
			),
		);

		$template = array(
			'template_id' => 'test-no-checkout',
			'name'        => 'No Checkout Widget',
			'version'     => '1.0.0',
			'content'     => $content,
			'settings'    => array(),
		);

		$result = $importer->import( $template, $page_id );

		$this->assertIsArray( $result );
		$this->assertTrue( $result['success'] );

		// No edd-checkout widget means post_content should be empty.
		$post = get_post( $page_id );
		$this->assertSame( '', $post->post_content );
	}

	/**
	 * Test that import creates a revision of the pre-import content.
	 *
	 * create_revision() lives on ElementorImporter (moved out of the abstract
	 * importer) and runs before the page content is overwritten, so users can
	 * restore the previous checkout page from the revision.
	 */
	public function test_import_creates_revision() {
		wp_set_current_user( self::$admin_user_id );

		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Revision Test Page',
				'post_status'  => 'publish',
				'post_content' => 'Original checkout content that should be preserved in a revision.',
			)
		);

		$importer = new ElementorImporter();
		$content  = array(
			array(
				'id'       => 'test_container',
				'elType'   => 'container',
				'isInner'  => false,
				'settings' => array(),
				'elements' => array(
					array(
						'id'         => 'checkout_widget',
						'elType'     => 'widget',
						'widgetType' => 'edd-checkout',
						'settings'   => array(),
						'elements'   => array(),
					),
				),
			),
		);

		$template = array(
			'template_id' => 'test-revision',
			'name'        => 'Revision Template',
			'version'     => '1.0.0',
			'content'     => $content,
			'settings'    => array(),
		);

		$result = $importer->import( $template, $page_id );

		$this->assertIsArray( $result );
		$this->assertTrue( $result['success'] );

		// A revision holding the pre-import content should exist.
		$revisions = wp_get_post_revisions( $page_id );
		$this->assertNotEmpty( $revisions );

		// The success response should report the created revision.
		$this->assertNotNull( $result['revision_id'] );
		$this->assertArrayHasKey( $result['revision_id'], $revisions );
	}

	/**
	 * Test that the import is refused when revisions are disabled for the page.
	 *
	 * Overwriting the checkout page without a restore point is a data-loss risk,
	 * so the importer must return a revisions_disabled error and leave the page
	 * content untouched instead of importing.
	 *
	 * @since 3.7.0
	 */
	public function test_import_blocked_when_revisions_disabled() {
		wp_set_current_user( self::$admin_user_id );

		$page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Revisions Disabled Page',
				'post_status'  => 'publish',
				'post_content' => 'Existing checkout content that must be preserved.',
			)
		);

		// Disable revisions so wp_revisions_enabled() returns false.
		add_filter( 'wp_revisions_to_keep', '__return_zero' );

		$importer = new ElementorImporter();
		$content  = array(
			array(
				'id'       => 'test_container',
				'elType'   => 'container',
				'isInner'  => false,
				'settings' => array(),
				'elements' => array(
					array(
						'id'         => 'checkout_widget',
						'elType'     => 'widget',
						'widgetType' => 'edd-checkout',
						'settings'   => array(),
						'elements'   => array(),
					),
				),
			),
		);

		$template = array(
			'template_id' => 'test-revisions-disabled',
			'name'        => 'Revisions Disabled Template',
			'version'     => '1.0.0',
			'content'     => $content,
			'settings'    => array(),
		);

		$result = $importer->import( $template, $page_id );

		remove_all_filters( 'wp_revisions_to_keep' );

		$this->assertWPError( $result );
		$this->assertSame( 'revisions_disabled', $result->get_error_code() );

		// The page must not have been overwritten with Elementor data.
		$this->assertEmpty( get_post_meta( $page_id, '_elementor_data', true ) );
	}

	/**
	 * Test that the importer finds a deeply nested edd-checkout widget.
	 */
	public function test_import_finds_deeply_nested_checkout_widget() {
		wp_set_current_user( self::$admin_user_id );

		$page_id = self::factory()->post->create(
			array(
				'post_type'   => 'page',
				'post_title'  => 'Deep Nest Page',
				'post_status' => 'publish',
			)
		);

		$importer = new ElementorImporter();
		$content  = array(
			array(
				'id'       => 'outer',
				'elType'   => 'container',
				'isInner'  => false,
				'settings' => array(),
				'elements' => array(
					array(
						'id'       => 'middle',
						'elType'   => 'container',
						'isInner'  => true,
						'settings' => array(),
						'elements' => array(
							array(
								'id'       => 'inner',
								'elType'   => 'container',
								'isInner'  => true,
								'settings' => array(),
								'elements' => array(
									array(
										'id'         => 'deep_checkout',
										'elType'     => 'widget',
										'widgetType' => 'edd-checkout',
										'settings'   => array(),
										'elements'   => array(),
									),
								),
							),
						),
					),
				),
			),
		);

		$template = array(
			'template_id' => 'test-deep-nest',
			'name'        => 'Deep Nested Checkout',
			'version'     => '1.0.0',
			'content'     => $content,
			'settings'    => array(),
		);

		$result = $importer->import( $template, $page_id );

		$this->assertIsArray( $result );
		$this->assertTrue( $result['success'] );

		$post = get_post( $page_id );
		$this->assertStringContainsString( '<!-- wp:edd/checkout {"layout":"","show_discount_form":true,"thumbnail_width":25} /-->', $post->post_content );
	}

	/**
	 * Test that {{asset:<id>}} tokens are localized in Elementor media controls and string values.
	 *
	 * The packaging contract references bundled images by `{{asset:<id>}}` token. Media controls
	 * (arrays with a `url`) get url + id + source updated to link to the imported attachment;
	 * embedded tokens in strings (e.g. Custom CSS) are replaced; non-mapped tokens are left alone.
	 *
	 * @since 3.7.0
	 */
	public function test_localize_asset_urls_rewrites_asset_tokens_and_strings() {
		$token = '{{asset:badge}}';
		$local = 'https://store.example.com/wp-content/uploads/2026/07/badge.png';
		$map   = array(
			$token => array(
				'id'  => 4242,
				'url' => $local,
			),
		);

		$content = array(
			array(
				'id'       => 'abc123',
				'elType'   => 'widget',
				'settings' => array(
					'image'          => array(
						'url' => $token,
						'id'  => 0,
						'alt' => 'Badge',
					),
					'edd_custom_css' => 'selector{background:url(' . $token . ');}',
					'keep'           => array(
						'url' => '{{asset:other}}',
						'id'  => 7,
					),
				),
			),
		);

		$importer = new ElementorImporter();
		$method   = new \ReflectionMethod( ElementorImporter::class, 'localize_asset_urls' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }
		$result = $method->invoke( $importer, $content, $map, 0 );

		$image = $result[0]['settings']['image'];
		$this->assertSame( $local, $image['url'] );
		$this->assertSame( 4242, $image['id'] );
		$this->assertSame( 'library', $image['source'] );
		$this->assertSame( 'Badge', $image['alt'], 'Existing control keys should be preserved.' );

		$this->assertStringContainsString( 'url(' . $local . ')', $result[0]['settings']['edd_custom_css'] );
		$this->assertStringNotContainsString( $token, wp_json_encode( $result ), 'The localized asset token must be replaced.' );

		// A media control whose token was not localized must be left untouched.
		$this->assertSame( '{{asset:other}}', $result[0]['settings']['keep']['url'] );
		$this->assertSame( 7, $result[0]['settings']['keep']['id'] );
	}

	/**
	 * Test that Custom CSS is held back from the data handed to Elementor's save.
	 *
	 * Elementor's Document::save() runs the tree through wp_kses_post() for any user without
	 * `unfiltered_html`, which rewrites a CSS child combinator to `&gt;` — the template imports but
	 * lays out wrong. Nothing may be left in the tree for that pass to mangle.
	 *
	 * @since 3.7.0
	 */
	public function test_extract_custom_css_holds_css_back_from_the_save() {
		$parent_css = '#edd_checkout_user_info>legend{grid-column:1/-1;}';
		$child_css  = '#edd_cc_address>#edd-card-zip-wrap{grid-column:2/3;}';

		$content = array(
			array(
				'id'       => 'parent1',
				'elType'   => 'edd-checkout-box',
				'settings' => array(
					'edd_custom_css' => $parent_css,
					'keep'           => 'untouched',
				),
				'elements' => array(
					array(
						'id'       => 'child1',
						'elType'   => 'edd-checkout-box',
						'settings' => array( 'edd_custom_css' => $child_css ),
					),
				),
			),
		);

		$importer = new ElementorImporter();
		$method   = new \ReflectionMethod( ElementorImporter::class, 'extract_custom_css' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$map    = array();
		$args   = array( $content, &$map );
		$result = $method->invokeArgs( $importer, $args );

		$this->assertSame(
			array(
				'parent1' => array( 'edd_custom_css' => $parent_css ),
				'child1'  => array( 'edd_custom_css' => $child_css ),
			),
			$map,
			"EDD's own control is collected by element ID, at any depth."
		);

		$this->assertSame( '', $result[0]['settings']['edd_custom_css'] );
		$this->assertSame( '', $result[0]['elements'][0]['settings']['edd_custom_css'] );
		$this->assertSame( 'untouched', $result[0]['settings']['keep'], 'Other settings must be left alone.' );

		// The tree handed to Elementor must survive its sanitizer unchanged.
		$kses = map_deep(
			$result,
			function ( $value ) {
				return is_bool( $value ) || is_null( $value ) ? $value : wp_kses_post( $value );
			}
		);
		$this->assertSame( $result, $kses, 'Nothing left in the tree may be altered by wp_kses_post().' );
	}

	/**
	 * Test that the held-back CSS is written to the saved document intact.
	 *
	 * Meta writes are not kses-filtered, so the combinators survive. EDD's control strips `<style>`
	 * tags whenever it prints, so its value is written through verbatim.
	 *
	 * @since 3.7.0
	 */
	public function test_restore_custom_css_writes_css_without_escaping() {
		$page_id = self::factory()->post->create( array( 'post_type' => 'page' ) );
		$saved   = array(
			array(
				'id'       => 'parent1',
				'elType'   => 'edd-checkout-box',
				'settings' => array( 'edd_custom_css' => '' ),
				'elements' => array(
					array(
						'id'       => 'child1',
						'elType'   => 'edd-checkout-box',
						'settings' => array( 'edd_custom_css' => '' ),
					),
				),
			),
		);

		update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $saved ) ) );
		update_post_meta( $page_id, '_elementor_css', array( 'time' => 1 ) );

		$box_css   = '/* <select> chevron */ #edd_checkout_user_info>legend{grid-column:1/-1;}';
		$child_css = '.bs-footsub>p{margin:0;}';
		$importer  = new ElementorImporter();
		$method    = new \ReflectionMethod( ElementorImporter::class, 'restore_custom_css' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }
		$method->invoke(
			$importer,
			$page_id,
			array(
				'parent1' => array( 'edd_custom_css' => $box_css ),
				'child1'  => array( 'edd_custom_css' => $child_css ),
			)
		);

		$stored = json_decode( get_post_meta( $page_id, '_elementor_data', true ), true );
		$box    = $stored[0]['settings']['edd_custom_css'];
		$child  = $stored[0]['elements'][0]['settings']['edd_custom_css'];

		$this->assertSame( $box_css, $box, "EDD's own control is written through verbatim, comments included." );
		$this->assertStringNotContainsString( '&gt;', $box, 'The CSS must not be HTML-escaped.' );

		$this->assertSame( $child_css, $child, 'A nested box takes its own CSS, combinator intact.' );

		$this->assertSame( '', get_post_meta( $page_id, '_elementor_css', true ), 'The stale stylesheet must be dropped so it rebuilds with this CSS.' );

		wp_delete_post( $page_id, true );
	}

	/**
	 * Test that the import aborts when the pre-import revision cannot be created.
	 *
	 * create_revision() collapses both a wp_save_post_revision() WP_Error and a
	 * no-fallback zero into a falsy return, so a falsy value means the current
	 * checkout content could not be backed up. The importer must bail with a
	 * recoverable revision_failed error and leave the page untouched rather than
	 * overwrite content it cannot restore.
	 *
	 * @since 3.7.0
	 */
	public function test_import_aborts_when_revision_creation_fails() {
		if ( ! defined( 'ELEMENTOR_VERSION' ) ) {
			define( 'ELEMENTOR_VERSION', '3.35.9' );
		}

		wp_set_current_user( self::$admin_user_id );

		$original_content = 'Original checkout content that must survive a failed backup.';
		$page_id          = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Revision Failure Page',
				'post_status'  => 'publish',
				'post_content' => $original_content,
			)
		);

		// The override models a genuine wp_save_post_revision() failure with no
		// prior revision to fall back to (both collapse to a falsy return).
		$importer = new class() extends ElementorImporter {
			public function create_revision( int $page_id ) {
				return false;
			}
		};

		$content = array(
			array(
				'id'       => 'test_container',
				'elType'   => 'container',
				'isInner'  => false,
				'settings' => array(),
				'elements' => array(
					array(
						'id'         => 'checkout_widget',
						'elType'     => 'widget',
						'widgetType' => 'edd-checkout',
						'settings'   => array(),
						'elements'   => array(),
					),
				),
			),
		);

		$template = array(
			'template_id' => 'test-revision-failure',
			'name'        => 'Revision Failure Template',
			'version'     => '1.0.0',
			'content'     => $content,
			'settings'    => array(),
		);

		$result = $importer->import( $template, $page_id );

		$this->assertWPError( $result );
		$this->assertSame( 'revision_failed', $result->get_error_code() );

		// The page must be left completely untouched — no content overwrite and no
		// Elementor data written — because there is no restore point.
		$post = get_post( $page_id );
		$this->assertSame( $original_content, $post->post_content );
		$this->assertEmpty( get_post_meta( $page_id, '_elementor_data', true ) );
	}
}

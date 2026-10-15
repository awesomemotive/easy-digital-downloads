<?php
/**
 * Abstract Importer Tests
 *
 * @package EDD\Tests\Checkout\Templates
 * @since   3.7.0
 */

namespace EDD\Tests\Checkout\Templates;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * AbstractImporter tests.
 *
 * Tests the shared functionality in AbstractImporter using
 * TestableImporter as a concrete implementation.
 */
class AbstractImporterTest extends EDD_UnitTestCase {

	/**
	 * Test page ID.
	 *
	 * @var int
	 */
	protected static $test_page_id;

	/**
	 * Set up test fixtures before class runs.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// Create a test page.
		self::$test_page_id = self::factory()->post->create(
			array(
				'post_type'    => 'page',
				'post_title'   => 'Test Checkout Page',
				'post_status'  => 'publish',
				'post_content' => 'Initial content',
			)
		);
	}

	/**
	 * Test site token replacement - site name.
	 */
	public function test_site_token_replacement_site_name() {
		update_option( 'blogname', 'Test Site Name' );

		$importer = new TestableImporter();
		$content  = 'Welcome to {{site_name}}!';
		$result   = $importer->public_replace_site_tokens( $content );

		$this->assertSame( 'Welcome to Test Site Name!', $result );
	}

	/**
	 * Test site token replacement - site URL.
	 */
	public function test_site_token_replacement_site_url() {
		$importer = new TestableImporter();
		$content  = 'Visit us at {{site_url}}';
		$result   = $importer->public_replace_site_tokens( $content );

		$this->assertStringContainsString( home_url(), $result );
	}

	/**
	 * Test that {{site_url}} token preserves literal ampersands in query strings.
	 *
	 * esc_url() HTML-entity-encodes & to &#038;, which corrupts URLs when
	 * the token is consumed inside JSON-encoded Elementor data or any
	 * non-HTML render path. esc_url_raw() must be used instead.
	 */
	public function test_site_url_token_preserves_query_ampersands() {
		$filter_callback = static function ( string $url ): string {
			return 'https://example.com/?a=1&b=2';
		};

		add_filter( 'home_url', $filter_callback );

		$importer = new TestableImporter();
		$result   = $importer->public_replace_site_tokens( '{{site_url}}' );

		remove_filter( 'home_url', $filter_callback );

		// The literal ampersand must survive — HTML entities must not appear.
		$this->assertStringContainsString( '&b=2', $result );
		$this->assertStringNotContainsString( '&#038;', $result );
		$this->assertStringNotContainsString( '&amp;', $result );
	}

	/**
	 * Test site token replacement - current year.
	 */
	public function test_site_token_replacement_current_year() {
		$importer = new TestableImporter();
		$content  = 'Copyright {{current_year}}';
		$result   = $importer->public_replace_site_tokens( $content );

		$this->assertSame( 'Copyright ' . gmdate( 'Y' ), $result );
	}

	/**
	 * Test site token replacement - site tagline.
	 */
	public function test_site_token_replacement_site_tagline() {
		update_option( 'blogdescription', 'Just another site' );

		$importer = new TestableImporter();
		$content  = '{{site_tagline}}';
		$result   = $importer->public_replace_site_tokens( $content );

		$this->assertSame( 'Just another site', $result );
	}

	/**
	 * Test site token replacement - site logo resolves the theme's custom logo.
	 *
	 * @since 3.7.1
	 */
	public function test_site_token_replacement_site_logo_uses_custom_logo() {
		$attachment_id = self::factory()->attachment->create_object(
			array(
				'file'           => 'custom-logo.png',
				'post_parent'    => 0,
				'post_mime_type' => 'image/png',
				'post_title'     => 'Custom Logo',
			)
		);
		set_theme_mod( 'custom_logo', $attachment_id );

		$expected = wp_get_attachment_image_url( $attachment_id, 'full' );
		$this->assertNotEmpty( $expected, 'The logo attachment resolved to no URL, so the token assertion would compare two empty strings.' );

		$importer = new TestableImporter();

		$this->assertSame( $expected, $importer->public_replace_site_tokens( '{{site_logo}}' ) );
	}

	/**
	 * Test site token replacement - site logo falls back to the site icon.
	 *
	 * @since 3.7.1
	 */
	public function test_site_token_replacement_site_logo_falls_back_to_site_icon() {
		$this->assertEmpty( get_theme_mod( 'custom_logo' ), 'A custom logo would short-circuit the fallback this test measures.' );

		$attachment_id = self::factory()->attachment->create_object(
			array(
				'file'           => 'site-icon.png',
				'post_parent'    => 0,
				'post_mime_type' => 'image/png',
				'post_title'     => 'Site Icon',
			)
		);
		update_option( 'site_icon', $attachment_id );

		$expected = wp_get_attachment_image_url( $attachment_id, 'full' );
		$this->assertNotEmpty( $expected, 'The icon attachment resolved to no URL, so the token assertion would compare two empty strings.' );

		$importer = new TestableImporter();

		$this->assertSame( $expected, $importer->public_replace_site_tokens( '{{site_logo}}' ) );
	}

	/**
	 * Test site token replacement - site logo is empty with no logo and no icon.
	 *
	 * @since 3.7.1
	 */
	public function test_site_token_replacement_site_logo_is_empty_without_logo_or_icon() {
		$this->assertEmpty( get_theme_mod( 'custom_logo' ) );
		$this->assertEmpty( get_option( 'site_icon' ) );

		$importer = new TestableImporter();

		$this->assertSame( 'Logo: ', $importer->public_replace_site_tokens( 'Logo: {{site_logo}}' ) );
	}

	/**
	 * Test content validation with empty content.
	 */
	public function test_validate_content_empty() {
		$importer = new TestableImporter();
		$template = array( 'content' => '' );
		$result   = $importer->public_validate_content( $template );

		$this->assertWPError( $result );
		$this->assertSame( 'empty_content', $result->get_error_code() );
	}

	/**
	 * Test content validation with valid content.
	 */
	public function test_validate_content_valid() {
		$importer = new TestableImporter();
		$template = array( 'content' => array( 'test' => 'content' ) );
		$result   = $importer->public_validate_content( $template );

		$this->assertTrue( $result );
	}

	/**
	 * Test metadata storage.
	 */
	public function test_store_metadata() {
		$importer = new TestableImporter();
		$template = array(
			'template_id' => 'test-template-123',
			'name'        => 'Test Template',
			'version'     => '1.5.0',
		);

		$importer->public_store_metadata( self::$test_page_id, $template );

		$this->assertSame( 'test-template-123', get_post_meta( self::$test_page_id, '_edd_checkout_template_id', true ) );
		$this->assertSame( 'Test Template', get_post_meta( self::$test_page_id, '_edd_checkout_template_name', true ) );
		$this->assertSame( '1.5.0', get_post_meta( self::$test_page_id, '_edd_checkout_template_version', true ) );
		$this->assertNotEmpty( get_post_meta( self::$test_page_id, '_edd_checkout_template_imported', true ) );
		$this->assertSame( 'testable', get_post_meta( self::$test_page_id, '_edd_checkout_template_editor', true ) );
	}

	/**
	 * Test success response format.
	 */
	public function test_success_response_format() {
		$importer = new TestableImporter();
		$result   = $importer->public_success_response( self::$test_page_id );

		$this->assertTrue( $result['success'] );
		$this->assertSame( self::$test_page_id, $result['page_id'] );
		$this->assertArrayHasKey( 'edit_url', $result );
		$this->assertArrayHasKey( 'message', $result );
	}

	/**
	 * Test success response with custom message.
	 */
	public function test_success_response_custom_message() {
		$importer = new TestableImporter();
		$result   = $importer->public_success_response( self::$test_page_id, 'Custom success!' );

		$this->assertSame( 'Custom success!', $result['message'] );
	}

	/**
	 * Test success response includes revision_id when a valid revision ID is provided.
	 *
	 * @since 3.7.0
	 */
	public function test_success_response_includes_revision_id_when_provided() {
		$importer = new TestableImporter();
		$result   = $importer->public_success_response( self::$test_page_id, '', 42 );

		$this->assertSame( 42, $result['revision_id'] );
	}

	/**
	 * Test success response sets revision_id to null when zero is passed.
	 *
	 * @since 3.7.0
	 */
	public function test_success_response_revision_id_null_when_zero() {
		$importer = new TestableImporter();
		$result   = $importer->public_success_response( self::$test_page_id, '', 0 );

		$this->assertNull( $result['revision_id'] );
	}

	/**
	 * Test success response sets revision_id to null when false is passed.
	 *
	 * @since 3.7.0
	 */
	public function test_success_response_revision_id_null_when_false() {
		$importer = new TestableImporter();
		$result   = $importer->public_success_response( self::$test_page_id, '', false );

		$this->assertNull( $result['revision_id'] );
	}

	/**
	 * Test error response format.
	 */
	public function test_error_response_format() {
		$importer = new TestableImporter();
		$result   = $importer->public_error_response( 'test_error', 'Test error message', 400 );

		$this->assertWPError( $result );
		$this->assertSame( 'test_error', $result->get_error_code() );
		$this->assertSame( 'Test error message', $result->get_error_message() );
		$this->assertSame( 400, $result->get_error_data()['status'] );
	}

	/**
	 * Test asset sideload returns an empty map when the template has no images.
	 *
	 * @since 3.7.0
	 */
	public function test_sideload_assets_empty_when_no_images() {
		$importer = new TestableImporter();

		$this->assertSame( array(), $importer->public_sideload_template_assets( array() ) );
		$this->assertSame( array(), $importer->public_sideload_template_assets( array( 'assets' => array( 'images' => array() ) ) ) );
	}

	/**
	 * Test asset sideload skips non-remote URLs (data URIs, relative paths, empty).
	 *
	 * These never touch the network, so no attachments should be created and the map is empty.
	 *
	 * @since 3.7.0
	 */
	public function test_sideload_assets_skips_non_remote_urls() {
		$importer = new TestableImporter();
		$template = array(
			'assets' => array(
				'images' => array(
					array( 'url' => 'data:image/png;base64,AAAA' ),
					array( 'url' => '/wp-content/uploads/local.png' ),
					array( 'url' => '' ),
				),
			),
		);

		$this->assertSame( array(), $importer->public_sideload_template_assets( $template ) );
	}

	/**
	 * Test asset sideload reuses an attachment previously imported from the same source URL.
	 *
	 * Exercises the dedup path (no network): a pre-seeded attachment carrying the source-URL
	 * meta is returned instead of downloading a duplicate.
	 *
	 * @since 3.7.0
	 */
	public function test_sideload_assets_reuses_existing_attachment() {
		$source_url    = 'https://cdn.example.com/templates/demo/badge.png';
		$attachment_id = self::factory()->attachment->create_object(
			array(
				'file'           => 'badge.png',
				'post_parent'    => 0,
				'post_mime_type' => 'image/png',
				'post_title'     => 'Badge',
			)
		);
		update_post_meta( $attachment_id, '_edd_checkout_template_asset_source', $source_url );

		$importer = new TestableImporter();
		$map      = $importer->public_sideload_template_assets(
			array(
				'assets' => array(
					'images' => array(
						array(
							'id'  => 'badge',
							'url' => $source_url,
							'alt' => 'Badge',
						),
					),
				),
			)
		);

		// The map is keyed by both the source URL and the {{asset:<id>}} packaging token.
		$this->assertArrayHasKey( $source_url, $map );
		$this->assertArrayHasKey( '{{asset:badge}}', $map );
		$this->assertSame( $attachment_id, $map[ $source_url ]['id'] );
		$this->assertSame( $attachment_id, $map['{{asset:badge}}']['id'] );
	}

	/**
	 * Test the sideload filename folds inner extensions.
	 *
	 * @since 3.7.0
	 */
	public function test_sideload_filename_folds_inner_extensions() {
		$importer = new TestableImporter();

		$this->assertSame( 'logo-php.png', $importer->public_sideload_filename( 'https://example.org/a/logo.php.png' ) );
		$this->assertSame( 'badge.png', $importer->public_sideload_filename( 'https://example.org/a/badge.png' ) );
	}

	/**
	 * Test that a real media import scopes its filters (nothing tags along) and stays in the
	 * standard media library even when EDD's protected-directory diversion condition is active.
	 *
	 * @since 3.7.0
	 */
	public function test_import_to_media_library_scopes_filters_and_avoids_protected_dir() {
		if ( ! defined( 'DIR_TESTDATA' ) || ! file_exists( DIR_TESTDATA . '/images/canola.jpg' ) ) {
			$this->markTestSkipped( 'WordPress test image fixtures are unavailable.' );
		}

		$tmp = wp_tempnam( 'canola.jpg' );
		copy( DIR_TESTDATA . '/images/canola.jpg', $tmp );

		// Force EDD's download upload-dir diversion condition to be active for this request.
		$download_id         = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$_REQUEST['post_id'] = $download_id;

		$importer = new TestableImporter();
		$method   = new \ReflectionMethod( \EDD\Pro\Checkout\Templates\Importer\AbstractImporter::class, 'import_to_media_library' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		$attachment_id = $method->invoke(
			$importer,
			array(
				'name'     => 'canola.jpg',
				'tmp_name' => $tmp,
			),
			'Test asset'
		);

		unset( $_REQUEST['post_id'] );

		$this->assertIsInt( $attachment_id );
		$this->assertGreaterThan( 0, $attachment_id );

		// EDD's download upload-dir prefilter must be restored exactly as it was.
		$this->assertSame( 5, has_filter( 'wp_handle_upload_prefilter', 'edd_change_downloads_upload_dir' ) );

		// The asset must live in the standard uploads directory, not the protected /uploads/edd/.
		$this->assertStringNotContainsString( '/uploads/edd/', (string) get_attached_file( $attachment_id ) );
	}
}

/**
 * Testable subclass of AbstractImporter.
 *
 * Exposes protected methods for testing.
 */
class TestableImporter extends \EDD\Pro\Checkout\Templates\Importer\AbstractImporter {

	/**
	 * Import implementation - returns success for testing.
	 *
	 * @param array $template Template data.
	 * @param int   $page_id  Page ID.
	 * @return array Success response.
	 */
	public function import( array $template, int $page_id ) {
		return $this->success_response( $page_id );
	}

	/**
	 * Can import - always true for testing.
	 *
	 * @return bool True.
	 */
	public function can_import(): bool {
		return true;
	}

	/**
	 * Get editor slug.
	 *
	 * @return string Editor slug.
	 */
	public function get_editor_slug(): string {
		return 'testable';
	}

	/**
	 * Get minimum version.
	 *
	 * @return string Version.
	 */
	public function get_min_version(): string {
		return '1.0.0';
	}

	/**
	 * Get edit URL.
	 *
	 * @param int $page_id Page ID.
	 * @return string Edit URL.
	 */
	public function get_edit_url( int $page_id ): string {
		return admin_url( 'post.php?post=' . $page_id . '&action=edit' );
	}

	/**
	 * Public wrapper for replace_site_tokens.
	 *
	 * @param string $content Content.
	 * @return string Processed content.
	 */
	public function public_replace_site_tokens( string $content ): string {
		return $this->replace_site_tokens( $content );
	}

	/**
	 * Public wrapper for validate_content.
	 *
	 * @param array $template Template data.
	 * @return bool|\WP_Error Result.
	 */
	public function public_validate_content( array $template ) {
		return $this->validate_content( $template );
	}

	/**
	 * Public wrapper for store_metadata.
	 *
	 * @param int   $page_id  Page ID.
	 * @param array $template Template data.
	 * @return void
	 */
	public function public_store_metadata( int $page_id, array $template ): void {
		$this->store_metadata( $page_id, $template );
	}

	/**
	 * Public wrapper for success_response.
	 *
	 * @param int        $page_id     Page ID.
	 * @param string     $message     Optional message.
	 * @param int|false  $revision_id Optional revision ID.
	 * @return array Response.
	 */
	public function public_success_response( int $page_id, string $message = '', $revision_id = false ): array {
		return $this->success_response( $page_id, $message, $revision_id );
	}

	/**
	 * Public wrapper for error_response.
	 *
	 * @param string $code    Error code.
	 * @param string $message Error message.
	 * @param int    $status  HTTP status.
	 * @return \WP_Error Error.
	 */
	public function public_error_response( string $code, string $message, int $status = 500 ): \WP_Error {
		return $this->error_response( $code, $message, $status );
	}

	/**
	 * Public wrapper for sideload_template_assets.
	 *
	 * @param array $template Template data.
	 * @return array Map of remote URL => localized asset.
	 */
	public function public_sideload_template_assets( array $template ): array {
		return $this->sideload_template_assets( $template );
	}

	/**
	 * Public wrapper for sideload_filename.
	 *
	 * @param string $url The remote URL.
	 * @return string The sanitized filename.
	 */
	public function public_sideload_filename( string $url ): string {
		return $this->sideload_filename( $url );
	}
}

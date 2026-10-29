<?php
/**
 * Attachment ownership checks for a download's file rows, saved through the metabox.
 *
 * @package     EDD\Tests\Admin\Downloads
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests\Admin\Downloads;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Downloads\Meta;
use EDD\Downloads\Files;
use EDD\Downloads\Process;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests for saving a download's file rows through the metabox.
 *
 * @since 3.7.1
 */
class DownloadFilesSave extends EDD_UnitTestCase {

	/**
	 * The absolute path of the file created for the no-attachment fixture.
	 *
	 * @var string
	 */
	private $loose_file_path;

	/**
	 * Absolute paths of the sized-variant files copied onto disk for a test's fixtures.
	 *
	 * @var string[]
	 */
	private $variant_file_paths = array();

	/**
	 * Resets the acting user and the posted file rows after each test.
	 */
	public function tear_down(): void {
		wp_set_current_user( 0 );
		unset( $_POST['edd_download_files'] );

		if ( ! empty( $this->loose_file_path ) && file_exists( $this->loose_file_path ) ) {
			unlink( $this->loose_file_path );
		}

		foreach ( $this->variant_file_paths as $variant_file_path ) {
			if ( file_exists( $variant_file_path ) ) {
				unlink( $variant_file_path );
			}
		}

		parent::tear_down();
	}

	/**
	 * A vendor cannot reference an attachment owned by another author.
	 */
	public function test_a_vendor_cannot_add_another_authors_attachment() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );

		$this->assertEmpty( get_post_meta( $download_id, 'edd_download_files', true ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index'         => 0,
					'attachment_id' => $attachment_id,
					'name'          => 'Product',
					'file'          => 'product.zip',
					'condition'     => 'all',
				),
			),
			$download_id
		);
		$row   = reset( $files );

		$this->assertArrayNotHasKey( 'attachment_id', $row );
		$this->assertSame( 'Product', $row['name'] );
		$this->assertSame( 'product.zip', $row['file'] );
	}

	/**
	 * A reference a download's file rows already carry survives a vendor's re-save.
	 */
	public function test_a_vendor_keeps_an_attachment_the_product_already_carries() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$row           = array(
			'index'         => 0,
			'attachment_id' => $attachment_id,
			'name'          => 'Product',
			'file'          => 'product.zip',
			'condition'     => 'all',
		);

		wp_set_current_user( 0 );
		update_post_meta( $download_id, 'edd_download_files', array( $row ) );

		$stored = get_post_meta( $download_id, 'edd_download_files', true );
		$this->assertSame( $attachment_id, reset( $stored )['attachment_id'] );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save( array( $row ), $download_id );

		$this->assertSame( $attachment_id, reset( $files )['attachment_id'] );
	}

	/**
	 * A vendor keeps their own attachment even without edit_post capability on it.
	 */
	public function test_a_vendor_keeps_their_own_attachment() {
		$vendor        = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$attachment_id = $this->create_attachment( $vendor );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );

		wp_set_current_user( $vendor );

		$this->assertFalse( current_user_can( 'edit_post', $attachment_id ) );

		$files = $this->save(
			array(
				array(
					'index'         => 0,
					'attachment_id' => $attachment_id,
					'name'          => 'Product',
					'file'          => 'product.zip',
					'condition'     => 'all',
				),
			),
			$download_id
		);

		$this->assertSame( $attachment_id, reset( $files )['attachment_id'] );
	}

	/**
	 * A store manager keeps another author's attachment.
	 */
	public function test_a_store_manager_keeps_another_authors_attachment() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$this->assertTrue( current_user_can( 'edit_post', $attachment_id ) );

		$files = $this->save(
			array(
				array(
					'index'         => 0,
					'attachment_id' => $attachment_id,
					'name'          => 'Product',
					'file'          => 'product.zip',
					'condition'     => 'all',
				),
			),
			$download_id
		);

		$this->assertSame( $attachment_id, reset( $files )['attachment_id'] );
	}

	/**
	 * A worker edits every product but cannot edit another author's attachment itself.
	 */
	public function test_a_shop_worker_keeps_another_authors_attachment() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_worker' ) ) );

		$this->assertTrue( current_user_can( 'edit_others_products' ), 'Fixture: the role edits every product.' );
		$this->assertFalse( current_user_can( 'edit_post', $attachment_id ), 'Fixture: the role cannot edit the attachment itself.' );

		$files = $this->save(
			array(
				array(
					'index'         => 0,
					'attachment_id' => $attachment_id,
					'name'          => 'Product',
					'file'          => 'product.zip',
					'condition'     => 'all',
				),
			),
			$download_id
		);

		$this->assertNotEmpty( $files, 'The row is kept.' );
		$this->assertSame( $attachment_id, reset( $files )['attachment_id'] );
	}

	/**
	 * A programmatic save with no acting user keeps every attachment reference.
	 */
	public function test_nothing_is_dropped_when_nobody_is_acting() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );

		wp_set_current_user( 0 );

		$this->assertSame( 0, get_current_user_id() );

		$files = $this->save(
			array(
				array(
					'index'         => 0,
					'attachment_id' => $attachment_id,
					'name'          => 'Product',
					'file'          => 'product.zip',
					'condition'     => 'all',
				),
			),
			$download_id
		);

		$this->assertSame( $attachment_id, reset( $files )['attachment_id'] );
	}

	/**
	 * A row with no name and no file is dropped.
	 */
	public function test_a_row_with_no_name_and_no_file_is_dropped() {
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );

		$this->assertEmpty( get_post_meta( $download_id, 'edd_download_files', true ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => '',
					'file'  => '',
				),
				array(
					'index' => 1,
					'name'  => 'Product',
					'file'  => 'product.zip',
				),
			),
			$download_id
		);

		$this->assertCount( 1, $files );
	}

	/**
	 * A row named before its file is picked is kept.
	 */
	public function test_a_named_row_with_no_file_and_no_attachment_is_kept() {
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$rows        = array(
			array(
				'index' => 0,
				'name'  => 'Product',
				'file'  => '',
			),
		);

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$files = $this->save( $rows, $download_id );

		$this->assertCount( 1, $files, 'The row is kept.' );
		$this->assertSame( 'Product', reset( $files )['name'] );
	}

	/**
	 * A file value the request shapes as a list has no stored form, so the row names nothing to deliver.
	 */
	public function test_a_file_value_that_is_not_a_string_removes_the_row() {
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => array( 'product.zip' ),
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming nothing to deliver is removed.' );
	}

	/**
	 * A file value carrying a null byte has no stored form, so the row names nothing to deliver.
	 */
	public function test_a_file_value_with_a_null_byte_removes_the_row() {
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$file        = "wp-content/uploads/pro\0duct.zip";

		$sanitized = sanitize_meta( 'edd_download_files', array( array( 'name' => 'Product', 'file' => $file ) ), 'post', 'download' );
		$this->assertStringContainsString( "\0", reset( $sanitized )['file'], 'Fixture: the meta sanitizer must keep the null byte, or the resolver never sees one.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					// The metabox hands the filter the raw request, which is slashed, so the null byte arrives escaped.
					'file'  => wp_slash( $file ),
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming nothing to deliver is removed.' );
	}

	/**
	 * The metabox save passes the download id to the files filter.
	 */
	public function test_the_metabox_save_passes_the_download_to_the_files_filter() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/downloads/metabox.php';

		if ( ! function_exists( 'edd_download_meta_box_fields_save' ) ) {
			$this->fail( 'edd_download_meta_box_fields_save() is not available.' );
		}

		$vendor        = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_attachment( $owner );
		$download_id   = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_author' => $vendor,
			)
		);

		$row           = array(
			'index'         => 0,
			'attachment_id' => $attachment_id,
			'name'          => 'Product',
			'file'          => 'product.zip',
			'condition'     => 'all',
		);

		wp_set_current_user( 0 );
		update_post_meta( $download_id, 'edd_download_files', array( $row ) );

		wp_set_current_user( $vendor );

		$this->assertTrue( current_user_can( 'edit_product', $download_id ) );

		$_POST['edd_download_files'] = array( $row );

		// Admin service providers are wired only when is_admin() is true, so attach the
		// subscriber here to model the request path this filter runs on in production.
		add_filter( 'edd_metabox_save_edd_download_files', array( new Meta(), 'download_files_value' ), 10, 2 );

		edd_download_meta_box_fields_save( $download_id, get_post( $download_id ) );

		$stored = get_post_meta( $download_id, 'edd_download_files', true );

		$this->assertSame( $attachment_id, reset( $stored )['attachment_id'] );
	}

	/**
	 * The files filter is registered with the download id as its second argument.
	 */
	public function test_the_files_filter_accepts_the_download_id() {
		$this->assertSame(
			array( 'download_files_value', 10, 2 ),
			Meta::get_subscribed_events()['edd_metabox_save_edd_download_files']
		);
	}

	/**
	 * A vendor cannot reference another author's upload by its URL.
	 */
	public function test_a_vendor_cannot_add_a_url_to_another_authors_upload() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_uploaded_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url           = wp_get_attachment_url( $attachment_id );

		$this->assertSame( $attachment_id, attachment_url_to_postid( $url ) );
		$this->assertEmpty( get_post_meta( $download_id, 'edd_download_files', true ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $url,
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming a refused file is removed.' );
	}

	/**
	 * A vendor cannot reference another author's upload by its path.
	 */
	public function test_a_vendor_cannot_add_a_path_to_another_authors_upload() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_uploaded_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$path          = get_attached_file( $attachment_id );

		$this->assertTrue( file_exists( $path ) );
		$this->assertEmpty( get_post_meta( $download_id, 'edd_download_files', true ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $path,
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming a refused file is removed.' );
	}

	/**
	 * An invalid attachment id does not exempt the file value from the ownership check.
	 */
	public function test_an_invalid_attachment_id_does_not_exempt_the_file() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_uploaded_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url           = wp_get_attachment_url( $attachment_id );

		$this->assertSame( $attachment_id, attachment_url_to_postid( $url ) );
		$this->assertNull( get_post( 999999 ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index'         => 0,
					'attachment_id' => 999999,
					'name'          => 'Product',
					'file'          => $url,
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming a refused file is removed.' );
	}

	/**
	 * A vendor keeps a sized variant of their own upload.
	 */
	public function test_a_vendor_keeps_a_sized_variant_of_their_own_upload() {
		$vendor        = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$attachment_id = $this->create_uploaded_attachment( $vendor );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url           = $this->create_sized_variant( $attachment_id );

		$this->assertNotSame( wp_get_attachment_url( $attachment_id ), $url );
		$this->assertSame( 0, attachment_url_to_postid( $url ) );
		$this->assertTrue( file_exists( end( $this->variant_file_paths ) ) );

		wp_set_current_user( $vendor );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $url,
				),
			),
			$download_id
		);

		$this->assertSame( $url, reset( $files )['file'] );
	}

	/**
	 * A vendor cannot add a sized variant of another author's upload.
	 */
	public function test_a_vendor_cannot_add_a_sized_variant_of_another_authors_upload() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_uploaded_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url           = $this->create_sized_variant( $attachment_id );

		$this->assertNotSame( wp_get_attachment_url( $attachment_id ), $url );
		$this->assertSame( 0, attachment_url_to_postid( $url ) );
		$this->assertTrue( file_exists( end( $this->variant_file_paths ) ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $url,
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming a refused file is removed.' );
	}

	/**
	 * A file named like a size variant is not resolved to an unrelated attachment.
	 */
	public function test_a_file_named_like_a_size_variant_is_not_resolved_to_an_unrelated_attachment() {
		$vendor        = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$attachment_id = $this->create_uploaded_attachment( $vendor );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$path          = get_attached_file( $attachment_id );
		$info          = pathinfo( $path );
		$decoy         = $info['filename'] . '-2x3.' . $info['extension'];
		$decoy_path    = $info['dirname'] . '/' . $decoy;

		copy( $path, $decoy_path );
		$this->variant_file_paths[] = $decoy_path;

		$decoy_url = str_replace( wp_basename( $path ), $decoy, wp_get_attachment_url( $attachment_id ) );
		$metadata  = wp_get_attachment_metadata( $attachment_id );
		$variants  = empty( $metadata['sizes'] ) ? array() : wp_list_pluck( $metadata['sizes'], 'file' );

		$this->assertSame( 0, attachment_url_to_postid( $decoy_url ) );
		$this->assertFalse( in_array( $decoy, $variants, true ) );

		wp_set_current_user( $vendor );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $decoy_url,
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming a refused file is removed.' );
	}

	/**
	 * A file value a download's rows already carry survives a vendor's re-save.
	 */
	public function test_a_vendor_keeps_a_file_the_product_already_carries() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_uploaded_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url           = wp_get_attachment_url( $attachment_id );
		$row           = array(
			'index' => 0,
			'name'  => 'Product',
			'file'  => $url,
		);

		$this->assertSame( $attachment_id, attachment_url_to_postid( $url ) );

		wp_set_current_user( 0 );
		update_post_meta( $download_id, 'edd_download_files', array( $row ) );

		$stored = get_post_meta( $download_id, 'edd_download_files', true );
		$this->assertSame( $url, reset( $stored )['file'] );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save( array( $row ), $download_id );

		$this->assertSame( $url, reset( $files )['file'] );
	}

	/**
	 * A vendor keeps a URL to their own upload even without edit_post capability on it.
	 */
	public function test_a_vendor_keeps_a_url_to_their_own_upload() {
		$vendor        = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$attachment_id = $this->create_uploaded_attachment( $vendor );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url           = wp_get_attachment_url( $attachment_id );

		$this->assertSame( $attachment_id, attachment_url_to_postid( $url ) );

		wp_set_current_user( $vendor );

		$this->assertFalse( current_user_can( 'edit_post', $attachment_id ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $url,
				),
			),
			$download_id
		);

		$this->assertSame( $url, reset( $files )['file'] );
	}

	/**
	 * A store manager keeps a URL to another author's upload.
	 */
	public function test_a_store_manager_keeps_a_url_to_another_authors_upload() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_uploaded_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url           = wp_get_attachment_url( $attachment_id );

		$this->assertSame( $attachment_id, attachment_url_to_postid( $url ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $url,
				),
			),
			$download_id
		);

		$this->assertSame( $url, reset( $files )['file'] );
	}

	/**
	 * A vendor cannot reach another author's upload by asking for it over http on an https store.
	 */
	public function test_a_vendor_cannot_add_an_http_url_to_another_authors_upload_on_an_https_store() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_uploaded_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url           = wp_get_attachment_url( $attachment_id );

		$this->assertSame( $attachment_id, attachment_url_to_postid( $url ), 'The file is indexed for its attachment.' );
		$this->assertSame( $owner, (int) get_post( $attachment_id )->post_author, 'The upload belongs to the other author.' );
		$this->assertSame( 0, strpos( $url, 'http://' ), 'The fixture URL carries the http scheme.' );

		$this->serve_the_store_over_https();

		$this->assertSame( 0, strpos( content_url(), 'https://' ), 'The store serves its content directory over https.' );
		$this->assertIsString( Process::local_reference( $url ), 'The http form of a local upload is local.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $url,
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming a refused file is removed.' );
	}

	/**
	 * A vendor keeps the http form of their own upload on an https store.
	 */
	public function test_a_vendor_keeps_an_http_url_to_their_own_upload_on_an_https_store() {
		$vendor        = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$attachment_id = $this->create_uploaded_attachment( $vendor );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url           = wp_get_attachment_url( $attachment_id );

		$this->assertSame( $attachment_id, attachment_url_to_postid( $url ), 'The file is indexed for its attachment.' );
		$this->assertSame( 0, strpos( $url, 'http://' ), 'The fixture URL carries the http scheme.' );

		$this->serve_the_store_over_https();

		$this->assertSame( 0, strpos( content_url(), 'https://' ), 'The store serves its content directory over https.' );

		wp_set_current_user( $vendor );

		$this->assertFalse( current_user_can( 'edit_others_products' ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $url,
				),
			),
			$download_id
		);

		$this->assertNotEmpty( $files, 'The row is kept.' );
		$this->assertSame( $url, reset( $files )['file'] );
	}

	/**
	 * An externally hosted URL is kept regardless of who is acting.
	 */
	public function test_an_external_url_is_kept_for_a_vendor() {
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url         = 'https://example.com/thing.zip';

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $url,
				),
			),
			$download_id
		);

		$this->assertSame( $url, reset( $files )['file'] );
	}

	/**
	 * A local file with no matching attachment is held to the broader capability.
	 */
	public function test_a_local_file_with_no_attachment_requires_the_broader_capability() {
		$upload_dir     = wp_upload_dir();
		$loose_file     = 'edd/loose.zip';
		$loose_file_url = trailingslashit( $upload_dir['baseurl'] ) . $loose_file;
		$loose_path     = $this->make_loose_file( trailingslashit( $upload_dir['basedir'] ) . $loose_file );

		$this->assertSame( 0, attachment_url_to_postid( $loose_file_url ) );
		$this->assertIsString( Process::local_reference( $loose_path ) );

		$vendor_download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$vendor_files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $loose_path,
				),
			),
			$vendor_download_id
		);

		$this->assertEmpty( $vendor_files, 'The row naming a refused file is removed.' );

		$manager_download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$this->assertTrue( current_user_can( 'edit_others_products' ) );

		$manager_files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $loose_path,
				),
			),
			$manager_download_id
		);
		$manager_row   = reset( $manager_files );

		$this->assertSame( $loose_path, $manager_row['file'] );
	}

	/**
	 * A file value is kept when no user is acting.
	 */
	public function test_a_file_is_kept_when_nobody_is_acting() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_uploaded_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url           = wp_get_attachment_url( $attachment_id );

		$this->assertSame( $attachment_id, attachment_url_to_postid( $url ) );

		wp_set_current_user( 0 );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $url,
				),
			),
			$download_id
		);

		$this->assertSame( $url, reset( $files )['file'] );
	}

	/**
	 * The save path calls nothing declared in the front-end include.
	 */
	public function test_the_save_path_calls_nothing_declared_in_the_front_end_include() {
		$declared = $this->functions_declared_by_the_download_request();

		$this->assertNotEmpty( $declared, 'The download request file declares functions.' );
		$this->assertContains( 'edd_is_local_file', $declared );

		$sources = array( 'src/Downloads/Files.php', 'src/Admin/Downloads/Meta.php' );

		foreach ( $sources as $source ) {
			$code = php_strip_whitespace( EDD_PLUGIN_DIR . $source );

			$this->assertNotEmpty( $code, $source . ' was read.' );

			foreach ( $declared as $function ) {
				$this->assertSame(
					0,
					preg_match( '/\b' . preg_quote( $function, '/' ) . '\s*\(/', $code ),
					$source . ' must not call ' . $function . '(), which only includes/process-download.php declares.'
				);
			}
		}
	}

	/**
	 * A relative path to another author's upload is held to the same rule as an absolute one.
	 */
	public function test_a_vendor_cannot_add_a_relative_path_to_another_authors_upload() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_uploaded_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$relative      = $this->relative_to_wordpress_root( get_attached_file( $attachment_id ) );

		$this->assertFalse( path_is_absolute( $relative ), 'The fixture is a relative path.' );
		$this->assertTrue( file_exists( ABSPATH . $relative ), 'The fixture resolves under the WordPress root.' );
		$this->assertIsString( Process::local_reference( $relative ), 'A relative path into the uploads tree is local.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $relative,
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming a refused file is removed.' );
	}

	/**
	 * A vendor keeps a relative path to their own upload.
	 */
	public function test_a_vendor_keeps_a_relative_path_to_their_own_upload() {
		$vendor        = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$attachment_id = $this->create_uploaded_attachment( $vendor );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$relative      = $this->relative_to_wordpress_root( get_attached_file( $attachment_id ) );

		$this->assertFalse( path_is_absolute( $relative ), 'The fixture is a relative path.' );
		$this->assertIsString( Process::local_reference( $relative ), 'A relative path into the uploads tree is local.' );
		$this->assertSame( 0, attachment_url_to_postid( $relative ), 'A relative path is not indexed as a URL.' );

		wp_set_current_user( $vendor );

		$this->assertFalse( current_user_can( 'edit_others_products' ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $relative,
				),
			),
			$download_id
		);

		$this->assertNotEmpty( $files, 'The row is kept.' );
		$this->assertSame( $relative, reset( $files )['file'] );
	}

	/**
	 * A path led by a current directory segment is held to the directory rule.
	 */
	public function test_a_vendor_cannot_add_a_dot_segment_path_to_another_authors_upload() {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$relative      = $this->relative_to_wordpress_root( get_attached_file( $attachment_id ) );
		$dotted        = './' . $relative;

		$this->assertSame( $attachment_id, attachment_url_to_postid( wp_get_attachment_url( $attachment_id ) ), 'The file is indexed for its attachment.' );
		$this->assertSame( $owner, (int) get_post( $attachment_id )->post_author, 'The upload belongs to the other author.' );
		$this->assertFalse( file_exists( ABSPATH . $relative ), 'The fixture file is not on disk, so the directory rule judges the path.' );
		$this->assertSame( $dotted, sanitize_text_field( $dotted ), 'The meta sanitizer keeps the dot segment.' );
		$this->assertIsString( Process::local_reference( $dotted ), 'A dot segment path into the uploads tree is local.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $dotted,
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming a refused file is removed.' );
	}

	/**
	 * A vendor keeps their own upload named by a path carrying a current directory segment.
	 */
	public function test_a_vendor_keeps_a_dot_segment_path_to_their_own_upload() {
		$vendor        = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$attachment_id = $this->create_uploaded_attachment( $vendor );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$relative      = $this->relative_to_wordpress_root( get_attached_file( $attachment_id ) );
		$dotted        = dirname( $relative ) . '/./' . wp_basename( $relative );

		$this->assertTrue( file_exists( ABSPATH . $relative ), "The vendor's own file is on disk." );
		$this->assertSame( $vendor, (int) get_post( $attachment_id )->post_author, 'The upload belongs to the vendor.' );
		$this->assertSame( $dotted, sanitize_text_field( $dotted ), 'The meta sanitizer keeps the dot segment.' );

		wp_set_current_user( $vendor );

		$this->assertFalse( current_user_can( 'edit_others_products' ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $dotted,
				),
			),
			$download_id
		);

		$this->assertNotEmpty( $files, 'The row is kept.' );
		$this->assertSame( $dotted, reset( $files )['file'] );
	}

	/**
	 * A parent segment is collapsed too, so a path that steps out and back resolves to the same attachment.
	 */
	public function test_a_vendor_keeps_a_parent_segment_path_to_their_own_upload() {
		$vendor        = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$attachment_id = $this->create_uploaded_attachment( $vendor );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$relative      = $this->relative_to_wordpress_root( get_attached_file( $attachment_id ) );
		$directory     = dirname( $relative );
		$stepped       = $directory . '/../' . wp_basename( $directory ) . '/' . wp_basename( $relative );

		$this->assertTrue( file_exists( ABSPATH . $relative ), "The vendor's own file is on disk." );
		$this->assertSame( $vendor, (int) get_post( $attachment_id )->post_author, 'The upload belongs to the vendor.' );
		$this->assertNotSame( $relative, $stepped, 'The submitted spelling differs from the stored one.' );

		wp_set_current_user( $vendor );

		$this->assertFalse( current_user_can( 'edit_others_products' ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $stepped,
				),
			),
			$download_id
		);

		$this->assertNotEmpty( $files, 'The row is kept.' );
		$this->assertSame( $stepped, reset( $files )['file'] );
	}

	/**
	 * An externally hosted URL is kept for an administrator.
	 */
	public function test_an_external_url_is_kept_for_an_administrator() {
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url         = 'https://example.com/file.zip';

		$this->assertFalse( Process::local_reference( $url ), 'An externally hosted URL is not read from this server.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'administrator' ) ) );

		$this->assertTrue( current_user_can( 'edit_others_products' ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $url,
				),
			),
			$download_id
		);

		$this->assertSame( $url, reset( $files )['file'] );
	}

	/**
	 * A vendor keeps a file value another service delivers.
	 */
	public function test_a_vendor_keeps_a_bucket_and_key_value() {
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$file        = 'edd-vendor-bucket/private/product.zip';

		$this->assertFalse( Process::local_reference( $file ), 'A bucket and key is not read from this server.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$this->assertFalse( current_user_can( 'edit_others_products' ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $file,
				),
			),
			$download_id
		);

		$this->assertNotEmpty( $files, 'The row is kept.' );
		$this->assertSame( $file, reset( $files )['file'] );
	}

	/**
	 * A path into the uploads tree is refused before its file is written.
	 */
	public function test_a_vendor_cannot_add_an_uploads_path_whose_file_is_not_there_yet() {
		$path        = trailingslashit( wp_upload_dir()['basedir'] ) . 'edd/not-written-yet.zip';
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );

		$this->assertFalse( file_exists( $path ), 'The fixture file is not on disk.' );
		$this->assertIsString( Process::local_reference( $path ), 'A path into the uploads tree is local.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $path,
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming a refused file is removed.' );
	}

	/**
	 * A row is removed when its file is refused, even where its attachment is the vendor's own.
	 */
	public function test_a_row_is_removed_when_its_file_is_refused() {
		$vendor         = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$owner          = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$own_attachment = $this->create_attachment( $vendor );
		$download_id    = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$url            = wp_get_attachment_url( $this->create_uploaded_attachment( $owner ) );

		wp_set_current_user( $vendor );

		$this->assertTrue( Files::can_reference_attachment( $own_attachment ), 'The vendor owns the attachment.' );
		$this->assertFalse( Files::can_reference_file( $url ), 'The file belongs to another author.' );

		$files = $this->save(
			array(
				array(
					'index'         => 0,
					'attachment_id' => $own_attachment,
					'name'          => 'Product',
					'file'          => $url,
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row is removed with its file.' );
	}

	/**
	 * A stored file path containing an apostrophe survives a vendor's re-save.
	 */
	public function test_a_vendor_keeps_a_stored_file_path_containing_an_apostrophe() {
		$path        = $this->make_loose_file( trailingslashit( wp_upload_dir()['basedir'] ) . "edd/john's-book.zip" );
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$row         = array(
			'index' => 0,
			'name'  => 'Product',
			'file'  => $path,
		);

		wp_set_current_user( 0 );
		update_post_meta( $download_id, 'edd_download_files', array( $row ) );

		$stored = get_post_meta( $download_id, 'edd_download_files', true );

		$this->assertSame( $path, reset( $stored )['file'], 'The stored value carries no slashes.' );
		$this->assertIsString( Process::local_reference( $path ), 'The stored path is local.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$this->assertFalse( current_user_can( 'edit_others_products' ) );

		// The metabox hands the filter the raw request, so the re-saved row arrives slashed.
		$submitted         = $row;
		$submitted['file'] = wp_slash( $row['file'] );

		$this->assertNotSame( $row['file'], $submitted['file'], 'The submitted row is slashed.' );

		$files = $this->save( array( $submitted ), $download_id );

		$this->assertNotEmpty( $files, 'The row is kept.' );
		$this->assertSame( $path, reset( $files )['file'] );
	}


	/**
	 * A vendor cannot reach another author's upload by spelling its path differently.
	 *
	 * @dataProvider variant_spellings
	 * @param string $prefix The characters the submitted value leads with.
	 * @param string $octet  A percent-encoded octet placed at the end of the value's first segment.
	 */
	public function test_a_vendor_cannot_add_a_variant_spelling_of_another_authors_upload( $prefix, $octet ) {
		$owner         = self::factory()->user->create( array( 'role' => 'administrator' ) );
		$attachment_id = $this->create_uploaded_attachment( $owner );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$relative      = $this->relative_to_wordpress_root( get_attached_file( $attachment_id ) );
		$variant       = $prefix . substr_replace( $relative, $octet, strpos( $relative, '/' ), 0 );

		$this->assertTrue( file_exists( ABSPATH . $relative ), "The other author's file is on disk." );
		$this->assertSame( $owner, (int) get_post( $attachment_id )->post_author, 'The upload belongs to the other author.' );
		$this->assertSame( $attachment_id, attachment_url_to_postid( wp_get_attachment_url( $attachment_id ) ), 'The file is indexed for its attachment.' );
		$this->assertNotSame( $relative, $variant, 'The submitted spelling differs from the stored one.' );

		// What the meta sanitizer keeps is the path the other author's file is served from.
		$probe_id = self::factory()->post->create( array( 'post_type' => 'download' ) );
		update_post_meta( $probe_id, 'edd_download_files', wp_slash( array( array( 'file' => $variant ) ) ) );
		$probe = get_post_meta( $probe_id, 'edd_download_files', true );

		$this->assertSame( $relative, reset( $probe )['file'], "The submitted spelling is stored as the other author's path." );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_vendor' ) ) );

		$this->assertFalse( Files::can_reference_file( $relative ), 'The stored path belongs to another author.' );

		// The metabox hands the filter the raw request, which is slashed.
		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => wp_slash( $variant ),
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'The row naming a refused file is removed.' );
	}

	/**
	 * A vendor keeps their own upload when it is submitted with a variant spelling.
	 *
	 * The control for the rule above: a row is refused for what its stored value resolves to, not
	 * for carrying characters the meta sanitizer removes.
	 */
	public function test_a_vendor_keeps_a_variant_spelling_of_their_own_upload() {
		$vendor        = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$attachment_id = $this->create_uploaded_attachment( $vendor );
		$download_id   = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$relative      = $this->relative_to_wordpress_root( get_attached_file( $attachment_id ) );
		$variant       = ' ' . $relative;

		$this->assertTrue( file_exists( ABSPATH . $relative ), "The vendor's own file is on disk." );
		$this->assertSame( $vendor, (int) get_post( $attachment_id )->post_author, 'The upload belongs to the vendor.' );

		wp_set_current_user( $vendor );

		$this->assertFalse( current_user_can( 'edit_others_products' ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => wp_slash( $variant ),
				),
			),
			$download_id
		);

		$this->assertNotEmpty( $files, 'The row is kept.' );
		$this->assertSame( $relative, reset( $files )['file'], 'The row is stored with the spelling the sanitizer keeps.' );
	}

	/**
	 * A file value keeps its backslashes when it is stored.
	 */
	public function test_a_file_path_keeps_its_backslashes_when_stored() {
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );
		$file        = 'C:\files\product.zip';
		$submitted   = wp_slash( $file );

		$this->assertNotSame( $file, $submitted, 'The metabox posts the value slashed.' );
		$this->assertSame( $file, wp_unslash( $submitted ), 'The posted value differs from the stored one only by slashing.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => $submitted,
				),
			),
			$download_id
		);

		$this->assertNotEmpty( $files, 'The row is kept.' );
		$this->assertSame( $file, reset( $files )['file'] );
	}

	/**
	 * A path led by a drive letter is a path, whichever separator follows the letter.
	 */
	public function test_a_drive_letter_path_is_read_as_a_path() {
		$this->assertTrue( $this->is_drive_path( 'C:\files\x.zip' ), 'A backslash follows the drive letter.' );
		$this->assertTrue( $this->is_drive_path( 'C:/files/x.zip' ), 'A forward slash follows the drive letter.' );
		$this->assertTrue( $this->is_drive_path( 'c:\x.zip' ), 'The drive letter is lowercase.' );
	}

	/**
	 * A URL is not read as a drive letter path.
	 */
	public function test_a_url_is_not_read_as_a_drive_letter_path() {
		$this->assertFalse( $this->is_drive_path( 'http://example.com/x.zip' ) );
	}

	/**
	 * A row whose file value sanitizes to nothing is removed.
	 */
	public function test_a_row_whose_file_sanitizes_to_nothing_is_removed() {
		$download_id = self::factory()->post->create( array( 'post_type' => 'download' ) );

		$this->assertSame( '', sanitize_text_field( '%20' ), 'The meta sanitizer keeps nothing of the submitted value.' );

		wp_set_current_user( self::factory()->user->create( array( 'role' => 'shop_manager' ) ) );

		$this->assertTrue( current_user_can( 'edit_others_products' ), 'The role may reference any file, so only the row check can remove it.' );

		$files = $this->save(
			array(
				array(
					'index' => 0,
					'name'  => 'Product',
					'file'  => '%20',
				),
			),
			$download_id
		);

		$this->assertEmpty( $files, 'A row whose file sanitizes to nothing is removed.' );
	}

	/**
	 * Spellings which differ from a stored file value only in what the meta sanitizer removes.
	 *
	 * @return array
	 */
	public function variant_spellings() {
		return array(
			'leading_space'         => array( ' ', '' ),
			'leading_tab'           => array( "\t", '' ),
			'leading_encoded_space' => array( '%20', '' ),
			'encoded_letter_inside' => array( '', '%41' ),
			'leading_tag'           => array( '<b>', '' ),
		);
	}

	/**
	 * Runs a metabox save through the files filter and returns what was persisted.
	 *
	 * @param array $rows    The submitted file rows.
	 * @param int   $post_id The download being saved.
	 * @return array
	 */
	private function save( array $rows, $post_id ) {
		$meta  = new Meta();
		$value = $meta->download_files_value( $rows, $post_id );

		update_post_meta( $post_id, 'edd_download_files', $value );

		return get_post_meta( $post_id, 'edd_download_files', true );
	}

	/**
	 * Whether the reference resolver reads a value as a Windows drive letter path.
	 *
	 * @param string $file The path or URL a file row names.
	 * @return bool
	 */
	private function is_drive_path( $file ) {
		$method = new \ReflectionMethod( Process::class, 'is_drive_path' );
		$method->setAccessible( true );

		return $method->invoke( null, $file );
	}

	/**
	 * The names of the functions the download request file declares.
	 *
	 * @return string[]
	 */
	private function functions_declared_by_the_download_request() {
		preg_match_all(
			'/function (edd_[a-z0-9_]+)\(/',
			file_get_contents( EDD_PLUGIN_DIR . 'includes/process-download.php' ),
			$matches
		);

		return $matches[1];
	}

	/**
	 * Puts the store's own URLs on https for the rest of the test.
	 */
	private function serve_the_store_over_https() {
		$to_https = function ( $url ) {
			return set_url_scheme( $url, 'https' );
		};

		foreach ( array( 'home_url', 'site_url', 'content_url' ) as $filter ) {
			add_filter( $filter, $to_https );
		}
	}

	/**
	 * An absolute path below the WordPress root, expressed relative to it.
	 *
	 * @param string $path The absolute path.
	 * @return string
	 */
	private function relative_to_wordpress_root( $path ) {
		return ltrim( str_replace( wp_normalize_path( ABSPATH ), '', wp_normalize_path( $path ) ), '/' );
	}

	/**
	 * Creates a file with no attachment behind it, removed when the test ends.
	 *
	 * @param string $path The absolute path to create.
	 * @return string The path created.
	 */
	private function make_loose_file( $path ) {
		$this->loose_file_path = $path;

		wp_mkdir_p( dirname( $path ) );
		file_put_contents( $path, 'edd-download-files-save-fixture' );

		return $path;
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
				'file'           => 'download-files-save-product.zip',
				'post_parent'    => 0,
				'post_author'    => $author,
				'post_status'    => 'inherit',
				'post_mime_type' => 'application/zip',
				'post_title'     => 'Product',
			)
		);
	}

	/**
	 * Creates a real uploaded attachment owned by the given user.
	 *
	 * @param int $author The owner.
	 * @return int
	 */
	private function create_uploaded_attachment( $author ) {
		$attachment_id = self::factory()->attachment->create_upload_object( DIR_TESTDATA . '/images/canola.jpg' );

		wp_update_post(
			array(
				'ID'          => $attachment_id,
				'post_author' => $author,
			)
		);

		return $attachment_id;
	}

	/**
	 * Creates a sized image variant for the given attachment, as the image editor would have,
	 * and registers it in the attachment's metadata.
	 *
	 * @param int $attachment_id The attachment the variant belongs to.
	 * @return string The variant's URL.
	 */
	private function create_sized_variant( $attachment_id ) {
		$path         = get_attached_file( $attachment_id );
		$info         = pathinfo( $path );
		$variant      = $info['filename'] . '-150x150.' . $info['extension'];
		$variant_path = $info['dirname'] . '/' . $variant;

		copy( $path, $variant_path );
		$this->variant_file_paths[] = $variant_path;

		$metadata                       = wp_get_attachment_metadata( $attachment_id );
		$metadata['sizes']['thumbnail'] = array(
			'file'      => $variant,
			'width'     => 150,
			'height'    => 150,
			'mime-type' => 'image/jpeg',
		);
		wp_update_attachment_metadata( $attachment_id, $metadata );

		return wp_get_attachment_image_url( $attachment_id, 'thumbnail' );
	}
}

<?php
/**
 * Tests for the EDD product abilities.
 *
 * @package     EDD\Tests\Abilities
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests\Abilities;

use EDD\Abilities\Products\Create;
use EDD\Abilities\Products\Read;
use EDD\Abilities\Products\Update;
use EDD\Tests\Helpers\EDD_Helper_Download;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Product ability tests.
 *
 * @since 3.7.1
 */
class Products extends EDD_UnitTestCase {

	/**
	 * Set the current user to the site administrator, who holds all shop capabilities.
	 */
	public function set_up() {
		parent::set_up();

		wp_set_current_user( 1 );
	}

	/**
	 * Restore the default write setting.
	 */
	public function tear_down() {
		edd_delete_option( \EDD\Abilities\Loader::WRITE_SETTING );
		parent::tear_down();
	}

	public function test_product_read_single_simple_download() {
		$download = EDD_Helper_Download::create_simple_download();

		$ability = new Read();
		$result  = $ability->execute( array( 'id' => $download->ID ) );

		$this->assertIsArray( $result );
		$this->assertSame( 1, $result['total'] );
		$this->assertCount( 1, $result['products'] );

		$product = $result['products'][0];
		$this->assertSame( $download->ID, $product['id'] );
		$this->assertSame( 'publish', $product['status'] );
		$this->assertFalse( $product['is_variable'] );
		$this->assertIsFloat( $product['price'] );
		$this->assertEqualsWithDelta( 20.0, $product['price'], 0.001 );
		$this->assertSame( array(), $product['variable_prices'] );

		// Single-lookup details are included.
		$this->assertSame( 20, $product['download_limit'] );
		$this->assertNotEmpty( $product['files'] );
		foreach ( $product['files'] as $file ) {
			$this->assertSame( array( 'file_id', 'name' ), array_keys( $file ) );
		}
		$this->assert_files_contain_no_urls( $product['files'] );
		$this->assertStringNotContainsString( 'simple-file1.jpg', wp_json_encode( $result ) );
	}

	public function test_product_read_single_variable_download() {
		$download = EDD_Helper_Download::create_variable_download();

		$ability = new Read();
		$result  = $ability->execute( array( 'id' => $download->ID ) );

		$product = $result['products'][0];
		$this->assertTrue( $product['is_variable'] );
		$this->assertNull( $product['price'] );
		$this->assertCount( 2, $product['variable_prices'] );
		$this->assertSame( array( 'Simple', 'Advanced' ), wp_list_pluck( $product['variable_prices'], 'name' ) );

		$amounts = wp_list_pluck( $product['variable_prices'], 'amount' );
		$this->assertEqualsWithDelta( 20.0, $amounts[0], 0.001 );
		$this->assertEqualsWithDelta( 100.0, $amounts[1], 0.001 );

		$this->assertCount( 2, $product['files'] );
		$this->assert_files_contain_no_urls( $product['files'] );
	}

	public function test_product_read_missing_product_returns_not_found() {
		$ability = new Read();
		$result  = $ability->execute( array( 'id' => 999999 ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_product_not_found', $result->get_error_code() );
	}

	public function test_product_read_list_filters_and_paginates() {
		EDD_Helper_Download::create_simple_download();
		EDD_Helper_Download::create_simple_download();
		EDD_Helper_Download::create_simple_download();
		wp_insert_post(
			array(
				'post_type'   => 'download',
				'post_title'  => 'Draft Product',
				'post_status' => 'draft',
			)
		);

		$ability = new Read();

		$all = $ability->execute( array() );
		$this->assertGreaterThanOrEqual( 4, $all['total'] );

		// List results omit single-lookup details.
		$this->assertArrayNotHasKey( 'files', $all['products'][0] );
		$this->assertArrayNotHasKey( 'download_limit', $all['products'][0] );

		$drafts = $ability->execute( array( 'status' => 'draft' ) );
		$this->assertGreaterThanOrEqual( 1, $drafts['total'] );
		foreach ( $drafts['products'] as $product ) {
			$this->assertSame( 'draft', $product['status'] );
		}

		$paged = $ability->execute( array( 'limit' => 2, 'offset' => 0 ) );
		$this->assertCount( 2, $paged['products'] );
		$this->assertSame( 2, $paged['limit'] );
		$this->assertTrue( $paged['has_more'] );
	}

	public function test_product_create_defaults_to_draft_and_sets_price() {
		$ability = new Create();
		$result  = $ability->execute(
			array(
				'name'  => 'Ability Product',
				'price' => 25.5,
			)
		);

		$this->assertIsArray( $result );
		$this->assertGreaterThan( 0, $result['product_id'] );
		$this->assertSame( 'Ability Product', $result['name'] );
		$this->assertSame( 'draft', $result['status'] );
		$this->assertEqualsWithDelta( 25.5, $result['price'], 0.001 );

		// The price meta was written.
		$this->assertEqualsWithDelta( 25.5, floatval( edd_get_download_price( $result['product_id'] ) ), 0.001 );
	}

	public function test_product_create_requires_publish_products() {
		$this->allow_writes();

		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		get_user_by( 'id', $user_id )->add_cap( 'edit_products' );
		wp_set_current_user( $user_id );

		// edit_products is enough to read, but not to create.
		$this->assertTrue( ( new Read() )->check_permissions( array() ) );
		$this->assertFalse( ( new Create() )->check_permissions( array() ) );

		wp_get_current_user()->add_cap( 'publish_products' );
		$this->assertTrue( ( new Create() )->check_permissions( array() ) );
	}

	public function test_product_update_renames_and_changes_price() {
		$download = EDD_Helper_Download::create_simple_download();

		$ability = new Update();
		$result  = $ability->execute(
			array(
				'product_id' => $download->ID,
				'name'       => 'Renamed Product',
				'price'      => 30,
			)
		);

		$this->assertIsArray( $result );
		$this->assertSame( $download->ID, $result['product_id'] );
		$this->assertSame( 'Renamed Product', $result['name'] );
		$this->assertEqualsWithDelta( 30.0, $result['price'], 0.001 );
		$this->assertSame( 'Renamed Product', get_post( $download->ID )->post_title );
		$this->assertEqualsWithDelta( 30.0, floatval( edd_get_download_price( $download->ID ) ), 0.001 );
	}

	public function test_product_update_missing_product_returns_not_found() {
		$ability = new Update();
		$result  = $ability->execute(
			array(
				'product_id' => 999999,
				'name'       => 'Nothing',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_product_not_found', $result->get_error_code() );
	}

	public function test_product_update_ownership_requires_edit_others_products() {
		$this->allow_writes();

		$author_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		get_user_by( 'id', $author_id )->add_cap( 'edit_products' );

		$other_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		get_user_by( 'id', $other_id )->add_cap( 'edit_products' );

		$product_id = wp_insert_post(
			array(
				'post_type'   => 'download',
				'post_title'  => 'Owned Product',
				'post_status' => 'draft',
				'post_author' => $author_id,
			)
		);

		$ability = new Update();

		// A non-author without edit_others_products may not edit it.
		wp_set_current_user( $other_id );
		$this->assertFalse( $ability->check_permissions( array( 'product_id' => $product_id ) ) );

		// Granting edit_others_products allows it.
		wp_get_current_user()->add_cap( 'edit_others_products' );
		$this->assertTrue( $ability->check_permissions( array( 'product_id' => $product_id ) ) );

		// The author may edit their own product without edit_others_products.
		wp_set_current_user( $author_id );
		$this->assertTrue( $ability->check_permissions( array( 'product_id' => $product_id ) ) );
	}

	public function test_product_update_price_on_variable_product_fails() {
		$download = EDD_Helper_Download::create_variable_download();

		$ability = new Update();
		$result  = $ability->execute(
			array(
				'product_id' => $download->ID,
				'price'      => 25,
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_variable_price_product', $result->get_error_code() );
	}

	public function test_vendor_cannot_read_products_authored_by_someone_else() {
		$vendor       = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$other_author = self::factory()->user->create( array( 'role' => 'administrator' ) );

		// The scoping only means anything if the role really holds edit_products
		// and lacks edit_others_products.
		$this->assertTrue( user_can( $vendor, 'edit_products' ) );
		$this->assertFalse( user_can( $vendor, 'edit_others_products' ) );

		$own_product = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_author' => $vendor,
				'post_status' => 'publish',
			)
		);
		$other_draft = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_author' => $other_author,
				'post_status' => 'draft',
			)
		);

		wp_set_current_user( $vendor );

		$result = ( new Read() )->execute( array() );
		$found  = wp_list_pluck( $result['products'], 'id' );

		$this->assertContains( $own_product, $found );
		$this->assertNotContains( $other_draft, $found );
	}

	public function test_vendor_cannot_read_another_authors_product_by_id() {
		$vendor       = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		$other_author = self::factory()->user->create( array( 'role' => 'administrator' ) );

		$this->assertTrue( user_can( $vendor, 'edit_products' ) );

		$other_draft = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_author' => $other_author,
				'post_status' => 'draft',
			)
		);

		wp_set_current_user( $vendor );

		$result = ( new Read() )->execute( array( 'id' => $other_draft ) );

		$this->assertWPError( $result );
	}

	public function test_product_earnings_are_withheld_without_sensitive_data_capability() {
		$accountant = self::factory()->user->create( array( 'role' => 'shop_accountant' ) );

		$this->assertTrue( user_can( $accountant, 'edit_products' ) );
		$this->assertFalse( user_can( $accountant, 'view_shop_sensitive_data' ) );

		$download = EDD_Helper_Download::create_simple_download();

		wp_set_current_user( $accountant );

		$result  = ( new Read() )->execute( array( 'id' => $download->ID ) );
		$product = $result['products'][0];

		$this->assertArrayNotHasKey( 'earnings', $product );
		$this->assertArrayNotHasKey( 'sales', $product );
	}

	public function test_role_without_publish_products_cannot_publish_a_product() {
		$accountant = self::factory()->user->create( array( 'role' => 'shop_accountant' ) );

		$this->assertTrue( user_can( $accountant, 'edit_products' ) );
		$this->assertFalse( user_can( $accountant, 'publish_products' ) );

		$product = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_author' => $accountant,
				'post_status' => 'draft',
			)
		);

		wp_set_current_user( $accountant );
		$this->allow_writes();

		$ability = new Update();

		$this->assertFalse(
			$ability->check_permissions(
				array(
					'product_id' => $product,
					'status'     => 'publish',
				)
			)
		);
		$this->assertSame( 'draft', get_post_status( $product ) );
	}

	public function test_variable_price_rejection_leaves_the_product_unchanged() {
		$download = EDD_Helper_Download::create_variable_download();
		$original = get_post( $download->ID )->post_title;

		$this->allow_writes();

		$result = ( new Update() )->execute(
			array(
				'product_id' => $download->ID,
				'name'       => 'Renamed By The Ability',
				'status'     => 'draft',
				'price'      => 25,
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( $original, get_post( $download->ID )->post_title );
		$this->assertSame( 'publish', get_post_status( $download->ID ) );
	}
	/**
	 * Turn the store's write access on, then off again after the test.
	 *
	 * The tests below assert what a capability does or does not permit, so the
	 * store-wide write setting has to be out of the way. Without this they would
	 * pass for the wrong reason: writes are off by default.
	 */
	/**
	 * An update request naming no product is refused, even for an administrator.
	 *
	 * Core validates input before permissions, so the sanctioned path never gets
	 * here; this pins the direct-call branch, which has no product to check
	 * ownership against.
	 */
	public function test_product_update_permission_is_refused_without_a_product_id() {
		$this->allow_writes();

		// The fixture: this user does hold the ability's own capability, so a
		// false below is the missing product and not a missing capability.
		$this->assertTrue( current_user_can( 'edit_products' ) );

		$ability = new Update();

		$this->assertFalse( $ability->check_permissions( null ), 'Null input was permitted.' );
		$this->assertFalse( $ability->check_permissions( array() ), 'Input without a product_id was permitted.' );
		$this->assertFalse( $ability->check_permissions( array( 'name' => 'No ID' ) ), 'Input naming no product was permitted.' );
	}

	private function allow_writes(): void {
		edd_update_option( \EDD\Abilities\Loader::WRITE_SETTING, true );
	}

	/**
	 * Asserts that a formatted files array contains no file URLs anywhere.
	 *
	 * Walks the array recursively: no key may be `url` or `file`, and no
	 * string value may contain a URL.
	 *
	 * @param array $data The files array from the ability output.
	 */
	private function assert_files_contain_no_urls( array $data ): void {
		foreach ( $data as $key => $value ) {
			$this->assertNotContains( (string) $key, array( 'url', 'file' ), "Unexpected file URL key: {$key}." );

			if ( is_array( $value ) ) {
				$this->assert_files_contain_no_urls( $value );
			} elseif ( is_string( $value ) ) {
				$this->assertDoesNotMatchRegularExpression( '#https?://#', $value, 'File values must never contain URLs.' );
			}
		}
	}
}

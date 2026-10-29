<?php
/**
 * Tests for access to the price variations handler.
 *
 * @package     EDD\Tests\Downloads
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests\Downloads;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * PriceVariationsAccess tests.
 *
 * @since 3.7.1
 * @group edd_downloads
 */
class PriceVariationsAccess extends EDD_UnitTestCase {

	/**
	 * The tier names the fixtures carry.
	 *
	 * @var array
	 */
	private $tiers = array( 'Insider Early Bird', 'Enterprise NDA Tier' );

	public function tearDown(): void {
		unset( $_POST['download_id'] );

		parent::tearDown();
	}

	/**
	 * The fixture precondition: setUp()'s rebuild of the role objects took, so the refusals
	 * below are refusing for the reason they claim rather than because no role has any
	 * capability at all.
	 */
	public function test_fixture_roles_carry_the_capabilities_these_cases_rest_on() {
		$vendor = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		wp_set_current_user( $vendor );

		$this->assertTrue( current_user_can( 'edit_products' ), 'A vendor has to be able to edit products.' );
		$this->assertFalse( current_user_can( 'edit_others_products' ), 'A vendor must not be able to edit other authors.' );

		$manager = self::factory()->user->create( array( 'role' => 'shop_manager' ) );
		wp_set_current_user( $manager );

		$this->assertTrue( current_user_can( 'edit_others_products' ), 'A manager has to be able to edit other authors.' );

		$accountant = self::factory()->user->create( array( 'role' => 'shop_accountant' ) );
		wp_set_current_user( $accountant );

		$this->assertFalse( current_user_can( 'edit_others_products' ), 'An accountant must not be able to edit other authors.' );
		$this->assertTrue( current_user_can( 'read_private_products' ), "An accountant has to be able to read another author's private product." );
	}

	/**
	 * Another author's draft product does not give up its tiers.
	 */
	public function test_another_authors_draft_is_refused() {
		$draft = $this->create_product( 'draft', $this->create_owner() );

		$this->act_as_vendor();

		$this->assertStringNotContainsString( 'Enterprise NDA Tier', $this->ask_for( $draft ) );
	}

	/**
	 * Another author's private product does not give up its tiers.
	 */
	public function test_another_authors_private_product_is_refused() {
		$private = $this->create_product( 'private', $this->create_owner() );

		$this->act_as_vendor();

		$this->assertStringNotContainsString( 'Enterprise NDA Tier', $this->ask_for( $private ) );
	}

	/**
	 * A private product still gives up its tiers to a caller who can only read it.
	 */
	public function test_another_authors_private_product_is_allowed_for_a_reader() {
		$private = $this->create_product( 'private', $this->create_owner() );

		$this->act_as_accountant();

		$this->assertStringContainsString( 'Enterprise NDA Tier', $this->ask_for( $private ) );
	}

	/**
	 * A refusal carries no signal beyond "nothing to show".
	 */
	public function test_a_refusal_matches_a_nonexistent_product() {
		$draft = $this->create_product( 'draft', $this->create_owner() );

		$this->act_as_vendor();

		$this->assertSame( $this->ask_for( 99999999 ), $this->ask_for( $draft ), 'The response body must match.' );
		$this->assertSame( $this->status_for( 99999999 ), $this->status_for( $draft ), 'The status must match.' );
	}

	/**
	 * The author's own draft product still gives up its tiers.
	 */
	public function test_the_authors_own_draft_is_allowed() {
		$vendor = $this->act_as_vendor();
		$draft  = $this->create_product( 'draft', $vendor );

		$this->assertStringContainsString( 'Enterprise NDA Tier', $this->ask_for( $draft ) );
	}

	/**
	 * A published product gives up its tiers to anyone who can edit products.
	 */
	public function test_a_published_product_is_allowed() {
		$published = $this->create_product( 'publish', $this->create_owner() );

		$this->act_as_vendor();

		$this->assertStringContainsString( 'Enterprise NDA Tier', $this->ask_for( $published ) );
	}

	/**
	 * Someone who can edit others' products still reads their draft's tiers.
	 */
	public function test_another_authors_draft_is_allowed_for_a_manager() {
		$draft = $this->create_product( 'draft', $this->create_owner() );

		$this->act_as_manager();

		$this->assertStringContainsString( 'Enterprise NDA Tier', $this->ask_for( $draft ) );
	}

	/**
	 * An identifier which is not a product is refused.
	 */
	public function test_an_id_which_is_not_a_product_is_refused() {
		$page = self::factory()->post->create( array( 'post_type' => 'page' ) );

		$this->act_as_manager();

		$this->assertEmpty( $this->ask_for( $page ) );
	}

	/**
	 * An identifier which does not exist is refused.
	 */
	public function test_an_id_which_does_not_exist_is_refused() {
		$this->act_as_manager();

		$this->assertEmpty( $this->ask_for( 99999999 ) );
	}

	/**
	 * Runs the handler and returns whatever it sent.
	 *
	 * @param int $download_id The product asked about.
	 * @return string
	 */
	private function ask_for( $download_id ) {
		$_POST['download_id'] = $download_id;

		ob_start();
		try {
			edd_check_for_download_price_variations();
		} catch ( \WPDieException $e ) {
			// The handler always ends by dying.
		}

		return ob_get_clean();
	}

	/**
	 * Runs the handler and returns the status it died with.
	 *
	 * @param int $download_id The product asked about.
	 * @return int
	 */
	private function status_for( $download_id ) {
		$_POST['download_id'] = $download_id;

		ob_start();
		try {
			edd_check_for_download_price_variations();
			$status = null;
		} catch ( \WPDieException $e ) {
			$status = $e->getCode();
		}
		ob_end_clean();

		return $status;
	}

	/**
	 * Creates a variable priced product with the given status and owner.
	 *
	 * @param string $status The post status.
	 * @param int    $author The owner.
	 * @return int
	 */
	private function create_product( $status, $author ) {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_title'  => 'Zephyr Tiers',
				'post_status' => $status,
				'post_author' => $author,
			)
		);

		$prices = array();
		foreach ( $this->tiers as $name ) {
			$prices[] = array(
				'name'   => $name,
				'amount' => 10,
			);
		}

		update_post_meta( $download_id, 'edd_price', '0.00' );
		update_post_meta( $download_id, '_variable_pricing', 1 );
		update_post_meta( $download_id, 'edd_variable_prices', $prices );

		return $download_id;
	}

	/**
	 * Creates a user who owns products but is not the actor.
	 *
	 * @return int
	 */
	private function create_owner() {
		return self::factory()->user->create( array( 'role' => 'administrator' ) );
	}

	/**
	 * Becomes a shop vendor.
	 *
	 * @return int
	 */
	private function act_as_vendor() {
		$vendor = self::factory()->user->create( array( 'role' => 'shop_vendor' ) );
		wp_set_current_user( $vendor );

		return $vendor;
	}

	/**
	 * Becomes a shop accountant.
	 *
	 * @return int
	 */
	private function act_as_accountant() {
		$accountant = self::factory()->user->create( array( 'role' => 'shop_accountant' ) );
		wp_set_current_user( $accountant );

		return $accountant;
	}

	/**
	 * Becomes a shop manager.
	 *
	 * @return int
	 */
	private function act_as_manager() {
		$manager = self::factory()->user->create( array( 'role' => 'shop_manager' ) );
		wp_set_current_user( $manager );

		return $manager;
	}
}

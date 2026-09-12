<?php
/**
 * Tests for EDD\Cache\Handler.
 *
 * @package   EDD\Tests\Cache
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.6.7
 */

namespace EDD\Tests\Cache;

use EDD\Cache\Handler as CacheHandler;
use EDD\Cache\NoCache;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * @coversDefaultClass \EDD\Cache\Handler
 */
class Handler extends EDD_UnitTestCase {

	/**
	 * ID of the checkout page post created for testing.
	 *
	 * @var int
	 */
	private int $checkout_id;

	/**
	 * ID of the success page post created for testing.
	 *
	 * @var int
	 */
	private int $success_id;

	/**
	 * Original REQUEST_URI, restored after each test.
	 *
	 * @var string
	 */
	private string $original_request_uri;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->checkout_id = self::factory()->post->create(
			array(
				'post_name'   => 'checkout',
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);

		$this->success_id = self::factory()->post->create(
			array(
				'post_name'   => 'purchase-confirmation',
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);

		edd_update_option( 'purchase_page', $this->checkout_id );
		edd_update_option( 'success_page', $this->success_id );

		$this->original_request_uri = $_SERVER['REQUEST_URI'] ?? '';
	}

	/**
	 * Tear down after each test.
	 */
	public function tearDown(): void {
		edd_delete_option( 'purchase_page' );
		edd_delete_option( 'success_page' );
		edd_delete_option( 'confirmation_page' );
		delete_transient( CacheHandler::TRANSIENT );
		$_SERVER['REQUEST_URI'] = $this->original_request_uri;
		NoCache::reset();

		parent::tearDown();
	}

	// -------------------------------------------------------------------------
	// get_subscribed_events
	// -------------------------------------------------------------------------

	/**
	 * @covers ::get_subscribed_events
	 */
	public function test_get_subscribed_events_returns_template_redirect_hook() {
		$events = CacheHandler::get_subscribed_events();

		$this->assertArrayHasKey( 'template_redirect', $events );
		$this->assertSame( array( 'check_request', 0 ), $events['template_redirect'] );
	}

	/**
	 * @covers ::get_subscribed_events
	 */
	public function test_get_subscribed_events_returns_admin_notices_hook() {
		$events = CacheHandler::get_subscribed_events();

		$this->assertArrayHasKey( 'admin_notices', $events );
		$this->assertSame( 'notices', $events['admin_notices'] );
	}

	/**
	 * @covers ::get_subscribed_events
	 */
	public function test_get_subscribed_events_returns_update_option_edd_settings_hook() {
		$events = CacheHandler::get_subscribed_events();

		$this->assertArrayHasKey( 'update_option_edd_settings', $events );
		$this->assertSame( 'flush_page_ids', $events['update_option_edd_settings'] );
	}

	/**
	 * @covers ::get_subscribed_events
	 */
	public function test_get_subscribed_events_returns_save_post_hook() {
		$events = CacheHandler::get_subscribed_events();

		$this->assertArrayHasKey( 'save_post', $events );
		$this->assertSame( array( 'maybe_flush_page_ids', 10, 1 ), $events['save_post'] );
	}

	/**
	 * @covers ::get_subscribed_events
	 */
	public function test_get_subscribed_events_returns_permalink_structure_changed_hook() {
		$events = CacheHandler::get_subscribed_events();

		$this->assertArrayHasKey( 'permalink_structure_changed', $events );
		$this->assertSame( 'flush_page_ids', $events['permalink_structure_changed'] );
	}

	// -------------------------------------------------------------------------
	// check_request
	// -------------------------------------------------------------------------

	/**
	 * The checkout page is a configured dynamic page and must receive
	 * no-cache headers regardless of which internal path fires.
	 *
	 * @covers ::check_request
	 */
	public function test_check_request_sets_no_cache_on_checkout_page() {
		$this->go_to( get_permalink( $this->checkout_id ) );
		$_SERVER['REQUEST_URI'] = '/checkout';

		( new CacheHandler() )->check_request();

		$this->assertNotFalse(
			has_filter( 'nocache_headers' ),
			'The checkout page should receive no-cache headers.'
		);
	}

	/**
	 * The success/confirmation page is a configured dynamic page and must
	 * receive no-cache headers.
	 *
	 * @covers ::check_request
	 */
	public function test_check_request_sets_no_cache_on_success_page() {
		$this->go_to( get_permalink( $this->success_id ) );
		$_SERVER['REQUEST_URI'] = '/purchase-confirmation';

		( new CacheHandler() )->check_request();

		$this->assertNotFalse(
			has_filter( 'nocache_headers' ),
			'The success page should receive no-cache headers.'
		);
	}

	/**
	 * A page stored in the confirmation_page option (not covered by
	 * edd_is_checkout() or edd_is_success_page()) must be caught by the
	 * is_page() fallback.
	 *
	 * @covers ::check_request
	 */
	public function test_check_request_sets_no_cache_for_confirmation_page_via_is_page() {
		$confirmation_id = self::factory()->post->create(
			array(
				'post_name'   => 'order-confirmation',
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);
		edd_update_option( 'confirmation_page', $confirmation_id );

		$this->go_to( get_permalink( $confirmation_id ) );
		$_SERVER['REQUEST_URI'] = '/order-confirmation';

		( new CacheHandler() )->check_request();

		$this->assertNotFalse(
			has_filter( 'nocache_headers' ),
			'A confirmation_page configured via options should receive no-cache headers.'
		);
	}

	/**
	 * A page that is not configured as any EDD dynamic page must not trigger
	 * no-cache headers.
	 *
	 * @covers ::check_request
	 */
	public function test_check_request_does_not_set_no_cache_for_unrelated_page() {
		$other_id = self::factory()->post->create(
			array(
				'post_name'   => 'about-us',
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);

		$this->go_to( get_permalink( $other_id ) );
		$_SERVER['REQUEST_URI'] = '/about-us';

		( new CacheHandler() )->check_request();

		$this->assertFalse(
			has_filter( 'nocache_headers' ),
			'An unrelated page must not receive no-cache headers.'
		);
	}

	/**
	 * On any request that is not the checkout or success page, check_request()
	 * should store the dynamic page IDs in the transient so subsequent requests
	 * skip the option look-ups.
	 *
	 * We navigate to an unrelated page so neither edd_is_checkout() nor
	 * edd_is_success_page() fires; the EDD pages configured in setUp should
	 * appear in the cached value.
	 *
	 * @covers ::check_request
	 */
	public function test_check_request_populates_transient_on_cache_miss() {
		$other_id = self::factory()->post->create(
			array(
				'post_name'   => 'blog',
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);

		$this->assertFalse(
			get_transient( CacheHandler::TRANSIENT ),
			'Transient should not exist before check_request() is called.'
		);

		$this->go_to( get_permalink( $other_id ) );
		$_SERVER['REQUEST_URI'] = '/blog';

		( new CacheHandler() )->check_request();

		$cached = get_transient( CacheHandler::TRANSIENT );
		$this->assertIsArray( $cached );
		$this->assertContains( $this->checkout_id, $cached );
		$this->assertContains( $this->success_id, $cached );
	}

	/**
	 * When the transient already holds the page-ID list, check_request() must
	 * honour it without rebuilding – confirmed by pre-seeding a sentinel value.
	 *
	 * @covers ::check_request
	 */
	public function test_check_request_uses_cached_transient_on_cache_hit() {
		$fake_ids = array( 99999 );
		set_transient( CacheHandler::TRANSIENT, $fake_ids, WEEK_IN_SECONDS );

		$other_id = self::factory()->post->create(
			array(
				'post_name'   => 'about-us',
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);

		$this->go_to( get_permalink( $other_id ) );
		$_SERVER['REQUEST_URI'] = '/about-us';

		( new CacheHandler() )->check_request();

		$this->assertSame(
			$fake_ids,
			get_transient( CacheHandler::TRANSIENT ),
			'Transient should remain the seeded value and not be rebuilt from options.'
		);
	}

	/**
	 * The checkout early-return path short-circuits before the transient
	 * look-up, so it must not populate the transient.
	 *
	 * @covers ::check_request
	 */
	public function test_check_request_does_not_populate_transient_for_checkout_page() {
		$this->go_to( get_permalink( $this->checkout_id ) );
		$_SERVER['REQUEST_URI'] = '/checkout';

		( new CacheHandler() )->check_request();

		$this->assertFalse(
			get_transient( CacheHandler::TRANSIENT ),
			'Transient should not be set when the checkout short-circuit fires.'
		);
	}

	// -------------------------------------------------------------------------
	// get_dynamic_page_ids  (private – tested via reflection)
	// -------------------------------------------------------------------------

	/**
	 * @covers ::check_request
	 */
	public function test_get_dynamic_page_ids_includes_configured_page_ids() {
		$ids = $this->invoke_get_dynamic_page_ids();

		$this->assertContains( $this->checkout_id, $ids );
		$this->assertContains( $this->success_id, $ids );
	}

	/**
	 * @covers ::check_request
	 */
	public function test_get_dynamic_page_ids_returns_empty_when_no_pages_configured() {
		edd_delete_option( 'purchase_page' );
		edd_delete_option( 'success_page' );
		edd_delete_option( 'confirmation_page' );

		$ids = $this->invoke_get_dynamic_page_ids();

		$this->assertEmpty( $ids );
	}

	/**
	 * @covers ::check_request
	 */
	public function test_get_dynamic_page_ids_excludes_empty_option_values() {
		edd_update_option( 'purchase_page', '' );

		$ids = $this->invoke_get_dynamic_page_ids();

		$this->assertNotContains( '', $ids );
	}

	// -------------------------------------------------------------------------
	// flush_page_ids
	// -------------------------------------------------------------------------

	/**
	 * @covers ::flush_page_ids
	 */
	public function test_flush_page_ids_deletes_existing_transient() {
		set_transient( CacheHandler::TRANSIENT, array( 1, 2 ), WEEK_IN_SECONDS );

		CacheHandler::flush_page_ids();

		$this->assertFalse(
			get_transient( CacheHandler::TRANSIENT ),
			'flush_page_ids() should delete the cached id transient.'
		);
	}

	/**
	 * @covers ::flush_page_ids
	 */
	public function test_flush_page_ids_is_safe_when_transient_does_not_exist() {
		$this->assertFalse( get_transient( CacheHandler::TRANSIENT ) );

		CacheHandler::flush_page_ids();

		$this->assertFalse( get_transient( CacheHandler::TRANSIENT ) );
	}

	// -------------------------------------------------------------------------
	// maybe_flush_page_ids
	// -------------------------------------------------------------------------

	/**
	 * Saving the checkout page must flush the transient so the next request
	 * rebuilds it with the updated slug.
	 *
	 * @covers ::maybe_flush_page_ids
	 */
	public function test_maybe_flush_page_ids_flushes_when_dynamic_page_is_saved() {
		set_transient( CacheHandler::TRANSIENT, array( $this->checkout_id ), WEEK_IN_SECONDS );

		( new CacheHandler() )->maybe_flush_page_ids( $this->checkout_id );

		$this->assertFalse(
			get_transient( CacheHandler::TRANSIENT ),
			'Saving a dynamic page should flush the transient.'
		);
	}

	/**
	 * Saving a post that is not a configured dynamic page must leave the
	 * transient intact.
	 *
	 * @covers ::maybe_flush_page_ids
	 */
	public function test_maybe_flush_page_ids_does_not_flush_for_unrelated_post() {
		$other_id = self::factory()->post->create(
			array(
				'post_name'   => 'about-us',
				'post_status' => 'publish',
				'post_type'   => 'page',
			)
		);

		set_transient( CacheHandler::TRANSIENT, array( $this->checkout_id ), WEEK_IN_SECONDS );

		( new CacheHandler() )->maybe_flush_page_ids( $other_id );

		$this->assertNotFalse(
			get_transient( CacheHandler::TRANSIENT ),
			'Saving an unrelated post must not flush the transient.'
		);
	}

	/**
	 * When no dynamic pages are configured, maybe_flush_page_ids() must not flush
	 * regardless of the post ID passed.
	 *
	 * @covers ::maybe_flush_page_ids
	 */
	public function test_maybe_flush_page_ids_does_not_flush_when_no_dynamic_pages_configured() {
		edd_delete_option( 'purchase_page' );
		edd_delete_option( 'success_page' );
		edd_delete_option( 'confirmation_page' );

		set_transient( CacheHandler::TRANSIENT, array(), WEEK_IN_SECONDS );

		( new CacheHandler() )->maybe_flush_page_ids( $this->checkout_id );

		$this->assertNotFalse(
			get_transient( CacheHandler::TRANSIENT ),
			'maybe_flush_page_ids() must not flush when no dynamic pages are configured.'
		);
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Invokes the private get_dynamic_page_ids() method via reflection.
	 *
	 * @return array
	 */
	private function invoke_get_dynamic_page_ids(): array {
		$handler    = new CacheHandler();
		$reflection = new \ReflectionClass( $handler );
		$method     = $reflection->getMethod( 'get_dynamic_page_ids' );
		if (PHP_VERSION_ID < 80100) {
            $method->setAccessible( true );
        }

		return $method->invoke( $handler );
	}
}

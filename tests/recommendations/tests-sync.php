<?php
/**
 * Tests for Recommendations Sync
 *
 * @group edd_recommendations
 * @group edd_pro
 */
namespace EDD\Tests\Recommendations;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Recommendations\Sync as RecommendationsSync;

class Sync extends EDD_UnitTestCase {

	/**
	 * Sync instance.
	 *
	 * @var Sync
	 */
	protected $sync;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'EDD Pro is not available. Recommendations Sync tests require EDD Pro.' );
		}

		parent::setUp();

		$this->sync = new RecommendationsSync();

		// Set up necessary options.
		edd_update_option( 'cart_recommendations', true );
		update_site_option( 'edd_pro_license_key', 'test_license_key' );

		// Create admin user with proper capabilities.
		$user_id = $this->factory->user->create( array( 'role' => 'administrator' ) );
		$user = new \WP_User( $user_id );

		// Grant all necessary product capabilities for meta cap mapping.
		$user->add_cap( 'edit_products' );
		$user->add_cap( 'edit_published_products' );
		$user->add_cap( 'edit_others_products' );
		$user->add_cap( 'edit_private_products' );
		$user->add_cap( 'publish_products' );
		$user->add_cap( 'read_private_products' );
		$user->add_cap( 'delete_products' );

		wp_set_current_user( $user_id );
	}

	/**
	 * Clean up after each test.
	 */
	public function tearDown(): void {
		edd_delete_option( 'cart_recommendations' );
		delete_site_option( 'edd_pro_license_key' );

		parent::tearDown();
	}

	/**
	 * Test Sync can be instantiated.
	 */
	public function test_sync_initialization() {
		$this->assertInstanceOf( RecommendationsSync::class, $this->sync );
	}

	/**
	 * Test get_subscribed_events returns correct events.
	 */
	public function test_get_subscribed_events() {
		$events = RecommendationsSync::get_subscribed_events();

		$this->assertIsArray( $events );
		$this->assertArrayHasKey( 'edd_save_download', $events );
		$this->assertEquals( array( 'sync_download', 10, 2 ), $events['edd_save_download'] );
	}

	/**
	 * Test sync_download doesn't sync draft downloads.
	 */
	public function test_sync_download_ignores_draft_downloads() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'draft',
			)
		);

		$download = get_post( $download_id );

		$this->sync->sync_download( $download_id, $download );

		// Verify no cron event was scheduled.
		$next = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );
		$this->assertFalse( $next );
	}

	/**
	 * Test sync_download schedules event for published downloads.
	 */
	public function test_sync_download_schedules_event_for_published_downloads() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		$download = get_post( $download_id );

		$this->sync->sync_download( $download_id, $download );

		// Verify cron event was scheduled.
		$next = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );
		$this->assertNotFalse( $next );
		$this->assertGreaterThan( time(), $next );
	}

	/**
	 * Test sync_download doesn't schedule if already scheduled.
	 */
	public function test_sync_download_doesnt_duplicate_scheduled_events() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		// Schedule an event manually.
		\EDD\Cron\Events\SingleEvent::add(
			time() + HOUR_IN_SECONDS,
			'edd_sync_single_download',
			array( $download_id )
		);

		$first_scheduled = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );

		// Try to sync again.
		$download = get_post( $download_id );
		$this->sync->sync_download( $download_id, $download );

		// Verify the scheduled time didn't change (no duplicate).
		$second_scheduled = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );

		$this->assertEquals( $first_scheduled, $second_scheduled );
	}

	/**
	 * Test sync_download doesn't sync when API unavailable.
	 */
	public function test_sync_download_doesnt_sync_when_api_unavailable() {
		delete_site_option( 'edd_pro_license_key' );

		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		$download = get_post( $download_id );

		$this->sync->sync_download( $download_id, $download );

		// Verify no cron event was scheduled.
		$next = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );
		$this->assertFalse( $next );
	}

	/**
	 * Test sync_download doesn't sync when recommendations disabled.
	 */
	public function test_sync_download_doesnt_sync_when_recommendations_disabled() {
		edd_update_option( 'cart_recommendations', false );

		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		$download = get_post( $download_id );

		$this->sync->sync_download( $download_id, $download );

		// Verify no cron event was scheduled.
		$next = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );
		$this->assertFalse( $next );
	}

	/**
	 * Test sync_download schedules event with proper delay.
	 */
	public function test_sync_download_schedules_event_with_delay() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		$download = get_post( $download_id );

		$before = time();
		$this->sync->sync_download( $download_id, $download );
		$after = time();

		$next = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );

		// Should be scheduled approximately 1 minute in the future.
		$expected_time = $before + ( 1 * MINUTE_IN_SECONDS );
		$this->assertGreaterThanOrEqual( $expected_time, $next );
		$this->assertEqualsWithDelta( $expected_time, $next, 5 ); // 5 second buffer
	}

	/**
	 * Test sync implements SubscriberInterface.
	 */
	public function test_sync_implements_subscriber_interface() {
		$this->assertInstanceOf( \EDD\EventManagement\SubscriberInterface::class, $this->sync );
	}

	/**
	 * Test sync_download is hooked to edd_save_download action.
	 */
	public function test_sync_download_is_hooked_to_edd_save_download() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		// Clear any existing scheduled events by removing from cron array.
		$timestamp = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );
		if ( $timestamp ) {
			\EDD\Cron\Events\SingleEvent::remove( 'edd_sync_single_download', array( $download_id ) );
		}

		// Trigger the action that should hook sync_download.
		$download = get_post( $download_id );
		do_action( 'edd_save_download', $download_id, $download );

		// Verify the event was scheduled by the hook.
		$next = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );
		$this->assertNotFalse( $next );
	}

	/**
	 * Test sync passes download ID to cron event.
	 */
	public function test_sync_passes_download_id_to_cron_event() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		$download = get_post( $download_id );
		$this->sync->sync_download( $download_id, $download );

		// Get the scheduled event.
		$crons = _get_cron_array();
		$event_found = false;

		foreach ( $crons as $timestamp => $cron ) {
			if ( isset( $cron['edd_sync_single_download'] ) ) {
				foreach ( $cron['edd_sync_single_download'] as $event ) {
					if ( isset( $event['args'][0] ) && $event['args'][0] === $download_id ) {
						$event_found = true;
						break 2;
					}
				}
			}
		}

		$this->assertTrue( $event_found, 'Cron event should include download ID in args' );
	}

	/**
	 * Test get_subscribed_events includes all expected hooks.
	 */
	public function test_get_subscribed_events_includes_all_hooks() {
		$events = RecommendationsSync::get_subscribed_events();

		$this->assertArrayHasKey( 'edd_save_download', $events );
		$this->assertArrayHasKey( 'edd_cart_recommendations_enabled', $events );
		$this->assertArrayHasKey( 'wp_delete_post', $events );
		$this->assertArrayHasKey( 'wp_trash_post', $events );
		$this->assertArrayHasKey( 'edd_sync_recommendations', $events );
	}


	/**
	 * Test delete_on_delete ignores non-download post types.
	 */
	public function test_delete_on_delete_ignores_non_download_post_types() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);

		// Add meta that would be deleted if delete was called.
		update_post_meta( $post_id, '_edd_recommendations_synced', true );
		update_post_meta( $post_id, '_edd_cached_recommendations', array( 'test' ) );

		$post = get_post( $post_id );
		$this->sync->delete_on_delete( $post_id, $post );

		// Verify meta was NOT deleted (because it's not a download).
		$this->assertEquals( true, get_post_meta( $post_id, '_edd_recommendations_synced', true ) );
		$this->assertEquals( array( 'test' ), get_post_meta( $post_id, '_edd_cached_recommendations', true ) );
	}

	/**
	 * Test delete_on_delete removes meta for downloads.
	 */
	public function test_delete_on_delete_removes_meta_for_downloads() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		// Add meta that should be deleted.
		update_post_meta( $download_id, '_edd_recommendations_synced', true );
		update_post_meta( $download_id, '_edd_cached_recommendations', array( 'test' ) );

		// Mock the API call to prevent actual request.
		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) {
				if ( strpos( $url, 'recommendations/data' ) !== false ) {
					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode( array( 'success' => true ) ),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$post = get_post( $download_id );
		$this->sync->delete_on_delete( $download_id, $post );

		// Verify meta was deleted.
		$this->assertEmpty( get_post_meta( $download_id, '_edd_recommendations_synced', true ) );
		$this->assertEmpty( get_post_meta( $download_id, '_edd_cached_recommendations', true ) );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test delete_on_trash ignores non-download post types.
	 */
	public function test_delete_on_trash_ignores_non_download_post_types() {
		$post_id = self::factory()->post->create(
			array(
				'post_type'   => 'post',
				'post_status' => 'publish',
			)
		);

		// Add meta that would be deleted if delete was called.
		update_post_meta( $post_id, '_edd_recommendations_synced', true );
		update_post_meta( $post_id, '_edd_cached_recommendations', array( 'test' ) );

		$this->sync->delete_on_trash( $post_id );

		// Verify meta was NOT deleted.
		$this->assertEquals( true, get_post_meta( $post_id, '_edd_recommendations_synced', true ) );
		$this->assertEquals( array( 'test' ), get_post_meta( $post_id, '_edd_cached_recommendations', true ) );
	}

	/**
	 * Test delete_on_trash removes meta for downloads.
	 */
	public function test_delete_on_trash_removes_meta_for_downloads() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		// Add meta that should be deleted.
		update_post_meta( $download_id, '_edd_recommendations_synced', true );
		update_post_meta( $download_id, '_edd_cached_recommendations', array( 'test' ) );

		// Mock the API call.
		add_filter(
			'pre_http_request',
			function( $preempt, $parsed_args, $url ) {
				if ( strpos( $url, 'recommendations/data' ) !== false ) {
					return array(
						'response' => array(
							'code'    => 200,
							'message' => 'OK',
						),
						'body'     => json_encode( array( 'success' => true ) ),
					);
				}

				return $preempt;
			},
			10,
			3
		);

		$this->sync->delete_on_trash( $download_id );

		// Verify meta was deleted.
		$this->assertEmpty( get_post_meta( $download_id, '_edd_recommendations_synced', true ) );
		$this->assertEmpty( get_post_meta( $download_id, '_edd_cached_recommendations', true ) );

		remove_all_filters( 'pre_http_request' );
	}

	/**
	 * Test sync_recommendations returns early without capability.
	 */
	public function test_sync_recommendations_returns_early_without_capability() {
		// Create a user without permissions.
		$user_id = $this->factory->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Clear any existing scheduled events.
		$timestamp = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_all_products' );
		if ( $timestamp ) {
			\EDD\Cron\Events\SingleEvent::remove( 'edd_sync_all_products' );
		}

		$data = array(
			'_wpnonce' => wp_create_nonce( 'edd_sync_recommendations' ),
		);

		$this->sync->sync_recommendations( $data );

		// Verify no event was scheduled.
		$next = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_all_products' );
		$this->assertFalse( $next );
	}

	/**
	 * Test sync_recommendations returns early with invalid nonce.
	 */
	public function test_sync_recommendations_returns_early_with_invalid_nonce() {
		// Clear any existing scheduled events.
		$timestamp = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_all_products' );
		if ( $timestamp ) {
			\EDD\Cron\Events\SingleEvent::remove( 'edd_sync_all_products' );
		}

		$data = array(
			'_wpnonce' => 'invalid_nonce',
		);

		$this->sync->sync_recommendations( $data );

		// Verify no event was scheduled.
		$next = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_all_products' );
		$this->assertFalse( $next );
	}

	/**
	 * Test sync_recommendations returns early when missing nonce.
	 */
	public function test_sync_recommendations_returns_early_when_missing_nonce() {
		// Clear any existing scheduled events.
		$timestamp = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_all_products' );
		if ( $timestamp ) {
			\EDD\Cron\Events\SingleEvent::remove( 'edd_sync_all_products' );
		}

		$this->sync->sync_recommendations( array() );

		// Verify no event was scheduled.
		$next = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_all_products' );
		$this->assertFalse( $next );
	}

	/**
	 * Test sync_download clears recommendation meta when scheduling.
	 */
	public function test_sync_download_clears_recommendation_meta() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		// Add meta that should be cleared.
		update_post_meta( $download_id, '_edd_recommendations_synced', true );
		update_post_meta( $download_id, '_edd_cached_recommendations', array( 'test' ) );

		$download = get_post( $download_id );
		$this->sync->sync_download( $download_id, $download );

		// Verify meta was cleared.
		$this->assertEmpty( get_post_meta( $download_id, '_edd_recommendations_synced', true ) );
		$this->assertEmpty( get_post_meta( $download_id, '_edd_cached_recommendations', true ) );
	}

	/**
	 * Test sync_download ignores revisions.
	 */
	public function test_sync_download_ignores_revisions() {
		$download_id = self::factory()->post->create(
			array(
				'post_type'   => 'download',
				'post_status' => 'publish',
			)
		);

		// Create a revision post.
		$revision = (object) array(
			'post_type'   => 'revision',
			'post_status' => 'publish',
		);

		// Clear any existing scheduled events.
		$timestamp = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );
		if ( $timestamp ) {
			\EDD\Cron\Events\SingleEvent::remove( 'edd_sync_single_download', array( $download_id ) );
		}

		$this->sync->sync_download( $download_id, $revision );

		// Verify no cron event was scheduled.
		$next = \EDD\Cron\Events\SingleEvent::next_scheduled( 'edd_sync_single_download', array( $download_id ) );
		$this->assertFalse( $next );
	}
}

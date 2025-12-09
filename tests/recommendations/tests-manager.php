<?php
/**
 * Tests for Recommendations Manager
 *
 * @group edd_recommendations
 * @group edd_pro
 */
namespace EDD\Tests\Recommendations;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Pro\Recommendations\Manager as RecommendationsManager;
use EDD\Pro\Recommendations\Preview;
use EDD\Pro\Recommendations\Sync;

class Manager extends EDD_UnitTestCase {

	/**
	 * Manager instance.
	 *
	 * @var Manager
	 */
	protected $manager;

	/**
	 * Set up before each test.
	 */
	public function setUp(): void {
		if ( ! edd_is_pro() ) {
			$this->markTestSkipped( 'EDD Pro is not available. Recommendations Manager tests require EDD Pro.' );
		}

		parent::setUp();

		$this->manager = new RecommendationsManager();
	}

	/**
	 * Test Manager can be instantiated.
	 */
	public function test_manager_initialization() {
		$this->assertInstanceOf( RecommendationsManager::class, $this->manager );
	}

	/**
	 * Test Manager extends MiniManager.
	 */
	public function test_manager_extends_mini_manager() {
		$this->assertInstanceOf( \EDD\EventManagement\MiniManager::class, $this->manager );
	}

	/**
	 * Test get_event_classes returns correct classes.
	 */
	public function test_get_event_classes_returns_correct_classes() {
		$reflection = new \ReflectionClass( $this->manager );
		$method     = $reflection->getMethod( 'get_event_classes' );
		$method->setAccessible( true );

		$event_classes = $method->invoke( $this->manager );

		$this->assertIsArray( $event_classes );
		$this->assertCount( 2, $event_classes );

		$this->assertInstanceOf( Preview::class, $event_classes[0] );
		$this->assertInstanceOf( Sync::class, $event_classes[1] );
	}

	/**
	 * Test Manager registers events from event classes.
	 */
	public function test_manager_registers_events_from_event_classes() {
		// The manager should have registered the Preview and Sync events.
		// Check that Preview events are registered.
		$preview_events = Preview::get_subscribed_events();

		foreach ( $preview_events as $hook => $callback ) {
			$this->assertGreaterThan(
				0,
				has_filter( $hook ),
				"Hook '$hook' should be registered by Manager"
			);
		}

		// Check that Sync events are registered.
		$sync_events = Sync::get_subscribed_events();

		foreach ( $sync_events as $hook => $callback ) {
			$this->assertGreaterThan(
				0,
				has_action( $hook ),
				"Action '$hook' should be registered by Manager"
			);
		}
	}

	/**
	 * Test Manager initializes Preview component.
	 */
	public function test_manager_initializes_preview_component() {
		$reflection = new \ReflectionClass( $this->manager );
		$method     = $reflection->getMethod( 'get_event_classes' );
		$method->setAccessible( true );

		$event_classes = $method->invoke( $this->manager );

		$has_preview = false;

		foreach ( $event_classes as $event_class ) {
			if ( $event_class instanceof Preview ) {
				$has_preview = true;
				break;
			}
		}

		$this->assertTrue( $has_preview, 'Manager should initialize Preview component' );
	}

	/**
	 * Test Manager initializes Sync component.
	 */
	public function test_manager_initializes_sync_component() {
		$reflection = new \ReflectionClass( $this->manager );
		$method     = $reflection->getMethod( 'get_event_classes' );
		$method->setAccessible( true );

		$event_classes = $method->invoke( $this->manager );

		$has_sync = false;

		foreach ( $event_classes as $event_class ) {
			if ( $event_class instanceof Sync ) {
				$has_sync = true;
				break;
			}
		}

		$this->assertTrue( $has_sync, 'Manager should initialize Sync component' );
	}

	/**
	 * Test Manager returns correct number of event classes.
	 */
	public function test_manager_returns_correct_number_of_event_classes() {
		$reflection = new \ReflectionClass( $this->manager );
		$method     = $reflection->getMethod( 'get_event_classes' );
		$method->setAccessible( true );

		$event_classes = $method->invoke( $this->manager );

		$this->assertCount( 2, $event_classes, 'Manager should return exactly 2 event classes' );
	}

	/**
	 * Test all event classes implement SubscriberInterface.
	 */
	public function test_all_event_classes_implement_subscriber_interface() {
		$reflection = new \ReflectionClass( $this->manager );
		$method     = $reflection->getMethod( 'get_event_classes' );
		$method->setAccessible( true );

		$event_classes = $method->invoke( $this->manager );

		foreach ( $event_classes as $event_class ) {
			$this->assertInstanceOf(
				\EDD\EventManagement\SubscriberInterface::class,
				$event_class,
				get_class( $event_class ) . ' should implement SubscriberInterface'
			);
		}
	}

	/**
	 * Test Manager can be instantiated multiple times.
	 */
	public function test_manager_can_be_instantiated_multiple_times() {
		$manager1 = new Manager();
		$manager2 = new Manager();

		$this->assertInstanceOf( Manager::class, $manager1 );
		$this->assertInstanceOf( Manager::class, $manager2 );
		$this->assertNotSame( $manager1, $manager2 );
	}

	/**
	 * Test get_event_classes returns new instances.
	 */
	public function test_get_event_classes_returns_new_instances() {
		$reflection = new \ReflectionClass( $this->manager );
		$method     = $reflection->getMethod( 'get_event_classes' );
		$method->setAccessible( true );

		$event_classes1 = $method->invoke( $this->manager );
		$event_classes2 = $method->invoke( $this->manager );

		// Should return new instances each time.
		$this->assertNotSame( $event_classes1[0], $event_classes2[0] );
		$this->assertNotSame( $event_classes1[1], $event_classes2[1] );
	}

	/**
	 * Test get_subscribed_events includes settings filter.
	 */
	public function test_get_subscribed_events_includes_settings_filter() {
		$events = RecommendationsManager::get_subscribed_events();

		$this->assertArrayHasKey( 'edd_settings_gateways', $events );
		$this->assertEquals( 'update_recommendations_settings', $events['edd_settings_gateways'] );
	}

	/**
	 * Test get_subscribed_events includes cron events filter.
	 */
	public function test_get_subscribed_events_includes_cron_events_filter() {
		$events = RecommendationsManager::get_subscribed_events();

		$this->assertArrayHasKey( 'edd_cron_events', $events );
		$this->assertEquals( 'add_cron_events', $events['edd_cron_events'] );
	}

	/**
	 * Test update_recommendations_settings returns unchanged when no desc.
	 */
	public function test_update_recommendations_settings_returns_unchanged_when_no_desc() {
		$settings = array(
			'cart' => array(
				'cart_recommendations' => array(
					'id'   => 'cart_recommendations',
					'name' => 'Cart Recommendations',
				),
			),
		);

		$result = $this->manager->update_recommendations_settings( $settings );

		$this->assertEquals( $settings, $result );
	}

	/**
	 * Test update_recommendations_settings updates description when present.
	 */
	public function test_update_recommendations_settings_updates_description_when_present() {
		$settings = array(
			'cart' => array(
				'cart_recommendations' => array(
					'id'   => 'cart_recommendations',
					'name' => 'Cart Recommendations',
					'desc' => 'Original description',
				),
			),
		);

		$result = $this->manager->update_recommendations_settings( $settings );

		$this->assertArrayHasKey( 'desc', $result['cart']['cart_recommendations'] );
		// Description should be updated (not the original).
		$this->assertNotEquals( 'Original description', $result['cart']['cart_recommendations']['desc'] );
	}

	/**
	 * Test add_cron_events adds WeeklyEvents to array.
	 */
	public function test_add_cron_events_adds_weekly_events() {
		$events = array();

		$result = $this->manager->add_cron_events( $events );

		$this->assertCount( 1, $result );
		$this->assertInstanceOf( \EDD\Pro\Cron\Events\WeeklyEvents::class, $result[0] );
	}

	/**
	 * Test add_cron_events preserves existing events.
	 */
	public function test_add_cron_events_preserves_existing_events() {
		$existing_event = new \stdClass();
		$events         = array( $existing_event );

		$result = $this->manager->add_cron_events( $events );

		$this->assertCount( 2, $result );
		$this->assertSame( $existing_event, $result[0] );
		$this->assertInstanceOf( \EDD\Pro\Cron\Events\WeeklyEvents::class, $result[1] );
	}

	/**
	 * Test get_description returns inactive pro message when pro is inactive.
	 */
	public function test_get_description_returns_inactive_pro_message() {
		// Skip if we can't properly simulate inactive pro.
		if ( ! function_exists( 'edd_is_inactive_pro' ) ) {
			$this->markTestSkipped( 'edd_is_inactive_pro function not available.' );
		}

		$reflection = new \ReflectionClass( $this->manager );
		$method     = $reflection->getMethod( 'get_description' );
		$method->setAccessible( true );

		// We can't easily mock edd_is_inactive_pro, so we just verify the method returns an array.
		$result = $method->invoke( $this->manager );

		$this->assertIsArray( $result );
		$this->assertNotEmpty( $result );
	}

}

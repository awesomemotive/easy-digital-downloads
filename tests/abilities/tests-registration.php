<?php
/**
 * Tests for the EDD Abilities API registration surface.
 *
 * @package     EDD\Tests\Abilities
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests\Abilities;

use EDD\Abilities\Ability;
use EDD\Abilities\Loader;
use EDD\Abilities\Registry;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Registration tests for the EDD abilities surface.
 *
 * @since 3.7.1
 */
class Registration extends EDD_UnitTestCase {

	/**
	 * Skip the whole class when the Abilities API is unavailable (WP < 6.9).
	 */
	public function set_up() {
		parent::set_up();

		if ( ! function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'The WordPress Abilities API is not available.' );
		}
	}

	/**
	 * Gets the abilities the core loader registers.
	 *
	 * @return Ability[]
	 */
	private function get_loader_abilities(): array {
		return Registry::get();
	}

	/**
	 * Gets one ability's own input schema.
	 *
	 * @param Ability $ability The ability to read.
	 * @return array
	 */
	private function get_input_schema( Ability $ability ): array {
		$method = new \ReflectionMethod( $ability, 'get_input_schema' );
		$method->setAccessible( true );

		return (array) $method->invoke( $ability );
	}

	public function test_loader_subscribes_to_abilities_hooks() {
		$events = Loader::get_subscribed_events();

		$this->assertArrayHasKey( 'wp_abilities_api_categories_init', $events );
		$this->assertArrayHasKey( 'wp_abilities_api_init', $events );
	}

	public function test_all_loader_entries_are_abilities() {
		$abilities = $this->get_loader_abilities();

		// The loop passes vacuously on an empty set, so assert the surface is
		// actually there. Named slugs rather than a count, which drifts.
		$this->assertNotEmpty( $abilities );
		$this->assertArrayHasKey( 'order-read', $abilities );
		$this->assertArrayHasKey( 'discount-delete', $abilities );

		foreach ( $abilities as $ability ) {
			$this->assertInstanceOf( Ability::class, $ability );
		}
	}

	public function test_ability_names_are_unique_and_valid() {
		$names = array();
		foreach ( $this->get_loader_abilities() as $ability ) {
			$name = $ability->get_name();

			$this->assertMatchesRegularExpression( '/^[a-z0-9-]+\/[a-z0-9-]+$/', $name );
			$this->assertStringStartsWith( 'edd/', $name );
			$this->assertNotContains( $name, $names, "Duplicate ability name: {$name}" );

			$names[] = $name;
		}
	}

	public function test_edd_category_is_registered() {
		$this->assertTrue( wp_has_ability_category( Loader::CATEGORY ) );

		$category = wp_get_ability_category( Loader::CATEGORY );
		$this->assertSame( 'Easy Digital Downloads', $category->get_label() );
	}

	public function test_all_abilities_are_registered_with_wordpress() {
		foreach ( $this->get_loader_abilities() as $ability ) {
			$name = $ability->get_name();

			$this->assertTrue( wp_has_ability( $name ), "Ability {$name} is not registered." );

			$registered = wp_get_ability( $name );
			$this->assertNotEmpty( $registered->get_label() );
			$this->assertNotEmpty( $registered->get_description() );
			$this->assertSame( Loader::CATEGORY, $registered->get_category() );

			$meta = $registered->get_meta();
			$this->assertTrue( $meta['show_in_rest'], "Ability {$name} is not exposed over REST." );

			$annotations = $meta['annotations'];
			foreach ( array( 'readonly', 'destructive', 'idempotent' ) as $key ) {
				$this->assertIsBool( $annotations[ $key ], "Ability {$name} annotation {$key} is not boolean." );
			}

			// A readonly ability can never be destructive.
			if ( $annotations['readonly'] ) {
				$this->assertFalse( $annotations['destructive'], "Ability {$name} is readonly but destructive." );
			}
		}
	}

	/**
	 * Re-firing the registration hook registers nothing a second time.
	 *
	 * Anything else on `wp_abilities_api_init` can fire it again, and the
	 * WordPress registry reports a repeat name through _doing_it_wrong(), which
	 * this test case turns into a failure on its own.
	 */
	public function test_firing_the_registration_hook_twice_registers_each_ability_once() {
		$abilities = $this->get_loader_abilities();

		// The fixture: the surface is already registered from the first firing,
		// so the second one below is what a repeat name would come from.
		$this->assertNotEmpty( $abilities );
		foreach ( $abilities as $ability ) {
			$this->assertTrue(
				wp_has_ability( $ability->get_name() ),
				"Ability {$ability->get_name()} was not registered before the second firing."
			);
		}

		$before = count( wp_get_abilities( array( 'category' => Loader::CATEGORY ) ) );

		do_action( 'wp_abilities_api_init' );

		$after = count( wp_get_abilities( array( 'category' => Loader::CATEGORY ) ) );

		$this->assertSame( $before, $after, 'Re-firing the hook changed how many abilities are registered.' );

		foreach ( $abilities as $ability ) {
			$this->assertTrue(
				wp_has_ability( $ability->get_name() ),
				"Ability {$ability->get_name()} is missing after the hook fired twice."
			);
		}
	}

	public function test_execute_denies_user_without_capability() {
		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$ability = wp_get_ability( 'edd/order-read' );
		$result  = $ability->execute( array() );

		$this->assertWPError( $result );
		$this->assertSame( 'ability_invalid_permissions', $result->get_error_code() );
	}

	public function test_no_registered_ability_is_reachable_by_a_subscriber() {
		$abilities = $this->get_loader_abilities();

		// An empty set would satisfy the loop below without measuring anything.
		$this->assertNotEmpty( $abilities );

		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// check_permissions() rather than execute(): execute() validates input first,
		// so an ability with required input would report input rather than permissions.
		foreach ( $abilities as $ability ) {
			$this->assertFalse(
				$ability->check_permissions( array() ),
				get_class( $ability ) . ' must deny a subscriber.'
			);
		}
	}

	public function test_rest_run_route_denies_a_subscriber() {
		// The assertion below cannot distinguish a denied request from an absent route.
		$this->assertContains( 'wp-abilities/v1', rest_get_server()->get_namespaces() );

		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		$response = rest_get_server()->dispatch(
			new \WP_REST_Request( 'GET', '/wp-abilities/v1/abilities/edd/order-read/run' )
		);

		$this->assertSame( 403, $response->get_status() );
		$this->assertSame( 'rest_ability_cannot_execute', $response->get_data()['code'] );
	}

	public function test_execute_allows_administrator() {
		wp_set_current_user( 1 );

		$ability = wp_get_ability( 'edd/order-read' );
		$result  = $ability->execute( array() );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'orders', $result );
		$this->assertArrayHasKey( 'total', $result );
	}

	public function test_input_validation_rejects_unknown_properties() {
		wp_set_current_user( 1 );

		$ability = wp_get_ability( 'edd/order-read' );
		$result  = $ability->execute( array( 'nonsense_property' => true ) );

		$this->assertWPError( $result );
		$this->assertSame( 'ability_invalid_input', $result->get_error_code() );
	}

	public function test_no_input_abilities_accept_empty_object_input() {
		wp_set_current_user( 1 );

		// MCP clients routinely send an empty object instead of omitting input.
		foreach ( array( 'edd/sales-summary', 'edd/settings', 'edd/health-check' ) as $name ) {
			$result = wp_get_ability( $name )->execute( array() );
			$this->assertIsArray( $result, "Ability {$name} rejected empty-object input." );
		}
	}

	/**
	 * Extensions can add to what a read ability returns.
	 *
	 * The filter fires in one place for every read ability, so the callback is
	 * told which ability it is looking at and what input produced the result.
	 */
	public function test_a_filter_can_add_a_key_to_a_read_ability_output() {
		$seen     = array();
		$callback = function ( $output, $name, $input ) use ( &$seen ) {
			$seen[]           = array( $name, $input );
			$output['tagged'] = $name;

			return $output;
		};

		add_filter( 'edd/abilities/read_output', $callback, 10, 3 );

		$result = Registry::get_ability( 'health-check' )->execute( array() );

		remove_filter( 'edd/abilities/read_output', $callback, 10 );

		$this->assertIsArray( $result );
		$this->assertArrayHasKey( 'tagged', $result );
		$this->assertSame( 'edd/health-check', $result['tagged'] );
		$this->assertCount( 1, $seen );
		$this->assertSame( 'edd/health-check', $seen[0][0] );
		$this->assertIsArray( $seen[0][1] );
	}

	/**
	 * A callback which hands back something other than an array is discarded.
	 *
	 * A callback which forgets to return gives back null, and a response built
	 * from that would no longer match the ability's own output schema.
	 */
	public function test_a_read_filter_which_returns_a_non_array_is_discarded() {
		// The fixture: the unfiltered response, so the assertion below compares
		// against what the ability returns rather than a shape guessed here.
		$expected = Registry::get_ability( 'health-check' )->execute( array() );
		$this->assertNotEmpty( $expected );

		$callback = function () {
			return null;
		};

		add_filter( 'edd/abilities/read_output', $callback );

		$result = Registry::get_ability( 'health-check' )->execute( array() );

		remove_filter( 'edd/abilities/read_output', $callback );

		$this->assertSame( $expected, $result );
	}

	/**
	 * Write abilities are not filtered, so a callback cannot reshape a write.
	 */
	public function test_the_output_filter_does_not_fire_for_a_write_ability() {
		$fired    = 0;
		$callback = function ( $output ) use ( &$fired ) {
			++$fired;

			return $output;
		};

		add_filter( 'edd/abilities/read_output', $callback );

		// The fixture: the same filter fires for a read, so a zero count below
		// is the write being left alone and not a filter which never ran.
		Registry::get_ability( 'health-check' )->execute( array() );
		$after_read = $fired;

		$order = parent::edd()->order->create( array( 'status' => 'pending' ) );
		$write = Registry::get_ability( 'order-update-status' )->execute(
			array(
				'order_id' => $order,
				'status'   => 'complete',
			)
		);

		remove_filter( 'edd/abilities/read_output', $callback );

		$this->assertSame( 1, $after_read, 'The filter never fired for a read ability.' );

		// The fixture: a write which errored out would report no filter either.
		$this->assertIsArray( $write );
		$this->assertSame( 'complete', edd_get_order( $order )->status );

		$this->assertSame( 1, $fired );
	}

	/**
	 * A missing required input is named from the ability's own schema.
	 *
	 * require_input() reads the schema's `required` list, so an ability declaring
	 * a key and an ability asking for it are one statement. A schema which stops
	 * declaring a key fails here rather than letting execute() read it as absent.
	 */
	public function test_a_missing_required_input_names_the_field_from_the_schema() {
		$checked = array();

		foreach ( $this->get_loader_abilities() as $slug => $ability ) {
			$required = $this->get_input_schema( $ability )['required'] ?? array();
			if ( empty( $required ) ) {
				continue;
			}

			$field  = (string) reset( $required );
			$result = $ability->execute( array() );

			$this->assertWPError( $result, "Ability {$slug} accepted empty input." );
			$this->assertSame( 'edd_ability_missing_input', $result->get_error_code(), "Ability {$slug} did not report missing input." );
			$this->assertStringContainsString( $field, $result->get_error_message(), "Ability {$slug} did not name {$field}." );

			$checked[] = $slug;
		}

		// Named slugs rather than a count, which drifts. Without them an empty
		// set would satisfy the loop above without measuring anything.
		$this->assertContains( 'order-create', $checked );
		$this->assertContains( 'order-update-status', $checked );
		$this->assertContains( 'order-note-add', $checked );
		$this->assertContains( 'customer-create', $checked );
		$this->assertContains( 'discount-delete', $checked );
	}
}

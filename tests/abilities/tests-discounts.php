<?php
/**
 * Tests for the EDD discount abilities.
 *
 * @package     EDD\Tests\Abilities
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.1
 */

namespace EDD\Tests\Abilities;

use EDD\Abilities\Discounts\Create;
use EDD\Abilities\Discounts\Delete;
use EDD\Abilities\Discounts\Read;
use EDD\Abilities\Discounts\Update;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Discount ability tests.
 *
 * @since 3.7.1
 */
class Discounts extends EDD_UnitTestCase {

	/**
	 * Set the current user to the site administrator, who holds all shop capabilities.
	 */
	public function set_up() {
		parent::set_up();
		wp_set_current_user( 1 );
	}

	/**
	 * Creates a discount via the test factory.
	 *
	 * @param array $args Overrides for the discount arguments.
	 * @return int The discount ID.
	 */
	private function create_discount( array $args = array() ): int {
		return parent::edd()->discount->create(
			wp_parse_args(
				$args,
				array(
					'name'        => 'Test Discount',
					'code'        => 'TESTDISCOUNT',
					'amount'      => 10,
					'amount_type' => 'percent',
					'status'      => 'active',
				)
			)
		);
	}

	public function test_discount_read_by_id_and_code() {
		$discount_id = $this->create_discount(
			array(
				'name'   => 'Read Me',
				'code'   => 'READMEDISC',
				'amount' => 15,
			)
		);

		$ability = new Read();

		$result = $ability->execute( array( 'id' => $discount_id ) );
		$this->assertIsArray( $result );
		$this->assertSame( 1, $result['total'] );

		$discount = $result['discounts'][0];
		$this->assertSame( $discount_id, $discount['id'] );
		$this->assertSame( 'READMEDISC', $discount['code'] );
		$this->assertSame( 'Read Me', $discount['name'] );
		$this->assertSame( 'active', $discount['status'] );
		$this->assertSame( 'percent', $discount['type'] );
		$this->assertEqualsWithDelta( 15.0, $discount['amount'], 0.001 );

		$by_code = $ability->execute( array( 'code' => 'READMEDISC' ) );
		$this->assertSame( $discount_id, $by_code['discounts'][0]['id'] );
	}

	public function test_discount_read_list_filters_by_status_and_type() {
		$this->create_discount(
			array(
				'name' => 'Percent Active',
				'code' => 'PCTACTIVE',
			)
		);
		$this->create_discount(
			array(
				'name'        => 'Flat Inactive',
				'code'        => 'FLATINACTIVE',
				'amount'      => 5,
				'amount_type' => 'flat',
				'status'      => 'inactive',
			)
		);

		$ability = new Read();

		$actives = $ability->execute( array( 'status' => 'active' ) );
		$this->assertGreaterThanOrEqual( 1, $actives['total'] );
		foreach ( $actives['discounts'] as $discount ) {
			$this->assertSame( 'active', $discount['status'] );
		}
		$this->assertContains( 'PCTACTIVE', wp_list_pluck( $actives['discounts'], 'code' ) );

		$flats = $ability->execute( array( 'type' => 'flat' ) );
		$this->assertGreaterThanOrEqual( 1, $flats['total'] );
		foreach ( $flats['discounts'] as $discount ) {
			$this->assertSame( 'flat', $discount['type'] );
		}
		$this->assertContains( 'FLATINACTIVE', wp_list_pluck( $flats['discounts'], 'code' ) );
	}

	public function test_discount_read_missing_returns_not_found() {
		$ability = new Read();

		$result = $ability->execute( array( 'id' => 999999 ) );
		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_discount_not_found', $result->get_error_code() );
		$this->assertSame( 404, $result->get_error_data()['status'] );

		$result = $ability->execute( array( 'code' => 'NOSUCHCODE' ) );
		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_discount_not_found', $result->get_error_code() );
	}

	public function test_discount_create_percent_happy_path() {
		$ability    = new Create();
		$start_date = gmdate( 'Y-m-d', strtotime( '+1 day' ) );
		$end_date   = gmdate( 'Y-m-d', strtotime( '+30 days' ) );
		$result     = $ability->execute(
			array(
				'code'              => 'SUMMER25',
				'name'              => 'Summer Sale',
				'amount'            => 25,
				'type'              => 'percent',
				'start_date'        => $start_date,
				'end_date'          => $end_date,
				'max_uses'          => 10,
				'min_charge_amount' => 50,
				'once_per_customer' => true,
			)
		);

		$this->assertIsArray( $result );
		$this->assertGreaterThan( 0, $result['id'] );
		$this->assertSame( 'SUMMER25', $result['code'] );
		$this->assertSame( 'Summer Sale', $result['name'] );
		$this->assertSame( 'inactive', $result['status'] );
		$this->assertSame( 'percent', $result['type'] );
		$this->assertEqualsWithDelta( 25.0, $result['amount'], 0.001 );
		$this->assertSame( 'global', $result['scope'] );
		$this->assertSame( 0, $result['use_count'] );
		$this->assertSame( 10, $result['max_uses'] );
		$this->assertEqualsWithDelta( 50.0, $result['min_charge_amount'], 0.001 );
		$this->assertTrue( $result['once_per_customer'] );
		$this->assertFalse( $result['is_maxed_out'] );

		// Dates normalize to the start and end of the requested days.
		$this->assertSame( "{$start_date} 00:00:00", $result['start_date'] );
		$this->assertSame( "{$end_date} 23:59:59", $result['end_date'] );
	}

	public function test_discount_create_defaults_to_inactive() {
		$ability = new Create();
		$result  = $ability->execute(
			array(
				'code'   => 'NEEDSREVIEW',
				'name'   => 'Needs Review',
				'amount' => 10,
				'type'   => 'percent',
			)
		);

		$this->assertSame( 'inactive', $result['status'] );

		// A code the store cannot yet use is not usable at checkout.
		$discount = edd_get_discount_by_code( 'NEEDSREVIEW' );
		$this->assertNotEmpty( $discount );
		$this->assertSame( 'inactive', $discount->status );
	}

	public function test_discount_create_honors_an_explicit_active_status() {
		$ability = new Create();
		$result  = $ability->execute(
			array(
				'code'   => 'LIVENOW',
				'name'   => 'Live Now',
				'amount' => 10,
				'type'   => 'percent',
				'status' => 'active',
			)
		);

		$this->assertSame( 'active', $result['status'] );
	}

	public function test_discount_read_reports_a_maxed_out_code() {
		$discount_id = $this->create_discount(
			array(
				'code'      => 'MAXEDOUT',
				'max_uses'  => 1,
				'use_count' => 1,
			)
		);

		// Assert the fixture actually carries the usage the assertion depends on.
		$discount = edd_get_discount( $discount_id );
		$this->assertSame( 1, (int) $discount->max_uses );
		$this->assertSame( 1, (int) $discount->use_count );

		$result = ( new Read() )->execute( array( 'id' => $discount_id ) );

		$this->assertIsArray( $result );
		$this->assertCount( 1, $result['discounts'] );
		$this->assertTrue( $result['discounts'][0]['is_maxed_out'] );
	}

	public function test_discount_create_duplicate_code_conflicts() {
		$this->create_discount( array( 'code' => 'DUPLICATED' ) );

		$ability = new Create();
		$result  = $ability->execute(
			array(
				'code'   => 'DUPLICATED',
				'name'   => 'Duplicate',
				'amount' => 10,
				'type'   => 'percent',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_discount_code_exists', $result->get_error_code() );
		$this->assertSame( 409, $result->get_error_data()['status'] );
	}

	public function test_discount_create_percent_over_100_fails() {
		$ability = new Create();
		$result  = $ability->execute(
			array(
				'code'   => 'TOOBIG',
				'name'   => 'Too Big',
				'amount' => 150,
				'type'   => 'percent',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_amount', $result->get_error_code() );
	}

	public function test_discount_update_changes_amount() {
		$discount_id = $this->create_discount( array( 'code' => 'AMOUNTCHANGE' ) );

		$ability = new Update();
		$result  = $ability->execute(
			array(
				'discount_id' => $discount_id,
				'amount'      => 20,
			)
		);

		$this->assertIsArray( $result );
		$this->assertEqualsWithDelta( 20.0, $result['amount'], 0.001 );
		$this->assertEqualsWithDelta( 20.0, floatval( edd_get_discount( $discount_id )->amount ), 0.001 );
	}

	public function test_discount_update_max_uses_zero_clears_limit() {
		$discount_id = $this->create_discount(
			array(
				'code'     => 'CLEARLIMIT',
				'max_uses' => 5,
			)
		);

		$ability = new Update();
		$result  = $ability->execute(
			array(
				'discount_id' => $discount_id,
				'max_uses'    => 0,
			)
		);

		$this->assertIsArray( $result );
		$this->assertNull( $result['max_uses'] );
	}

	public function test_discount_update_status_archived() {
		$discount_id = $this->create_discount( array( 'code' => 'ARCHIVEME' ) );

		$ability = new Update();
		$result  = $ability->execute(
			array(
				'discount_id' => $discount_id,
				'status'      => 'archived',
			)
		);

		$this->assertIsArray( $result );
		$this->assertSame( 'archived', $result['status'] );
	}

	public function test_discount_delete_unused_discount() {
		$discount_id = $this->create_discount( array( 'code' => 'DELETEME' ) );

		$ability = new Delete();
		$result  = $ability->execute( array( 'discount_id' => $discount_id ) );

		$this->assertIsArray( $result );
		$this->assertTrue( $result['deleted'] );
		$this->assertSame( $discount_id, $result['discount_id'] );
		$this->assertFalse( edd_get_discount( $discount_id ) );
	}

	public function test_discount_delete_used_discount_conflicts() {
		$discount_id = $this->create_discount( array( 'code' => 'USEDCODE' ) );
		edd_update_discount( $discount_id, array( 'use_count' => 3 ) );

		$ability = new Delete();
		$result  = $ability->execute( array( 'discount_id' => $discount_id ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_discount_in_use', $result->get_error_code() );
		$this->assertSame( 409, $result->get_error_data()['status'] );

		// The discount was not deleted.
		$this->assertNotEmpty( edd_get_discount( $discount_id ) );
	}

	public function test_discount_delete_missing_returns_not_found() {
		$ability = new Delete();
		$result  = $ability->execute( array( 'discount_id' => 999999 ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_discount_not_found', $result->get_error_code() );
		$this->assertSame( 404, $result->get_error_data()['status'] );
	}

	/**
	 * Deleting routes over POST, not DELETE; WriteAbility::get_annotations() says why.
	 */
	public function test_discount_delete_is_destructive_and_not_idempotent() {
		if ( ! function_exists( 'wp_register_ability' ) ) {
			$this->markTestSkipped( 'The WordPress Abilities API is not available.' );
		}

		$this->assertTrue( wp_has_ability( 'edd/discount-delete' ) );

		$this->assertSame(
			array(
				'readonly'    => false,
				'destructive' => true,
				'idempotent'  => false,
			),
			wp_get_ability( 'edd/discount-delete' )->get_meta()['annotations']
		);
	}

	/**
	 * A discount row with no stored scope is reported as global.
	 *
	 * The column defaults to an empty string, so a row saved without the scope
	 * radio carries neither declared value, and the schema declares both.
	 */
	public function test_discount_read_reports_an_empty_scope_as_global() {
		$discount_id = $this->create_discount( array( 'code' => 'NOSCOPE' ) );

		// The fixture: the assertion only measures the formatter if the stored
		// value really is empty.
		$this->assertSame( '', (string) edd_get_discount( $discount_id )->scope );

		$result = ( new Read() )->execute( array( 'id' => $discount_id ) );

		$this->assertIsArray( $result );
		$this->assertSame( 'global', $result['discounts'][0]['scope'] );
	}

	public function test_discount_create_rejects_a_start_date_not_given_as_year_month_day() {
		// A full ISO-8601 string is the shape a caller reaches for, and the one
		// EDD's date helper would silently shift to the current time.
		$result = ( new Create() )->execute(
			array(
				'code'       => 'ISODATE',
				'name'       => 'ISO Date',
				'amount'     => 10,
				'type'       => 'percent',
				'start_date' => '2026-06-01T00:00:00Z',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_input', $result->get_error_code() );
		$this->assertStringContainsString( 'start_date', $result->get_error_message() );
	}

	public function test_discount_create_rejects_an_impossible_calendar_date() {
		$result = ( new Create() )->execute(
			array(
				'code'       => 'BADDATE',
				'name'       => 'Bad Date',
				'amount'     => 10,
				'type'       => 'percent',
				'start_date' => '2026-13-45',
			)
		);

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_invalid_date', $result->get_error_code() );
	}

	public function test_discount_create_stores_the_requested_start_date() {
		$result = ( new Create() )->execute(
			array(
				'code'       => 'GOODDATE',
				'name'       => 'Good Date',
				'amount'     => 10,
				'type'       => 'percent',
				'start_date' => '2026-06-01',
			)
		);

		$this->assertIsArray( $result );

		// A guard that rejected everything would also pass the two tests above,
		// so assert the accepted date actually lands on the requested day.
		$discount = edd_get_discount( $result['id'] );
		$this->assertStringStartsWith( '2026-06-01', $discount->start_date );
	}

	public function test_discount_date_inputs_are_validated_by_the_schema() {
		$ability = new Create();
		$method  = new \ReflectionMethod( $ability, 'get_input_schema' );
		$method->setAccessible( true );
		$schema = $method->invoke( $ability );

		foreach ( array( 'start_date', 'end_date' ) as $field ) {
			$property = $schema['properties'][ $field ];

			// The Abilities API has no validator for `format: date`, so the
			// pattern is what actually rejects a bad value.
			$this->assertTrue( rest_validate_value_from_schema( '2026-06-01', $property, $field ) );
			$this->assertWPError( rest_validate_value_from_schema( '2026-06-01T00:00:00Z', $property, $field ) );
			$this->assertWPError( rest_validate_value_from_schema( 'banana', $property, $field ) );
		}
	}

	public function test_discount_update_with_no_fields_reports_no_updates() {
		$discount_id = $this->create_discount();

		// Mirrors customer-update, which returns 400 rather than reporting the
		// unchanged record as a successful write.
		$result = ( new Update() )->execute( array( 'discount_id' => $discount_id ) );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_no_updates', $result->get_error_code() );
	}

	public function test_discount_capabilities_and_write_gate() {
		$original = edd_get_option( \EDD\Abilities\Loader::WRITE_SETTING, false );

		$read   = new Read();
		$writes = array( new Create(), new Update(), new Delete() );

		$user_id = self::factory()->user->create( array( 'role' => 'subscriber' ) );
		wp_set_current_user( $user_id );

		// Open the gate first, or it would deny the writes on the store setting and
		// this phase would never reach their capability at all.
		edd_update_option( \EDD\Abilities\Loader::WRITE_SETTING, true );

		foreach ( array_merge( array( $read ), $writes ) as $ability ) {
			$this->assertFalse(
				$ability->check_permissions( array() ),
				get_class( $ability ) . ' must deny a subscriber.'
			);
		}

		// The capability alone opens the read and none of the writes.
		wp_get_current_user()->add_cap( 'manage_shop_discounts' );
		edd_delete_option( \EDD\Abilities\Loader::WRITE_SETTING );

		$this->assertTrue( $read->check_permissions( array() ) );
		foreach ( $writes as $ability ) {
			$this->assertFalse(
				$ability->check_permissions( array() ),
				get_class( $ability ) . ' must also require the write setting.'
			);
		}

		// Opening the store setting is what makes the capability sufficient.
		edd_update_option( \EDD\Abilities\Loader::WRITE_SETTING, true );
		foreach ( $writes as $ability ) {
			$this->assertTrue(
				$ability->check_permissions( array() ),
				get_class( $ability ) . ' must allow the capability once writes are on.'
			);
		}

		if ( false === $original ) {
			edd_delete_option( \EDD\Abilities\Loader::WRITE_SETTING );
		} else {
			edd_update_option( \EDD\Abilities\Loader::WRITE_SETTING, $original );
		}
	}

	public function test_discount_update_reports_a_write_that_did_not_land() {
		$discount_id = $this->create_discount();

		// Force the failure a race would produce: the row is gone by the time
		// the write runs, so edd_update_discount() cannot apply it.
		$vanish = function ( $data, $id ) {
			edd_delete_discount( $id );
		};
		add_action( 'edd_pre_update_discount', $vanish, 10, 2 );

		$result = ( new Update() )->execute(
			array(
				'discount_id' => $discount_id,
				'amount'      => 50,
			)
		);

		remove_action( 'edd_pre_update_discount', $vanish, 10 );

		$this->assertWPError( $result );
		$this->assertSame( 'edd_ability_discount_not_updated', $result->get_error_code() );
	}
}

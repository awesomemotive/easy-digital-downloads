<?php
/**
 * Manual discount deactivation tests.
 *
 * @package     EDD\Tests\Discounts
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.2
 */

namespace EDD\Tests\Discounts;

defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Discounts\ManualDeactivation;
use EDD\Tests\PHPUnit\EDD_UnitTestCase;

/**
 * Tests telling a discount set inactive on purpose from one deactivated by reaching its cap.
 *
 * @group edd_discounts
 * @coversDefaultClass \EDD\Discounts\ManualDeactivation
 */
class ManualDeactivationTest extends EDD_UnitTestCase {

	use DiscountFixtures;

	/**
	 * The request URI before the test.
	 *
	 * @var string|null
	 */
	private $original_request_uri;

	/**
	 * The current user before the test.
	 *
	 * @var int
	 */
	private $original_user_id;

	/**
	 * Sets up each test.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();
		$this->forget_cached_claims();

		$this->original_user_id     = get_current_user_id();
		$this->original_request_uri = $_SERVER['REQUEST_URI'] ?? null;
		$_SERVER['REQUEST_URI']     = '/wp-admin/edit.php?post_type=download&page=edd-discounts';
	}

	/**
	 * Tears down each test.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		wp_set_current_user( $this->original_user_id );

		if ( is_null( $this->original_request_uri ) ) {
			unset( $_SERVER['REQUEST_URI'] );
		} else {
			$_SERVER['REQUEST_URI'] = $this->original_request_uri;
		}

		parent::tear_down();
	}

	/**
	 * @covers ::record
	 */
	public function test_setting_a_discount_inactive_marks_it_deactivated_on_purpose() {
		$discount = $this->create_discount();

		edd_update_discount_status( $discount->id, 'inactive' );

		$this->assertTrue( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::record
	 */
	public function test_reactivating_a_discount_clears_the_mark() {
		$discount = $this->create_discount();
		edd_update_discount_status( $discount->id, 'inactive' );

		edd_update_discount_status( $discount->id, 'active' );

		$this->assertFalse( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::record
	 */
	public function test_archiving_a_discount_set_inactive_clears_the_mark() {
		$discount = $this->create_discount();
		edd_update_discount_status( $discount->id, 'inactive' );

		edd_update_discount_status( $discount->id, 'archived' );

		$this->assertFalse( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::record
	 */
	public function test_reaching_the_cap_does_not_mark_the_discount() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );

		edd_get_discount( $discount->id )->increase_usage();

		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
		$this->assertFalse( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::record
	 */
	public function test_setting_a_discount_inactive_at_its_cap_does_not_mark_it() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
			)
		);

		edd_update_discount_status( $discount->id, 'inactive' );

		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
		$this->assertFalse( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::mark
	 */
	public function test_deactivating_a_used_up_code_from_the_list_marks_it_deactivated_on_purpose() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php';
		$discount = $this->used_up_discount();
		wp_set_current_user( $this->discount_manager() );

		edd_deactivate_discount(
			array(
				'_wpnonce' => wp_create_nonce( 'edd_discount_nonce' ),
				'discount' => $discount->id,
			)
		);

		$this->assertTrue( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::mark
	 */
	public function test_failed_deactivation_from_the_list_does_not_mark_the_code() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/discounts/discount-actions.php';
		global $wpdb;
		$discount = $this->create_discount();
		wp_set_current_user( $this->discount_manager() );

		$fail_status_update = function ( $query ) use ( $wpdb ) {
			if ( 0 === strpos( $query, 'UPDATE' ) && false !== strpos( $query, $wpdb->edd_adjustments ) ) {
				$query .= ' AND 1 = 0';
			}

			return $query;
		};
		add_filter( 'query', $fail_status_update );

		edd_deactivate_discount(
			array(
				'_wpnonce' => wp_create_nonce( 'edd_discount_nonce' ),
				'discount' => $discount->id,
			)
		);

		remove_filter( 'query', $fail_status_update );

		$this->assertSame( 'active', edd_get_discount( $discount->id )->status );
		$this->assertFalse( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::mark
	 */
	public function test_saving_an_active_code_at_its_cap_as_inactive_marks_it_deactivated_on_purpose() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
			)
		);
		$this->assertSame( 'active', edd_get_discount( $discount->id )->status );

		$this->save_from_edit_screen( $discount, 'inactive' );

		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
		$this->assertTrue( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::mark
	 */
	public function test_saving_an_expired_code_at_its_cap_as_inactive_marks_it_deactivated_on_purpose() {
		$discount = $this->create_discount(
			array(
				'max_uses'  => 1,
				'use_count' => 1,
				'status'    => 'expired',
			)
		);
		$this->assertSame( 'expired', edd_get_discount( $discount->id )->status );

		$this->save_from_edit_screen( $discount, 'inactive' );

		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
		$this->assertTrue( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::mark
	 */
	public function test_saving_a_used_up_code_as_inactive_does_not_mark_it() {
		$discount = $this->used_up_discount();

		$this->save_from_edit_screen( $discount, 'inactive' );

		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
		$this->assertFalse( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::record
	 */
	public function test_creating_a_discount_inactive_does_not_mark_it() {
		$discount = $this->create_discount( array( 'status' => 'inactive' ) );

		$this->assertSame( 'inactive', edd_get_discount( $discount->id )->status );
		$this->assertFalse( ManualDeactivation::is_manual( $discount->id ) );
	}

	/**
	 * @covers ::record
	 */
	public function test_setting_another_adjustment_type_inactive_does_not_mark_it() {
		$adjustment_id = edd_add_adjustment(
			array(
				'name'   => 'Handling',
				'type'   => 'fee',
				'status' => 'active',
			)
		);

		edd_update_adjustment( $adjustment_id, array( 'status' => 'inactive' ) );

		$this->assertSame( 'inactive', edd_get_adjustment( $adjustment_id )->status );
		$this->assertFalse( ManualDeactivation::is_manual( $adjustment_id ) );
	}

	/**
	 * Creates a discount deactivated by reaching its cap.
	 *
	 * @return \EDD_Discount
	 */
	private function used_up_discount() {
		$discount = $this->create_discount( array( 'max_uses' => 1 ) );
		edd_get_discount( $discount->id )->increase_usage();

		$discount = edd_get_discount( $discount->id );
		$this->assertSame( 'inactive', $discount->status );
		$this->assertFalse( ManualDeactivation::is_manual( $discount->id ) );

		return $discount;
	}

	/**
	 * Saves a discount from the edit screen with a new status.
	 *
	 * @param \EDD_Discount $discount The discount.
	 * @param string        $status   The status posted by the form.
	 * @return void
	 */
	private function save_from_edit_screen( $discount, $status ) {
		wp_set_current_user( $this->discount_manager() );

		( new \EDD\Admin\Discounts\Manager() )->update(
			array(
				'edd-discount-nonce' => wp_create_nonce( 'edd_discount_nonce' ),
				'discount-id'        => $discount->id,
				'name'               => $discount->name,
				'code'               => $discount->code,
				'type'               => $discount->type,
				'amount'             => $discount->amount,
				'max_uses'           => $discount->max_uses,
				'status'             => $status,
			)
		);
	}

	/**
	 * Creates a user who can manage discounts.
	 *
	 * @return int The user ID.
	 */
	private function discount_manager() {
		return self::factory()->user->create( array( 'role' => 'administrator' ) );
	}
}

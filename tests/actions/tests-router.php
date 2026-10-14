<?php

/**
 * Request action router tests.
 *
 * @package EDD\Tests\Actions
 */

namespace EDD\Tests\Actions;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Actions\Router;
use EDD\Database\Query;
use EDD\Tests\Helpers\Samples\RouterSampleQuery;

class RequestRouter extends EDD_UnitTestCase {

	/**
	 * Hooks fired during a test, in order.
	 *
	 * @var array
	 */
	private $fired = array();

	public function set_up() {
		parent::set_up();

		$this->fired = array();
		Router::reset();
	}

	public function tear_down() {
		unset( EDD()->components['router_sample'] );
		Router::reset();
		set_current_screen( 'front' );
		unset( $_GET[ Router::FRONTEND_PARAM ], $_POST[ Router::FRONTEND_PARAM ], $_GET[ Router::ADMIN_PARAM ], $_POST[ Router::ADMIN_PARAM ] );

		parent::tear_down();
	}

	public function test_frontend_action_is_dispatched() {
		$this->listen_for( 'edd_router_sample_action' );

		Router::frontend( array( Router::FRONTEND_PARAM => 'router_sample_action' ) );

		$this->assertSame( array( 'edd_router_sample_action' ), $this->fired );
	}

	public function test_request_array_is_passed_to_the_hook() {
		$received = null;
		add_action(
			'edd_router_sample_action',
			function ( $request ) use ( &$received ) {
				$received = $request;
			}
		);

		$request = array(
			Router::FRONTEND_PARAM => 'router_sample_action',
			'order_id'             => '42',
		);
		Router::frontend( $request );

		$this->assertSame( $request, $received, 'The hook should receive the request array unchanged.' );
	}

	public function test_action_key_is_sanitized() {
		$this->listen_for( 'edd_router_sample_action' );

		// sanitize_key() lowercases, so the uppercase value must reach the same hook.
		Router::frontend( array( Router::FRONTEND_PARAM => 'Router_Sample_Action' ) );

		$this->assertSame( array( 'edd_router_sample_action' ), $this->fired );
	}

	public function test_admin_action_key_is_sanitized() {
		$this->listen_for( 'edd_router_sample_action' );

		$this->use_admin_screen();

		Router::admin( array( Router::ADMIN_PARAM => 'ROUTER_SAMPLE_ACTION!!' ) );

		$this->assertSame(
			array( 'edd_router_sample_action' ),
			$this->fired,
			'The admin dispatcher must sanitize the key rather than concatenating it raw.'
		);
	}

	public function test_empty_action_dispatches_nothing() {
		$this->listen_for( 'edd_' );

		$this->use_admin_screen();

		Router::admin( array( Router::ADMIN_PARAM => '' ) );

		$this->assertSame( array(), $this->fired, 'An empty action must not fire a bare edd_ hook.' );
	}

	public function test_admin_dispatch_is_refused_on_the_front_end() {
		$this->listen_for( 'edd_router_sample_action' );

		// No admin screen is set, so this is a front-end request.
		Router::admin( array( Router::ADMIN_PARAM => 'router_sample_action' ) );

		$this->assertSame(
			array(),
			$this->fired,
			'The admin dispatcher must refuse to run outside an admin or CLI request.'
		);
	}

	public function test_non_scalar_action_dispatches_nothing() {
		$this->listen_for( 'edd_router_sample_action' );

		Router::frontend( array( Router::FRONTEND_PARAM => array( 'router_sample_action' ) ) );

		$this->assertSame( array(), $this->fired );
	}

	public function test_blocked_hook_is_not_dispatched() {
		$this->listen_for( 'edd_router_blocked_sample_action' );
		$this->block( 'edd_router_blocked_sample_action' );

		Router::frontend( array( Router::FRONTEND_PARAM => 'router_blocked_sample_action' ) );

		$this->assertSame( array(), $this->fired );
	}

	public function test_blocked_hook_still_fires_from_code() {
		$this->listen_for( 'edd_router_blocked_sample_action' );
		$this->block( 'edd_router_blocked_sample_action' );

		do_action( 'edd_router_blocked_sample_action', array() );

		$this->assertSame(
			array( 'edd_router_blocked_sample_action' ),
			$this->fired,
			'Blocking must only stop router dispatch, never the hook itself.'
		);
	}

	public function test_blocked_hook_is_not_reachable_in_the_admin() {
		$this->listen_for( 'edd_router_blocked_sample_action' );
		$this->block( 'edd_router_blocked_sample_action' );

		$this->use_admin_screen();

		Router::admin( array( Router::ADMIN_PARAM => 'router_blocked_sample_action' ) );

		$this->assertSame( array(), $this->fired );
	}

	/**
	 * @dataProvider order_lifecycle_hooks
	 */
	public function test_order_lifecycle_hooks_are_blocked_by_default( $hook ) {
		$this->assertTrue(
			Router::is_blocked( $hook ),
			sprintf( '%s is fired from code with order arguments, so it must not be request dispatchable.', $hook )
		);
	}

	/**
	 * @dataProvider cron_hooks
	 */
	public function test_cron_hooks_are_blocked_by_default( $hook ) {
		$this->assertTrue(
			Router::is_blocked( $hook ),
			sprintf( '%s runs scheduled work, so it must not be request dispatchable.', $hook )
		);
	}

	public function test_user_registration_request_does_not_fire_the_insert_user_hook() {
		$this->listen_for( 'edd_insert_user' );

		Router::frontend( array( Router::FRONTEND_PARAM => 'insert_user' ) );

		$this->assertSame( array(), $this->fired );
	}

	/**
	 * @dataProvider database_hooks
	 */
	public function test_database_hooks_are_blocked_by_default( $hook ) {
		$this->assertTrue(
			Router::is_blocked( $hook ),
			sprintf( '%s is fired by Berlin with row arguments, so it must not be request dispatchable.', $hook )
		);
	}

	public function test_every_core_query_hook_is_blocked() {
		$unblocked = array();

		foreach ( glob( EDD_PLUGIN_DIR . 'src/Database/Queries/*.php' ) as $file ) {
			$class      = 'EDD\\Database\\Queries\\' . basename( $file, '.php' );
			$reflection = new \ReflectionClass( $class );
			if ( $reflection->isAbstract() || ! $reflection->isSubclassOf( Query::class ) || empty( $reflection->getDefaultProperties()['item_name'] ) ) {
				continue;
			}

			$query     = new $class();
			$item_name = $query->item_name;
			$hooks     = array( "edd_{$item_name}_added", "edd_{$item_name}_updated", "edd_{$item_name}_deleted" );
			foreach ( wp_filter_object_list( $query->columns, array( 'transition' => true ), 'and', 'name' ) as $column ) {
				$hooks[] = "edd_transition_{$item_name}_{$column}";
			}

			$unblocked = array_merge( $unblocked, array_filter( $hooks, fn( $hook ) => ! Router::is_blocked( $hook ) ) );
		}

		$this->assertSame(
			array(),
			$unblocked,
			'Every core query class must be registered as a component, or its hooks stay request dispatchable.'
		);
	}

	public function test_registered_component_hooks_are_blocked() {
		edd_register_component(
			'router_sample',
			array(
				'query' => RouterSampleQuery::class,
				'table' => false,
			)
		);
		Router::reset();

		$this->assertTrue( Router::is_blocked( 'edd_router_sample_deleted' ), 'A query class registered by an extension must be covered without editing the router.' );
		$this->assertTrue( Router::is_blocked( 'edd_transition_router_sample_status' ) );
	}

	public function test_order_deleted_request_does_not_reach_listener() {
		$this->listen_for( 'edd_order_deleted' );

		do_action( 'edd_order_deleted', 1 );
		Router::frontend( array( Router::FRONTEND_PARAM => 'order_deleted' ) );

		$this->assertSame( array( 'edd_order_deleted' ), $this->fired, 'Only the direct do_action() may reach the listener.' );
	}

	public function test_deleting_an_order_still_fires_order_deleted() {
		$order_id = parent::edd()->order->create();
		$this->listen_for( 'edd_order_deleted' );

		edd_delete_order( $order_id );

		$this->assertSame( array( 'edd_order_deleted' ), $this->fired, 'Blocking the router must not stop Berlin firing the hook.' );
	}

	public function test_ordinary_action_is_not_blocked() {
		$this->assertFalse( Router::is_blocked( 'edd_add_to_cart' ) );
	}

	public function test_deferred_action_waits_for_the_deferred_pass() {
		$this->listen_for( 'edd_add_to_cart' );

		Router::frontend( array( Router::FRONTEND_PARAM => 'add_to_cart' ) );

		$this->assertSame( array(), $this->fired, 'add_to_cart is deferred and must not fire on init.' );
	}

	public function test_deferred_action_fires_on_the_deferred_pass() {
		$this->listen_for( 'edd_add_to_cart' );

		Router::frontend( array( Router::FRONTEND_PARAM => 'add_to_cart' ), true );

		$this->assertSame( array( 'edd_add_to_cart' ), $this->fired );
	}

	public function test_immediate_action_does_not_fire_on_the_deferred_pass() {
		$this->listen_for( 'edd_router_sample_action' );

		Router::frontend( array( Router::FRONTEND_PARAM => 'router_sample_action' ), true );

		$this->assertSame( array(), $this->fired );
	}

	public function test_deferral_does_not_apply_in_the_admin() {
		$this->listen_for( 'edd_add_to_cart' );

		$this->use_admin_screen();

		Router::admin( array( Router::ADMIN_PARAM => 'add_to_cart' ) );

		$this->assertSame(
			array( 'edd_add_to_cart' ),
			$this->fired,
			'The admin has a single pass, so a deferred front-end action must still dispatch there.'
		);
	}

	/**
	 * The order, payment and note hooks which carry order arguments from code.
	 *
	 * @return array
	 */
	public function order_lifecycle_hooks() {
		return array(
			'order built'        => array( 'edd_insert_payment' ),
			'order built legacy' => array( 'edd_built_order' ),
			'manual order'       => array( 'edd_post_add_manual_order' ),
			'before completion'  => array( 'edd_pre_complete_purchase' ),
			'item completion'    => array( 'edd_complete_download_purchase' ),
			'completion'         => array( 'edd_complete_purchase' ),
			'after payment'      => array( 'edd_after_payment_actions' ),
			'after order'        => array( 'edd_after_order_actions' ),
			'status change'      => array( 'edd_update_payment_status' ),
			'refund'             => array( 'edd_refund_order' ),
			'before delete'      => array( 'edd_payment_delete' ),
			'after delete'       => array( 'edd_payment_deleted' ),
			'before destroy'     => array( 'edd_pre_destroy_order' ),
			'after destroy'      => array( 'edd_order_destroyed' ),
			'before note insert' => array( 'edd_pre_insert_payment_note' ),
			'note insert'        => array( 'edd_insert_payment_note' ),
			'before note delete' => array( 'edd_pre_delete_payment_note' ),
			'after note delete'  => array( 'edd_post_delete_payment_note' ),
		);
	}

	/**
	 * The scheduled-event hooks.
	 *
	 * @return array
	 */
	public function cron_hooks() {
		return array(
			'log pruning'            => array( 'edd_prune_logs' ),
			'paypal connect sync'    => array( 'edd_paypal_v3_sync_connect' ),
			'legacy email cleanup'   => array( 'edd_email_legacy_data_cleanup' ),
			'customer recalculation' => array( 'edd_recalculate_customer_deferred' ),
			'payment scheduled'      => array( 'edd_after_payment_scheduled_actions' ),
			'sales recalculation'    => array( 'edd_recalculate_download_sales_earnings_deferred' ),
			'symlink cleanup'        => array( 'edd_cleanup_file_symlinks' ),
			'daily'                  => array( 'edd_daily_scheduled_events' ),
			'weekly'                 => array( 'edd_weekly_scheduled_events' ),
			'weekly pro'             => array( 'edd_pro_weekly_scheduled_events' ),
			'session purge'          => array( 'edd_cleanup_sessions' ),
			'new user email'         => array( 'edd_send_new_user_email' ),
		);
	}

	/**
	 * Berlin lifecycle and transition hooks with known listeners.
	 *
	 * @return array
	 */
	public function database_hooks() {
		return array(
			'order deleted'            => array( 'edd_order_deleted' ),
			'order item added'         => array( 'edd_order_item_added' ),
			'customer updated'         => array( 'edd_customer_updated' ),
			'order status transition'  => array( 'edd_transition_order_status' ),
			'order item status change' => array( 'edd_transition_order_item_status' ),
		);
	}

	/**
	 * Puts the request into an admin context, so is_admin() reports true.
	 */
	private function use_admin_screen() {
		set_current_screen( 'dashboard' );
	}

	/**
	 * Records every firing of a hook.
	 *
	 * @param string $hook The hook to record.
	 */
	private function listen_for( $hook ) {
		add_action(
			$hook,
			function () use ( $hook ) {
				$this->fired[] = $hook;
			}
		);
	}

	/**
	 * Adds a hook to the blocklist and rebuilds the cache.
	 *
	 * @param string $hook The hook to block.
	 */
	private function block( $hook ) {
		add_filter(
			'edd/actions/router/blocked_hooks',
			function ( $blocked ) use ( $hook ) {
				$blocked[] = $hook;

				return $blocked;
			}
		);
		Router::reset();
	}
}

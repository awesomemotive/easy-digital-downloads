<?php
namespace EDD\Tests\PHPUnit;

require_once dirname( __FILE__ ) . '/factory.php';

use EDD\Tests\Factory;
use Yoast\WPTestUtils\WPIntegration\TestCase as BaseTestCase;

/**
 * Defines a basic fixture to run multiple tests.
 *
 * Resets the state of the WordPress installation before and after every test.
 *
 * Includes utility functions and assertions useful for testing WordPress.
 *
 * All WordPress unit tests should inherit from this class.
 */
abstract class EDD_UnitTestCase extends BaseTestCase {

	/**
	 * Holds the original GMT offset for restoration during class tear down.
	 *
	 * @var string
	 */
	public static $original_gmt_offset;

	/**
	 * Runs once before any tests run.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		edd_install();

		global $current_user;

		new \WP_Roles();

		// Since roles are checked later, let's add these caps now.
		$admin_role = get_role( 'administrator' );
		$admin_role->add_cap( 'view_shop_sensitive_data' );
		$admin_role->add_cap( 'export_shop_reports' );
		$admin_role->add_cap( 'manage_shop_settings' );
		$admin_role->add_cap( 'manage_shop_discounts' );

		$current_user = new \WP_User( 1 );
		$current_user->set_role( 'administrator' );
		wp_update_user(
			array(
				'ID'         => 1,
				'first_name' => 'Admin',
				'last_name'  => 'User',
			)
		);

		add_filter( 'edd_log_email_errors', '__return_false' );
	}

	public static function tearDownAfterClass(): void {
		self::_delete_all_edd_data();

		delete_option( 'gmt_offset' );
		EDD()->utils->get_gmt_offset( true );

		delete_option( 'timezone_string', '' );
		EDD()->utils->get_time_zone( true );

		parent::tearDownAfterClass();
	}

	/**
	 * Runs before each test method.
	 */
	public function setUp(): void {
		parent::setUp();

		$this->expectDeprecatedEDD();
	}

	/**
	 * Sets up logic for the @expectEDDeprecated annotation for deprecated elements in EDD.
	 */
	function expectDeprecatedEDD() {
		$annotations = $this->getAnnotations();
		foreach ( array( 'class', 'method' ) as $depth ) {
			if ( ! empty( $annotations[ $depth ]['expectEDDeprecated'] ) ) {
				$this->expected_deprecated = array_merge( $this->expected_deprecated, $annotations[ $depth ]['expectEDDeprecated'] );
			}
		}

		add_action( 'edd_should_trigger_deprecation_notices', '__return_false' );
		add_action( 'edd_deprecated_function_run', array( $this, 'deprecated_function_run' ), 10, 3 );
		add_action( 'edd_deprecated_argument_run', array( $this, 'deprecated_function_run' ), 10, 3 );
		add_action( 'edd_deprecated_hook_run', array( $this, 'deprecated_function_run' ), 10, 3 );
	}

	protected static function edd() {
		static $factory = null;
		if ( ! $factory ) {
			$factory = new Factory();
		}
		return $factory;
	}

	protected static function _delete_all_edd_data() {
		edd_setup_components();

		foreach ( EDD()->components as $component ) {
			$thing = $component->get_interface( 'table' );

			if ( $thing instanceof \EDD\Database\Table ) {
				$thing->truncate();
			}

			$thing = $component->get_interface( 'meta' );

			if ( $thing instanceof \EDD\Database\Table ) {
				$thing->truncate();
			}
		}

		$edd_taxonomies = array( 'download_category', 'download_tag' );
		$edd_post_types = array( 'download' );
		foreach ( $edd_post_types as $post_type ) {

			$edd_taxonomies = array_merge( $edd_taxonomies, get_object_taxonomies( $post_type ) );
			$items          = get_posts(
				array(
					'post_type'   => $post_type,
					'post_status' => 'any',
					'numberposts' => -1,
					'fields'      => 'ids',
				)
			);

			if ( $items ) {
				foreach ( $items as $item ) {
					wp_delete_post( $item, true );
				}
			}
		}

		global $wpdb;
		/** Delete All the Terms & Taxonomies */
		foreach ( array_unique( array_filter( $edd_taxonomies ) ) as $taxonomy ) {

			$terms = $wpdb->get_results( $wpdb->prepare( "SELECT t.*, tt.* FROM $wpdb->terms AS t INNER JOIN $wpdb->term_taxonomy AS tt ON t.term_id = tt.term_id WHERE tt.taxonomy IN ('%s') ORDER BY t.name ASC", $taxonomy ) );

			// Delete Terms.
			if ( $terms ) {
				foreach ( $terms as $term ) {
					$wpdb->delete( $wpdb->term_relationships, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
					$wpdb->delete( $wpdb->term_taxonomy, array( 'term_taxonomy_id' => $term->term_taxonomy_id ) );
					$wpdb->delete( $wpdb->terms, array( 'term_id' => $term->term_id ) );
				}
			}

			// Delete Taxonomies.
			$wpdb->delete( $wpdb->term_taxonomy, array( 'taxonomy' => $taxonomy ), array( '%s' ) );
		}

		/** Delete the Plugin Pages */
		$edd_created_pages = array( 'purchase_page', 'success_page', 'failure_page', 'purchase_history_page' );
		foreach ( $edd_created_pages as $p ) {
			if ( ! empty( $edd_settings[ $p ] ) ) {
				wp_delete_post( $p, true );
			}
		}

		/** Delete all the Plugin Options */
		$edd_options = array(
			'edd_completed_upgrades',
			'edd_default_api_version',
			'edd_earnings_total',
			'edd_earnings_total_without_tax',
			'edd_settings',
			'edd_tracking_notice',
			'edd_tax_rates',
			'edd_use_php_sessions',
			'edd_version',
			'edd_version_upgraded_from',
			'edd_notification_req_timeout',
			'edd_pass_licenses',
			'edd_pass_data',
			'edd_tokenizer_signing_key',
			'edd_session_handling',
			'edd_licensed_extensions',
			'edd_activation_date',
			'edd_pro_activation_date',
			'edd_onboarding_completed',
			'edd_onboarding_started',
			'edd_onboarding_latest_step',

			// Widgets
			'widget_edd_product_details',
			'widget_edd_cart_widget',
			'widget_edd_categories_tags_widget',

			// Deprecated 3.0.0
			'wp_edd_customers_db_version',
			'wp_edd_customermeta_db_version',
			'_edd_table_check',
		);
		foreach ( $edd_options as $option ) {
			delete_option( $option );
		}

		$site_options = array(
			'edd_all_extension_data',
			'edd_extension_tag_1578_data',
			'edd_extension_product_28530_data',
			'edd_extension_product_375153_data',
			'edd_extension_product_37976_data',
			'edd_pro_license',
			'edd_pro_license_key',
		);
		foreach ( $site_options as $site_option ) {
			delete_site_option( $site_option );
		}

		/** Delete Capabilities */
		EDD()->roles->remove_caps();

		/** Delete the Roles */
		$edd_roles = array( 'shop_manager', 'shop_accountant', 'shop_worker', 'shop_vendor' );
		foreach ( $edd_roles as $role ) {
			remove_role( $role );
		}

		/** Cleanup Cron Events */
		wp_clear_scheduled_hook( 'edd_daily_scheduled_events' );
		wp_clear_scheduled_hook( 'edd_daily_cron' );
		wp_clear_scheduled_hook( 'edd_weekly_cron' );
		wp_clear_scheduled_hook( 'edd_email_summary_cron' );
		wp_clear_scheduled_hook( 'edd_weekly_scheduled_events' );

		// Remove any transients we've left behind
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_edd\_%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_edd\_%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_transient\_timeout\_edd\_%'" );
		$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE '\_site\_transient\_timeout\_edd\_%'" );
	}

	/**
	 * Checks if all items in the array are of the given type.
	 *
	 * @param string $type   Type to check against.
	 * @param array  $actual Supplied array to check.
	 */
	public function assertStringContainsStringOnlyType( $type, $actual ) {
		$standard_types = array(
			'numeric',
			'integer',
			'int',
			'float',
			'string',
			'boolean',
			'bool',
			'null',
			'array',
			'object',
			'resource',
			'scalar',
		);

		if ( in_array( $type, $standard_types, true ) ) {
			if ( class_exists( 'PHPUnit\Framework\Constraint\isType' ) ) {
				$constraint = new \PHPUnit\Framework\Constraint\isType( $type );
			} else {
				$constraint = new \PHPUnit_Framework_Constraint_IsType( $type );
			}
		} else {
			if ( class_exists( 'PHPUnit\Framework\Constraint\IsInstanceOf' ) ) {
				$constraint = new \PHPUnit\Framework\Constraint\IsInstanceOf( $type );
			} else {
				$constraint = new \PHPUnit_Framework_Constraint_IsInstanceOf( $type );
			}
		}

		foreach ( $actual as $item ) {
			if ( class_exists( '\PHPUnit\Framework\Assert' ) ) {
				\PHPUnit\Framework\Assert::assertThat( $item, $constraint );
			} else {
				\PHPUnit_Framework_Assert::assertThat( $item, $constraint );
			}
		}
	}

}

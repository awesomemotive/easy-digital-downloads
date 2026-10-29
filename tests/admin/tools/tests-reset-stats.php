<?php
/**
 * Reset Stats Tests.
 */
namespace EDD\Tests\Admin\Tools;

use EDD\Tests\PHPUnit\EDD_UnitTestCase;
use EDD\Tests\Helpers\EDD_Helper_Download;

/**
 * @coversDefaultClass \EDD_Tools_Reset_Stats
 *
 * @group edd_tools
 * @group edd_reset_stats
 */
class ResetStats extends EDD_UnitTestCase {

	/**
	 * Download IDs.
	 *
	 * @var int[]
	 */
	protected static $download_ids = array();

	/**
	 * Order IDs.
	 *
	 * @var int[]
	 */
	protected static $order_ids = array();

	/**
	 * Set up fixtures once.
	 */
	public static function wpSetUpBeforeClass() {
		// Create downloads with sales/earnings meta.
		for ( $i = 0; $i < 3; $i++ ) {
			$download                = EDD_Helper_Download::create_simple_download();
			$download_id             = $download->ID;
			self::$download_ids[]    = $download_id;

			update_post_meta( $download_id, '_edd_download_sales', 10 + $i );
			update_post_meta( $download_id, '_edd_download_earnings', 100.00 + $i );
			update_post_meta( $download_id, '_edd_download_gross_sales', 12 + $i );
			update_post_meta( $download_id, '_edd_download_gross_earnings', 120.00 + $i );
		}

		// Create orders so the tool has data to process.
		self::$order_ids = parent::edd()->order->create_many( 3 );
	}

	/**
	 * Set the current user to an admin with the required capability.
	 */
	public function setUp(): void {
		parent::setUp();
		wp_set_current_user( 1 );
	}

	/**
	 * Helper to run all steps of the reset tool to completion.
	 *
	 * @return \EDD_Tools_Reset_Stats
	 */
	private function run_reset_tool() {
		require_once EDD_PLUGIN_DIR . 'includes/admin/tools/class-edd-tools-reset-stats.php';

		$tool = new \EDD_Tools_Reset_Stats();
		$tool->pre_fetch();

		$step = 1;
		while ( true ) {
			$tool->step = $step;
			$more       = $tool->process_step();
			if ( ! $more ) {
				break;
			}
			++$step;
		}

		// Flush the object cache so get_post_meta() reflects the DB changes.
		wp_cache_flush();

		return $tool;
	}

	/**
	 * @covers ::reset_download_stats
	 * @covers ::process_step
	 */
	public function test_reset_clears_download_sales_meta() {
		// Verify meta exists before reset.
		foreach ( self::$download_ids as $download_id ) {
			$this->assertNotEmpty( get_post_meta( $download_id, '_edd_download_sales', true ) );
		}

		$this->run_reset_tool();

		foreach ( self::$download_ids as $download_id ) {
			$this->assertEmpty( get_post_meta( $download_id, '_edd_download_sales', true ) );
		}
	}

	/**
	 * @covers ::reset_download_stats
	 * @covers ::process_step
	 */
	public function test_reset_clears_download_earnings_meta() {
		// Re-add meta since tests may run in any order.
		foreach ( self::$download_ids as $download_id ) {
			update_post_meta( $download_id, '_edd_download_earnings', 100.00 );
		}

		$this->run_reset_tool();

		foreach ( self::$download_ids as $download_id ) {
			$this->assertEmpty( get_post_meta( $download_id, '_edd_download_earnings', true ) );
		}
	}

	/**
	 * @covers ::reset_download_stats
	 * @covers ::process_step
	 */
	public function test_reset_clears_download_gross_sales_meta() {
		foreach ( self::$download_ids as $download_id ) {
			update_post_meta( $download_id, '_edd_download_gross_sales', 12 );
		}

		$this->run_reset_tool();

		foreach ( self::$download_ids as $download_id ) {
			$this->assertEmpty( get_post_meta( $download_id, '_edd_download_gross_sales', true ) );
		}
	}

	/**
	 * @covers ::reset_download_stats
	 * @covers ::process_step
	 */
	public function test_reset_clears_download_gross_earnings_meta() {
		foreach ( self::$download_ids as $download_id ) {
			update_post_meta( $download_id, '_edd_download_gross_earnings', 120.00 );
		}

		$this->run_reset_tool();

		foreach ( self::$download_ids as $download_id ) {
			$this->assertEmpty( get_post_meta( $download_id, '_edd_download_gross_earnings', true ) );
		}
	}

	/**
	 * @covers ::process_step
	 */
	public function test_reset_clears_store_earnings_total() {
		update_option( 'edd_earnings_total', 500.00 );

		$this->run_reset_tool();

		$this->assertEquals( 0, get_option( 'edd_earnings_total' ) );
	}

	/**
	 * @covers ::process_step
	 */
	public function test_reset_sets_done_flag_and_message() {
		$tool = $this->run_reset_tool();

		$this->assertTrue( $tool->done );
		$this->assertNotEmpty( $tool->message );
	}

	/**
	 * @covers ::process_step
	 */
	public function test_reset_preserves_download_posts() {
		$this->run_reset_tool();

		foreach ( self::$download_ids as $download_id ) {
			$this->assertNotNull( get_post( $download_id ) );
		}
	}
}

<?php
/**
 * Easy Digital Downloads Test Case
 *
 * Adapted from the WordPress Unit Test Case.
 *
 * @package EDD_Unit_Tests
 * @since 1.0
 * @author Sunny Ratilal
 */

require_once dirname( __FILE__ ) . '../vendor/wordpress-tests/lib/factory.php';

class EDD_Framework_TestCase extends WP_UnitTestCase {
	protected $factory;

	public function setUp() {
		global $wpdb;

		set_time_limit( 0 );
		ignore_user_abort( true );

		$this->edd_factory = new EDD_Framework_Factory;
		new EDD_Die_Handler;

		$wpdb->suppress_errors = false;
		$wpdb->show_errors = true;
		$wpdb->db_connect();

		ini_set('display_errors', 1 );

		$this->clean_up_globals();
		$this->start_transaction();
	}

	public function tearDown() {
		global $wpdb;
		$wpdb->query( 'ROLLBACK' );
	}

	public function clean_up_globals() {
		$_GET = array();
		$_POST = array();
		$this->flush_cache();
	}

	public function flush_cache() {
		global $wp_object_cache;

		$wp_object_cache->group_ops = array();
		$wp_object_cache->stats = array();
		$wp_object_cache->memcache_debug = array();
		$wp_object_cache->cache = array();

		if ( method_exists( $wp_object_cache, '__remoteset' ) ) {
			$wp_object_cache->__remoteset();
		}

		wp_cache_flush();
	}

	public function start_transaction() {
		global $wpdb;
		$wpdb->query( 'SET autocommit = 0;' );
		$wpdb->query( 'START TRANSACTION;' );
	}

	public function go_to( $url ) {
		// note: the WP and WP_Query classes like to silently fetch parameters
		// from all over the place (globals, GET, etc), which makes it tricky
		// to run them more than once without very carefully clearing everything
		$_GET = $_POST = array();
		foreach (array('query_string', 'id', 'postdata', 'authordata', 'day', 'currentmonth', 'page', 'pages', 'multipage', 'more', 'numpages', 'pagenow') as $v) {
			if ( isset( $GLOBALS[$v] ) ) unset( $GLOBALS[$v] );
		}
		$parts = parse_url($url);
		if (isset($parts['scheme'])) {
			$req = $parts['path'];
			if (isset($parts['query'])) {
				$req .= '?' . $parts['query'];
				// parse the url query vars into $_GET
				parse_str($parts['query'], $_GET);
			} else {
				$parts['query'] = '';
			}
		}
		else {
			$req = $url;
		}

		$_SERVER['REQUEST_URI'] = $req;
		unset($_SERVER['PATH_INFO']);

		$this->flush_cache();
		unset($GLOBALS['wp_query'], $GLOBALS['wp_the_query']);
		$GLOBALS['wp_the_query'] =& new WP_Query();
		$GLOBALS['wp_query'] =& $GLOBALS['wp_the_query'];
		$GLOBALS['wp'] =& new WP();

		// clean out globals to stop them polluting wp and wp_query
		foreach ($GLOBALS['wp']->public_query_vars as $v) {
			unset($GLOBALS[$v]);
		}
		foreach ($GLOBALS['wp']->private_query_vars as $v) {
			unset($GLOBALS[$v]);
		}

		$GLOBALS['wp']->main($parts['query']);
	}
}
<?php
/**
 * Base Easy Digital Downloads Custom Table Class.
 *
 * @package     EDD
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0.0
 */
namespace EDD\Database\Tables;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\\EDD\\Database\\Tables\\Base' ) ) :
/**
 * A base WordPress database table class, which facilitates the creation of
 * and schema changes to individual database tables.
 *
 * This class is intended to be extended for each unique database table,
 * including global multisite tables and users tables.
 *
 * It exists to make managing database tables in WordPress as easy as possible.
 *
 * Extending this class comes with several automatic benefits:
 * - Activation hook makes it great for plugins
 * - Tables store their versions in the database independently
 * - Tables upgrade via independent upgrade abstract methods
 * - Multisite friendly - site tables switch on "switch_blog" action
 *
 * @since 3.0
 */
abstract class Base extends \EDD\Database\Base {

	/**
	 * @var string Table name, without the global table prefix
	 */
	protected $name = '';

	/**
	 * @var string Optional description.
	 */
	protected $description = '';

	/**
	 * @var int Database version
	 */
	protected $version = 0;

	/**
	 * @var boolean Is this table for a site, or global
	 */
	protected $global = false;

	/**
	 * @var string Passed directly into register_activation_hook()
	 */
	protected $file = EDD_PLUGIN_FILE;

	/**
	 * @var string Database version key (saved in _options or _sitemeta)
	 */
	protected $db_version_key = '';

	/**
	 * @var string Current database version
	 */
	protected $db_version = 0;

	/**
	 * @var string Table prefix
	 */
	protected $prefix = '';

	/**
	 * @var string Table name
	 */
	protected $table_name = '';

	/**
	 * @var string Table schema
	 */
	protected $schema = '';

	/**
	 * @var string Database character-set & collation for table
	 */
	protected $charset_collation = '';

	/**
	 * @var string The last error, if any.
	 */
	protected $last_error = '';

	/**
	 * @var array Key => value array of versions => methods
	 */
	protected $upgrades = array();

	/** Methods ***************************************************************/

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 3.0
	 */
	public function __construct() {

		// Setup the database
		$this->setup();

		// Bail if setup failed
		if ( empty( $this->name ) || empty( $this->db_version_key ) ) {
			return;
		}

		// Get the version of he table currently in the database
		$this->get_db_version();

		// Add the table to the object
		$this->set_wpdb_tables();

		// Setup the database schema
		$this->set_schema();

		// Add hooks to WordPress actions
		$this->add_hooks();

		// Maybe force upgrade if testing
		if ( $this->is_testing() ) {
			$this->maybe_upgrade();
		}
	}

	/** Abstract **************************************************************/

	/**
	 * Setup this database table
	 *
	 * @since 3.0
	 */
	protected abstract function set_schema();

	/** Public ****************************************************************/

	/**
	 * Update table version & references.
	 *
	 * Hooked to the "switch_blog" action.
	 *
	 * @since 3.0
	 *
	 * @param int $site_id The site being switched to
	 */
	public function switch_blog( $site_id = 0 ) {

		// Update DB version based on the current site
		if ( ! $this->is_global() ) {
			$this->db_version = get_blog_option( $site_id, $this->db_version_key, false );
		}

		// Update table references based on th current site
		$this->set_wpdb_tables();
	}

	/**
	 * Maybe upgrade the database table. Handles creation & schema changes.
	 *
	 * Hooked to the "admin_init" action.
	 *
	 * @since 3.0
	 */
	public function maybe_upgrade() {

		// Is an upgrade needed?
		$is_current = version_compare( $this->db_version, $this->version, '>=' );

		// Bail if database table is current
		if ( true === $is_current ) {
			return;
		}

		// Bail if global and upgrading global tables is not allowed
		if ( $this->is_global() && ! wp_should_upgrade_global_tables() ) {
			return;
		}

		// Upgrade
		if ( $this->exists() ) {
			$this->upgrade();

		// Create
		} else {
			$created = $this->create();

			// Set the DB version if create was successful
			if ( true === $created ) {
				$this->set_db_version();
			}
		}
	}

	/**
	 * Check if table already exists
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function exists() {
		$query    = "SHOW TABLES LIKE %s";
		$like     = $this->get_db()->esc_like( $this->table_name );
		$prepared = $this->get_db()->prepare( $query, $like );
		$result   = $this->get_db()->get_var( $prepared );

		// Does the table exist?
		return $this->is_success( $result );
	}

	/**
	 * Create the table
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function create() {
		$query  = "CREATE TABLE {$this->table_name} ( {$this->schema} ) {$this->charset_collation};";
		$result = $this->get_db()->query( $query );

		// Was the table created?
		return $this->is_success( $result );
	}

	/**
	 * Drop the database table
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function drop() {
		$query  = "DROP TABLE {$this->table_name}";
		$result = $this->get_db()->query( $query );

		// Query success/fail
		return $this->is_success( $result );
	}

	/**
	 * Truncate the database table
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function truncate() {
		$query  = "TRUNCATE TABLE {$this->table_name}";
		$result = $this->get_db()->query( $query );

		// Query success/fail
		return $this->is_success( $result );
	}

	/**
	 * Delete all items from the database table
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function delete_all() {
		$query   = "DELETE FROM {$this->table_name}";
		$deleted = $this->get_db()->query( $query );

		// Query success/fail
		return $deleted;
	}

	/**
	 * Upgrade this database table
	 *
	 * @since 3.0
	 *
	 * return boolean
	 */
	public function upgrade() {
		$result = false;

		// Bail if no upgrades
		if ( empty( $this->upgrades ) ) {
			return true;
		}

		// Try to do all known upgrades
		foreach ( $this->upgrades as $version => $method ) {
			$result = $this->upgrade_to( $version, $method );

			// Bail if an error occurs, to avoid skipping ahead
			if ( ! $this->is_success( $result ) ) {
				return false;
			}
		}

		// Success/fail
		return $this->is_success( $result );
	}

	/**
	 * Upgrade to a specific database version
	 *
	 * @since 3.0
	 *
	 * @param string $version
	 * @param string $method
	 * @return boolean
	 */
	public function upgrade_to( $version = '', $method = '' ) {

		// Set the db_version property
		$this->get_db_version();

		// Bail if no upgrade is needed
		if ( version_compare( $this->db_version, $version, '>=' ) ) {
			return false;
		}

		// Allow self-named upgrade methods
		if ( empty( $method ) ) {
			$method = $version;
		}

		// Is the method callable?
		$callable = $this->get_callable( $method );

		// Bail if no callable upgrade method was found
		if ( empty( $callable ) ) {
			return false;
		}

		// Do the upgrade
		$result  = call_user_func( $callable );
		$success = $this->is_success( $result );

		// Bail if upgrade failed
		if ( true !== $success ) {
			return false;
		}

		// Set the database version to this successful version
		$this->set_db_version( $version );

		// Return success
		return true;
	}

	/**
	 * Return the current table version from the database.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_version() {
		$this->get_db_version();

		return $this->db_version;
	}

	/** Protected *************************************************************/

	/**
	 * Return the global database interface
	 *
	 * @since 3.0
	 *
	 * @return wpdb
	 */
	protected static function get_db() {
		return isset( $GLOBALS['wpdb'] )
			? $GLOBALS['wpdb']
			: new stdClass();
	}

	/**
	 * Check if the query failed
	 *
	 * @since 3.0
	 *
	 * @param mixed $result
	 * @return boolean
	 */
	protected function is_success( $result = false ) {

		// Bail if no row exists
		if ( empty( $result ) ) {
			$retval = false;

		// Bail if an error occurred
		} elseif ( is_wp_error( $result ) ) {
			$this->last_error = $result;
			$retval           = false;

		// No errors
		} else {
			$retval = true;
		}

		// Return the result
		return (bool) $retval;
	}

	/** Private ***************************************************************/

	/**
	 * Setup the necessary table variables
	 *
	 * @since 3.0
	 */
	private function setup() {

		// Bail if no WordPress database interface is available
		if ( ! $this->get_db() ) {
			return;
		}

		// Sanitize the database table name
		$this->name = $this->sanitize_table_name( $this->name );

		// Bail if database table name was garbage
		if ( false === $this->name ) {
			return;
		}

		// Maybe create database key
		if ( empty( $this->db_version_key ) ) {
			$this->db_version_key = "wpdb_{$this->name}_version";
		}
	}

	/**
	 * Modify the database object and add the table to it
	 *
	 * This must be done directly because WordPress does not have a mechanism
	 * for manipulating them safely
	 *
	 * @since 3.0
	 */
	private function set_wpdb_tables() {

		// Global
		if ( $this->is_global() ) {
			$this->prefix                       = $this->get_db()->get_blog_prefix( 0 );
			$this->get_db()->{$this->name}      = "{$this->prefix}{$this->name}";
			$this->get_db()->ms_global_tables[] = $this->name;

		// Site
		} else {
			$this->prefix                  = $this->get_db()->get_blog_prefix( null );
			$this->get_db()->{$this->name} = "{$this->prefix}{$this->name}";
			$this->get_db()->tables[]      = $this->name;
		}

		// Set the table name locally
		$this->table_name = $this->get_db()->{$this->name};

		// Charset
		if ( ! empty( $this->get_db()->charset ) ) {
			$this->charset_collation = "DEFAULT CHARACTER SET {$this->get_db()->charset}";
		}

		// Collation
		if ( ! empty( $this->get_db()->collate ) ) {
			$this->charset_collation .= " COLLATE {$this->get_db()->collate}";
		}
	}

	/**
	 * Set the database version for the table
	 *
	 * @since 3.0
	 */
	private function set_db_version( $version = '' ) {

		// Set the class version
		if ( empty( $version ) ) {
			$version = $this->version;
		} else {
			$this->db_version = $version;
		}

		// Update the DB version
		$this->is_global()
			? update_network_option( get_main_network_id(), $this->db_version_key, $version )
			:         update_option(                        $this->db_version_key, $version );
	}

	/**
	 * Get the table version from the database
	 *
	 * @since 3.0
	 */
	private function get_db_version() {
		$this->db_version = $this->is_global()
			? get_network_option( get_main_network_id(), $this->db_version_key, false )
			:         get_option(                        $this->db_version_key, false );
	}

	/**
	 * Add class hooks to WordPress actions
	 *
	 * @since 3.0
	 */
	private function add_hooks() {

		// Activation hook
		register_activation_hook( $this->file, array( $this, 'maybe_upgrade' ) );

		// Add table to the global database object
		add_action( 'switch_blog', array( $this, 'switch_blog'   ) );
		add_action( 'admin_init',  array( $this, 'maybe_upgrade' ) );
	}

	/**
	 * Check if the current request is from some kind of test.
	 *
	 * This is primarily used to skip 'admin_init' and force-install tables.
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	private function is_testing() {
		return (bool)

			// Tests constant is being used
			( defined( 'WP_TESTS_DIR' ) && WP_TESTS_DIR )

			||

			// Scaffolded (https://make.wordpress.org/cli/handbook/plugin-unit-tests/)
			function_exists( '_manually_load_plugin' );
	}

	/**
	 * Check if table is global
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	private function is_global() {
		return ( true === $this->global );
	}

	/**
	 * Try to get a callable upgrade method, with some magic to avoid needing to
	 * do this dance repeatedly inside subclasses.
	 *
	 * @since 3.0
	 *
	 * @param string $method
	 * @return boolean
	 */
	private function get_callable( $method = '' ) {
		$callable = $method;

		// Look for global function
		if ( ! is_callable( $callable ) ) {
			$callable = array( $this, $method );

			// Look for local clas method
			if ( ! is_callable( $callable ) ) {
				$callable = array( $this, "__{$method}" );

				// Look for method prefixed with "__"
				if ( ! is_callable( $callable ) ) {
					$callable = false;
				}
			}
		}

		// Return callable, if any
		return $callable;
	}

	/**
	 * Sanitize a table name string
	 *
	 * Applies the following formatting to a string:
	 * - No accents
	 * - No special characters
	 * - No hyphens
	 * - No double underscores
	 * - No trailing underscores
	 *
	 * @since 3.0
	 *
	 * @param string $name The name of the database table
	 *
	 * @return string Sanitized database table name
	 */
	private function sanitize_table_name( $name = '' ) {

		// Trim spaces off the ends
		$unspace = trim( $name );

		// Only non-accented table names (avoid truncation)
		$accents = remove_accents( $unspace );

		// Only lowercase characters, hyphens, and dashes (avoid index corruption)
		$lower   = sanitize_key( $accents );

		// Replace hyphens with single underscores
		$under   = str_replace( '-',  '_', $lower );

		// Single underscores only
		$single  = str_replace( '__', '_', $under );

		// Remove trailing underscores
		$clean   = trim( $single, '_' );

		// Bail if table name was garbaged
		if ( empty( $clean ) ) {
			return false;
		}

		// Return the cleaned table name
		return $clean;
	}
}
endif;
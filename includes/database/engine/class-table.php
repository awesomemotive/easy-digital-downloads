<?php
/**
 * Base Custom Database Table Class.
 *
 * @package     EDD
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Database;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
abstract class Table extends Base {

	/**
	 * @var string Table name, without the global table prefix
	 */
	protected $name = '';

	/**
	 * @var string Optional description.
	 */
	protected $description = '';

	/**
	 * @var mixed Database version
	 */
	protected $version = '';

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
	 * @var mixed Current database version
	 */
	protected $db_version = 0;

	/**
	 * @var string Table prefix, including the site prefix
	 */
	protected $table_prefix = '';

	/**
	 * @var string Table name
	 */
	protected $table_name = '';

	/**
	 * @var string Table name, prefixed from the base
	 */
	protected $prefixed_name = '';

	/**
	 * @var string Table schema
	 */
	protected $schema = '';

	/**
	 * @var string Database character-set & collation for table
	 */
	protected $charset_collation = '';

	/**
	 * @var array Key => value array of versions => methods
	 */
	protected $upgrades = array();

	/** Interfaces ************************************************************/

	/**
	 * Array of interface objects instantiated during init
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $interfaces = array();

	/**
	 * Array of interface keys
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	private $interface_keys = array(
		'schema' => false,
		'table'  => false,
		'query'  => false,
		'object' => false,
		'meta'   => false
	);

	/** Methods ***************************************************************/

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 3.0
	 */
	public function __construct() {

		// Setup the database table
		$this->setup();

		// Bail if setup failed
		if ( empty( $this->name ) || empty( $this->db_version_key ) ) {
			return;
		}

		// Add the table to the database interface
		$this->set_db_interface();

		// Set the database schema
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
	 * Setup the database table
	 *
	 * @since 3.0
	 */
	protected abstract function set_schema();

	/** Multisite *************************************************************/

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

		// Update interface for switched site
		$this->set_db_interface();
	}

	/** Public Helpers ********************************************************/

	/**
	 * Maybe upgrade the database table. Handles creation & schema changes.
	 *
	 * Hooked to the `admin_init` action.
	 *
	 * @since 3.0
	 */
	public function maybe_upgrade() {

		// Bail if not upgradeable
		if ( ! $this->is_upgradeable() ) {
			return;
		}

		// Bail if upgrade not needed
		if ( ! $this->needs_upgrade() ) {
			return;
		}

		// Upgrade
		if ( $this->exists() ) {
			$this->upgrade();

		// Install
		} else {
			$this->install();
		}
	}

	/**
	 * Return whether this table needs an upgrade.
	 *
	 * @since 3.0
	 *
	 * @param mixed $version Database version to check if upgrade is needed
	 *
	 * @return boolean True if table needs upgrading. False if not.
	 */
	public function needs_upgrade( $version = false ) {

		// Use the current table version if none was passed
		if ( empty( $version ) ) {
			$version = $this->version;
		}

		// Get the current database version
		$this->get_db_version();

		// Is the database table up to date?
		$is_current = version_compare( $this->db_version, $version, '>=' );

		// Return false if current, true if out of date
		return ( true === $is_current )
			? false
			: true;
	}

	/**
	 * Return whether this table can be upgraded.
	 *
	 * @since 3.0
	 *
	 * @return boolean True if table can be upgraded. False if not.
	 */
	public function is_upgradeable() {

		// Bail if global and upgrading global tables is not allowed
		if ( $this->is_global() && ! wp_should_upgrade_global_tables() ) {
			return false;
		}

		// Kinda weird, but assume it is
		return true;
	}

	/**
	 * Return the current table version from the database.
	 *
	 * This is public method for accessing a private variable so that it cannot
	 * be externally modified.
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_version() {
		$this->get_db_version();

		return $this->db_version;
	}

	/**
	 * Install a database table by creating the table and setting the version.
	 *
	 * @since 3.0
	 */
	public function install() {
		$created = $this->create();

		// Set the DB version if create was successful
		if ( true === $created ) {
			$this->set_db_version();
		}
	}

	/**
	 * Destroy a database table by dropping the table and deleting the version.
	 *
	 * @since 3.0
	 */
	public function uninstall() {
		$dropped = $this->drop();

		// Delete the DB version if drop was successful
		if ( true === $dropped ) {
			$this->delete_db_version();
		}
	}

	/** Public Management *****************************************************/

	/**
	 * Check if table already exists
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function exists() {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return;
		}

		// Query statement
		$query    = "SHOW TABLES LIKE %s";
		$like     = $db->esc_like( $this->table_name );
		$prepared = $db->prepare( $query, $like );
		$result   = $db->get_var( $prepared );

		// Does the table exist?
		return $this->is_success( $result );
	}

	/**
	 * Check if table already exists
	 *
	 * @since 3.0
	 *
	 * @return bool
	 */
	public function column_exists( $name = '' ) {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return;
		}

		// Query statement
		$query    = "SHOW COLUMNS FROM {$this->table_name} LIKE %s";
		$like     = $db->esc_like( $name );
		$prepared = $db->prepare( $query, $like );
		$result   = $db->query( $prepared );

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

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return;
		}

		// Query statement
		$query  = "CREATE TABLE {$this->table_name} ( {$this->schema} ) {$this->charset_collation};";
		$result = $db->query( $query );

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

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return;
		}

		// Query statement
		$query  = "DROP TABLE {$this->table_name}";
		$result = $db->query( $query );

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

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return;
		}

		// Query statement
		$query  = "TRUNCATE TABLE {$this->table_name}";
		$result = $db->query( $query );

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

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return;
		}

		// Query statement
		$query   = "DELETE FROM {$this->table_name}";
		$deleted = $db->query( $query );

		// Query success/fail
		return $deleted;
	}

	/**
	 * Count the number of items in the database table
	 *
	 * @since 3.0
	 *
	 * @return mixed
	 */
	public function count() {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return;
		}

		// Query statement
		$query = "SELECT COUNT(*) FROM {$this->table_name}";
		$count = $db->get_var( $query );

		// Query success/fail
		return $count;
	}

	/** Auto-Increment ********************************************************/

	/**
	 * Set the auto_increment value of the database table
	 *
	 * @since 3.0
	 *
	 * @param int $number
	 *
	 * @return mixed
	 */
	public function set_auto_increment( $number = 1 ) {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return;
		}

		// Number cannot be less than 1
		if ( ! is_numeric( $number ) || ( $number <= 0 ) ) {
			$number = 1;
		}

		// Format the number
		$number = absint( $number );

		// Query statement
		$query  = "ALTER TABLE {$this->table_name} AUTO_INCREMENT={$number};";
		$result = $db->query( $query );

		// Query success/fail
		return $this->is_success( $result );
	}

	/** Upgrades **************************************************************/

	/**
	 * Upgrade the database table
	 *
	 * @since 3.0
	 *
	 * return boolean
	 */
	public function upgrade() {
		$result = false;

		// Remove all upgrades that have already been completed
		$upgrades = array_filter( (array) $this->upgrades, function( $value ) {
			return version_compare( $value, $this->db_version, '>' );
		} );

		// Bail if no upgrades or database version is missing
		if ( empty( $upgrades ) || empty( $this->db_version ) ) {
			$this->set_db_version();
			return true;
		}

		// Try to do all known upgrades
		foreach ( $upgrades as $version => $method ) {
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
	 * @param mixed  $version Database version to check if upgrade is needed
	 * @param string $method
	 *
	 * @return boolean
	 */
	public function upgrade_to( $version = '', $method = '' ) {

		// Bail if no upgrade is needed
		if ( ! $this->needs_upgrade( $version ) ) {
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

	/** Private ***************************************************************/

	/**
	 * Setup the necessary table variables
	 *
	 * @since 3.0
	 */
	private function setup() {

		// Bail if no database interface is available
		if ( ! $this->get_db() ) {
			return;
		}

		// Sanitize the database table name
		$this->name = $this->sanitize_table_name( $this->name );

		// Bail if database table name was garbage
		if ( false === $this->name ) {
			return;
		}

		// Setup the prefixed name
		$this->prefixed_name = $this->apply_prefix( $this->name );

		// Maybe create database key
		if ( empty( $this->db_version_key ) ) {
			$this->db_version_key = "wpdb_{$this->prefixed_name}_version";
		}
	}

	/**
	 * Set this table up in the database interface, usually the $wpdb global.
	 *
	 * This must be done directly because WordPress does not have a mechanism
	 * for manipulating them safely
	 *
	 * @since 3.0
	 */
	private function set_db_interface() {

		// Get the database once, to avoid duplicate function calls
		$db = $this->get_db();

		// Bail if no database
		if ( empty( $db ) ) {
			return;
		}

		// Set variables for global tables
		if ( $this->is_global() ) {
			$site_id = 0;
			$tables  = 'ms_global_tables';

		// Set variables for per-site tables
		} else {
			$site_id = null;
			$tables  = 'tables';
		}

		// Set the table prefix and prefix the table name
		$this->table_prefix  = $db->get_blog_prefix( $site_id );

		// Get the prefixed table name
		$prefixed_table_name = "{$this->table_prefix}{$this->prefixed_name}";

		// Set the database interface
		$db->{$this->prefixed_name} = $this->table_name = $prefixed_table_name;

		// Create the array if it does not exist
		if ( ! isset( $db->{$tables} ) ) {
			$db->{$tables} = array();
		}

		// Add the table to the global table array
		$db->{$tables}[] = $this->prefixed_name;

		// Charset
		if ( ! empty( $db->charset ) ) {
			$this->charset_collation = "DEFAULT CHARACTER SET {$db->charset}";
		}

		// Collation
		if ( ! empty( $db->collate ) ) {
			$this->charset_collation .= " COLLATE {$db->collate}";
		}
	}

	/**
	 * Set the database version for the table
	 *
	 * @since 3.0
	 *
	 * @param mixed $version Database version to set when upgrading/creating
	 */
	private function set_db_version( $version = '' ) {

		// If no version is passed during an upgrade, use the current version
		if ( empty( $version ) ) {
			$version = $this->version;
		}

		// Update the DB version
		$this->is_global()
			? update_network_option( get_main_network_id(), $this->db_version_key, $version )
			:         update_option(                        $this->db_version_key, $version );

		// Set the DB version
		$this->db_version = $version;
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
	 * Delete the table version from the database
	 *
	 * @since 3.0
	 */
	private function delete_db_version() {
		$this->db_version = $this->is_global()
			? delete_network_option( get_main_network_id(), $this->db_version_key, false )
			:         delete_option(                        $this->db_version_key, false );
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

			// Look for local class method
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
}
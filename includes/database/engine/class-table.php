<?php
/**
 * Base Custom Database Table Class.
 *
 * @package     Database
 * @subpackage  Table
 * @copyright   Copyright (c) 2020
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0.0
 */
namespace EDD\Database;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * A base database table class, which facilitates the creation of (and schema
 * changes to) individual database tables.
 *
 * This class is intended to be extended for each unique database table,
 * including global tables for multisite, and users tables.
 *
 * It exists to make managing database tables as easy as possible.
 *
 * Extending this class comes with several automatic benefits:
 * - Activation hook makes it great for plugins
 * - Tables store their versions in the database independently
 * - Tables upgrade via independent upgrade abstract methods
 * - Multisite friendly - site tables switch on "switch_blog" action
 *
 * @since 1.0.0
 */
abstract class Table extends Base {

	/**
	 * Table name, without the global table prefix.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $name = '';

	/**
	 * Optional description.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $description = '';

	/**
	 * Database version.
	 *
	 * @since 1.0.0
	 * @var   mixed
	 */
	protected $version = '';

	/**
	 * Is this table for a site, or global.
	 *
	 * @since 1.0.0
	 * @var   bool
	 */
	protected $global = false;

	/**
	 * Database version key (saved in _options or _sitemeta)
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $db_version_key = '';

	/**
	 * Current database version.
	 *
	 * @since 1.0.0
	 * @var   mixed
	 */
	protected $db_version = 0;

	/**
	 * Table prefix, including the site prefix.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $table_prefix = '';

	/**
	 * Table name.
	 *
	 * @since 1.0.0
	 * @var  string
	 */
	protected $table_name = '';

	/**
	 * Table name, prefixed from the base.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $prefixed_name = '';

	/**
	 * Table schema.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $schema = '';

	/**
	 * Database character-set & collation for table.
	 *
	 * @since 1.0.0
	 * @var   string
	 */
	protected $charset_collation = '';

	/**
	 * Key => value array of versions => methods.
	 *
	 * @since 1.0.0
	 * @var   array
	 */
	protected $upgrades = array();

	/** Methods ***************************************************************/

	/**
	 * Hook into queries, admin screens, and more!
	 *
	 * @since 1.0.0
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

		// Add hooks
		$this->add_hooks();

		// Maybe force upgrade if testing
		if ( $this->is_testing() ) {
			$this->maybe_upgrade();
		}
	}

	/** Abstract **************************************************************/

	/**
	 * Setup this database table.
	 *
	 * @since 1.0.0
	 */
	protected abstract function set_schema();

	/** Multisite *************************************************************/

	/**
	 * Update table version & references.
	 *
	 * Hooked to the "switch_blog" action.
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @param mixed $version Database version to check if upgrade is needed
	 *
	 * @return bool True if table needs upgrading. False if not.
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
	 * @since 1.0.0
	 *
	 * @return bool True if table can be upgraded. False if not.
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
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * @since 1.0.0
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
	 * Check if table already exists.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function exists() {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return false;
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
	 * Create the table.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function create() {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return false;
		}

		// Query statement
		$query  = "CREATE TABLE {$this->table_name} ( {$this->schema} ) {$this->charset_collation}";
		$result = $db->query( $query );

		// Was the table created?
		return $this->is_success( $result );
	}

	/**
	 * Drop the database table.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function drop() {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return false;
		}

		// Query statement
		$query  = "DROP TABLE {$this->table_name}";
		$result = $db->query( $query );

		// Did the table get dropped?
		return $this->is_success( $result );
	}

	/**
	 * Truncate the database table.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function truncate() {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return false;
		}

		// Query statement
		$query  = "TRUNCATE TABLE {$this->table_name}";
		$result = $db->query( $query );

		// Did the table get truncated?
		return $this->is_success( $result );
	}

	/**
	 * Delete all items from the database table.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function delete_all() {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return false;
		}

		// Query statement
		$query   = "DELETE FROM {$this->table_name}";
		$deleted = $db->query( $query );

		// Did the table get emptied?
		return $deleted;
	}

	/**
	 * Clone this database table.
	 *
	 * Pair with copy().
	 *
	 * @since 1.1.0
	 *
	 * @param string $new_table_name The name of the new table, without prefix
	 *
	 * @return bool
	 */
	public function _clone( $new_table_name = '' ) {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return false;
		}

		// Sanitize the new table name
		$table_name = $this->sanitize_table_name( $new_table_name );

		// Bail if new table name is invalid
		if ( empty( $table_name ) ) {
			return false;
		}

		// Query statement
		$table  = $this->apply_prefix( $table_name );
		$query  = "CREATE TABLE {$table} LIKE {$this->table_name}";
		$result = $db->query( $query );

		// Did the table get cloned?
		return $this->is_success( $result );
	}

	/**
	 * Copy the contents of this table to a new table.
	 *
	 * Pair with clone().
	 *
	 * @since 1.1.0
	 *
	 * @param string $new_table_name The name of the new table, without prefix
	 *
	 * @return bool
	 */
	public function copy( $new_table_name = '' ) {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return false;
		}

		// Sanitize the new table name
		$table_name = $this->sanitize_table_name( $new_table_name );

		// Bail if new table name is invalid
		if ( empty( $table_name ) ) {
			return false;
		}

		// Query statement
		$table  = $this->apply_prefix( $table_name );
		$query  = "INSERT INTO {$table} SELECT * FROM {$this->table_name}";
		$result = $db->query( $query );

		// Did the table get copied?
		return $this->is_success( $result );
	}

	/**
	 * Count the number of items in the database table.
	 *
	 * @since 1.0.0
	 *
	 * @return int
	 */
	public function count() {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return 0;
		}

		// Query statement
		$query = "SELECT COUNT(*) FROM {$this->table_name}";
		$count = $db->get_var( $query );

		// Query success/fail
		return intval( $count );
	}

	/**
	 * Check if column already exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name Value
	 *
	 * @return bool
	 */
	public function column_exists( $name = '' ) {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return false;
		}

		// Query statement
		$query    = "SHOW COLUMNS FROM {$this->table_name} LIKE %s";
		$like     = $db->esc_like( $name );
		$prepared = $db->prepare( $query, $like );
		$result   = $db->query( $prepared );

		// Does the column exist?
		return $this->is_success( $result );
	}

	/**
	 * Check if index already exists.
	 *
	 * @since 1.0.0
	 *
	 * @param string $name   Value
	 * @param string $column Column name
	 *
	 * @return bool
	 */
	public function index_exists( $name = '', $column = 'Key_name' ) {

		// Get the database interface
		$db = $this->get_db();

		// Bail if no database interface is available
		if ( empty( $db ) ) {
			return false;
		}

		$column = esc_sql( $column );

		// Query statement
		$query    = "SHOW INDEXES FROM {$this->table_name} WHERE {$column} LIKE %s";
		$like     = $db->esc_like( $name );
		$prepared = $db->prepare( $query, $like );
		$result   = $db->query( $prepared );

		// Does the index exist?
		return $this->is_success( $result );
	}

	/** Upgrades **************************************************************/

	/**
	 * Upgrade this database table.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	public function upgrade() {

		// Get pending upgrades
		$upgrades = $this->get_pending_upgrades();

		// Bail if no upgrades
		if ( empty( $upgrades ) ) {
			$this->set_db_version();

			// Return, without failure
			return true;
		}

		// Default result
		$result = false;

		// Try to do the upgrades
		foreach ( $upgrades as $version => $callback ) {

			// Do the upgrade
			$result = $this->upgrade_to( $version, $callback );

			// Bail if an error occurs, to avoid skipping upgrades
			if ( ! $this->is_success( $result ) ) {
				return false;
			}
		}

		// Success/fail
		return $this->is_success( $result );
	}

	/**
	 * Return array of upgrades that still need to run.
	 *
	 * @since 1.1.0
	 *
	 * @return array Array of upgrade callbacks, keyed by their db version.
	 */
	public function get_pending_upgrades() {

		// Default return value
		$upgrades = array();

		// Bail if no upgrades, or no database version to compare to
		if ( empty( $this->upgrades ) || empty( $this->db_version ) ) {
			return $upgrades;
		}

		// Loop through all upgrades, and pick out the ones that need doing
		foreach ( $this->upgrades as $version => $callback ) {
			if ( true === version_compare( $version, $this->db_version, '>' ) ) {
				$upgrades[ $version ] = $callback;
			}
		}

		// Return
		return $upgrades;
	}

	/**
	 * Upgrade to a specific database version.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed  $version  Database version to check if upgrade is needed
	 * @param string $callback Callback function or class method to call
	 *
	 * @return bool
	 */
	public function upgrade_to( $version = '', $callback = '' ) {

		// Bail if no upgrade is needed
		if ( ! $this->needs_upgrade( $version ) ) {
			return false;
		}

		// Allow self-named upgrade callbacks
		if ( empty( $callback ) ) {
			$callback = $version;
		}

		// Is the callback... callable?
		$callable = $this->get_callable( $callback );

		// Bail if no callable upgrade was found
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
	 * Setup the necessary table variables.
	 *
	 * @since 1.0.0
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

		// Separator
		$glue = '_';

		// Setup the prefixed name
		$this->prefixed_name = $this->apply_prefix( $this->name, $glue );

		// Maybe create database key
		if ( empty( $this->db_version_key ) ) {
			$this->db_version_key = implode(
				$glue,
				array(
					sanitize_key( $this->db_global ),
					$this->prefixed_name,
					'version'
				)
			);
		}
	}

	/**
	 * Set this table up in the database interface.
	 *
	 * This must be done directly because the database interface does not
	 * have a common mechanism for manipulating them safely.
	 *
	 * @since 1.0.0
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
	 * Set the database version for the table.
	 *
	 * @since 1.0.0
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
	 * Get the table version from the database.
	 *
	 * @since 1.0.0
	 */
	private function get_db_version() {
		$this->db_version = $this->is_global()
			? get_network_option( get_main_network_id(), $this->db_version_key, 1 )
			: get_option( $this->db_version_key, 1 );
	}

	/**
	 * Delete the table version from the database.
	 *
	 * @since 1.0.0
	 */
	private function delete_db_version() {
		$this->db_version = $this->is_global()
			? delete_network_option( get_main_network_id(), $this->db_version_key )
			:         delete_option(                        $this->db_version_key );
	}

	/**
	 * Add class hooks to the parent application actions.
	 *
	 * @since 1.0.0
	 */
	private function add_hooks() {

		// Add table to the global database object
		add_action( 'switch_blog', array( $this, 'switch_blog'   ) );
		add_action( 'admin_init',  array( $this, 'maybe_upgrade' ) );
	}

	/**
	 * Check if the current request is from some kind of test.
	 *
	 * This is primarily used to skip 'admin_init' and force-install tables.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_testing() {
		return edd_is_doing_unit_tests();
	}

	/**
	 * Check if table is global.
	 *
	 * @since 1.0.0
	 *
	 * @return bool
	 */
	private function is_global() {
		return ( true === $this->global );
	}

	/**
	 * Try to get a callable upgrade, with some magic to avoid needing to
	 * do this dance repeatedly inside subclasses.
	 *
	 * @since 1.0.0
	 *
	 * @param string $callback
	 *
	 * @return mixed Callable string, or false if not callable
	 */
	private function get_callable( $callback = '' ) {

		// Default return value
		$callable = $callback;

		// Look for global function
		if ( ! is_callable( $callable ) ) {

			// Fallback to local class method
			$callable = array( $this, $callback );
			if ( ! is_callable( $callable ) ) {

				// Fallback to class method prefixed with "__"
				$callable = array( $this, "__{$callback}" );
				if ( ! is_callable( $callable ) ) {
					$callable = false;
				}
			}
		}

		// Return callable string, or false if not callable
		return $callable;
	}
}

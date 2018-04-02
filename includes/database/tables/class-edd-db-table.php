<?php

/**
 * A Base WordPress Database Table class
 *
 * @version 3.0.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'EDD_DB_Table' ) ) :
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
	 * @since 3.0.0
	 */
	abstract class EDD_DB_Table extends EDD_DB_Base {

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

		/** Methods ***************************************************************/

		/**
		 * Hook into queries, admin screens, and more!
		 *
		 * @since 3.0.0
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
		 * @since 3.0.0
		 */
		protected abstract function set_schema();

		/**
		 * Upgrade this database table
		 *
		 * @since 3.0.0
		 */
		protected abstract function upgrade();

		/** Public ****************************************************************/

		/**
		 * Update table version & references.
		 *
		 * Hooked to the "switch_blog" action.
		 *
		 * @since 3.0.0
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
		 * @since 3.0.0
		 */
		public function maybe_upgrade() {

			// Is an upgrade needed?
			$needs_upgrade = version_compare( (int) $this->db_version, (int) $this->version, '>=' );

			// Bail if no upgrade needed
			if ( true === $needs_upgrade ) {
				return;
			}

			// Bail if global and upgrading global tables is not allowed
			if ( $this->is_global() && ! wp_should_upgrade_global_tables() ) {
				return;
			}

			// Create or upgrade?
			$this->exists()
				? $this->upgrade()
				: $this->create();

			// Only set database version if table exists
			if ( $this->exists() ) {
				$this->set_db_version();
			}
		}

		/**
		 * Check if table already exists
		 *
		 * @since 3.0.0
		 *
		 * @return bool
		 */
		public function exists() {
			$query       = "SHOW TABLES LIKE %s";
			$like        = $this->get_db()->esc_like( $this->table_name );
			$prepared    = $this->get_db()->prepare( $query, $like );
			$table_exist = $this->get_db()->get_var( $prepared );

			// Does the table exist?
			return ! empty( $table_exist );
		}

		/**
		 * Truncate the database table
		 *
		 * @since 3.0.0
		 *
		 * @return mixed
		 */
		public function truncate() {
			$query     = "TRUNCATE TABLE {$this->table_name}";
			$truncated = $this->get_db()->query( $query );

			// Query success/fail
			return $truncated;
		}

		/**
		 * Delete all items from the database table
		 *
		 * @since 3.0.0
		 *
		 * @return mixed
		 */
		public function delete_all() {
			$query   = "DELETE FROM {$this->table_name}";
			$deleted = $this->get_db()->query( $query );

			// Query success/fail
			return $deleted;
		}

		/** Private ***************************************************************/

		/**
		 * Return the global database interface
		 *
		 * @since 3.0.0
		 *
		 * @return wpdb
		 */
		private static function get_db() {
			return isset( $GLOBALS['wpdb'] )
				? $GLOBALS['wpdb']
				: new stdClass();
		}

		/**
		 * Setup the necessary table variables
		 *
		 * @since 3.0.0
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
		 * @since 3.0.0
		 */
		private function set_wpdb_tables() {

			// Global
			if ( $this->is_global() ) {
				$prefix                             = $this->get_db()->get_blog_prefix( 0 );
				$this->get_db()->{$this->name}      = "{$prefix}{$this->name}";
				$this->get_db()->ms_global_tables[] = $this->name;

				// Site
			} else {
				$prefix                        = $this->get_db()->get_blog_prefix( null );
				$this->get_db()->{$this->name} = "{$prefix}{$this->name}";
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
		 * Global table version in "_sitemeta" on the main network
		 *
		 * @since 3.0.0
		 */
		private function set_db_version() {

			// Set the class version
			$this->db_version = $this->version;

			// Update the DB version
			$this->is_global()
				? update_network_option( null, $this->db_version_key, $this->version )
				:         update_option(       $this->db_version_key, $this->version );
		}

		/**
		 * Get the table version from the database
		 *
		 * Global table version from "_sitemeta" on the main network
		 *
		 * @since 3.0.0
		 */
		private function get_db_version() {
			$this->db_version = $this->is_global()
				? get_network_option( null, $this->db_version_key, false )
				:         get_option(       $this->db_version_key, false );
		}

		/**
		 * Add class hooks to WordPress actions
		 *
		 * @since 3.0.0
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
		 * @since 1.4.0
		 *
		 * @return bool
		 */
		private function is_testing() {
			return (bool)

				       // Tests constant is being used
			       ( defined( 'WP_TESTS_DIR' ) && WP_TESTS_DIR )

			       // Scaffolded (https://make.wordpress.org/cli/handbook/plugin-unit-tests/)
			       || function_exists( '_manually_load_plugin' );
		}

		/**
		 * Create the table
		 *
		 * @since 3.0.0
		 */
		private function create() {

			// Include file with dbDelta() for create/upgrade usages
			if ( ! function_exists( 'dbDelta' ) ) {
				require_once ABSPATH . 'wp-admin/includes/upgrade.php';
			}

			// Bail if dbDelta() moved in WordPress core
			if ( ! function_exists( 'dbDelta' ) ) {
				return false;
			}

			// Run CREATE TABLE query
			$query   = "CREATE TABLE {$this->table_name} ( {$this->schema} ) {$this->charset_collation};";
			$created = dbDelta( array( $query ) );

			// Was the table created?
			return ! empty( $created );
		}

		/**
		 * Check if table is global
		 *
		 * @since 3.0.0
		 *
		 * @return bool
		 */
		private function is_global() {

			// Is the table global?
			return ( true === $this->global );
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
		 * @since 3.0.0
		 *
		 * @param string $name The name of the database table
		 *
		 * @return string Sanitized database table name
		 */
		private function sanitize_table_name( $name = '' ) {

			// Only non-accented table names (avoid truncation)
			$accents = remove_accents( $name );

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
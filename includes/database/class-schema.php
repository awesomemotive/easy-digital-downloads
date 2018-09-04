<?php
/**
 * Base Schema Class.
 *
 * @package     EDD
 * @subpackage  Database\Schemas
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Database;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( '\\EDD\\Database\\Schema' ) ) :
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
	class Schema extends Base {

		/**
		 * Array of database column objects to turn into \EDD\Database\Column
		 *
		 * @since 3.0
		 * @access public
		 * @var array
		 */
		protected $columns = array();

		/**
		 * Invoke new column objects based on array of column data
		 *
		 * @since 3.0
		 * @access public
		 */
		public function __construct() {

			// Bail if no columns
			if ( empty( $this->columns ) || ! is_array( $this->columns ) ) {
				return;
			}

			// Juggle original columns array
			$columns       = $this->columns;
			$this->columns = array();

			// Loop through columns and create objects from them
			foreach ( $columns as $column ) {
				if ( is_array( $column ) ) {
					$this->columns[] = new Column( $column );
				} elseif ( $column instanceof Column ) {
					$this->columns[] = $column;
				}
			}
		}
	}
endif;

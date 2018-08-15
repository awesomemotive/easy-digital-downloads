<?php
/**
 * Discount Meta Table.
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

/**
 * Setup the global "edd_discountmeta" database table
 *
 * @since 3.0
 */
final class Discount_Meta extends Base {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_discountmeta';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 201808010002;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'201808010002' => 201808010002
	);

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$max_index_length = 191;
		$this->schema     = "meta_id bigint(20) unsigned NOT NULL auto_increment,
			edd_discount_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY (meta_id),
			KEY edd_discount_id (edd_discount_id),
			KEY meta_key (meta_key({$max_index_length}))";
	}

	/**
	 * Upgrade to version 201808010002
	 * - Migrate data from edd_adjustmentmeta to edd_discountmeta.
	 *
	 * This is only for 3.0 beta testers, and can be removed in 3.0.1 or above.
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __201808010002() {

		// Old adjustments table
		$table_name = $this->get_db()->get_blog_prefix( null ) . 'edd_adjustmentmeta';

		// Does old table exist?
		$query    = "SHOW TABLES LIKE %s";
		$like     = $this->get_db()->esc_like( $table_name );
		$prepared = $this->get_db()->prepare( $query, $like );
		$result   = $this->get_db()->get_var( $prepared );

		// Bail if no old table
		if ( empty( $result ) || is_wp_error( $result ) ) {
			return true;
		}

		// Get the contents
		$discounts = $this->get_db()->get_results( "SELECT * FROM {$table_name}" );

		// Migrate discounts to adjustments
		if ( ! empty( $discounts ) ) {
			foreach ( $discounts as $discount ) {
				$this->get_db()->insert( $this->table_name, array(
					'edd_discount_id' => $discount->edd_adjustment_id,
					'meta_key'        => $discount->meta_key,
					'meta_value'      => $discount->meta_value,
				) );
			}
		}

		// Delete the old option
		delete_option( 'wpdb_edd_adjustmentmeta_version' );

		// Attempt to drop the old table
		$this->get_db()->query( "
			DROP TABLE {$table_name};
		" );

		// Return success/fail
		return true;
	}
}
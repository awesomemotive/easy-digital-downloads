<?php
/**
 * Tax Rates Table.
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
 * Setup the global "edd_tax_rates" database table
 *
 * @since 3.0
 */
final class Tax_Rates extends Base {

	/**
	 * Table name
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'edd_tax_rates';

	/**
	 * Database version
	 *
	 * @access protected
	 * @since 3.0
	 * @var int
	 */
	protected $version = 20180801002;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.0
	 *
	 * @var array
	 */
	protected $upgrades = array(
		'20180801002' => 20180801002,
	);

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = "id bigint(20) unsigned NOT NULL auto_increment,
			status varchar(20) NOT NULL default 'active',
			country varchar(200) NOT NULL default '',
			region varchar(200) NOT NULL default '',
			scope varchar(20) NOT NULL default 'region',
			rate decimal(18,9) NOT NULL default '0',
			start_date datetime NOT NULL default '0000-00-00 00:00:00',
			end_date datetime NOT NULL default '0000-00-00 00:00:00',
			date_created datetime NOT NULL default '0000-00-00 00:00:00',
			date_modified datetime NOT NULL default '0000-00-00 00:00:00',
			uuid varchar(100) NOT NULL default '',
			PRIMARY KEY (id),
			KEY status_country_region (status(20),country(200),region(200)),
			KEY date_created (date_created),
			KEY date_start_end (start_date,end_date)";
	}

	/**
	 * Upgrade to version 20180801002
	 * - Migrate data from edd_adjustments to edd_discounts
	 *
	 * This is only for 3.0 beta testers, and can be removed in 3.0.1 or above.
	 *
	 * @since 3.0
	 *
	 * @return boolean
	 */
	protected function __20180801002() {

		// Old adjustments table
		$table_name = $this->get_db()->get_blog_prefix( null ) . 'edd_adjustments';

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
		$tax_rates = $this->get_db()->get_results( "SELECT * FROM {$table_name} WHERE type = 'tax_rate'" );

		// Migrate adjustments to tax rates
		if ( ! empty( $tax_rates ) ) {
			foreach ( $tax_rates as $tax_rate ) {
				$this->get_db()->insert( $this->table_name, array(
					'status'        => $tax_rate->status,
					'country'       => $tax_rate->name,
					'region'        => $tax_rate->description,
					'scope'         => $tax_rate->scope,
					'rate'          => $tax_rate->amount,
					'date_created'  => $tax_rate->date_created,
					'date_modified' => $tax_rate->date_modified,
					'start_date'    => $tax_rate->start_date,
					'end_date'      => $tax_rate->end_date,
					'uuid'          => $tax_rate->uuid,
				) );
			}
		}

		// Delete the old option
		delete_option( 'wpdb_edd_adjustments_version' );

		// Attempt to drop the old table
		$this->get_db()->query( "DROP TABLE {$table_name};" );

		// Return success/fail
		return true;
	}
}
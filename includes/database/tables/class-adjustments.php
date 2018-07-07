<?php
/**
 * Adjustments Table.
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

if ( class_exists( '\\EDD\\Database\\Tables\\Base' ) ) :
/**
 * Setup the global "edd_adjustments" database table
 *
 * @since 3.0
 */
final class Adjustments extends Base {

    /**
     * Table name
     *
     * @access protected
     * @since 3.0
     * @var string
     */
    protected $name = 'edd_adjustments';

    /**
     * Database version
     *
     * @access protected
     * @since 3.0
     * @var int
     */
    protected $version = 201806140002;

    /**
     * Array of upgrade versions and methods
     *
     * @since 3.0
     *
     * @var array
     */
    protected $upgrades = array(
        '201806140002' => 201806140002
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
            parent bigint(20) unsigned NOT NULL default '0',
            name varchar(200) NOT NULL default '',
            code varchar(50) NOT NULL default '',
            status varchar(20) NOT NULL default '',
            type varchar(20) NOT NULL default '',
            scope varchar(20) NOT NULL default 'all',
            amount_type varchar(20) NOT NULL default '',
            amount decimal(18,9) NOT NULL default '0',
            description longtext NOT NULL default '',
            max_uses bigint(20) unsigned NOT NULL default '0',
            use_count bigint(20) unsigned NOT NULL default '0',
            once_per_customer int(1) NOT NULL default '0',
            min_charge_amount decimal(18,9) NOT NULL default '0',
            product_condition varchar(20) NOT NULL DEFAULT 'all',
            date_created datetime NOT NULL default '0000-00-00 00:00:00',
            date_modified datetime NOT NULL default '0000-00-00 00:00:00',
            start_date datetime NOT NULL default '0000-00-00 00:00:00',
            end_date datetime NOT NULL default '0000-00-00 00:00:00',
            PRIMARY KEY (id),
            KEY code_status_type_scope_amount (code(50),status(20),type(20),scope(20),amount_type(20)),
            KEY date_created (date_created),
            KEY date_start_end (start_date,end_date)";
    }

    /**
     * Upgrade to version 201806140002
     * - Migrate data from edd_discounts to edd_adjustments
     *
	 * This is only for 3.0 beta testers, and can be removed in 3.0.1 or above.
	 *
     * @since 3.0
     *
     * @return boolean
     */
    protected function __201806140002() {

        // Old discounts table
        $table_name = $this->get_db()->get_blog_prefix( null ) . 'edd_discounts';

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
					'parent'            => $discount->parent,
					'name'              => $discount->name,
					'code'              => $discount->code,
					'status'            => $discount->status,
					'type'              => 'discount',
					'scope'             => $discount->scope,
					'amount_type'       => $discount->type,
					'amount'            => $discount->amount,
					'description'       => $discount->description,
					'max_uses'          => $discount->max_uses,
					'use_count'         => $discount->use_count,
					'once_per_customer' => $discount->once_per_customer,
					'min_cart_price'    => $discount->min_cart_price,
					'product_condition' => $discount->product_condition,
					'date_created'      => $discount->date_created,
					'date_modified'     => $discount->date_modified,
					'start_date'        => $discount->start_date,
					'end_date'          => $discount->end_date
				) );
			}
		}

		// Delete the old option
		delete_option( 'wpdb_edd_discounts_version' );

		// Attempt to drop the old table
		$this->get_db()->query( "
			DROP TABLE {$table_name};
		" );

        // Return success/fail
        return true;
    }
}
endif;
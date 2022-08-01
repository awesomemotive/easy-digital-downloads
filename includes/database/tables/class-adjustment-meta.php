<?php
/**
 * Adjustment Meta Table.
 *
 * @package     EDD
 * @subpackage  Database\Tables
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */
namespace EDD\Database\Tables;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Database\Table;

/**
 * Setup the global "edd_adjustmentmeta" database table.
 *
 * @since 3.0
 */
final class Adjustment_Meta extends Table {

	/**
	 * Table name.
	 *
	 * @access protected
	 * @since 3.0
	 * @var string
	 */
	protected $name = 'adjustmentmeta';

    /**
     * Database version.
     *
     * @access protected
     * @since 3.0
     * @var int
     */
    protected $version = 201806142;

    /**
     * Array of upgrade versions and methods.
     *
     * @access protected
     * @since 3.0
     * @var array
     */
    protected $upgrades = array();

	/**
	 * Setup the database schema.
	 *
	 * @access protected
	 * @since 3.0
	 */
	protected function set_schema() {
		$max_index_length = 191;
		$this->schema     = "meta_id bigint(20) unsigned NOT NULL auto_increment,
			edd_adjustment_id bigint(20) unsigned NOT NULL default '0',
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext DEFAULT NULL,
			PRIMARY KEY (meta_id),
			KEY edd_adjustment_id (edd_adjustment_id),
			KEY meta_key (meta_key({$max_index_length}))";
	}
}

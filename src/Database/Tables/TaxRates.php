<?php
/**
 * TaxRates Table Class.
 *
 * @package     EDD\Database\Tables
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.5.0
 */

namespace EDD\Database\Tables;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Database\Table;

/**
 * Class TaxRates
 *
 * @since 3.5.0
 * @package EDD\Database\Tables
 */
class TaxRates extends Table {

	/**
	 * Table name.
	 *
	 * @access protected
	 * @since 3.5.0
	 * @var string
	 */
	protected $name = 'tax_rates';

	/**
	 * Database version.
	 *
	 * @access protected
	 * @since 3.5.0
	 * @var int
	 */
	protected $version = 202501140;

	/**
	 * Array of upgrade versions and methods
	 *
	 * @since 3.5.0
	 *
	 * @var array
	 */
	protected $upgrades = array();

	/**
	 * Set up the database schema.
	 *
	 * @access protected
	 * @since 3.5.0
	 * @return void
	 */
	protected function set_schema() {
		$this->schema = '
			id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
			country varchar(64) DEFAULT NULL,
			state varchar(64) DEFAULT NULL,
			amount decimal(18,9) NOT NULL DEFAULT 0,
			scope varchar(20) NOT NULL DEFAULT "country",
			status varchar(20) NOT NULL DEFAULT "active",
			source varchar(20) NOT NULL DEFAULT "manual",
			date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			date_modified datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY (id),
			KEY country_state (country, state)
		';
	}
}

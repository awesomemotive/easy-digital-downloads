<?php

/**
 * Orders: EDD_DB_Query class
 *
 * @package Plugins/EDD/Database/Queries
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_DB_Query' ) ) :
/**
 * Base class used for querying custom database tables.
 *
 * This extends the WP_DB_Query base class, and adds the EDD specific prefix.
 *
 * @since 3.0.0
 *
 * @see EDD_DB_Query::__construct() for accepted arguments.
 */
class EDD_DB_Query extends WP_DB_Query {

	/**
	 * Global prefix used for EDD specific tables/hooks/cache-groups/etc...
	 *
	 * @since 3.0.0
	 *
	 * @var string
	 */
	public $prefix = 'edd';
}
endif;

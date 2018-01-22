<?php

/**
 * EDD_DB_Table class
 *
 * @package Plugins/EDD/Database/Object
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

if ( class_exists( 'WP_DB_Table' ) ) :
/**
 * Setup the EDD specific database table class, which sets the plugin
 * file variable for all future subclasses.
 *
 * @since 3.0.0
 */
class EDD_DB_Table extends WP_DB_Table {

	/**
	 * File passed to register_activation_hook()
	 *
	 * This is the same for all of EDD
	 *
	 * @access protected
	 * @since 3.0.0
	 * @var string
	 */
	protected $file = EDD_PLUGIN_FILE;

	/**
	 * Setup the database schema
	 *
	 * @access protected
	 * @since 3.0.0
	 * @return void
	 */
	protected function set_schema() {

	}

	/**
	 * Handle schema changes
	 *
	 * @access protected
	 * @since 3.0.0
	 * @return void
	 */
	protected function upgrade() {

	}
}
endif;

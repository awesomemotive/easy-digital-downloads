<?php
/**
 * API Key Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Tools/APIKeys
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_API_Keys_Table Class
 *
 * Renders the API Keys table
 *
 * @since 2.0
 */
class EDD_API_Keys_Table extends WP_List_Table {

	/**
	 * @var int Number of items per page
	 * @since 2.0
	 */
	public $per_page = 30;

	/**
	 * @var object Query results
	 * @since 2.0
	 */
	private $keys;

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => __( 'API Key', 'edd' ),     // Singular name of the listed records
			'plural'    => __( 'API Keys', 'edd' ),    // Plural name of the listed records
			'ajax'      => false                       // Does this table support ajax?
		) );

		$this->query();
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 2.0
	 *
	 * @param array $item Contains all the data of the keys
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		return $item[ $column_name ];
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 2.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'user'         => __( 'User', 'edd' ),
			'key'          => __( 'API Key', 'edd' ),
			'secret'       => __( 'API Secret', 'edd' ),
			'token'        => __( 'API Token', 'edd' ),
			'action'       => __( 'Actions', 'edd' )
		);

		return $columns;
	}

	/**
	 * Retrieve the current page number
	 *
	 * @access public
	 * @since 2.0
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
	}

	/**
	 * Performs the key query
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function query() {

		$orderby  = isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'title';
		$order    = isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
		$users    = get_users();
		$keys     = array();

		foreach( $users as $user ) {
			$user_meta = get_user_meta( $user->ID, 'edd_user_secret_key', true );

			if( !empty( $user->edd_user_public_key ) ) {
				$keys[$user->ID]['user']   = '<a href="' . add_query_arg( 'user_id', $user->ID, 'user-edit.php' ) . '">' . $user->user_login . '</a>';
				$keys[$user->ID]['key']    = $user->edd_user_public_key;
				$keys[$user->ID]['secret'] = $user->edd_user_secret_key;
				$keys[$user->ID]['token']  = hash( 'md5', $user->edd_user_secret_key . $user->edd_user_public_key );
				$keys[$user->ID]['action'] = '<a href="' . add_query_arg( array( 'user_id' => $user->ID, 'edd_set_api_key' => 'true' ), 'user-edit.php' )  . '">' . __( 'Revoke', 'edd' ) . '</a>';
			}
		}

		return $keys;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 2.0
	 * @return void
	 */
	public function prepare_items() {
		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns
		$sortable = array(); // Not sortable... for now

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$data = $this->query();

		$current_page = $this->get_pagenum();

		$total_items = count( $data );

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $total_items / $this->per_page )
			)
		);
	}
}
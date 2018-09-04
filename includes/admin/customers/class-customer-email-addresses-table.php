<?php
/**
 * Customer Email Addresses Table Class
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;

/**
 * EDD_Customer_Email_Addresses_Table Class
 *
 * Renders the Customer Reports table
 *
 * @since 3.0
 */
class EDD_Customer_Email_Addresses_Table extends List_Table {

	/**
	 * The arguments for the data set
	 *
	 * @var array
	 * @since  2.6
	 */
	public $args = array();

	/**
	 * Get things started
	 *
	 * @since 3.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct(
			array(
				'singular' => __( 'Email', 'easy-digital-downloads' ),
				'plural'   => __( 'Emails', 'easy-digital-downloads' ),
				'ajax'     => false,
			)
		);

		$this->process_bulk_action();
		$this->get_counts();
	}

	/**
	 * Gets the name of the primary column.
	 *
	 * @since 2.5
	 * @access protected
	 *
	 * @return string Name of the primary column.
	 */
	protected function get_primary_column_name() {
		return 'email';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 3.0
	 *
	 * @param array  $item Contains all the data of the customers
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'id':
				$value = $item['id'];
				break;

			case 'email':
				$value = '<a href="mailto:' . esc_attr( $item['email'] ) . '">' . esc_html( $item['email'] ) . '</a>';
				break;

			case 'type':
				$value = ( 'primary' === $item['type'] )
					? esc_html_e( 'Primary', 'easy-digital-downloads' )
					: esc_html_e( 'Secondary', 'easy-digital-downloads' );
				break;

			case 'date_created':
				$value = '<time datetime="' . esc_attr( $item['date_created'] ) . '">' . edd_date_i18n( $item['date_created'], 'M. d, Y' ) . '<br>' . edd_date_i18n( $item['date_created'], 'H:i' ) . '</time>';
				break;

			default:
				$value = isset( $item[ $column_name ] )
					? $item[ $column_name ]
					: null;
				break;
		}

		// Filter & return
		return apply_filters( 'edd_customers_column_' . $column_name, $value, $item['id'] );
	}

	/**
	 * Return the contents of the "Name" column
	 *
	 * @since 3.0
	 *
	 * @param array $item
	 * @return string
	 */
	public function column_email( $item ) {
		$state  = '';
		$status = $this->get_status();
		$email  = ! empty( $item['email'] ) ? $item['email'] : '&mdash;';

		// Get the item status
		$item_status = ! empty( $item['status'] )
			? $item['status']
			: 'verified';

		// Get the customer ID
		$customer_id = ! empty( $item['customer_id'] )
			? absint( $item['customer_id'] )
			: 0;

		// Link to customer
		$customer_url = edd_get_admin_url(
			array(
				'page' => 'edd-customers',
				'view' => 'overview',
				'id'   => $customer_id,
			)
		);

		// Actions
		$actions = array(
			'view' => '<a href="' . esc_url( $customer_url ) . '">' . __( 'View', 'easy-digital-downloads' ) . '</a>',
		);

		// Non-primary email actions
		if ( 'primary' !== $item_status ) {
			$actions['delete'] = '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-customers&view=delete&id=' . $item['id'] ) . '">' . __( 'Delete', 'easy-digital-downloads' ) . '</a>';
		}

		// State
		if ( ( ! empty( $status ) && ( $status !== $item_status ) ) || ( $item_status !== 'active' ) ) {
			switch ( $status ) {
				case 'pending':
					$value = __( 'Pending', 'easy-digital-downloads' );
					break;
				case 'verified':
				case '':
				default:
					$value = __( 'Active', 'easy-digital-downloads' );
					break;
			}

			$state = ' &mdash; ' . $value;
		}

		// Concatenate and return
		return '<strong><a class="row-title" href="' . esc_url( $customer_url ) . '">' . esc_html( $email ) . '</a>' . esc_html( $state ) . '</strong>' . $this->row_actions( $actions );
	}

	/**
	 * Return the contents of the "Name" column
	 *
	 * @since 3.0
	 *
	 * @param array $item
	 * @return string
	 */
	public function column_customer( $item ) {

		// Get the customer ID
		$customer_id = ! empty( $item['customer_id'] )
			? absint( $item['customer_id'] )
			: 0;

		// Bail if no customer ID
		if ( empty( $customer_id ) ) {
			return '&mdash;';
		}

		// Try to get the customer
		$customer = edd_get_customer( $customer_id );

		// Bail if customer no longer exists
		if ( empty( $customer ) ) {
			return '&mdash;';
		}

		// Link to customer
		$customer_url = edd_get_admin_url(
			array(
				'page'      => 'edd-customers',
				'page_type' => 'emails',
				's'         => 'c:' . absint( $customer_id ),
			)
		);

		// Concatenate and return
		return '<a href="' . esc_url( $customer_url ) . '">' . esc_html( $customer->name ) . '</a>';
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param EDD_Customer $item Customer object.
	 *
	 * @return string Displays a checkbox
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'customer',
			/*$2%s*/ $item['id']
		);
	}

	/**
	 * Retrieve the customer counts
	 *
	 * @access public
	 * @since 3.0
	 * @return void
	 */
	public function get_counts() {
		$this->counts = edd_get_customer_email_address_counts();
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 3.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return apply_filters(
			'edd_report_customer_columns',
			array(
				'cb'           => '<input type="checkbox" />',
				'email'        => __( 'Email', 'easy-digital-downloads' ),
				'customer'     => __( 'Customer', 'easy-digital-downloads' ),
				'type'         => __( 'Type', 'easy-digital-downloads' ),
				'date_created' => __( 'Date', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Get the sortable columns
	 *
	 * @since 2.1
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'date_created' => array( 'date_created', true ),
			'email'        => array( 'email', true ),
			'customer'     => array( 'customer_id', false ),
			'type'         => array( 'type', false ),
		);
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since 3.0
	 * @return array Array of the bulk actions
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => __( 'Delete', 'easy-digital-downloads' ),
		);
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 3.0
	 */
	public function process_bulk_action() {
		if ( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-customers' ) ) {
			return;
		}

		$ids = isset( $_GET['customer'] )
			? $_GET['customer']
			: false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $id ) {
			switch ( $this->current_action() ) {
				case 'delete':
					edd_delete_customer_email_address( $id );
					break;
			}
		}
	}

	/**
	 * Get all of the items to display, given the current filters
	 *
	 * @since 3.0
	 *
	 * @return array $data All the row data
	 */
	public function get_items() {
		$data    = array();
		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$search  = $this->get_search();
		$status  = $this->get_status();
		$order   = isset( $_GET['order'] ) ? sanitize_text_field( $_GET['order'] ) : 'DESC'; // WPCS: CSRF ok.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id'; // WPCS: CSRF ok.

		$args = array(
			'limit'   => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby,
			'status'  => $status,
		);

		// Email
		if ( is_email( $search ) ) {
			$args['email'] = $search;

			// Address ID
		} elseif ( is_numeric( $search ) ) {
			$args['id'] = $search;

			// Customer ID
		} elseif ( strpos( $search, 'c:' ) !== false ) {
			$args['customer_id'] = trim( str_replace( 'c:', '', $search ) );

			// Any...
		} else {
			$args['search']         = $search;
			$args['search_columns'] = array( 'email' );
		}

		$this->args = $args;
		$emails     = edd_get_customer_email_addresses( $args );

		if ( $emails ) {
			foreach ( $emails as $customer ) {
				$data[] = array(
					'id'           => $customer->id,
					'email'        => $customer->email,
					'customer_id'  => $customer->customer_id,
					'status'       => $customer->status,
					'type'         => $customer->type,
					'date_created' => $customer->date_created,
				);
			}
		}

		return $data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 3.0
	 * @return void
	 */
	public function prepare_items() {
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns(),
		);

		$this->items = $this->get_items();

		$status = $this->get_status( 'total' );

		// Setup pagination
		$this->set_pagination_args(
			array(
				'total_pages' => ceil( $this->counts[ $status ] / $this->per_page ),
				'total_items' => $this->counts[ $status ],
				'per_page'    => $this->per_page,
			)
		);
	}
}

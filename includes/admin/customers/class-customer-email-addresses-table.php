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
	 * Get things started
	 *
	 * @since 3.0
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'email',
			'plural'   => 'emails',
			'ajax'     => false
		) );

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
	 * @param array $item Contains all the data of the customers
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {

			case 'id' :
				$value = $item['id'];
				break;

			case 'email' :
				$value = '<a href="mailto:' . rawurlencode( $item['email'] ) . '">' . esc_html( $item['email'] ) . '</a>';
				break;

			case 'type' :
				$value = ( 'primary' === $item['type'] )
					? esc_html__( 'Primary',   'easy-digital-downloads' )
					: esc_html__( 'Secondary', 'easy-digital-downloads' );
				break;

			case 'date_created' :
				$value = '<time datetime="' . esc_attr( $item['date_created'] ) . '">' . edd_date_i18n( $item['date_created'], 'M. d, Y' ) . '<br>' . edd_date_i18n( $item['date_created'], 'H:i' ) . ' ' . edd_get_timezone_abbr() . '</time>';
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
		$state    = '';
		$status   = $this->get_status();
		$email    = ! empty( $item['email']  ) ? $item['email'] : '&mdash;';

		// Get the item status
		$item_status = ! empty( $item['status'] )
			? $item['status']
			: 'verified';

		// Get the customer ID
		$customer_id = ! empty( $item['customer_id'] )
			? absint( $item['customer_id'] )
			: 0;

		// Link to customer
		$customer_url = edd_get_admin_url( array(
			'page' => 'edd-customers',
			'view' => 'overview',
			'id'   => absint( $customer_id ),
		) );

		// State
		if ( ( ! empty( $status ) && ( $status !== $item_status ) ) || ( $item_status !== 'active' ) ) {
			switch ( $status ) {
				case 'pending' :
					$value = __( 'Pending', 'easy-digital-downloads' );
					break;
				case 'verified' :
				case '' :
				default :
					$value = __( 'Active', 'easy-digital-downloads' );
					break;
			}

			$state = ' &mdash; ' . $value;
		}

		// Concatenate and return
		return '<strong><a class="row-title" href="' . esc_url( $customer_url ) . '#edd_general_emails">' . esc_html( $email ) . '</a>' . esc_html( $state ) . '</strong>' . $this->row_actions( $this->get_row_actions( $item ) );
	}

	/**
	 * Gets the row actions for the customer email address.
	 *
	 * @since 3.0
	 * @param array $item
	 * @return array
	 */
	private function get_row_actions( $item ) {
		// Link to customer
		$customer_url = edd_get_admin_url(
			array(
				'page' => 'edd-customers',
				'view' => 'overview',
				'id'   => urlencode( $item['customer_id'] ),
			)
		);

		// Actions
		$actions = array(
			'view' => '<a href="' . esc_url( $customer_url ) . '#edd_general_emails">' . __( 'View', 'easy-digital-downloads' ) . '</a>',
		);

		// Non-primary email actions
		if ( ! empty( $item['email'] ) && ( empty( $item['type'] ) || 'primary' !== $item['type'] ) ) {
			$delete_url = wp_nonce_url( edd_get_admin_url( array(
				'page'       => 'edd-customers',
				'view'       => 'overview',
				'id'         => urlencode( $item['customer_id'] ),
				'email'      => rawurlencode( $item['email'] ),
				'edd_action' => 'customer-remove-email',
			) ), 'edd-remove-customer-email' );
			$actions['delete'] = '<a href="' . esc_url( $delete_url ) . '">' . esc_html__( 'Delete', 'easy-digital-downloads' ) . '</a>';
		}

		/**
		 * Filter the customer email address row actions.
		 *
		 * @since 3.0
		 * @param array $actions The array of row actions.
		 * @param array $item    The specific item (customer email address).
		 */
		return apply_filters( 'edd_customer_email_address_row_actions', $actions, $item );
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
		$customer_url = edd_get_admin_url( array(
			'page'      => 'edd-customers',
			'page_type' => 'emails',
			's'         => 'c:' . absint( $customer_id )
		) );

		// Concatenate and return
		return '<a href="' . esc_url( $customer_url ) . '">' . esc_html( $customer->name ) . '</a>';
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 3.0
	 *
	 * @param array $item
	 *
	 * @return string Displays a checkbox
	 */
	public function column_cb( $item ) {
		$is_primary         = ! empty( $item['type'] ) && 'primary' === $item['type'];
		$primary_attributes = $is_primary ? ' disabled' : '';
		$title              = $is_primary ? __( 'Primary email addresses cannot be deleted.', 'easy-digital-downloads' ) : '';

		return sprintf(
			'<input type="checkbox" name="%1$s[]" id="%1$s-%2$s" value="%2$s" title="%4$s"%5$s /><label for="%1$s-%2$s" class="screen-reader-text">%3$s</label>',
			/*$1%s*/ esc_attr( 'customer' ),
			/*$2%s*/ esc_attr( $item['id'] ),
			/* translators: customer email */
			esc_html( sprintf( __( 'Select %s', 'easy-digital-downloads' ), $item['email'] ) ),
			/*$4%s*/ esc_attr( $title ),
			/*$5%s*/ $primary_attributes
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
		return apply_filters( 'edd_report_customer_columns', array(
			'cb'            => '<input type="checkbox" />',
			'email'         => __( 'Email',    'easy-digital-downloads' ),
			'customer'      => __( 'Customer', 'easy-digital-downloads' ),
			'type'          => __( 'Type',     'easy-digital-downloads' ),
			'date_created'  => __( 'Date',     'easy-digital-downloads' )
		) );
	}

	/**
	 * Get the sortable columns
	 *
	 * @since 2.1
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'date_created'  => array( 'date_created',   true  ),
			'email'         => array( 'email',          true  ),
			'customer'      => array( 'customer_id',    false ),
			'type'          => array( 'type',           false )
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
			'delete' => __( 'Delete', 'easy-digital-downloads' )
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

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-emails' ) ) {
			return;
		}

		$ids = isset( $_GET['customer'] )
			? $_GET['customer']
			: false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		/*
		 * Only non-primary email addresses can be deleted, so we're building up a safelist using the provided
		 * IDs. Each ID will be matched against this prior to deletion.
		 */
		$non_primary_address_ids = edd_get_customer_email_addresses( array(
			'id__in'       => $ids,
			'type__not_in' => array( 'primary' ),
			'fields'       => 'id'
		) );

		foreach ( $ids as $id ) {
			switch ( $this->current_action() ) {
				case 'delete' :
					if ( in_array( $id, $non_primary_address_ids ) ) {
						edd_delete_customer_email_address( $id );
					}
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
	public function get_data() {
		$data   = array();
		$search = $this->get_search();
		$args   = array( 'status'  => $this->get_status() );

		// Account for search stripping the "+" from emails.
		if ( strpos( $search, ' ' ) ) {
			$original_query = $search;
			$search         = str_replace( ' ', '+', $search );
			if ( ! is_email( $search ) ) {
				$search = $original_query;
			}
		}

		// Email.
		if ( is_email( $search ) ) {
			$args['email'] = $search;

		// Address ID.
		} elseif ( is_numeric( $search ) ) {
			$args['id'] = $search;

		// Customer ID.
		} elseif ( strpos( $search, 'c:' ) !== false ) {
			$args['customer_id'] = trim( str_replace( 'c:', '', $search ) );

		// Any...
		} else {
			$args['search']         = $search;
			$args['search_columns'] = array( 'email' );
		}

		// Parse pagination.
		$this->args = $this->parse_pagination_args( $args );

		// Get the data.
		$emails = edd_get_customer_email_addresses( $this->args );

		if ( ! empty( $emails ) ) {
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
			$this->get_sortable_columns()
		);

		$this->items = $this->get_data();

		$status = $this->get_status( 'total' );

		// Setup pagination
		$this->set_pagination_args( array(
			'total_pages' => ceil( $this->counts[ $status ] / $this->per_page ),
			'total_items' => $this->counts[ $status ],
			'per_page'    => $this->per_page
		) );
	}

	/**
	 * Generate the table navigation above or below the table.
	 * We're overriding this to turn off the referer param in `wp_nonce_field()`.
	 *
	 * @param string $which
	 * @since 3.1.0.4
	 */
	protected function display_tablenav( $which ) {
		if ( 'top' === $which ) {
			wp_nonce_field( 'bulk-' . $this->_args['plural'], '_wpnonce', false );
		}
		?>
		<div class="tablenav <?php echo esc_attr( $which ); ?>">

			<?php if ( $this->has_items() ) : ?>
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php
			endif;
			$this->extra_tablenav( $which );
			$this->pagination( $which );
			?>

			<br class="clear"/>
		</div>
		<?php
	}
}

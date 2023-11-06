<?php
/**
 * Customer Reports Table Class
 *
 * @package     EDD
 * @subpackage  Reports
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.5
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;

/**
 * EDD_Customer_Reports_Table Class
 *
 * Renders the Customer Reports table
 *
 * @since 1.5
 */
class EDD_Customer_Reports_Table extends List_Table {

	/**
	 * Get things started
	 *
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'customer',
			'plural'   => 'customers',
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
		return 'name';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.5
	 *
	 * @param array $item Contains all the data of the customers
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {

		$timezone_abbreviation = edd_get_timezone_abbr();

		switch ( $column_name ) {

			case 'id' :
				$value = esc_html( $item['id'] );
				break;

			case 'email' :
				$value = '<a href="mailto:' . rawurlencode( $item['email'] ) . '">' . esc_html( $item['email'] ) . '</a>';
				break;

			case 'order_count' :
				$url = edd_get_admin_url( array(
					'page'     => 'edd-payment-history',
					'customer' => rawurlencode( $item['id'] ),
				) );
				$value = '<a href="' . esc_url( $url ) . '">' . esc_html( number_format_i18n( $item['order_count'] ) ) . '</a>';
				break;

			case 'spent' :
				$value = edd_currency_filter( edd_format_amount( $item[ $column_name ] ) );
				break;

			case 'date_created' :
				$value = '<time datetime="' . esc_attr( $item['date_created'] ) . '">' . edd_date_i18n( $item['date_created'], 'M. d, Y' ) . '<br>' . edd_date_i18n( $item['date_created'], 'H:i' ) . ' ' . $timezone_abbreviation . '</time>';
				break;

			default:
				$value = isset( $item[ $column_name ] )
					? esc_html( $item[ $column_name ] )
					: null;
				break;
		}

		// Filter & return
		return apply_filters( 'edd_customers_column_' . esc_attr( $column_name ), $value, $item['id'] );
	}

	/**
	 * Return the contents of the "Name" column
	 *
	 * @since 3.0
	 *
	 * @param array $item
	 * @return string
	 */
	public function column_name( $item ) {
		$state  = '';
		$status = $this->get_status();
		$name   = ! empty( $item['name'] ) ? $item['name'] : '&mdash;';

		$item_status = ! empty( $item['status'] )
			? $item['status']
			: 'active';

		// State.
		if ( ( ! empty( $status ) && ( $status !== $item_status ) ) || ( 'active' !== $item_status ) ) {
			switch ( $item_status ) {
				case 'pending':
					$value = __( 'Pending', 'easy-digital-downloads' );
					break;
				case 'disabled':
					$value = __( 'Disabled', 'easy-digital-downloads' );
					break;
				case 'inactive':
					$value = __( 'Inactive', 'easy-digital-downloads' );
					break;
				case 'active':
				case '':
				default:
					$value = __( 'Active', 'easy-digital-downloads' );
					break;
			}

			$state = ' &mdash; ' . $value;
		}

		// Get the customer's avatar.
		$avatar = get_avatar( $item['email'], 32 );

		// View URL.
		$view_url = edd_get_admin_url(
			array(
				'page' => 'edd-customers',
				'view' => 'overview',
				'id'   => urlencode( $item['id'] ),
			)
		);

		// Concatenate and return.
		return $avatar . '<a class="row-title" href="' . esc_url( $view_url ) . '">' . esc_html( $name ) . '</a>' . esc_html( $state ) . $this->row_actions( $this->get_row_actions( $item ) );
	}

	/**
	 * Gets the row actions for the customer.
	 *
	 * @since 3.0
	 * @param array $item
	 * @return array
	 */
	private function get_row_actions( $item ) {
		$view_url   = edd_get_admin_url(
			array(
				'page' => 'edd-customers',
				'view' => 'overview',
				'id'   => urlencode( $item['id'] ),
			)
		);
		$logs_url   = edd_get_admin_url(
			array(
				'page'     => 'edd-tools',
				'tab'      => 'logs',
				'customer' => urlencode( $item['id'] ),
			)
		);
		$delete_url = edd_get_admin_url(
			array(
				'page' => 'edd-customers',
				'view' => 'delete',
				'id'   => urlencode( $item['id'] ),
			)
		);
		$actions    = array(
			'view'   => '<a href="' . esc_url( $view_url ) . '">' . __( 'Edit', 'easy-digital-downloads' ) . '</a>',
			'logs'   => '<a href="' . esc_url( $logs_url ) . '">' . __( 'Logs', 'easy-digital-downloads' ) . '</a>',
			'delete' => '<a href="' . esc_url( $delete_url ) . '#edd_general_delete">' . __( 'Delete', 'easy-digital-downloads' ) . '</a>',
		);

		/**
		 * Filter the customer row actions.
		 *
		 * @since 3.0
		 * @param array $actions The array of row actions.
		 * @param array $item    The specific item (customer).
		 */
		return apply_filters( 'edd_customer_row_actions', $actions, $item );
	}

	/**
	 * Render the checkbox column
	 *
	 * @since 3.0
	 *
	 * @param array $item Customer data.
	 *
	 * @return string Displays a checkbox
	 */
	public function column_cb( $item ) {
		$name = empty( $item['name'] ) ? $item['email'] : $item['name'];

		return sprintf(
			'<input type="checkbox" name="%1$s[]" id="%1$s-%2$s" value="%2$s" /><label for="%1$s-%2$s" class="screen-reader-text">%3$s</label>',
			/*$1%s*/ 'customer',
			/*$2%s*/ esc_attr( $item['id'] ),
			/* translators: customer name or email */
			esc_html( sprintf( __( 'Select %s', 'easy-digital-downloads' ), $name ) )
		);
	}

	/**
	 * Retrieve the customer counts
	 *
	 * @since 3.0
	 * @return void
	 */
	public function get_counts() {
		$this->counts = edd_get_customer_counts();
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.5
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return apply_filters( 'edd_report_customer_columns', array(
			'cb'            => '<input type="checkbox" />',
			'name'          => __( 'Name',   'easy-digital-downloads' ),
			'email'         => __( 'Email',  'easy-digital-downloads' ),
			'order_count'   => __( 'Orders', 'easy-digital-downloads' ),
			'spent'         => __( 'Spent',  'easy-digital-downloads' ),
			'date_created'  => __( 'Date',   'easy-digital-downloads' )
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
			'name'          => array( 'name',           true  ),
			'email'         => array( 'email',          true  ),
			'order_count'   => array( 'purchase_count', false ),
			'spent'         => array( 'purchase_value', false )
		);
	}

	/**
	 * Retrieve the bulk actions
	 *
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
	 * @since 3.0
	 */
	public function process_bulk_action() {
		if ( empty( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-customers' ) ) {
			return;
		}

		check_admin_referer( 'bulk-customers' );

		$ids = isset( $_GET['customer'] )
			? $_GET['customer']
			: false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		foreach ( $ids as $id ) {
			switch ( $this->current_action() ) {
				case 'delete' :
					edd_delete_customer( $id );
					break;
			}
		}
	}

	/**
	 * Builds and retrieves all the reports data.
	 *
	 * @since 1.5
	 * @deprecated 3.0 Use get_data()
	 *
	 * @return array All the data for customer reports.
	 */
	public function reports_data() {
		_edd_deprecated_function( __METHOD__, '3.0', 'EDD_Customer_Reports_Table::get_data()' );

		return $this->get_data();
	}

	/**
	 * Retrieves all of the items to display, given the current filters.
	 *
	 * @since 3.0
	 *
	 * @return array $data All the row data.
	 */
	public function get_data() {
		$data   = array();
		$search = $this->get_search();
		$args   = array( 'status' => $this->get_status() );

		// Account for search stripping the "+" from emails.
		if ( strpos( $search, ' ' ) ) {
			$original_query = $search;
			$search         = str_replace( ' ', '+', $search );
			if ( ! is_email( $search ) ) {
				$search = $original_query;
			}
		}

		// Email search.
		if ( is_email( $search ) ) {
			$args['email'] = $search;

			// Customer ID.
		} elseif ( is_numeric( $search ) ) {
			$args['id'] = $search;
		} elseif ( strpos( $search, 'c:' ) !== false ) {
			$args['id'] = trim( str_replace( 'c:', '', $search ) );

			// User ID.
		} elseif ( strpos( $search, 'user:' ) !== false ) {
			$args['user_id'] = trim( str_replace( 'u:', '', $search ) );
		} elseif ( strpos( $search, 'u:' ) !== false ) {
			$args['user_id'] = trim( str_replace( 'u:', '', $search ) );

			// Other...
		} else {
			$args['search']         = $search;
			$args['search_columns'] = array( 'name', 'email' );
		}

		// Parse pagination
		$this->args = $this->parse_pagination_args( $args );

		if ( is_email( $search ) ) {
			$customer_emails = new EDD\Database\Queries\Customer_Email_Address();
			$customer_ids    = $customer_emails->query(
				array(
					'fields' => 'customer_id',
					'email'  => $search,
				)
			);

			$customers = edd_get_customers(
				array(
					'id__in' => $customer_ids,
				)
			);
		} else {
			$customers  = edd_get_customers( $this->args );
		}

		// Get the data
		if ( ! empty( $customers ) ) {
			foreach ( $customers as $customer ) {
				$data[] = array(
					'id'           => $customer->id,
					'user_id'      => $customer->user_id,
					'name'         => $customer->name,
					'email'        => $customer->email,
					'order_count'  => $customer->purchase_count,
					'spent'        => $customer->purchase_value,
					'date_created' => $customer->date_created,
					'status'       => $customer->status,
				);
			}
		}

		return $data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.5
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

		// Add condition to be sure we don't divide by zero.
		// If $this->per_page is 0, then set total pages to 1.
		$total_pages = $this->per_page ? ceil( (int) $this->counts[ $status ] / (int) $this->per_page ) : 1;

		// Setup pagination
		$this->set_pagination_args( array(
			'total_pages' => $total_pages,
			'total_items' => $this->counts[ $status ],
			'per_page'    => $this->per_page,
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

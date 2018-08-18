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

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Customer_Reports_Table Class
 *
 * Renders the Customer Reports table
 *
 * @since 1.5
 */
class EDD_Customer_Reports_Table extends WP_List_Table {

	/**
	 * Number of items per page
	 *
	 * @var int
	 * @since 1.5
	 */
	public $per_page = 30;

	/**
	 * Discount counts, keyed by status
	 *
	 * @var array
	 * @since 3.0
	 */
	public $counts = array(
		'active'   => 0,
		'inactive' => 0,
		'expired'  => 0,
		'total'    => 0
	);

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
	 * @since 1.5
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'Customer',  'easy-digital-downloads' ),
			'plural'   => __( 'Customers', 'easy-digital-downloads' ),
			'ajax'     => false
		) );

		$this->process_bulk_action();
		$this->get_counts();
	}

	/**
	 * Show the search field
	 *
	 * @since 1.7
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {

		// Bail if no customers and no search
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}

		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}

		?>

		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>

		<?php
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
		switch ( $column_name ) {

			case 'id' :
				$value = $item['id'];
				break;

			case 'email' :
				$value = '<a href="mailto:' . esc_attr( $item['email'] ) . '">' . esc_html( $item['email'] ) . '</a>';
				break;

			case 'order_count' :
				$url = edd_get_admin_url( array(
					'page'     => 'edd-payment-history',
					'customer' => $item['id']
				) );
				$value = '<a href="' . esc_url( $url ) . '">' . esc_html( $item['order_count'] ) . '</a>';
				break;

			case 'spent' :
				$value = edd_currency_filter( edd_format_amount( $item[ $column_name ] ) );
				break;

			case 'date_created' :
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
	public function column_name( $item ) {
		$state    = '';
		$status   = ! empty( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$name     = ! empty( $item['name'] ) ? $item['name'] : '&mdash;';
		$view_url = admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id=' . $item['id'] );
		$actions  = array(
			'view'   => '<a href="' . $view_url . '">' . __( 'Edit', 'easy-digital-downloads' ) . '</a>',
			'logs'   => '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-tools&tab=logs&customer=' . $item['id'] ) . '">' . __( 'Logs', 'easy-digital-downloads' ) . '</a>',
			'delete' => '<a href="' . admin_url( 'edit.php?post_type=download&page=edd-customers&view=delete&id=' . $item['id'] ) . '">' . __( 'Delete', 'easy-digital-downloads' ) . '</a>',
		);

		$item_status = ! empty( $item['status'] )
			? $item['status']
			: 'active';

		// State
		if ( ( ! empty( $status ) && ( $status !== $item_status ) ) || ( $item_status !== 'active' ) ) {
			switch ( $status ) {
				case 'pending' :
					$value = __( 'Pending', 'easy-digital-downloads' );
					break;
				case 'active' :
				case '' :
				default :
					$value = __( 'Active', 'easy-digital-downloads' );
					break;
			}

			$state = ' &mdash; ' . $value;
		}

		// Get the customer's avatar
		$avatar = get_avatar( $item['email'], 32 );

		// Concatenate and return
		return $avatar . '<strong><a class="row-title" href="' . esc_url( $view_url ) . '">' . esc_html( $name ) . '</a>' . esc_html( $state ) . '</strong>' . $this->row_actions( $actions );
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
		$this->counts = edd_get_customer_counts();
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 1.4
	 *
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base          = $this->get_base_url();
		$current       = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$is_all        = empty( $current ) || ( 'all' === $current );
		$total_count   = '&nbsp;<span class="count">(' . esc_html( $this->counts['total']   ) . ')</span>';
		$active_count  = '&nbsp;<span class="count">(' . esc_html( $this->counts['active']  ) . ')</span>';
		$pending_count = '&nbsp;<span class="count">(' . esc_html( $this->counts['pending'] ) . ')</span>';

		return array(
			'all'     => sprintf( '<a href="%s"%s>%s</a>', esc_url( remove_query_arg( 'status', $base         ) ), $is_all                ? ' class="current"' : '', __( 'All',     'easy-digital-downloads' ) . $total_count   ),
			'active'  => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'active',  $base ) ), 'active'  === $current ? ' class="current"' : '', __( 'Active',  'easy-digital-downloads' ) . $active_count  ),
			'pending' => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'pending', $base ) ), 'pending' === $current ? ' class="current"' : '', __( 'Pending', 'easy-digital-downloads' ) . $pending_count )
		);
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
				case 'delete' :
					edd_delete_customer( $id );
					break;
			}
		}
	}

	/**
	 * Retrieve the current page number
	 *
	 * @since 1.5
	 * @return int Current page number
	 */
	public function get_paged() {
		return isset( $_GET['paged'] )
			? absint( $_GET['paged'] )
			: 1;
	}

	/**
	 * Retrieves the search query string
	 *
	 * @since 1.7
	 * @return mixed string If search is present, false otherwise
	 */
	public function get_search() {
		return ! empty( $_GET['s'] )
			? urldecode( trim( $_GET['s'] ) )
			: false;
	}

	/**
	 * Build all the reports data
	 *
	 * @since 1.5
	 *
	 * @return array $reports_data All the data for customer reports
	 */
	public function reports_data() {
		$data    = array();
		$paged   = $this->get_paged();
		$offset  = $this->per_page * ( $paged - 1 );
		$search  = $this->get_search();
		$status  = isset( $_GET['status']  ) ? sanitize_text_field( $_GET['status']  ) : ''; // WPCS: CSRF ok.
		$order   = isset( $_GET['order']   ) ? sanitize_text_field( $_GET['order']   ) : 'DESC'; // WPCS: CSRF ok.
		$orderby = isset( $_GET['orderby'] ) ? sanitize_text_field( $_GET['orderby'] ) : 'id'; // WPCS: CSRF ok.

		$args = array(
			'limit'   => $this->per_page,
			'offset'  => $offset,
			'order'   => $order,
			'orderby' => $orderby,
			'status'  => $status,
		);

		if ( is_email( $search ) ) {
			$args['email'] = $search;
		} elseif ( is_numeric( $search ) ) {
			$args['id'] = $search;
		} elseif ( strpos( $search, 'user:' ) !== false ) {
			$args['user_id'] = trim( str_replace( 'user:', '', $search ) );
		} else {
			$args['search']         = $search;
			$args['search_columns'] = array( 'name', 'email' );
		}

		$this->args = $args;
		$customers  = edd_get_customers( $args );

		if ( $customers ) {
			foreach ( $customers as $customer ) {
				$data[] = array(
					'id'           => $customer->id,
					'user_id'      => $customer->user_id,
					'name'         => $customer->name,
					'email'        => $customer->email,
					'order_count'  => $customer->purchase_count,
					'spent'        => $customer->purchase_value,
					'date_created' => $customer->date_created,
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

		$this->items = $this->reports_data();

		$status = isset( $_GET['status'] )
			? sanitize_key( $_GET['status'] )
			: 'total';

		// Setup pagination
		$this->set_pagination_args( array(
			'total_pages' => ceil( $this->counts[ $status ] / $this->per_page ),
			'total_items' => $this->counts[ $status ],
			'per_page'    => $this->per_page
		) );
	}
}

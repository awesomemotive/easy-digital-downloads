<?php
/**
 * Payment History Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * EDD_Payment_History_Table Class
 *
 * Renders the Payment History table on the Payment History page
 *
 * @since 1.4
 */
class EDD_Payment_History_Table extends WP_List_Table {
	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.4
	 */
	public $per_page = 30;

	/**
	 * URL of this page
	 *
	 * @var string
	 * @since 1.4.1
	 */
	public $base_url;

	/**
	 * Total number of payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $total_count;

	/**
	 * Total number of complete payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $complete_count;

	/**
	 * Total number of pending payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $pending_count;

	/**
	 * Total number of refunded payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $refunded_count;

	/**
	 * Total number of failed payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $failed_count;

	/**
	 * Total number of revoked payments
	 *
	 * @var int
	 * @since 1.4
	 */
	public $revoked_count;

	/**
	 * Total number of abandoned payments
	 *
	 * @var int
	 * @since 1.6
	 */
	public $abandoned_count;

	/**
	 * Get things started
	 *
	 * @access public
	 * @since 1.4
	 * @uses EDD_Payment_History_Table::get_payment_counts()
	 * @see WP_List_Table::__construct()
	 * @return void
	 */
	public function __construct() {
		global $status, $page;

		// Set parent defaults
		parent::__construct( array(
			'singular'  => edd_get_label_singular(),    // Singular name of the listed records
			'plural'    => edd_get_label_plural(),    	// Plural name of the listed records
			'ajax'      => false             			// Does this table support ajax?
		) );

		$this->get_payment_counts();

		$this->base_url = admin_url( 'edit.php?post_type=download&page=edd-payment-history' );
	}

	/**
	 * Show the search field
	 *
	 * @since 1.4
	 * @access public
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return void
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
?>
		<p class="search-box">
			<?php do_action( 'edd_payment_history_search' ); ?>
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array('ID' => 'search-submit') ); ?>
		</p>
<?php
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 1.4
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base = $this->base_url;

		$current        = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$complete_count = '&nbsp;<span class="count">(' . $this->complete_count . ')</span>';
		$pending_count  = '&nbsp;<span class="count">(' . $this->pending_count  . ')</span>';
		$refunded_count = '&nbsp;<span class="count">(' . $this->refunded_count . ')</span>';
		$failed_count   = '&nbsp;<span class="count">(' . $this->failed_count   . ')</span>';
		$abandoned_count= '&nbsp;<span class="count">(' . $this->abandoned_count . ')</span>';
		$revoked_count  = '&nbsp;<span class="count">(' . $this->revoked_count   . ')</span>';

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'edd') . $total_count ),
			'publish'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'publish', $base ), $current === 'publish' ? ' class="current"' : '', __('Completed', 'edd') . $complete_count ),
			'pending'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'pending', $base ), $current === 'pending' ? ' class="current"' : '', __('Pending', 'edd') . $pending_count ),
			'refunded'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'refunded', $base ), $current === 'refunded' ? ' class="current"' : '', __('Refunded', 'edd') . $refunded_count ),
			'revoked'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'revoked', $base ), $current === 'revoked' ? ' class="current"' : '', __('Revoked', 'edd') . $revoked_count ),
			'failed'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'failed', $base ), $current === 'failed' ? ' class="current"' : '', __('Failed', 'edd') . $failed_count ),
			'abandoned'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'abandoned', $base ), $current === 'abandoned' ? ' class="current"' : '', __('Abadoned', 'edd') . $abandoned_count )
		);

		return apply_filters( 'edd_payments_table_views', $views );
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'cb'        => '<input type="checkbox" />', //Render a checkbox instead of text
			'ID'     	=> __( 'ID', 'edd' ),
			'email'  	=> __( 'Email', 'edd' ),
			'details'  	=> __( 'Details', 'edd' ),
			'amount'  	=> __( 'Amount', 'edd' ),
			'date'  	=> __( 'Date', 'edd' ),
			'user'  	=> __( 'User', 'edd' ),
			'status'  	=> __( 'Status', 'edd' )
		);

		return apply_filters( 'edd_payments_table_columns', $columns );
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.4
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		$columns = array(
			'ID' 		=> array( 'ID', true ),
			'amount' 	=> array( 'amount', false ),
			'date' 		=> array( 'date', false )
		);
		return apply_filters( 'edd_payments_table_sortable_columns', $columns );
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.4
	 *
	 * @param array $item Contains all the data of the discount code
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'amount' :
				$value   = edd_currency_filter( edd_format_amount( $item[ $column_name ] ) );
				break;
			case 'date' :
				$date    = strtotime( $item[ $column_name ] );
				$value   = date_i18n( get_option( 'date_format' ), $date );
				break;
			case 'status' :
				$payment = get_post( $item['ID'] );
				$value   = edd_get_payment_status( $payment, true );
				break;
			case 'details' :
				$value = '<a href="' . add_query_arg( 'id', $item['ID'], admin_url( 'edit.php?post_type=download&page=edd-payment-history&edd-action=view-order-details' ) ) . '">' . __( 'View Order Details', 'edd' ) . '</a>';
				break;
			default:
				$value   = isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
				break;

		}
		return apply_filters( 'edd_payments_table_column', $value, $item['ID'], $column_name );
	}

	/**
	 * Render the Email Column
	 *
	 * @access public
	 * @since 1.4
	 * @param array $item Contains all the data of the payment
	 * @return string Data shown in the Email column
	 */
	public function column_email( $item ) {
		$payment     = get_post( $item['ID'] );

		$row_actions = array();

		$row_actions['edit'] = '<a href="' . add_query_arg( array( 'edd-action' => 'edit-payment', 'purchase_id' => $payment->ID ), $this->base_url ) . '">' . __( 'Edit', 'edd' ) . '</a>';

		if ( edd_is_payment_complete( $payment->ID ) )
			$row_actions['email_links'] = '<a href="' . add_query_arg( array( 'edd-action' => 'email_links', 'purchase_id' => $payment->ID ), $this->base_url ) . '">' . __( 'Resend Purchase Receipt', 'edd' ) . '</a>';

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'edd-action' => 'delete_payment', 'purchase_id' => $payment->ID ), $this->base_url ), 'edd_payment_nonce') . '">' . __( 'Delete', 'edd' ) . '</a>';

		$row_actions = apply_filters( 'edd_payment_row_actions', $row_actions, $payment );

		$value = $item['email'] . $this->row_actions( $row_actions );

		return apply_filters( 'edd_payments_table_column', $value, $item['ID'], 'email' );
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 1.4
	 * @param array $item Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	public function column_cb( $item ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'payment',
			$item['ID']
		);
	}

	/**
	 * Render the User Column
	 *
	 * @access public
	 * @since 1.4
	 * @param array $item Contains all the data of the payment
	 * @return string Data shown in the User column
	 */
	public function column_user( $item ) {
		$user_info = edd_get_payment_meta_user_info( $item['ID'] );
		$user_id = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email'];

		if ( is_numeric( $user_id ) ) {
			$user = get_userdata( $user_id ) ;
			$display_name = is_object( $user ) ? $user->display_name : __( 'guest', 'edd' );
		} else {
			$display_name = __( 'guest', 'edd' );
		}

		$value = '<a href="' . remove_query_arg( 'paged', add_query_arg( 'user', $user_id ) ) . '">' . $display_name . '</a>';
		return apply_filters( 'edd_payments_table_column', $value, $item['ID'], 'user' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since 1.4
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'delete' => __( 'Delete', 'edd' )
		);

		return apply_filters( 'edd_payments_table_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function process_bulk_action() {
		$ids = isset( $_GET['payment'] ) ? $_GET['payment'] : false;

		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		foreach ( $ids as $id ) {
			// Detect when a bulk action is being triggered...
			if ( 'delete' === $this->current_action() ) {
				edd_delete_purchase( $id );
			}
			do_action( 'edd_payments_table_do_bulk_action', $id, $this->current_action() );
		}
	}

	/**
	 * Retrieve the payment counts
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function get_payment_counts() {
		$payment_count 	= wp_count_posts( 'edd_payment' );

		$this->complete_count = $payment_count->publish;
		$this->pending_count  = $payment_count->pending;
		$this->refunded_count = $payment_count->refunded;
		$this->failed_count   = $payment_count->failed;
		$this->revoked_count  = $payment_count->revoked;
		$this->abandoned_count= $payment_count->abandoned;
		$this->total_count    = $payment_count->publish + $payment_count->pending + $payment_count->refunded + $payment_count->failed + $payment_count->abandoned + $payment_count->trash;
	}

	/**
	 * Retrieve all the data for all the payments
	 *
	 * @access public
	 * @since 1.4
	 * @return array $payment_data Array of all the data for the payments
	 */
	public function payments_data() {
		$payments_data = array();

		if ( isset( $_GET['paged'] ) ) $page = $_GET['paged']; else $page = 1;

		$per_page       = $this->per_page;
		$mode           = edd_is_test_mode()            ? 'test'                            : 'live';
		$orderby 		= isset( $_GET['orderby'] )     ? $_GET['orderby']                  : 'ID';
		$order 			= isset( $_GET['order'] )       ? $_GET['order']                    : 'DESC';
		$order_inverse 	= $order == 'DESC'              ? 'ASC'                             : 'DESC';
		$order_class 	= strtolower( $order_inverse );
		$user 			= isset( $_GET['user'] )        ? $_GET['user']                     : null;
		$status 		= isset( $_GET['status'] )      ? $_GET['status']                   : 'any';
		$meta_key		= isset( $_GET['meta_key'] )    ? $_GET['meta_key']                 : null;
		$year 			= isset( $_GET['year'] )        ? $_GET['year']                     : null;
		$month 			= isset( $_GET['m'] )           ? $_GET['m']                        : null;
		$day 			= isset( $_GET['day'] )         ? $_GET['day']                      : null;
		$search         = isset( $_GET['s'] )           ? sanitize_text_field( $_GET['s'] ) : null;

		$payments = edd_get_payments( array(
			'number'   => $per_page,
			'page'     => isset( $_GET['paged'] ) ? $_GET['paged'] : null,
			'mode'     => $mode,
			'orderby'  => $orderby,
			'order'    => $order,
			'user'     => $user,
			'status'   => $status,
			'meta_key' => $meta_key,
			'year'	   => $year,
			'month'    => $month,
			'day' 	   => $day,
			's'        => $search
		) );

		if ( $payments ) {
			foreach ( $payments as $payment ) {
				$user_info 		= edd_get_payment_meta_user_info( $payment->ID );
				$cart_details	= edd_get_payment_meta_cart_details( $payment->ID );

				$user_id = isset( $user_info['ID'] ) && $user_info['ID'] != -1 ? $user_info['ID'] : $user_info['email'];

				$payments_data[] = array(
					'ID' 		=> $payment->ID,
					'email' 	=> edd_get_payment_user_email( $payment->ID ),
					'products' 	=> $cart_details,
					'amount' 	=> edd_get_payment_amount( $payment->ID ),
					'date' 		=> $payment->post_date,
					'user' 		=> $user_id,
					'status' 	=> $payment->post_status
				);
			}
		}
		return $payments_data;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.4
	 * @uses EDD_Payment_History_Table::get_columns()
	 * @uses EDD_Payment_History_Table::get_sortable_columns()
	 * @uses EDD_Payment_History_Table::process_bulk_action()
	 * @uses EDD_Payment_History_Table::payments_data()
	 * @uses WP_List_Table::get_pagenum()
	 * @uses WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->per_page;

		$columns = $this->get_columns();

		$hidden = array(); // No hidden columns

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$data = $this->payments_data();

		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch ( $status ) {
			case 'publish':
				$total_items = $this->complete_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'refunded':
				$total_items = $this->refunded_count;
				break;
			case 'failed':
				$total_items = $this->failed_count;
				break;
			case 'revoked':
				$total_items = $this->revoked_count;
			case 'abandoned':
				$total_items = $this->abandoned_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
			default:
				// Retrieve the count of the non-default-EDD status
				$count       = wp_count_posts( 'edd_payment' );
				$total_items = $count->{$status};
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,                  	// WE have to calculate the total number of items
				'per_page'    => $per_page,                     	// WE have to determine how many items to show on a page
				'total_pages' => ceil( $total_items / $per_page )   // WE have to calculate the total number of pages
			)
		);
	}
}
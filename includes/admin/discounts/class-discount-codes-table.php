<?php
/**
 * Discount Codes Table Class
 *
 * @package     EDD
 * @subpackage  Admin/Discounts
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;

/**
 * EDD_Discount_Codes_Table Class
 *
 * Renders the Discount Codes table on the Discount Codes page
 *
 * @since 1.4
 * @since 3.0 Updated to work with the discount code migration to custom tables.
 */
class EDD_Discount_Codes_Table extends List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.4
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
	 * Get things started
	 *
	 * @since 1.4
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		parent::__construct( array(
			'singular' => 'discount',
			'plural'   => 'discounts',
			'ajax'     => false,
		) );

		$this->process_bulk_action();
		$this->get_counts();
	}

	/**
	 * Show the search field.
	 *
	 * @since 1.4
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 */
	public function search_box( $text, $input_id ) {

		// Bail if no customers and no search
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) { // WPCS: CSRF ok.
			return;
		}

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) ) { // WPCS: CSRF ok.
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}

		if ( ! empty( $_REQUEST['order'] ) ) { // WPCS: CSRF ok.
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}
		?>

		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>"/>
			<?php submit_button( esc_html( $text ), 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>

		<?php
	}

	/**
	 * Get the base URL for the discount list table
	 *
	 * @since 3.0
	 *
	 * @return string
	 */
	public function get_base_url() {

		// Remove some query arguments
		$base = remove_query_arg( edd_admin_removable_query_args(), edd_get_admin_base_url() );

		// Add base query args
		return edd_get_admin_url( array(
			'page' => 'edd-discounts'
		), $base );
	}

	/**
	 * Retrieve the view types
	 *
	 * @since 1.4
	 *
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base           = $this->get_base_url();
		$current        = isset( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
		$total_count    = '&nbsp;<span class="count">(' . esc_html( $this->counts['total']    ) . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . esc_html( $this->counts['active']   ) . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . esc_html( $this->counts['inactive'] ) . ')</span>';
		$expired_count  = '&nbsp;<span class="count">(' . esc_html( $this->counts['expired']  ) . ')</span>';

		$is_all = empty( $current ) || ( 'all' === $current );
		$views  = array(
			'all'      => sprintf( '<a href="%s"%s>%s</a>', esc_url( remove_query_arg( 'status', $base          ) ), $is_all                 ? ' class="current"' : '', __( 'All',      'easy-digital-downloads' ) . $total_count    ),
			'active'   => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'active',   $base ) ), 'active'   === $current ? ' class="current"' : '', __( 'Active',   'easy-digital-downloads' ) . $active_count   ),
			'inactive' => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'inactive', $base ) ), 'inactive' === $current ? ' class="current"' : '', __( 'Inactive', 'easy-digital-downloads' ) . $inactive_count ),
			'expired'  => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'expired',  $base ) ), 'expired'  === $current ? ' class="current"' : '', __( 'Expired',  'easy-digital-downloads' ) . $expired_count  )
		);

		return $views;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @since 1.4
	 *
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		return array(
			'cb'         => '<input type="checkbox" />',
			'name'       => __( 'Name',       'easy-digital-downloads' ),
			'code'       => __( 'Code',       'easy-digital-downloads' ),
			'amount'     => __( 'Amount',     'easy-digital-downloads' ),
			'use_count'  => __( 'Uses',       'easy-digital-downloads' ),
			'start_date' => __( 'Start Date', 'easy-digital-downloads' ),
			'end_date'   => __( 'End Date',   'easy-digital-downloads' )
		);
	}

	/**
	 * Retrieve the sortable columns
	 *
	 * @since 1.4
	 *
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'name'       => array( 'name',       false ),
			'code'       => array( 'code',       false ),
			'use_count'  => array( 'use_count',  false ),
			'start_date' => array( 'start_date', false ),
			'end_date'   => array( 'end_date',   false )
		);
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
	 * @since 1.4
	 *
	 * @param EDD_Discount $discount Discount object.
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	public function column_default( $discount, $column_name ) {
		return property_exists( $discount, $column_name ) ? $discount->$column_name : '';
	}

	/**
	 * This function renders the amount column.
	 *
	 * @since 3.0
	 *
	 * @param EDD_Discount $discount Data for the discount code.
	 * @return string Formatted amount.
	 */
	public function column_amount( $discount ) {
		return edd_format_discount_rate( $discount->type, $discount->amount );
	}

	/**
	 * This function renders the start column.
	 *
	 * @since 3.0
	 *
	 * @param EDD_Discount $discount Discount object.
	 * @return string Start  date
	 */
	public function column_start_date( $discount ) {
		$start_date = $discount->start_date;

		if ( $start_date ) {
			$display = edd_date_i18n( $start_date, 'M. d, Y' ) . '<br>' . edd_date_i18n( $start_date, 'H:i' );
		} else {
			$display = '&mdash;';
		}

		return $display;
	}

	/**
	 * Render the Expiration column.
	 *
	 * @since 3.0
	 *
	 * @param EDD_Discount $discount Discount object.
	 * @return string Expiration date.
	 */
	public function column_end_date( $discount ) {
		$expiration = $discount->end_date;

		if ( $expiration ) {
			$display = edd_date_i18n( $expiration, 'M. d, Y' ) . '<br>' . edd_date_i18n( $expiration, 'H:i' );
		} else {
			$display = '&mdash;';
		}

		return $display;
	}

	/**
	 * Render the Name column.
	 *
	 * @since 1.4
	 *
	 * @param EDD_Discount $discount Discount object.
	 * @return string Data shown in the Name column
	 */
	public function column_name( $discount ) {
		$base        = $this->get_base_url();
		$state       = '';
		$row_actions = array();
		$status      = ! empty( $_GET['status'] ) // WPCS: CSRF ok.
			? sanitize_key( $_GET['status'] )
			: '';

		// Bail if current user cannot manage discounts
		if ( ! current_user_can( 'manage_shop_discounts' ) ) {
			return;
		}

		// State
		if ( ( ! empty( $status ) && ( $status !== $discount->status ) ) || ( 'active' !== $discount->status ) ) {
			$state = ' &mdash; ' . edd_get_discount_status_label( $discount->id );
		}

		// Edit
		$row_actions['edit'] = '<a href="' . add_query_arg( array(
			'edd-action' => 'edit_discount',
			'discount'   => $discount->id,
		), $base ) . '">' . __( 'Edit', 'easy-digital-downloads' ) . '</a>';

		// Active, so add "deactivate" action
		if ( 'active' === strtolower( $discount->status ) ) {
			$row_actions['deactivate'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
				'edd-action' => 'deactivate_discount',
				'discount'   => $discount->id,
			), $base ), 'edd_discount_nonce' ) ) . '">' . __( 'Deactivate', 'easy-digital-downloads' ) . '</a>';

			// Inactive, so add "activate" action
		} elseif ( 'inactive' === strtolower( $discount->status ) ) {
			$row_actions['activate'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
				'edd-action' => 'activate_discount',
				'discount'   => $discount->id,
			), $base ), 'edd_discount_nonce' ) ) . '">' . __( 'Activate', 'easy-digital-downloads' ) . '</a>';
		}

		// Delete
		if ( 0 === (int) $discount->use_count ) {
			$row_actions['delete'] = '<a href="' . esc_url( wp_nonce_url( add_query_arg( array(
				'edd-action' => 'delete_discount',
				'discount'   => $discount->id,
			), $base ), 'edd_discount_nonce' ) ) . '">' . __( 'Delete', 'easy-digital-downloads' ) . '</a>';
		}

		// Filter all discount row actions
		$row_actions = apply_filters( 'edd_discount_row_actions', $row_actions, $discount );

		// Wrap discount title in strong anchor
		$discount_title = '<strong><a class="row-title" href="' . add_query_arg( array(
			'edd-action' => 'edit_discount',
			'discount'   => $discount->id,
		), $base ) . '">' . stripslashes( $discount->name ) . '</a>' . esc_html( $state ) . '</strong>';

		// Return discount title & row actions
		return $discount_title . $this->row_actions( $row_actions );
	}

	/**
	 * Render the checkbox column
	 *
	 * @since 1.4
	 *
	 * @param EDD_Discount $discount Discount object.
	 * @return string Checkbox HTML.
	 */
	public function column_cb( $discount ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			/*$1%s*/ 'discount',
			/*$2%s*/ $discount->id
		);
	}

	/**
	 * Return discount code wrapped in a `<code>` tag.
	 *
	 * @since 3.0
	 *
	 * @param EDD_Discount $discount Discount object.
	 * @return string Discount code HTML.
	 */
	public function column_code( $discount ) {
		return '<code class="edd-discount-code">' . $discount->code . '</code>';
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @since 1.7.2
	 */
	public function no_items() {
		esc_html_e( 'No discounts found.', 'easy-digital-downloads' );
	}

	/**
	 * Retrieve the bulk actions
	 *
	 * @since 1.4
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		return array(
			'activate'   => __( 'Activate',   'easy-digital-downloads' ),
			'deactivate' => __( 'Deactivate', 'easy-digital-downloads' ),
			'delete'     => __( 'Delete',     'easy-digital-downloads' )
		);
	}

	/**
	 * Process bulk actions.
	 *
	 * @since 1.4
	 */
	public function process_bulk_action() {

		// Bail if a nonce was not supplied.
		if ( ! isset( $_REQUEST['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_REQUEST['_wpnonce'], 'bulk-discounts' ) ) {
			return;
		}

		$ids = isset( $_GET['discount'] )
			? $_GET['discount']
			: false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids = wp_parse_id_list( $_GET['discount'] );

		foreach ( $ids as $id ) {
			switch ( $this->current_action() ) {
				case 'delete':
					edd_delete_discount( $id );
					break;
				case 'activate':
					edd_update_discount_status( $id, 'active' );
					break;
				case 'deactivate':
					edd_update_discount_status( $id, 'inactive' );
					break;
			}
		}
	}

	/**
	 * Retrieve the discount code counts.
	 *
	 * @since 1.4
	 */
	public function get_counts() {
		$this->counts = edd_get_discount_counts();
	}

	/**
	 * Retrieve all the data for all the discount codes.
	 *
	 * @since 1.4
	 *
	 * @return array Discount codes.
	 */
	public function discount_codes_data() {

		// Query args
		$orderby = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby']  ) : 'id';
		$order   = isset( $_GET['order']   ) ? sanitize_key( $_GET['order']    ) : 'DESC';
		$status  = isset( $_GET['status']  ) ? sanitize_key( $_GET['status']   ) : '';
		$search  = isset( $_GET['s']       ) ? sanitize_text_field( $_GET['s'] ) : null;
		$paged   = isset( $_GET['paged']   ) ? absint( $_GET['paged']          ) : 1;

		// Get discounts
		return edd_get_discounts( array(
			'number'  => $this->per_page,
			'paged'   => $paged,
			'orderby' => $orderby,
			'order'   => $order,
			'status'  => $status,
			'search'  => $search
		) );
	}

	/**
	 * Setup the final data for the table
	 *
	 * @since 1.4
	 */
	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );
		$this->items           = $this->discount_codes_data();

		$status = isset( $_GET['status'] ) // WPCS: CSRF ok.
			? sanitize_key( $_GET['status'] )
			: 'total';

		// Setup pagination
		$this->set_pagination_args( array(
			'total_items' => $this->counts[ $status ],
			'per_page'    => $this->per_page,
			'total_pages' => ceil( $this->counts[ $status ] / $this->per_page )
		) );
	}
}
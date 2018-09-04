<?php
/**
 * Order History Table.
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

use EDD\Admin\List_Table;

/**
 * EDD_Payment_History_Table Class
 *
 * Displays a list of orders on the 'Orders' page.
 *
 * @since 1.4
 * @since 3.0 Updated to use new query methods.
 *            Updated to use new nomenclature.
 */
class EDD_Payment_History_Table extends List_Table {

	/**
	 * URL of this page.
	 *
	 * @var string
	 * @since 1.4.1
	 */
	public $base_url;

	/**
	 * Constructor.
	 *
	 * @see WP_List_Table::__construct()
	 *
	 * @since 1.4
	 */
	public function __construct() {

		// Set parent defaults
		parent::__construct(
			array(
				'singular' => __( 'Order', 'easy-digital-downloads' ),
				'plural'   => __( 'Orders', 'easy-digital-downloads' ),
				'ajax'     => false,
			)
		);

		$this->set_base_url();
		$this->filter_bar_hooks();
		$this->get_payment_counts();
	}

	/**
	 * Set the base URL.
	 *
	 * This retains the current order-type, or 'sale' by default.
	 *
	 * @since 3.0
	 */
	private function set_base_url() {

		// Use registered types
		$types = array_keys( edd_get_order_types() );
		if ( ! empty( $_GET['order_type'] ) && in_array( $_GET['order_type'], $types, true ) ) {
			$type = sanitize_key( $_GET['order_type'] );

			// Default to 'sale' if type is unrecognized
		} else {
			$type = 'sale';
		}

		// Carry the type over to the base URL
		$this->base_url = edd_get_admin_url(
			array(
				'page'       => 'edd-payment-history',
				'order_type' => $type,
			)
		);
	}

	/**
	 * Hook in filter bar actions
	 *
	 * @since 3.0
	 */
	private function filter_bar_hooks() {
		add_action( 'edd_admin_filter_bar_orders', array( $this, 'filter_bar_items' ) );
		add_action( 'edd_after_admin_filter_bar_orders', array( $this, 'filter_bar_searchbox' ) );
	}

	/**
	 * Display advanced filters.
	 *
	 * @since 1.4
	 * @since 3.0 Add a filter for modes.
	 *            Display 'Advanced Filters'
	 */
	public function advanced_filters() {
		edd_admin_filter_bar( 'orders' );
	}

	/**
	 * Output filter bar items
	 *
	 * @since 3.0
	 */
	public function filter_bar_items() {

		// Get values
		$start_date                = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date                  = isset( $_GET['end-date'] ) ? sanitize_text_field( $_GET['end-date'] ) : null;
		$gateway                   = isset( $_GET['gateway'] ) ? sanitize_key( $_GET['gateway'] ) : 'all';
		$mode                      = isset( $_GET['mode'] ) ? sanitize_key( $_GET['mode'] ) : 'all';
		$order_total_filter_type   = isset( $_GET['order-amount-filter-type'] ) ? sanitize_text_field( $_GET['order-amount-filter-type'] ) : false;
		$order_total_filter_amount = isset( $_GET['order-amount-filter-value'] ) ? sanitize_text_field( $_GET['order-amount-filter-value'] ) : '';
		$country                   = isset( $_GET['order-country-filter-value'] ) ? sanitize_text_field( $_GET['order-country-filter-value'] ) : '';
		$region                    = isset( $_GET['order-region-filter-value'] ) ? sanitize_text_field( $_GET['order-region-filter-value'] ) : '';

		$status    = $this->get_status();
		$clear_url = $this->base_url;

		// Filters
		$all_modes    = edd_get_payment_modes();
		$all_gateways = edd_get_payment_gateways();

		// Advanced filters
		$advanced_filters_applied = (bool) ! empty( $order_total_filter_amount ) || ! empty( $country ) || ! empty( $region );
		$advanced_filters_applied = apply_filters( 'edd_orders_table_advanced_filters_applied', $advanced_filters_applied );

		$maybe_show_filters = ( true === $advanced_filters_applied )
			? 'open'
			: '';

		// No modes
		if ( empty( $all_modes ) ) {
			$modes = array();

			// Add "All" and pluck labels
		} else {
			$modes = array_merge(
				array(
					'all' => __( 'All modes', 'easy-digital-downloads' ),
				),
				wp_list_pluck( $all_modes, 'admin_label' )
			);
		}

		// No gateways
		if ( empty( $all_gateways ) ) {
			$gateways = array();

			// Add "All" and pluck labels
		} else {
			$gateways = array_merge(
				array(
					'all' => __( 'All gateways', 'easy-digital-downloads' ),
				),
				wp_list_pluck( $all_gateways, 'admin_label' )
			);
		}

		/**
		 * Allow gateways that aren't registered the standard way to be displayed in the dropdown.
		 *
		 * @since 2.8.11
		 */
		$gateways = apply_filters( 'edd_payments_table_gateways', $gateways );

		// Output the items
		if ( ! empty( $modes ) ) : ?>

			<span id="edd-mode-filter">
				<?php
				echo EDD()->html->select(
					array(
						'options'          => $modes,
						'name'             => 'mode',
						'id'               => 'mode',
						'selected'         => $mode,
						'show_option_all'  => false,
						'show_option_none' => false,
					)
				);
				?>
			</span>

		<?php endif; ?>

		<span id="edd-date-filters" class="edd-from-to-wrapper">
			<?php

			echo EDD()->html->date_field(
				array(
					'id'          => 'start-date',
					'name'        => 'start-date',
					'placeholder' => _x( 'From', 'date filter', 'easy-digital-downloads' ),
					'value'       => $start_date,
				)
			);

			echo EDD()->html->date_field(
				array(
					'id'          => 'end-date',
					'name'        => 'end-date',
					'placeholder' => _x( 'To', 'date filter', 'easy-digital-downloads' ),
					'value'       => $end_date,
				)
			);

			?>
		</span>
		<?php

		if ( ! empty( $gateways ) ) :
			?>

			<span id="edd-gateway-filter">
				<?php
				echo EDD()->html->select(
					array(
						'options'          => $gateways,
						'name'             => 'gateway',
						'id'               => 'gateway',
						'selected'         => $gateway,
						'show_option_all'  => false,
						'show_option_none' => false,
					)
				);
				?>
			</span>

				<?php endif; ?>

		<span id="edd-advanced-filters" class="<?php echo esc_attr( $maybe_show_filters ); ?>">
			<input type="button" class="edd-advanced-filters-button button-secondary" value="<?php esc_html_e( 'More', 'easy-digital-downloads' ); ?>"/>

			<div class="inside">
				<fieldset>
					<legend for="order-amount-filter-type"><?php esc_html_e( 'Amount is', 'easy-digital-downloads' ); ?></legend>
					<?php
					$options = array(
						'=' => __( 'equal to', 'easy-digital-downloads' ),
						'>' => __( 'greater than', 'easy-digital-downloads' ),
						'<' => __( 'less than', 'easy-digital-downloads' ),
					);

					echo EDD()->html->select(
						array(
							'id'               => 'order-amount-filter-type',
							'name'             => 'order-amount-filter-type',
							'options'          => $options,
							'selected'         => $order_total_filter_type,
							'show_option_all'  => false,
							'show_option_none' => false,
						)
					);
					?>

					<input type="number" name="order-amount-filter-value" min="0" step="0.01" value="<?php echo esc_attr( $order_total_filter_amount ); ?>"/>
				</fieldset>

				<fieldset>
					<legend><?php esc_html_e( 'Country & Region', 'easy-digital-downloads' ); ?></legend>
					<?php
					echo EDD()->html->select(
						array(
							'name'             => 'order-country-filter-value',
							'class'            => 'edd_countries_filter',
							'options'          => edd_get_country_list(),
							'chosen'           => true,
							'selected'         => $country,
							'show_option_none' => false,
							'placeholder'      => __( 'Choose a Country', 'easy-digital-downloads' ),
							'show_option_all'  => __( 'All Countries', 'easy-digital-downloads' ),
							'data'             => array(
								'nonce' => wp_create_nonce( 'edd-country-field-nonce' ),
							),
						)
					);
					echo EDD()->html->select(
						array(
							'name'             => 'order-region-filter-value',
							'class'            => 'edd_regions_filter',
							'options'          => edd_get_shop_states( $country ),
							'chosen'           => true,
							'selected'         => $region,
							'show_option_none' => false,
							'placeholder'      => __( 'Choose a Region', 'easy-digital-downloads' ),
							'show_option_all'  => __( 'All Regions', 'easy-digital-downloads' ),
						)
					);
					?>
				</fieldset>

				<?php

				// Third party plugin support
				if ( has_action( 'edd_payment_advanced_filters_after_fields' ) ) :
					?>

					<fieldset class="edd-add-on-filters">
						<legend><?php esc_html_e( 'Extras', 'easy-digital-downloads' ); ?></legend>

						<?php do_action( 'edd_payment_advanced_filters_after_fields' ); ?>

					</fieldset>

				<?php endif; ?>
			</div>
		</span>

		<span id="edd-after-core-filters">
			<input type="submit" class="button-secondary" value="<?php esc_html_e( 'Filter', 'easy-digital-downloads' ); ?>"/>

			<?php if ( ! empty( $start_date ) || ! empty( $end_date ) || ! empty( $order_total_filter_type ) || ( 'all' !== $gateway ) ) : ?>
				<a href="<?php echo esc_url( $clear_url ); ?>" class="button-secondary">
					<?php esc_html_e( 'Clear', 'easy-digital-downloads' ); ?>
				</a>
			<?php endif; ?>
		</span>

		<?php if ( ! empty( $status ) ) : ?>
			<input type="hidden" name="status" value="<?php echo esc_attr( $status ); ?>"/>
			<?php
		endif;
	}

	/**
	 * Output the filter bar searchbox
	 *
	 * @since 3.0
	 */
	public function filter_bar_searchbox() {
		do_action( 'edd_payment_advanced_filters_row' );

		$this->search_box( esc_html__( 'Search', 'easy-digital-downloads' ), 'edd-payments' );
	}

	/**
	 * Show the search field.
	 *
	 * @since 1.4
	 *
	 * @param string $text     Label for the search box.
	 * @param string $input_id ID of the search box.
	 */
	public function search_box( $text, $input_id ) {

		// Bail if no customers and no search.
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

		<p class="search-form">
			<?php do_action( 'edd_payment_history_search' ); ?>
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" placeholder="<?php esc_html_e( 'Search orders...', 'easy-digital-downloads' ); ?>" value="<?php _admin_search_query(); ?>" />
		</p>

		<?php
	}

	/**
	 * Message to be displayed when there are no items.
	 *
	 * @since 3.0
	 */
	public function no_items() {
		esc_html_e( 'No orders found.', 'easy-digital-downloads' );
	}

	/**
	 * Retrieve the table columns.
	 *
	 * @since 1.4
	 *
	 * @return array $columns Array of all the list table columns.
	 */
	public function get_columns() {
		return apply_filters(
			'edd_payments_table_columns',
			array(
				'cb'       => '<input type="checkbox" />', // Render a checkbox instead of text
				'number'   => __( 'Number', 'easy-digital-downloads' ),
				'customer' => __( 'Customer', 'easy-digital-downloads' ),
				'gateway'  => __( 'Gateway', 'easy-digital-downloads' ),
				'amount'   => __( 'Amount', 'easy-digital-downloads' ),
				'date'     => __( 'Completed', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Retrieve the sortable columns.
	 *
	 * @since 1.4
	 *
	 * @return array Array of all the sortable columns.
	 */
	public function get_sortable_columns() {
		return apply_filters(
			'edd_payments_table_sortable_columns',
			array(
				'number'   => array( 'id', true ),
				'customer' => array( 'customer_id', false ),
				'gateway'  => array( 'gateway', false ),
				'amount'   => array( 'total', false ),
				'date'     => array( 'date_created', false ),
			)
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
		return 'number';
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @since 1.4
	 * @since 3.0 Updated to use the new EDD\Orders\Order class.
	 *
	 * @param EDD\Orders\Order $order       Order object.
	 * @param string           $column_name The name of the column.
	 *
	 * @return string Column name.
	 */
	public function column_default( $order, $column_name ) {
		switch ( $column_name ) {
			case 'amount':
				$value = edd_currency_filter( edd_format_amount( $order->total ), $order->currency );
				break;
			case 'date':
				$value = '<time datetime="' . esc_attr( EDD()->utils->date( $order->date_created, null, true )->toDateTimeString() ) . '">' . edd_date_i18n( EDD()->utils->date( $order->date_created, null, true )->toDateTimeString(), 'M. d, Y' ) . '<br>' . edd_date_i18n( EDD()->utils->date( $order->date_created, null, true )->toDateTimeString(), 'H:i' ) . '</time>';
				break;
			case 'gateway':
				$value = edd_get_gateway_admin_label( $order->gateway );
				break;
			default:
				$value = method_exists( $order, 'get_' . $column_name )
					? call_user_func( array( $order, 'get_' . $column_name ) )
					: '';
				break;
		}

		return apply_filters( 'edd_payments_table_column', $value, $order->id, $column_name );
	}

	/**
	 * Render the Email column.
	 *
	 * @since 1.4
	 * @since 3.0 Updated to use the new EDD\Orders\Order class.
	 *
	 * @param EDD\Orders\Order $order Order object.
	 * @return string Data shown in the Email column
	 */
	public function column_email( $order ) {

		// Always include the "View" link
		$row_actions = array();

		// Add search term string back to base URL
		$search_terms = isset( $_GET['s'] )
			? trim( $_GET['s'] )
			: '';

		if ( ! empty( $search_terms ) ) {
			$this->base_url = add_query_arg( 's', $search_terms, $this->base_url );
		}

		$email = $order->email;

		// Resend
		if ( 'publish' === $order->status && ! empty( $email ) ) {
			$row_actions['email_links'] = '<a href="' . add_query_arg(
				array(
					'edd-action'  => 'email_links',
					'purchase_id' => $order->id,
				),
				$this->base_url
			) . '">' . __( 'Resend Receipt', 'easy-digital-downloads' ) . '</a>';
		}

		// This exists for backwards compatibility purposes.
		$payment     = edd_get_payment( $order->id );
		$row_actions = apply_filters( 'edd_payment_row_actions', $row_actions, $payment );

		if ( empty( $email ) ) {
			$email = __( '(unknown)', 'easy-digital-downloads' );
		}

		// Concatenate the results
		$value = $email . $this->row_actions( $row_actions );

		// Filter & return
		return apply_filters( 'edd_payments_table_column', $value, $order->id, 'email' );
	}

	/**
	 * Render the checkbox column.
	 *
	 * @since 1.4
	 * @since 3.0 Updated to use the new EDD\Orders\Order class.
	 *
	 * @param EDD\Orders\Order $order Order object.
	 * @return string Displays a checkbox.
	 */
	public function column_cb( $order ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			'order',
			$order->id
		);
	}

	/**
	 * Render the ID column.
	 *
	 * @since 2.0
	 * @since 3.0 Updated to use the new EDD\Orders\Order class.
	 *
	 * @param EDD\Orders\Order $order Order object.
	 * @return string Displays a checkbox.
	 */
	public function column_number( $order ) {
		$state  = '';
		$status = $this->get_status();

		// State
		if ( ( ! empty( $status ) && ( $order->status !== $status ) ) || ( empty( $status ) && ( 'publish' !== $order->status ) ) ) {
			$state = ' &mdash; ' . edd_get_payment_status_label( $order->status );
		}

		// View URL
		$view_url = edd_get_admin_url(
			array(
				'page' => 'edd-payment-history',
				'view' => 'view-order-details',
				'id'   => $order->id,
			)
		);

		// Default row actions
		$row_actions = array(
			'view' => '<a href="' . esc_url( $view_url ) . '">' . esc_html__( 'Edit', 'easy-digital-downloads' ) . '</a>',
		);

		// Refund
		if ( 'publish' === $order->status ) {
			$refund_url            = add_query_arg( array(), admin_url( 'edit.php' ) );
			$row_actions['refund'] = '<a href="' . esc_url( $refund_url ) . '">' . esc_html__( 'Refund', 'easy-digital-downloads' ) . '</a>';
		}

		// Keep Delete at the end
		$delete_url            = wp_nonce_url(
			add_query_arg(
				array(
					'edd-action'  => 'delete_payment',
					'purchase_id' => $order->id,
				),
				$this->base_url
			),
			'edd_payment_nonce'
		);
		$row_actions['delete'] = '<a href="' . esc_url( $delete_url ) . '">' . esc_html__( 'Delete', 'easy-digital-downloads' ) . '</a>';

		// Row actions
		$actions = $this->row_actions( $row_actions );

		// Primary link
		$link = '<strong><a class="row-title" href="' . esc_url( $view_url ) . '">' . esc_html( $order->get_number() ) . '</a>' . esc_html( $state ) . '</strong>';

		// Concatenate & return the results
		return $link . $actions;
	}

	/**
	 * Render the Customer column.
	 *
	 * @since 2.4.3
	 * @since 3.0 Updated to use the new EDD\Orders\Order class.
	 *
	 * @param EDD\Orders\Order $order Order object.
	 * @return string Data shown in the Customer column.
	 */
	public function column_customer( $order ) {
		$customer_id = $order->customer_id;
		$customer    = edd_get_customer( $customer_id );

		// Actions if exists
		if ( ! empty( $customer ) ) {

			// Use customer name, if exists
			$name = ! empty( $customer->name )
				? $customer->name
				: __( 'No Name', 'easy-digital-downloads' );

			// Link to View Customer
			$url = edd_get_admin_url(
				array(
					'page' => 'edd-customers',
					'view' => 'overview',
					'id'   => $customer_id,
				)
			);

			$name = '<a href="' . esc_url( $url ) . '">' . $name . '</a>';
		} else {
			$name = '&mdash;';
		}

		return $name;
	}

	/**
	 * Retrieve the bulk actions.
	 *
	 * @since 1.4
	 *
	 * @return array $actions Bulk actions.
	 */
	public function get_bulk_actions() {
		return apply_filters(
			'edd_payments_table_bulk_actions',
			array(
				'set-status-publish'     => __( 'Mark Completed', 'easy-digital-downloads' ),
				'set-status-pending'     => __( 'Mark Pending', 'easy-digital-downloads' ),
				'set-status-processing'  => __( 'Mark Processing', 'easy-digital-downloads' ),
				'set-status-refunded'    => __( 'Mark Refunded', 'easy-digital-downloads' ),
				'set-status-revoked'     => __( 'Mark Revoked', 'easy-digital-downloads' ),
				'set-status-failed'      => __( 'Mark Failed', 'easy-digital-downloads' ),
				'set-status-abandoned'   => __( 'Mark Abandoned', 'easy-digital-downloads' ),
				'set-status-preapproval' => __( 'Mark Preapproved', 'easy-digital-downloads' ),
				'set-status-cancelled'   => __( 'Mark Cancelled', 'easy-digital-downloads' ),
				'resend-receipt'         => __( 'Resend  Receipts', 'easy-digital-downloads' ),
				'delete'                 => __( 'Delete', 'easy-digital-downloads' ),
			)
		);
	}

	/**
	 * Process the bulk actions.
	 *
	 * @since 1.4
	 * @since 3.0 Updated to display _doing_it_wrong().
	 *
	 * @see edd_orders_list_table_process_bulk_actions()
	 */
	public function process_bulk_action() {
		_doing_it_wrong( __FUNCTION__, 'Orders list table bulk actions are now handled by edd_orders_list_table_process_bulk_actions(). Please do not call this method directly.', 'EDD 3.0' );
	}

	/**
	 * Retrieve the payment counts.
	 *
	 * @since 1.4
	 */
	public function get_payment_counts() {

		// Get the type to get counts for
		$type = ! empty( $_GET['order_type'] )
			? sanitize_key( $_GET['order_type'] )
			: 'sale';

		// Get order counts by type
		$this->counts = edd_get_order_counts(
			array(
				'type' => $type,
			)
		);
	}

	/**
	 * Retrieve all the data for all the orders.
	 *
	 * @since 1.4
	 *
	 * @return array $payment_data Array of all the data for the orders.
	 */
	public function payments_data() {
		$args = array();

		$per_page   = $this->per_page;
		$status     = $this->get_status();
		$paged      = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : null;
		$user       = isset( $_GET['user'] ) ? absint( $_GET['user'] ) : null;
		$customer   = isset( $_GET['customer'] ) ? absint( $_GET['customer'] ) : null;
		$orderby    = isset( $_GET['orderby'] ) ? sanitize_key( $_GET['orderby'] ) : 'id';
		$order      = isset( $_GET['order'] ) ? sanitize_key( $_GET['order'] ) : 'DESC';
		$search     = isset( $_GET['s'] ) ? sanitize_text_field( $_GET['s'] ) : null;
		$start_date = isset( $_GET['start-date'] ) ? sanitize_text_field( $_GET['start-date'] ) : null;
		$end_date   = isset( $_GET['end-date'] ) ? sanitize_text_field( $_GET['end-date'] ) : $start_date;
		$gateway    = isset( $_GET['gateway'] ) ? sanitize_text_field( $_GET['gateway'] ) : null;
		$mode       = isset( $_GET['mode'] ) ? sanitize_text_field( $_GET['mode'] ) : null;
		$type       = isset( $_GET['order_type'] ) ? sanitize_text_field( $_GET['order_type'] ) : 'sale';

		/**
		 * Introduced as part of #6063. Allow a gateway to specified based on the context.
		 *
		 * @see   https://github.com/easydigitaldownloads/easy-digital-downloads/issues/6063
		 * @since 2.8.11
		 *
		 * @param string $gateway
		 */
		$gateway = apply_filters( 'edd_payments_table_search_gateway', $gateway );

		if ( $gateway === 'all' ) {
			$gateway = null;
		}

		if ( $mode === 'all' ) {
			$mode = null;
		}

		$args = array(
			'number'   => $per_page,
			'paged'    => $paged,
			'orderby'  => $orderby,
			'order'    => $order,
			'user'     => $user,
			'customer' => $customer,
			'status'   => $status,
			'gateway'  => $gateway,
			'mode'     => $mode,
			'type'     => $type,
			's'        => $search,
		);

		// Search
		if ( is_string( $search ) && ( false !== strpos( $search, 'txn:' ) ) ) {
			$args['search_in_notes'] = true;
			$args['search']          = trim( str_replace( 'txn:', '', $args['s'] ) );
		}

		// Date query
		if ( ! empty( $start_date ) || ! empty( $end_date ) ) {

			// start AND end
			$args['date_query'] = array(
				'relation' => 'AND',
			);

			// Start (of day)
			if ( ! empty( $start_date ) ) {
				$args['date_query'][] = array(
					'column' => 'date_created',
					'after'  => date( 'Y-m-d 00:00:00', strtotime( $start_date ) ),
				);
			}

			// End (of day)
			if ( ! empty( $end_date ) ) {
				$args['date_query'][] = array(
					'column' => 'date_created',
					'before' => date( 'Y-m-d 23:59:59', strtotime( $end_date ) ),
				);
			}
		}

		// Maybe filter by order amount.
		if ( isset( $_GET['order-amount-filter-type'] ) && isset( $_GET['order-amount-filter-value'] ) ) {
			if ( ! empty( $_GET['order-amount-filter-value'] ) && 0 !== strlen( $_GET['order-amount-filter-value'] ) ) {
				$filter_type   = sanitize_text_field( $_GET['order-amount-filter-type'] );
				$filter_amount = floatval( sanitize_text_field( $_GET['order-amount-filter-value'] ) );

				$args['compare'] = array(
					array(
						'key'     => 'total',
						'value'   => $filter_amount,
						'compare' => $filter_type,
					),
				);
			}
		}

		// Maybe filter by country.
		if ( isset( $_GET['order-country-filter-value'] ) ) {
			$country = ! empty( $_GET['order-country-filter-value'] )
				? sanitize_text_field( $_GET['order-country-filter-value'] )
				: '';

			$args['country'] = $country;
		}

		// Maybe filter by region.
		if ( isset( $_GET['order-region-filter-value'] ) ) {
			$region = ! empty( $_GET['order-region-filter-value'] )
				? sanitize_text_field( $_GET['order-region-filter-value'] )
				: '';

			$args['region'] = $region;
		}

		// No empties
		$r = wp_parse_args( array_filter( $args ) );

		// Force EDD\Orders\Order objects to be returned
		$r['output'] = 'orders';
		$p           = new EDD_Payments_Query( $r );

		// Setup items
		$items = $p->get_payments();

		// Get customer IDs and count from payments
		$customer_ids = array_unique( wp_list_pluck( $items, 'customer_id' ) );
		$cust_count   = count( $customer_ids );

		// Maybe prime customer objects (if more than number of queries)
		if ( $cust_count > 1 ) {
			edd_get_customers(
				array(
					'id__in'        => $customer_ids,
					'no_found_rows' => true,
					'number'        => $cust_count,
				)
			);
		}

		// Return items
		return $items;
	}

	/**
	 * Setup the final data for the table.
	 *
	 * @since 1.4
	 */
	public function prepare_items() {
		wp_reset_vars( array( 'action', 'order', 'orderby', 'order', 's' ) );

		$hidden      = array(); // No hidden columns
		$columns     = $this->get_columns();
		$sortable    = $this->get_sortable_columns();
		$status      = $this->get_status( 'total' );
		$this->items = $this->payments_data();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->set_pagination_args(
			array(
				'total_items' => $this->counts[ $status ],
				'per_page'    => $this->per_page,
				'total_pages' => ceil( $this->counts[ $status ] / $this->per_page ),
			)
		);
	}
}

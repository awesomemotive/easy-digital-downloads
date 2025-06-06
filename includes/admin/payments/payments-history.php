<?php
/**
 * Functions to render Orders page.
 *
 * @package     EDD
 * @subpackage  Admin/Payments
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/** Navigation ****************************************************************/

/**
 * Output the primary orders page navigation
 *
 * @since 3.0
 * @param string $active_tab
 */
function edd_orders_page_primary_nav( $active_tab = '', $show_search = false ) {
	$tabs = array();
	foreach ( edd_get_order_pages() as $type => $label ) {
		$tabs[ $type ] = array(
			'name' => $label,
			'url'  => edd_get_admin_url(
				array(
					'page'       => 'edd-payment-history',
					'order_type' => $type,
				),
			),
		);
	}

	$navigation = new EDD\Admin\Menu\SecondaryNavigation(
		$tabs,
		'edd-payment-history',
		array(
			'active_tab'  => $active_tab,
			'show_search' => $show_search,
		)
	);
	$navigation->render();
}

/**
 * Retrieve the order pages.
 *
 * Used only by the primary tab navigation for orders.
 *
 * @since 3.0
 *
 * @return array
 */
function edd_get_order_pages() {

	// Get types and setup return value
	$types  = edd_get_order_types();
	$retval = array();

	// Loop through and get type IDs and labels
	foreach ( $types as $type_id => $type ) {

		// Skip if hidden
		if ( empty( $type['show_ui'] ) ) {
			continue;
		}

		// Add to return array
		$retval[ $type_id ] = ! empty( $type['labels']['plural'] )
			? $type['labels']['plural']
			: ucwords( $type_id );
	}

	// Filter & return
	return (array) apply_filters( 'edd_get_order_pages', $retval );
}

/**
 * Get the payment view
 *
 * @since 3.0
 *
 * @return string
 */
function edd_get_payment_view() {
	return ! empty( $_GET['view'] )     // WPCS: CSRF ok.
		? sanitize_key( $_GET['view'] ) // WPCS: CSRF ok.
		: 'list';
}

/**
 * Render one of the Order pages.
 *
 * @since 1.0
 * @since 3.0 Nomenclature updated for consistency.
 *            Add a link to manually add orders.
 *            Changed to switch statement.
 */
function edd_payment_history_page() {

	// What are we viewing?
	switch ( edd_get_payment_view() ) {

		// View Order
		case 'view-order-details':
			require_once EDD_PLUGIN_DIR . 'includes/admin/payments/view-order-details.php';
			break;

		// Add Order
		case 'add-order':
			require_once EDD_PLUGIN_DIR . 'includes/admin/payments/add-order.php';
			edd_add_order_page_content();
			break;

		// View Refund
		case 'view-refund-details':
			require_once EDD_PLUGIN_DIR . 'includes/admin/payments/view-refund.php';
			edd_view_refund_page_content();
			break;

		// List Table
		case 'list':
		default:
			edd_order_list_table_content();
			break;
	}
}

/**
 * Output the list table used to list out all orders.
 *
 * @since 3.0
 */
function edd_order_list_table_content() {
	require_once EDD_PLUGIN_DIR . 'includes/admin/payments/class-payments-table.php';
	$orders_table = new EDD_Payment_History_Table();
	$orders_table->prepare_items();

	$active_tab = sanitize_key( $orders_table->get_request_var( 'order_type', 'sale' ) );
	$admin_url  = edd_get_admin_url( array( 'page' => 'edd-payment-history' ) );

	edd_orders_page_primary_nav( $active_tab, true );
	?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Orders', 'easy-digital-downloads' ); ?></h1>
		<?php
		if ( 'sale' === $active_tab ) {
			$add_new_url = add_query_arg( array( 'view' => 'add-order' ), $admin_url );
			printf(
				'<a href="%s" class="page-title-action">%s</a>',
				esc_url( $add_new_url ),
				esc_html__( 'Add Order', 'easy-digital-downloads' )
			);
		}
		?>
		<hr class="wp-header-end">

		<?php do_action( 'edd_payments_page_top' ); ?>

		<form id="edd-payments-filter" method="get" action="<?php echo esc_url( $admin_url ); ?>">
			<input type="hidden" name="post_type" value="download" />
			<input type="hidden" name="page" value="edd-payment-history" />
			<input type="hidden" name="order_type" value="<?php echo esc_attr( $active_tab ); ?>" />
			<?php
			$orders_table->views();
			$orders_table->advanced_filters();
			$orders_table->display();
			?>
		</form>

		<?php do_action( 'edd_payments_page_bottom' ); ?>
	</div>

	<?php
}

/**
 * Orders admin titles.
 *
 * @since 1.6
 * @since 3.0 Updated to use new nomenclature.
 *
 * @param string $admin_title
 * @param string $title
 *
 * @return string Updated admin title.
 */
function edd_view_order_details_title( $admin_title, $title ) {

	// Bail if we aren't on the Orders page.
	if ( 'download_page_edd-payment-history' !== get_current_screen()->base ) {
		return $admin_title;
	}

	// Get the view
	$view = edd_get_payment_view();

	// Which view?
	switch ( $view ) {

		// Edit/View
		case 'view-order-details':
		case 'edit-payment':
			$title = __( 'Edit Order', 'easy-digital-downloads' ) . ' &mdash; ' . $admin_title;
			break;

		// Add
		case 'add-order':
			$title = __( 'Add Order', 'easy-digital-downloads' ) . ' &mdash; ' . $admin_title;
			break;

		// List
		case 'list':
		default:
			$title = $admin_title;
			break;
	}

	return $title;
}
add_filter( 'admin_title', 'edd_view_order_details_title', 10, 2 );

<?php
/**
 * Dashboard Widgets
 *
 * @package     EDD
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Registers the dashboard widgets
 *
 * @author Sunny Ratilal
 * @since 1.2.2
 * @return void
 */
function edd_register_dashboard_widgets() {
	if ( current_user_can( apply_filters( 'edd_dashboard_stats_cap', 'view_shop_reports' ) ) ) {
		wp_add_dashboard_widget( 'edd_dashboard_sales', __('Easy Digital Downloads Sales Summary','easy-digital-downloads' ), 'edd_dashboard_sales_widget' );
	}
}
add_action('wp_dashboard_setup', 'edd_register_dashboard_widgets', 10 );

/**
 * Sales Summary Dashboard Widget
 *
 * Builds and renders the Sales Summary dashboard widget. This widget displays
 * the current month's sales and earnings, total sales and earnings best selling
 * downloads as well as recent purchases made on your EDD Store.
 *
 * @author Sunny Ratilal
 * @since 1.2.2
 * @return void
 */
function edd_dashboard_sales_widget() {
	if ( ! edd_has_upgrade_completed( 'migrate_orders' ) ) {
		if ( get_option( 'edd_v30_cli_migration_running' ) ) {
			printf(
				'<p>%1$s %2$s</p>',
				esc_html__( 'Easy Digital Downloads is performing a database migration via WP-CLI.', 'easy-digital-downloads' ),
				esc_html__( 'This summary will be available when that has completed.', 'easy-digital-downloads' )
			);
			return;
		}
		global $wpdb;
		$orders = $wpdb->get_var( "SELECT ID FROM {$wpdb->posts} WHERE post_type = 'edd_payment' LIMIT 1" );
		if ( ! empty( $orders ) ) {
			$url = add_query_arg(
				array(
					'page'        => 'edd-upgrades',
					'edd-upgrade' => 'v30_migration',
				),
				admin_url( 'index.php' )
			);
			printf(
				'<p>%1$s %2$s<a href="%3$s">%4$s</a></p>',
				esc_html__( 'Easy Digital Downloads needs to upgrade the database.', 'easy-digital-downloads' ),
				esc_html__( 'This summary will be available when that has completed.', 'easy-digital-downloads' ),
				esc_url( $url ),
				esc_html__( 'Begin the upgrade.', 'easy-digital-downloads' )
			);
			return;
		}
	}
	wp_enqueue_script( 'edd-admin-dashboard' );

	/**
	 * Action hook to add content to the dashboard widget.
	 * This content will not be replaced by the AJAX function:
	 * only the "edd-loading" content will.
	 *
	 * @since 2.11.4
	 */
	do_action( 'edd_dashboard_sales_widget' );
	?>
	<p class="edd-loading"><img src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/loading.gif' ); ?>"></p>
	<?php
}

/**
 * Gets the sales earnings/count data for the dashboard widget.
 *
 * @since 3.0.0
 * @return array
 */
function edd_get_dashboard_sales_widget_data() {
	$data   = array();
	$ranges = array( 'this_month', 'last_month', 'today', 'total' );
	foreach ( $ranges as $range ) {
		$args = array(
			'range'         => $range,
			'output'        => 'formatted',
			'revenue_type'  => 'net',
		);
		if ( 'total' === $range ) {
			unset( $args['range'] );
		}
		// Remove filters so that deprecation notices are not unnecessarily logged outside of reports.
		remove_all_filters( 'edd_report_views' );
		$stats          = new EDD\Stats( $args );
		$data[ $range ] = array(
			'earnings' => $stats->get_order_earnings(),
			'count'    => $stats->get_order_count(),
		);
	}

	return $data;
}

/**
 * Loads the dashboard sales widget via ajax
 *
 * @since 2.1
 * @return void
 */
function edd_load_dashboard_sales_widget( ) {

	if ( ! current_user_can( apply_filters( 'edd_dashboard_stats_cap', 'view_shop_reports' ) ) ) {
		die();
	}

	$stats = new EDD_Payment_Stats();
	$data  = edd_get_dashboard_sales_widget_data(); ?>
	<div class="edd_dashboard_widget">
		<div class="table table_left table_current_month">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Current Month', 'easy-digital-downloads' ); ?> &mdash; <?php esc_html_e( 'Net', 'easy-digital-downloads' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t monthly_earnings"><?php esc_html_e( 'Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="b b-earnings"><?php echo esc_html( $data['this_month']['earnings'] ); ?></td>
					</tr>
						<td class="first t monthly_sales"><?php echo esc_html( _n( 'Sale', 'Sales', $data['this_month']['count'], 'easy-digital-downloads' ) ); ?></td>
						<td class="b b-sales"><?php echo esc_html( $data['this_month']['count'] ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_today">
			<table>
				<thead>
					<tr>
						<td colspan="2">
							<?php esc_html_e( 'Today', 'easy-digital-downloads' ); ?> &mdash; <?php esc_html_e( 'Net', 'easy-digital-downloads' ); ?>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t sales"><?php esc_html_e( 'Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="last b b-earnings">
							<?php echo esc_html( $data['today']['earnings'] ); ?>
						</td>
					</tr>
					<tr class="t sales">
						<td class="t sales"><?php echo esc_html( _n( 'Sale', 'Sales', $data['today']['count'], 'easy-digital-downloads' ) ); ?></td>
						<td class="last b b-sales"><?php echo esc_html( $data['today']['count'] ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_left table_last_month">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Last Month', 'easy-digital-downloads' ); ?> &mdash; <?php esc_html_e( 'Net', 'easy-digital-downloads' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t earnings"><?php esc_html_e( 'Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="b b-last-month-earnings"><?php echo esc_html( $data['last_month']['earnings'] ); ?></td>
					</tr>
					<tr>
						<td class="first t sales"><?php echo esc_html( _n( 'Sale', 'Sales', $data['last_month']['count'], 'easy-digital-downloads' ) ); ?></td>
						<td class="b b-last-month-sales"><?php echo esc_html( $data['last_month']['count'] ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_totals">
			<table>
				<thead>
					<tr>
						<td colspan="2">
							<?php esc_html_e( 'All Time', 'easy-digital-downloads' ); ?> &mdash; <?php esc_html_e( 'Net', 'easy-digital-downloads' ); ?>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t earnings"><?php esc_html_e( 'Total Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="last b b-earnings"><?php echo esc_html( $data['total']['earnings'] ); ?></td>
					</tr>
					<tr>
						<td class="t sales"><?php echo esc_html( _n( 'Sale', 'Sales', $data['total']['count'], 'easy-digital-downloads' ) ); ?></td>
						<td class="last b b-sales"><?php echo esc_html( $data['total']['count'] ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div style="clear: both"></div>
		<?php do_action( 'edd_sales_summary_widget_after_stats', $stats ); ?>
		<?php
		$orders = edd_get_orders(
			array(
				'number' => 5,
				'status' => edd_get_net_order_statuses(),
				'type'   => 'sale',
			)
		);

		if ( $orders ) { ?>
		<div class="table recent_orders">
			<h3><?php esc_html_e( 'Recent Orders', 'easy-digital-downloads' ); ?></h3>
			<ul>
			<?php
			foreach ( $orders as $order ) {
				$link = edd_get_admin_url(
					array(
						'page' => 'edd-payment-history',
						'view' => 'view-order-details',
						'id'   => urlencode( $order->id ),
					),
					admin_url( 'edit.php' )
				);
				?>
				<li class="edd_order_label">
					<a href="<?php echo esc_url( $link ); ?>">
						<?php
						$customer      = edd_get_customer( $order->customer_id );
						$customer_name = ! empty( $customer->name ) ? $customer->name : __( 'No Name', 'easy-digital-downloads' );
						$item_count    = edd_count_order_items( array( 'order_id' => $order->id ) );
						echo wp_kses_post(
							sprintf(
								/* translators: 1. customer name; 2. number of items purchased; 3. order total */
								_n(
									'%1$s purchased %2$s item for <strong>%3$s</strong>',
									'%1$s purchased %2$s items for <strong>%3$s</strong>',
									$item_count,
									'easy-digital-downloads'
								),
								$customer_name,
								$item_count,
								edd_currency_filter( edd_format_amount( edd_get_order_total( $order->id ) ), $order->currency )
							)
						);
						?>
					</a>
					<br /><?php echo esc_html( edd_date_i18n( $order->date_created ) ); ?> &mdash; <?php echo edd_get_status_label( $order->status ); ?>
				</li>
				<?php } // End foreach ?>
		</ul>
			<?php
			$all_orders_link = edd_get_admin_url(
				array(
					'page' => 'edd-payment-history',
				)
			);
			?>
		<a href="<?php echo esc_url( $all_orders_link ); ?>" class="button-secondary"><?php esc_html_e( 'View All Orders', 'easy-digital-downloads' ); ?></a>
		</div>
		<?php } // End if ?>
		<?php do_action( 'edd_sales_summary_widget_after_orders', $orders ); ?>
		<?php
		if ( has_action( 'edd_sales_summary_widget_after_purchases' ) ) {
			_edd_deprecated_hook(
				'edd_sales_summary_widget_after_purchases',
				'3.1.0.1',
				'edd_sales_summary_widget_after_orders',
				'Note: The replacement hook uses the EDD 3.0 order objects, instead of payment objects. Developers will need to make adjustments accordingly.'
			);
			do_action( 'edd_sales_summary_widget_after_purchases', edd_get_payments( array( 'number' => 5, 'status' => 'complete' ) ) );
		}
		?>
	</div>
	<?php
	die();
}
add_action( 'wp_ajax_edd_load_dashboard_widget', 'edd_load_dashboard_sales_widget' );

/**
 * Add download count to At a glance widget
 *
 * @author Daniel J Griffiths
 * @since 2.1
 * @return void
 */
function edd_dashboard_at_a_glance_widget( $items ) {
	$num_posts = wp_count_posts( 'download' );

	if ( $num_posts && $num_posts->publish ) {
		$text = _n( '%s ' . edd_get_label_singular(), '%s ' . edd_get_label_plural(), $num_posts->publish, 'easy-digital-downloads' );

		$text = sprintf( $text, number_format_i18n( $num_posts->publish ) );

		if ( current_user_can( 'edit_products' ) ) {
			$text = sprintf( '<a class="download-count" href="edit.php?post_type=download">%1$s</a>', $text );
		} else {
			$text = sprintf( '<span class="download-count">%1$s</span>', $text );
		}

		$items[] = $text;
	}

	return $items;
}
add_filter( 'dashboard_glance_items', 'edd_dashboard_at_a_glance_widget', 1 );

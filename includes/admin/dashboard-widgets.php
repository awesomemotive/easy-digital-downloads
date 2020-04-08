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
function edd_dashboard_sales_widget( ) {
	wp_enqueue_script( 'edd-admin-dashboard' );
	echo '<p><img src=" ' . esc_attr( set_url_scheme( EDD_PLUGIN_URL . 'assets/images/loading.gif', 'relative' ) ) . '"/></p>';
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

	$stats = new EDD_Payment_Stats; ?>
	<div class="edd_dashboard_widget">
		<div class="table table_left table_current_month">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Current Month', 'easy-digital-downloads' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t monthly_earnings"><?php _e( 'Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="b b-earnings"><?php echo edd_currency_filter( edd_format_amount( $stats->get_earnings( 0, 'this_month' ) ) ); ?></td>
					</tr>
					<tr>
						<?php $monthly_sales = $stats->get_sales( 0, 'this_month', false, array( 'complete', 'revoked' ) ); ?>
						<td class="first t monthly_sales"><?php echo _n( 'Sale', 'Sales', $monthly_sales, 'easy-digital-downloads' ); ?></td>
						<td class="b b-sales"><?php echo edd_format_amount( $monthly_sales, false ); ?></td>
					</tr>
				</tbody>
			</table>
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Last Month', 'easy-digital-downloads' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t earnings"><?php echo __( 'Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="b b-last-month-earnings"><?php echo edd_currency_filter( edd_format_amount( $stats->get_earnings( 0, 'last_month' ) ) ); ?></td>
					</tr>
					<tr>
						<td class="first t sales">
							<?php $last_month_sales = $stats->get_sales( 0, 'last_month', false, array( 'complete', 'revoked' ) ); ?>
							<?php echo _n( 'Sale', 'Sales', edd_format_amount( $last_month_sales, false ), 'easy-digital-downloads' ); ?>
						</td>
						<td class="b b-last-month-sales">
							<?php echo $last_month_sales; ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_today">
			<table>
				<thead>
					<tr>
						<td colspan="2">
							<?php _e( 'Today', 'easy-digital-downloads' ); ?>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t sales"><?php _e( 'Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="last b b-earnings">
							<?php $earnings_today = $stats->get_earnings( 0, 'today', false ); ?>
							<?php echo edd_currency_filter( edd_format_amount( $earnings_today ) ); ?>
						</td>
					</tr>
					<tr>
						<td class="t sales">
							<?php _e( 'Sales', 'easy-digital-downloads' ); ?>
						</td>
						<td class="last b b-sales">
							<?php $sales_today = $stats->get_sales( 0, 'today', false, array( 'complete', 'revoked' ) ); ?>
							<?php echo edd_format_amount( $sales_today, false ); ?>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_totals">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php _e( 'Totals', 'easy-digital-downloads' ) ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t earnings"><?php _e( 'Total Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="last b b-earnings"><?php echo edd_currency_filter( edd_format_amount( edd_get_total_earnings() ) ); ?></td>
					</tr>
					<tr>
						<td class="t sales"><?php _e( 'Total Sales', 'easy-digital-downloads' ); ?></td>
						<td class="last b b-sales"><?php echo edd_format_amount( edd_get_total_sales(), false ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div style="clear: both"></div>
		<?php do_action( 'edd_sales_summary_widget_after_stats', $stats ); ?>
		<?php
		$orders = edd_get_orders( array( 'number' => 5, 'status' => 'complete', 'type' => 'sale' ) );

		if ( $orders ) { ?>
		<div class="table recent_purchases">
			<h3><?php esc_html_e( 'Recent Purchases', 'easy-digital-downloads' ); ?></h3>
			<ul>
			<?php
			foreach ( $orders as $order ) {
				$link = add_query_arg(
					array(
						'post_type' => 'download',
						'page'      => 'edd-payment-history',
						'view'      => 'view-order-details',
						'id'        => $order->ID,
					),
					admin_url( 'edit.php' )
				);
				?>
				<li class="edd_order_label">
					<a href="<?php echo esc_url( $link ); ?>">
						<?php
						$customer      = edd_get_customer( $order->customer_id );
						$customer_name = $customer->name ? $customer->name : __( 'No Name', 'easy-digital-downloads' );
						$item_counts   = edd_get_order_item_counts( array( 'order_id' => $order->ID ) );
						printf(
							wp_kses_post(
								/* translators: 1. customer name; 2. number of items purchased; 3. order total */
								_n(
									'%1$s purchased %2$s item for <strong>%3$s</strong>',
									'%1$s purchased %2$s items for <strong>%3$s</strong>',
									$item_counts['total'],
									'easy-digital-downloads'
								)
							),
							esc_html( $customer_name ),
							esc_html( $item_counts['total'] ),
							esc_html( edd_currency_filter( edd_format_amount( edd_get_order_total( $order->ID ) ) ) ),
							esc_html( $order->get_number() )
						);
						?>
					</a>
					<br /><?php echo esc_html( edd_date_i18n( $order->date_completed ) ); ?>
				</li>
				<?php } // End foreach ?>
		</ul>
			<?php
			$all_purchases_link = add_query_arg(
				array(
					'post_type' => 'download',
					'page'      => 'edd-payment-history',
				),
				admin_url( 'edit.php' )
			);
			?>
		<a href="<?php echo esc_url( $all_purchases_link ); ?>" class="button-secondary"><?php esc_html_e( 'View All Purchases', 'easy-digital-downloads' ); ?></a>
		</div>
		<?php } // End if ?>
		<?php do_action( 'edd_sales_summary_widget_after_purchases', $orders ); ?>
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

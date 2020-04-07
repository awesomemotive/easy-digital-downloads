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
 * Get the sales earnings/count data for the dashboard widget.
 *
 * @since 3.0.0
 * @return array
 */
function edd_get_dashboard_sales_widget_data() {
	$data               = array();
	$this_month_sales   = new EDD\Stats(
		array(
			'range'  => 'this_month',
			'output' => 'formatted',
		)
	);
	$data['this_month'] = array(
		'earnings' => $this_month_sales->get_order_earnings(),
		'count'    => $this_month_sales->get_order_count(),
	);
	$last_month_sales   = new EDD\Stats(
		array(
			'range'  => 'last_month',
			'output' => 'formatted',
		)
	);
	$data['last_month'] = array(
		'earnings' => $last_month_sales->get_order_earnings(),
		'count'    => $last_month_sales->get_order_count(),
	);
	$today_sales        = new EDD\Stats(
		array(
			'range'  => 'today',
			'output' => 'formatted',
		)
	);
	$data['today']      = array(
		'earnings' => $today_sales->get_order_earnings(),
		'count'    => $today_sales->get_order_count(),
	);
	$total_sales        = new EDD\Stats(
		array(
			'output' => 'formatted',
		)
	);
	$data['total']      = array(
		'earnings' => $total_sales->get_order_earnings(),
		'count'    => $total_sales->get_order_count(),
	);

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
						<td colspan="2"><?php esc_html_e( 'Current Month', 'easy-digital-downloads' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t monthly_earnings"><?php esc_html_e( 'Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="b b-earnings"><?php echo esc_attr( $data['this_month']['earnings'] ); ?></td>
					</tr>
						<td class="first t monthly_sales"><?php echo esc_html( _n( 'Sale', 'Sales', $data['this_month']['count'], 'easy-digital-downloads' ) ); ?></td>
						<td class="b b-sales"><?php echo esc_attr( $data['this_month']['count'] ); ?></td>
					</tr>
				</tbody>
			</table>
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Last Month', 'easy-digital-downloads' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="first t earnings"><?php esc_html_e( 'Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="b b-last-month-earnings"><?php echo esc_attr( $data['last_month']['earnings'] ); ?></td>
					</tr>
					<tr>
						<td class="first t sales"><?php echo esc_html( _n( 'Sale', 'Sales', $data['last_month']['count'], 'easy-digital-downloads' ) ); ?></td>
						<td class="b b-last-month-sales"><?php echo esc_attr( $data['last_month']['count'] ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_today">
			<table>
				<thead>
					<tr>
						<td colspan="2">
							<?php esc_html_e( 'Today', 'easy-digital-downloads' ); ?>
						</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t sales"><?php esc_html_e( 'Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="last b b-earnings">
							<?php echo esc_attr( $data['today']['earnings'] ); ?>
						</td>
					</tr>
					<tr class="t sales">
						<td class="t sales"><?php echo esc_html( _n( 'Sale', 'Sales', $data['today']['count'], 'easy-digital-downloads' ) ); ?></td>
						<td class="last b b-sales"><?php echo esc_attr( $data['today']['count'] ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_totals">
			<table>
				<thead>
					<tr>
						<td colspan="2"><?php esc_html_e( 'Totals', 'easy-digital-downloads' ); ?></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td class="t earnings"><?php esc_html_e( 'Total Earnings', 'easy-digital-downloads' ); ?></td>
						<td class="last b b-earnings"><?php echo esc_attr( $data['total']['earnings'] ); ?></td>
					</tr>
					<tr>
						<td class="t sales"><?php echo esc_html( _n( 'Sale', 'Sales', $data['total']['count'], 'easy-digital-downloads' ) ); ?></td>
						<td class="last b b-sales"><?php echo esc_attr( $data['total']['count'] ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div style="clear: both"></div>
		<?php do_action( 'edd_sales_summary_widget_after_stats', $stats ); ?>
		<?php
		$payments = edd_get_payments( array( 'number' => 5, 'status' => 'complete' ) );

		if ( $payments ) { ?>
		<div class="table recent_purchases">
			<table>
				<thead>
					<tr>
						<td colspan="2">
							<?php _e( 'Recent Purchases', 'easy-digital-downloads' ); ?>
							<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history' ); ?>">&nbsp;&ndash;&nbsp;<?php _e( 'View All', 'easy-digital-downloads' ); ?></a>
						</td>
					</tr>
				</thead>
				<tbody>
					<?php
					foreach ( $payments as $payment ) { ?>
						<tr>
							<td class="edd_order_label">
								<a href="<?php echo add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details' ) ); ?>">
									<?php echo get_the_title( $payment->ID ) ?>
									&mdash; <?php echo $payment->email ?>
								</a>
								<?php if ( ! empty( $payment->user_id ) && ( $payment->user_id > 0 ) ) {
									$user = get_user_by( 'id', $payment->user_id );
									if ( $user ) {
										echo "(" . $user->data->user_login . ")";
									}
								} ?>
							</td>
							<td class="edd_order_price">
								<a href="<?php echo add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details' ) ); ?>">
									<span class="edd_price_label"><?php echo edd_currency_filter( edd_format_amount( $payment->total ), edd_get_payment_currency_code( $payment->ID ) ); ?></span>
								</a>
							</td>
						</tr>
						<?php
					} // End foreach ?>
				</tbody>
			</table>
		</div>
		<?php } // End if ?>
		<?php do_action( 'edd_sales_summary_widget_after_purchases', $payments ); ?>
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

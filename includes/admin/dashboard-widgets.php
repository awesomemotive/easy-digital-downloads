<?php
/**
 * Dashboard Widgets
 *
 * @package     EDD
 * @subpackage  Admin/Dashboard
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Registers the dashboard widgets
 *
 * @author Sunny Ratilal
 * @since 1.2.2
 * @return void
 */
function edd_register_dashboard_widgets() {
	if ( current_user_can( apply_filters( 'edd_dashboard_stats_cap', 'view_shop_reports' ) ) ) {
		wp_add_dashboard_widget( 'edd_dashboard_sales', __('Easy Digital Downloads Sales Summary', 'edd'), 'edd_dashboard_sales_widget' );
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
	$stats = new EDD_Payment_Stats; ?>
	<div class="edd_dashboard_widget">
		<div class="table table_left table_monthly">
			<p class="sub"><?php _e( 'Current Month', 'edd' ) ?></p>
			<table class="table_current_month">
				<tbody>
					<tr class="first">
						<td class="first b b-earnings"><?php echo edd_currency_filter( edd_format_amount( $stats->get_earnings( 0, 'this_month' ) ) ); ?></td>
						<td class="t monthly_earnings"><?php _e( 'Earnings', 'edd' ); ?></td>
					</tr>
					<tr>
						<?php $monthly_sales = $stats->get_sales( 0, 'this_month', false, array( 'publish', 'revoked' ) ); ?>
						<td class="first b b-sales"><?php echo $monthly_sales; ?></td>
						<td class="t monthly_sales"><?php echo _n( 'Sale', 'Sales', $monthly_sales, 'edd' ); ?></td>
					</tr>
				</tbody>
			</table>
			<p class="label_heading table_left table_last_month"><?php _e( 'Last Month', 'edd' ) ?></p>
			<table>
				<tbody>
					<tr class="first">
						<td class="first b b-earnings"><?php echo edd_currency_filter( edd_format_amount( $stats->get_earnings( 0, 'last_month' ) ) ); ?></td>
						<td class="t monthly_earnings"><?php _e( 'Earnings', 'edd' ); ?></td>
					</tr>
					<tr>
						<?php $monthly_sales = $stats->get_sales( 0, 'last_month', false, array( 'publish', 'revoked' ) ); ?>
						<td class="first b b-sales"><?php echo $monthly_sales; ?></td>
						<td class="t monthly_sales"><?php echo _n( 'Sale', 'Sales', $monthly_sales, 'edd' ); ?></td>
					</tr>
				</tbody>
			</table>
		</div>
		<div class="table table_right table_today">
			<p class="sub"><?php _e( 'Today', 'edd' ) ?></p>
			<table>
				<tbody>
					<tr class="first">
						<?php $today_earnings = $stats->get_earnings( 0, 'today', false, array( 'publish', 'revoked' ) ); ?>
						<td class="b b-earnings"><?php echo edd_currency_filter( edd_format_amount( $today_earnings ) ); ?></td>
						<td class="last t earnings"><?php _e( 'Earnings', 'edd' ); ?></td>
					</tr>
					<tr>
						<?php $today_sales = $stats->get_sales( 0, 'today', false, array( 'publish', 'revoked' ) ); ?>
						<td class="b b-sales"><?php echo $today_sales; ?></td>
						<td class="last t sales"><?php _e( 'Sales', 'edd' ); ?></td>
					</tr>
				</tbody>
			</table>
			<?php
			$best_selling = $stats->get_best_selling( 1 );
			if ( ! empty( $best_selling ) ) {
				foreach( $best_selling as $top_seller ) { ?>
					<p class="lifetime_best_selling label_heading"><?php _e('Lifetime Best Selling', 'edd') ?></p>
					<p><span class="lifetime_best_selling_label"><?php echo $top_seller->sales; ?></span> <a href="<?php echo get_permalink( $top_seller->download_id ); ?>"><?php echo get_the_title( $top_seller->download_id ); ?></a></p>
			<?php } } ?>
		</div>
		<div style="clear: both"></div>
		<div class="table table_totals">
			<table>
				<tbody>
					<tr class="first">
						<td>
							<span class="b b-earnings"><?php echo edd_currency_filter( edd_format_amount( edd_get_total_earnings() ) ); ?></span>
							<span class="last t earnings"><?php _e( 'Total Earnings', 'edd' ); ?></span>
						</td>
						<td>
							<span class="b b-sales"><?php echo edd_get_total_sales(); ?></span>
							<span class="last t sales"><?php _e( 'Total Sales', 'edd' ); ?></span>
						</td>
					</tr>
				</tbody>
			</table>
		</div>
		<div style="clear: both"></div>
		<?php
		$p_query = new EDD_Payments_Query( array(
			'number'   => 5,
			'status'   => 'publish'
		) );

		$payments = $p_query->get_payments();

		if ( $payments ) { ?>
		<p class="edd_dashboard_widget_subheading">
			<?php _e( 'Recent Purchases', 'edd' ); ?>
			<a href="<?php echo admin_url( 'edit.php?post_type=download&page=edd-payment-history' ); ?>">&nbsp;&ndash;&nbsp;<?php _e( 'View All', 'edd' ); ?></a>
		</p>
		<div class="table recent_purchases">
			<table>
				<tbody>
					<?php
					foreach ( $payments as $payment ) { ?>
						<tr>
							<td>
								<span class="edd-recent-title"><?php echo get_the_title( $payment->ID ) ?></span>
								<span class="edd-recent-email">&nbsp;&ndash;&nbsp;(<?php echo $payment->user_info['email'] ?>)&nbsp;&ndash;&nbsp;</span>
								<span class="edd-recent-amount"><?php echo edd_currency_filter( edd_format_amount( $payment->total ) ); ?></span>
								<a href="<?php echo add_query_arg( 'id', $payment->ID, admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details' ) ); ?>" title="<?php printf( __( 'Purchase Details for Payment #%s', 'edd' ), $payment->ID ); ?> "><?php _e( 'View Order Details', 'edd' ); ?></a>
							</td>
						</tr>
						<?php
					} // End foreach ?>
				</tbody>
			</table>
		</div>
		<?php } // End if ?>
	</div>
	<?php
}
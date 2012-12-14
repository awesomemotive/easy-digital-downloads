<?php
/**
 * Admin Widgets
 *
 * @package     Easy Digital Downloads
 * @subpackage  Dashboard Widgets
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Register Dashboard Widgets
 *
 * Registers the dashboard widgets.
 *
 * @access      private
 * @author      Sunny Ratilal
 * @since       1.2.2
*/

function edd_register_dashboard_widgets() {

	if ( current_user_can( apply_filters( 'edd_dashboard_stats_cap', 'edit_pages' ) ) ) {
		wp_add_dashboard_widget( 'edd_dashboard_sales', __('Easy Digital Downloads Sales Summary', 'edd'), 'edd_dashboard_sales_widget' );
	}

}
add_action('wp_dashboard_setup', 'edd_register_dashboard_widgets' );


/**
 * Sales Summary Dashboard Widget
 *
 *
 * @access      private
 * @author      Sunny Ratilal
 * @since       1.2.2
*/

function edd_dashboard_sales_widget() {

	$top_selling_args = array(
		'post_type'              => 'download',
		'posts_per_page'         => 1,
		'post_status'            => 'publish',
		'meta_key'               => '_edd_download_sales',
		'meta_compare'           => '>',
		'meta_value'             => 0,
		'orderby'                => 'meta_value_num',
		'cache_results'          => false,
		'update_post_term_cache' => false,
		'no_found_rows'          => true,
		'order'                  => 'DESC'
	);

	$top_selling = get_posts( $top_selling_args );

	?>
	<div class="table table_current_month">
		<p class="sub"><?php _e('Current Month', 'edd') ?></p>
		<table>
			<tbody>
				<tr class="first">
					<td class="first b"><?php echo edd_currency_filter( edd_format_amount( edd_get_earnings_by_date( null, date('n'), date('Y')) ) ); ?></td>
					<td class="t monthly_earnings"><?php _e('Earnings', 'edd'); ?></td>
				</tr>
				<tr>
					<?php $monthly_sales = edd_get_sales_by_date( null, date('n'), date('Y') ); ?>
					<td class="first b"><?php echo $monthly_sales; ?></td>
					<td class="t monthly_sales"><?php echo _n('Sale', 'Sales', $monthly_sales, 'edd'); ?></td>
				</tr>
			</tbody>
		</table>
		<p class="label_heading"><?php _e('Last Month', 'edd') ?></p>
		<div>
			<?php echo __('Earnings', 'edd') . ':&nbsp;<span class="edd_price_label">' . edd_currency_filter( edd_format_amount( edd_get_earnings_by_date( null, date('n') - 1, date('Y') ) ) ) . '</span>'; ?>
		</div>
		<div>
			<?php $last_month_sales = edd_get_sales_by_date( null, date('n') - 1, date('Y') ); ?>
			<?php echo _n('Sale', 'Sales', $last_month_sales, 'edd') . ':&nbsp;' . '<span class="edd_price_label">' . $last_month_sales . '</span>'; ?>
		</div>
	</div>
	<div class="table table_totals">
		<p class="sub"><?php _e('Totals', 'edd') ?></p>
		<table>
			<tbody>
				<tr class="first">
					<td class="b b-earnings"><?php echo edd_currency_filter( edd_format_amount( edd_get_total_earnings() ) ); ?></td>
					<td class="last t earnings"><?php _e('Total Earnings', 'edd'); ?></td>
				</tr>
				<tr>
					<td class="b b-sales"><?php echo edd_get_total_sales(); ?></td>
					<td class="last t sales"><?php _e('Total Sales', 'edd'); ?></td>
				</tr>
			</tbody>
		</table>
		<?php if ( $top_selling ) {
			foreach( $top_selling as $list ) { ?>
				<p class="lifetime_best_selling label_heading"><?php _e('Lifetime Best Selling', 'edd') ?></p>
				<p><span class="lifetime_best_selling_label"><?php echo edd_get_download_sales_stats( $list->ID ); ?></span> <a href="<?php echo get_permalink( $list->ID ); ?>"><?php echo get_the_title( $list->ID ); ?></a></p>
		<?php } } ?>
	</div>
	<div style="clear: both"></div>
	<p class="edd_dashboard_widget_subheading"><?php _e( 'Recent Purchases', 'edd' ); ?></p>
	<div class="table recent_purchases">
		<table>
			<tbody>
				<?php
				$payments = edd_get_payments( array(
					'number'   => 5,
					'mode'     => 'live',
					'orderby'  => 'post_date',
					'order'    => 'DESC',
					'user'     => null,
					'status'   => 'publish',
					'meta_key' => null
				) );

				if ( $payments ) {
					foreach ( $payments as $payment ) {
						$payment_meta = edd_get_payment_meta( $payment->ID );
				?>
				<tr>
					<td><?php echo get_the_title( $payment->ID ) ?> - (<?php echo $payment_meta['email'] ?>) - <span class="edd_price_label"><?php echo edd_currency_filter( edd_format_amount( edd_get_payment_amount( $payment->ID ) ) ); ?></span> - <a href="#TB_inline?width=640&amp;inlineId=purchased-files-<?php echo $payment->ID; ?>" class="thickbox" title="<?php printf( __( 'Purchase Details for Payment #%s', 'edd' ), $payment->ID ); ?> "><?php _e( 'View Order Details', 'edd' ); ?></a>
						<div id="purchased-files-<?php echo $payment->ID; ?>" style="display:none;">
							<?php
								$cart_items = edd_get_payment_meta_cart_details( $payment->ID );
								if( empty( $cart_items ) || !$cart_items ) {
									$cart_items = maybe_unserialize( $payment_meta['downloads'] );
								}
							?>
							<h4><?php echo _n( __( 'Purchased File', 'edd' ), __( 'Purchased Files', 'edd' ), count( $cart_items ) ); ?></h4>
							<ul class="purchased-files-list">
							<?php
								if( $cart_items ) {
									foreach( $cart_items as $key => $cart_item ) {
										echo '<li>';
											$id = isset( $payment_meta['cart_details'] ) ? $cart_item['id'] : $cart_item;
											$price_override = isset( $payment_meta['cart_details'] ) ? $cart_item['price'] : null;
											$user_info = edd_get_payment_meta_user_info( $payment->ID );
											$price = edd_get_download_final_price( $id, $user_info, $price_override );
											echo '<a href="' . admin_url( 'post.php?post=' . $id . '&action=edit' ) . '" target="_blank">' . get_the_title( $id ) . '</a>';
											echo  ' - ';
											if( isset( $cart_items[ $key ]['item_number'])) {
												$price_options = $cart_items[ $key ]['item_number']['options'];
												if( isset( $price_options['price_id'] ) ) {
													echo edd_get_price_option_name( $id, $price_options['price_id'] );
													echo ' - ';
												}
											}
											echo edd_currency_filter( edd_format_amount( $price ) );
										echo '</li>';
									}
								}
							?>
							</ul>
							<?php $payment_date = strtotime( $payment->post_date ); ?>
							<p><?php echo __( 'Date and Time:', 'edd' ) . ' ' . date_i18n( get_option( 'date_format' ), $payment_date ) . ' ' . date_i18n( get_option( 'time_format' ), $payment_date ) ?>
							<p><?php echo __( 'Discount used:', 'edd' ) . ' '; if( isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ) { echo $user_info['discount']; } else { _e( 'none', 'edd' ); } ?>
							<p><?php echo __( 'Total:', 'edd' ) . ' ' . edd_currency_filter( edd_format_amount( edd_get_payment_amount( $payment->ID ) ) ); ?></p>

							<div class="purcase-personal-details">
								<h4><?php _e( 'Buyer\'s Personal Details:', 'edd' ); ?></h4>
								<ul>
									<li><?php echo __( 'Name:', 'edd' ) . ' ' . $user_info['first_name'] . ' ' . $user_info['last_name']; ?></li>
									<li><?php echo __( 'Email:', 'edd' ) . ' ' . $payment_meta['email']; ?></li>
									<?php do_action( 'edd_payment_personal_details_list', $payment_meta, $user_info ); ?>
								</ul>
							</div>
							<?php
							$gateway = edd_get_payment_gateway( $payment->ID );
							if( $gateway ) { ?>
							<div class="payment-method">
								<h4><?php _e('Payment Method:', 'edd'); ?></h4>
								<span class="payment-method-name"><?php echo edd_get_gateway_admin_label( $gateway ); ?></span>
							</div>
							<?php } ?>
							<div class="purchase-key-wrap">
								<h4><?php _e('Purchase Key', 'edd'); ?></h4>
								<span class="purchase-key"><?php echo $payment_meta['key']; ?></span>
							</div>
							<p><a id="edd-close-purchase-details" class="button-secondary" onclick="tb_remove();" title="<?php _e('Close', 'edd'); ?>"><?php _e('Close', 'edd'); ?></a></p>
						</div>
					</td>
				</tr>
				<?php }
				} ?>
			</tbody>
		</table>
	</div>
	<?php
}
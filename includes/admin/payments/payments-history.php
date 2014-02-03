<?php
/**
 * Admin Payment History
 *
 * @package     Easy Digital Downloads
 * @subpackage  Admin Payment History
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Payment History Page
 *
 * Renders the payment history page contents.
 *
 * @access      private
 * @since       1.0
 * @return      void
*/

function edd_payment_history_page() {
	global $edd_options;
	
	if( isset( $_GET['edd-action'] ) && $_GET['edd-action'] == 'edit-payment' ) {
		include_once( EDD_PLUGIN_DIR . '/includes/admin/payments/edit-payment.php' );
	} else {
		$current_page = admin_url( 'edit.php?post_type=download&page=edd-payment-history' );
		?>
		<div class="wrap">
			<?php 
			if( isset( $_GET['p'] ) ) $page = $_GET['p']; else $page = 1;
			$per_page = 20;
			if( isset( $_GET['show'] ) && $_GET['show'] > 0 ) {
				$per_page = intval( $_GET['show'] );
			}
			$total_pages = 1;
			$offset = $per_page * ( $page - 1 );
			
			$mode = isset( $_GET['mode'] ) ? $_GET['mode'] : 'live';
			if( edd_is_test_mode() && !isset( $_GET['mode'] ) ) $mode = 'test';
			
			$orderby 		= isset( $_GET['orderby'] ) ? $_GET['orderby'] : 'ID';
			$order 			= isset( $_GET['order'] ) ? $_GET['order'] : 'DESC';
			$order_inverse 	= $order == 'DESC' ? 'ASC' : 'DESC';
			$order_class 	= strtolower($order_inverse);
			$user 			= isset( $_GET['user'] ) ? $_GET['user'] : null;
			$status 		= isset( $_GET['status'] ) ? $_GET['status'] : 'any';
			$meta_key		= isset( $_GET['meta_key'] ) ? $_GET['meta_key'] : null;

			$payment_args = array(
					'offset'   => $offset,
					'number'   => $per_page, 
					'orderby'  => $orderby, 
					'order'    => $order, 
					'user'     => $user, 
					'status'   => $status, 
					'meta_key' => $meta_key 
			);
			
			if ( isset( $_GET['mode'] ) ) {
				$payment_args['mode'] = $mode, 
			}

			$payments 		= edd_get_payments( $payment_args );
			$payment_count 	= wp_count_posts( 'edd_payment' );

			$total_count 	= $payment_count->publish + $payment_count->pending + $payment_count->refunded + $payment_count->trash;

			switch( $status ) {
				case 'publish':
					$current_count = $payment_count->publish;
					break;
				case 'pending':
					$current_count = $payment_count->pending;
					break;
				case 'refunded':
					$current_count = $payment_count->refunded;
					break;
				case 'any':
					$current_count = $total_count;
					break;
			}

			$total_pages = ceil( $current_count / $per_page );
			
			?>
			<h2><?php _e( 'Payment History', 'edd' ); ?></h2>
			<?php do_action( 'edd_payments_page_top' ); ?>
			<ul class="subsubsub">
				<li class="all">
					<a href="<?php echo remove_query_arg( 'status' ); ?>" <?php echo !isset( $_GET['status'] ) ? 'class="current"' : ''; ?>>
						<?php _e( 'All', 'edd' ); ?> 
						<span class="count">(<?php echo $total_count; ?>)</span>
					</a> |
				</li>
				<li class="publish">
					<a href="<?php echo add_query_arg( 'status', 'publish' ); ?>" <?php echo isset( $_GET['status'] ) && $_GET['status'] == 'publish' ? 'class="current"' : ''; ?>><?php _e( 'Completed', 'edd' ); ?> <span class="count">(<?php echo $payment_count->publish; ?>)</span></a> |
				</li>
				<li class="pending">
					<a href="<?php echo add_query_arg( 'status', 'pending' ); ?>" <?php echo isset( $_GET['status'] ) && $_GET['status'] == 'pending' ? 'class="current"' : ''; ?>><?php _e( 'Pending', 'edd' ); ?> <span class="count">(<?php echo $payment_count->pending; ?>)</span></a> |
				</li>
				<li class="refunded">
					<a href="<?php echo add_query_arg( 'status', 'refunded' ); ?>" <?php echo isset( $_GET['status'] ) && $_GET['status'] == 'refunded' ? 'class="current"' : ''; ?>><?php _e( 'Refunded', 'edd' ); ?> <span class="count">(<?php echo $payment_count->refunded; ?>)</span></a> |
				</li>
				<?php do_action( 'edd_payments_page_statuses' ); ?>
			</ul>
			<ul class="subsubsub edd-export-payments">
				<li>&nbsp;<?php _e( 'Export', 'edd' ); ?>: <a href="<?php echo add_query_arg( 'export', 'csv' ); ?>">CSV</a></li>
				<?php do_action( 'edd_payments_page_export_options' ); ?>
			</ul>	
			<form id="payments-filter" action="<?php echo admin_url( 'edit.php' ); ?>" method="get" style="float: right; margin-bottom: 5px;">
				<label for="edd-mode"><?php _e('Payment mode', 'edd'); ?></label>
				<select name="mode" id="edd-mode">
					<option value="live" <?php selected( 'live', $mode ); ?>><?php _e( 'Live', 'edd' ); ?></option>
					<option value="test" <?php selected( 'test', $mode ); ?>><?php _e( 'Test', 'edd' ); ?></option>
				</select>
				<input type="hidden" name="page" value="edd-payment-history"/>
				<input type="hidden" name="post_type" value="download"/>
				<?php if( isset( $_GET['user'] ) ) { ?>
					<input type="hidden" name="user" value="<?php echo $_GET['user']; ?>"/>
				<?php } ?>
				<?php if( isset( $_GET['status'] ) ) { ?>
					<input type="hidden" name="status" value="<?php echo $_GET['status']; ?>"/>
				<?php } ?>
				<label for="edd_show"><?php _e( 'Payments per page', 'edd' ); ?></label>
				<input type="text" class="regular-text" style="width:30px;" id="edd_show" name="show" value="<?php echo isset( $_GET['show'] ) ? $_GET['show'] : ''; ?>"/>
				<input type="submit" class="button-secondary" value="<?php _e( 'Show', 'edd' ); ?>"/>
			</form>
			<?php if( isset( $_GET['user'] ) ) { 
				$user = is_numeric( $user ) ? get_userdata( $user ) : $user; 
				$user = is_object( $user ) ? $user->display_name : $user;
				?>
				<p class="clear"><?php echo __('Showing payments for: ', 'edd') . '&nbsp' . $user; ?>&nbsp;-&nbsp;<a href="<?php echo remove_query_arg( 'user' ); ?>"><?php _e( 'clear', 'edd' ); ?></a></div>
			<?php } ?>
			<table class="wp-list-table widefat fixed posts edd-payments">
				<thead>
					<tr>
						<th style="width: 60px;" class="manage-column column-title sortable <?php echo $order_class; echo $orderby == 'ID' ? ' sorted' : ''; ?>">
						    <a href="<?php echo add_query_arg( array( 'orderby' => 'ID', 'order' => $order_inverse ) ); ?>" title="<?php _e('ID', 'edd'); ?>"><span><?php _e( 'ID', 'edd' ); ?></span> <span class="sorting-indicator"></span></a>
						</th>
						<th style="width: 250px;"><?php _e('Email', 'edd'); ?></th>
						<th><?php _e('Products', 'edd'); ?></th>
						<th>
							<a href="<?php echo add_query_arg( array( 'meta_key' => '_edd_payment_total', 'order' => $order_inverse, 'orderby' => 'meta_value_num' ) ); ?>" title="<?php _e( 'Price', 'edd' ); ?>"><span><?php _e( 'Amount', 'edd' ); ?></span> <span class="sorting-indicator"></span></a>
						</th>
						<th class="manage-column column-title sortable <?php echo $order_class; echo $orderby == 'Date' ? ' sorted' : ''; ?>">
						    <a href="<?php echo add_query_arg( array( 'orderby' => 'post_date', 'order' => $order_inverse ) ); ?>" title="<?php _e( 'Date', 'edd' ); ?>"><span><?php _e( 'Date', 'edd' ); ?></span> <span class="sorting-indicator"></span></a>
						</th>
						<th><?php _e('User', 'edd'); ?></span></th>
						<th class="manage-column column-title sortable <?php echo $order_class; echo $orderby == 'Status' ? ' sorted' : ''; ?>">
						    <a href="<?php echo add_query_arg( array( 'orderby' => 'post_status', 'order' => $order_inverse ) ); ?>" title="<?php _e( 'Status', 'edd' ); ?>"><span><?php _e( 'Status', 'edd' ); ?></span> <span class="sorting-indicator"></span></a>
						</th>
					</tr>
				</thead>
				<tfoot>
					<tr>
						<th style="width: 40px;"><?php _e( 'ID', 'edd' ); ?></th>
						<th style="width: 250px;"><?php _e( 'Email', 'edd' ); ?></th>
						<th><?php _e( 'Products', 'edd' ); ?></th>
						<th><?php _e( 'Amount', 'edd' ); ?></th>
						<th><?php _e( 'Date', 'edd' ); ?></th>
						<th><?php _e( 'User', 'edd' ); ?></th>
						<th><?php _e( 'Status', 'edd' ); ?></th>
					</tr>
				</tfoot>
				<tbody>
					<?php
						if( $payments ) :
							$i = 0;
							foreach( $payments as $payment ): ?>
								<?php 
								$payment_meta = get_post_meta( $payment->ID, '_edd_payment_meta', true );
								$user_info = maybe_unserialize( $payment_meta['user_info'] );
								$classes = array();
								$classes[] = edd_is_odd( $i ) ? 'alternate' : '';
								$payment_classes = get_post_class( apply_filters( 'edd_payment_row_classes', $classes ), $payment->ID );
								?>
								<tr class="edd_payment <?php echo implode( ' ', $payment_classes ); ?>">
									<td><?php echo $payment->ID; ?></td>
									<td>
										<?php echo $payment_meta['email']; ?>
										<div class="row-actions">
											<?php 
											$row_actions = array(
												'edit' => '<a href="' . add_query_arg( array( 'edd-action' => 'edit-payment', 'purchase_id' => $payment->ID ) ) . '">' . __( 'Edit', 'edd' ) . '</a>',
												'email_links' => edd_is_payment_complete( $payment->ID ) ? '<a href="' . add_query_arg( array( 'edd-action' => 'email_links', 'purchase_id' => $payment->ID) ) . '">' . __( 'Resend Purchase Receipt', 'edd' ) . '</a>' : NULL,
												'delete' => '<a href="' . wp_nonce_url( add_query_arg( array( 'edd-action' => 'delete_payment', 'purchase_id' => $payment->ID ) ), 'edd_payment_nonce') . '">' . __( 'Delete', 'edd' ) . '</a>'
											);
											$row_actions = apply_filters( 'edd_payment_row_actions', $row_actions, $payment );
											$action_count = count( $row_actions ); $i = 1;
											foreach( $row_actions as $key => $action ) {
												if( $action_count == $i ) { $sep = ''; } else { $sep = ' | '; }
												echo !is_null( $action ) ? '<span class="' . $key . '">' . $action . '</span>' . $sep : '';
												$i++;
											}
											?>
										</div>
									</td>
									<td><a href="#TB_inline?width=640&amp;inlineId=purchased-files-<?php echo $payment->ID; ?>" class="thickbox" title="<?php printf( __( 'Purchase Details for Payment #%s', 'edd' ), $payment->ID ); ?> "><?php _e( 'View Order Details', 'edd' ); ?></a>
										<div id="purchased-files-<?php echo $payment->ID; ?>" style="display:none;">
											<?php 
												$cart_items = isset( $payment_meta['cart_details'] ) ? maybe_unserialize($payment_meta['cart_details']) : false;
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
															
															// retrieve the ID of the download
															$id = isset( $payment_meta['cart_details'] ) ? $cart_item['id'] : $cart_item;
															
															// if download has variable prices, override the default price
															$price_override = isset( $payment_meta['cart_details'] ) ? $cart_item['price'] : null; 

															// get the user information
															$user_info = edd_get_payment_meta_user_info( $payment->ID );
															
															// calculate the final item price
															$price = edd_get_download_final_price( $id, $user_info, $price_override );
															
															// show name of download
															echo '<a href="' . admin_url( 'post.php?post=' . $id . '&action=edit' ) . '" target="_blank">' . get_the_title( $id ) . '</a>';
															
															echo  ' - ';
															
															if( isset( $cart_items[ $key ]['item_number'])) {

																$price_options = $cart_items[ $key ]['item_number']['options'];
																															
																if( isset( $price_options['price_id'] ) ) {
																	echo edd_get_price_option_name( $id, $price_options['price_id'] );
																	echo ' - ';
																}
															}	
															// show price
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
									<td style="text-transform:uppercase;"><?php echo edd_currency_filter( edd_format_amount( edd_get_payment_amount( $payment->ID ) ) ); ?></td>
									<td><?php echo date( apply_filters( 'edd_payments_page_date_format', get_option('date_format') ), strtotime( $payment->post_date )); ?></td>
									<td>
										<?php $user_id = isset( $user_info['id'] ) && $user_info['id'] != -1 ? $user_info['id'] : $user_info['email']?>
										<a href="<?php echo remove_query_arg( 'p', add_query_arg( 'user', $user_id ) ); ?>">
											<?php
											if( is_numeric( $user_id ) ) {
												$user = get_user_by('id', $user_id);
												echo is_object( $user ) ? $user->display_name : __('guest', 'edd');												
											} else {
												echo __( 'guest', 'edd' );
											}
											?>
										</a>
									</td>
									<td><?php echo edd_get_payment_status( $payment, true ); ?></td>
								</tr>
							<?php
							$i++;
							endforeach;
						else : ?>
						<tr><td colspan="7"><?php _e( 'No payments recorded yet', 'edd' ); ?></td></tr>
					<?php endif;?>
				</table>
				<div class="tablenav">

					<div class="left edd-total-earnings">
						<p><?php _e( 'Total Earnings:', 'edd' ); ?>&nbsp;<strong><?php echo edd_get_total_earnings(); ?></strong></p>
						<?php do_action( 'edd_payments_page_earnings' ); ?>
					</div>
					<?php if( $total_pages > 1 ) : ?>
						<div class="tablenav-pages alignright">
							<?php

								$query_string = $_SERVER['QUERY_STRING'];

								$base = 'edit.php?' . remove_query_arg( 'p', $query_string ) . '%_%';

								echo paginate_links( array(
									'base' => $base,
									'format' => '&p=%#%',
									'prev_text' => '&laquo; ' . __( 'Previous', 'edd' ),
									'next_text' => __( 'Next', 'edd' ) . ' &raquo;',
									'total' => $total_pages,
									'current' => $page,
									'end_size' => 1,
									'mid_size' => 5,
								));
							?>	
						</div>
					<?php endif; ?>
				</div><!--end .tablenav-->
				<?php do_action( 'edd_payments_page_bottom' ); ?>
		</div><!--end wrap-->
		<?php
	}
}

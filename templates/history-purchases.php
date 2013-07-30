<?php
// Retrieve all purchases for the current user
$purchases = edd_get_users_purchases( get_current_user_id(), 20, true, array( 'pending', 'revoked', 'publish', 'failed', 'preapproval', 'refunded' ) );
if ( $purchases ) : ?>
	<table id="edd_user_history">
		<thead>
			<tr class="edd_purchase_row">
				<?php do_action('edd_purchase_history_header_before'); ?>
				<th class="edd_purchase_id"><?php _e('ID', 'edd'); ?></th>
				<th class="edd_purchase_date"><?php _e('Date', 'edd'); ?></th>
				<th class="edd_purchase_amount"><?php _e('Amount', 'edd'); ?></th>
				<th class="edd_purchase_details"><?php _e('Details', 'edd'); ?></th>
				<?php do_action('edd_purchase_history_header_after'); ?>
			</tr>
		</thead>
		<?php foreach ( $purchases as $post ) : setup_postdata( $post ); ?>
			<?php $purchase_data = edd_get_payment_meta( $post->ID ); ?>
			<tr class="edd_purchase_row">
				<?php do_action( 'edd_purchase_history_row_start', $post->ID, $purchase_data ); ?>
				<td class="edd_purchase_id">#<?php echo absint( $post->ID ); ?></td>
				<td class="edd_purchase_date"><?php echo date_i18n( get_option('date_format'), strtotime( get_post_field( 'post_date', $post->ID ) ) ); ?></td>
				<td class="edd_purchase_amount">
					<span class="edd_purchase_amount"><?php echo edd_currency_filter( edd_format_amount( edd_get_payment_amount( $post->ID ) ) ); ?></span>
					<?php if( $post->post_status != 'publish' ) : ?>
					<span class="edd_purchase_sep">&nbsp;&ndash;&nbsp;</span>
					<span class="edd_purchase_status <?php echo $post->post_status; ?>"><?php echo edd_get_payment_status( $post, true ); ?></span>
					<?php endif; ?>
				</td>
				<td class="edd_purchase_details">
					<a href="<?php echo add_query_arg( 'payment_key', edd_get_payment_key( $post->ID ), edd_get_success_page_uri() ); ?>"><?php _e( 'View Details and Downloads', 'edd' ); ?></a>
				</td>
				<?php do_action( 'edd_purchase_history_row_end', $post->ID, $purchase_data ); ?>
			</tr>
		<?php endforeach; ?>
	</table>
	<div id="edd_purchase_history_pagination" class="edd_pagination navigation">
		<?php
		$big = 999999;
		echo paginate_links( array(
			'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
			'format'  => '?paged=%#%',
			'current' => max( 1, get_query_var( 'paged' ) ),
			'total'   => ceil( edd_count_purchases_of_customer() / 20 ) // 20 items per page
		) );
		?>
	</div>
	<?php wp_reset_postdata(); ?>
<?php else : ?>
	<p class="edd-no-purchases"><?php _e('You have not made any purchases', 'edd'); ?></p>
<?php endif;
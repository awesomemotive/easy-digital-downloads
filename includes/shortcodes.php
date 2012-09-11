<?php
/**
 * Shortcodes
 *
 * @package     Easy Digital Downloads
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Purchase Link Shortcode
 *
 * Retrieves a download and displays the purchase form.
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_download_shortcode( $atts, $content = null ) {	
	extract( shortcode_atts( array(
			'id' => '',
			'text' => __('Purchase', 'edd'),
			'style' => 'button',
			'color' => 'blue',
			'class' => ''
		), $atts )
	);
	
	$download = edd_get_download($id);
	
	if($download) {
		return edd_get_purchase_link($download->ID, $text, $style, $color, $class);
	}
}
add_shortcode('purchase_link', 'edd_download_shortcode');


/**
 * Download History Shortcode
 *
 * Displays a user's download history.
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_download_history() {
	global $user_ID, $edd_options;
	
	if(is_user_logged_in()) {
		$purchases = edd_get_users_purchases($user_ID);
		
		ob_start();
			if($purchases) { ?>
				<table id="edd_user_history">
					<thead>
						<tr>
							<?php do_action('edd_user_history_header_before'); ?>
							<th class="edd_download_download_name_header"><?php _e('Download Name', 'edd'); ?></th>
							<?php if( ! edd_no_redownload() ) { ?>
								<th class="edd_download_download_files_header"><?php _e('Files', 'edd'); ?></th>
							<?php } ?>							
							<?php do_action('edd_user_history_header_after'); ?>
						</tr>
					</thead>
					<?php 
					foreach($purchases as $purchase) {

						$downloads = edd_get_downloads_of_purchase($purchase->ID);
						$payment_meta = edd_get_payment_meta( $purchase->ID );

						if($downloads) {
							foreach($downloads as $download) {

								echo '<tr class="edd_download_history_row">';

									$id = isset($payment_meta['cart_details']) ? $download['id'] : $download;

									$price_id = isset($download['options']['price_id']) ? $download['options']['price_id'] : null;
									
									$download_files = edd_get_download_files( $id, $price_id );

									do_action('edd_user_history_table_begin', $purchase->ID);

									echo '<td>' . get_the_title($id) . '</td>';

									if( ! edd_no_redownload() ) {		

										echo '<td>';

										if($download_files) {
											foreach($download_files as $filekey => $file) {

												$download_url = edd_get_download_file_url($payment_meta['key'], $payment_meta['email'], $filekey, $id);
												echo'<div class="edd_download_file"><a href="' . $download_url . '" class="edd_download_file_link">' . $file['name'] . '</a></div>';
											
											} 
										} else {
											_e('No downloadable files found.', 'edd');
										}

										echo '</td>';

									}

									do_action('edd_user_history_table_end', $purchase->ID);

								echo '</tr>';

							}
						}
					}
				echo '</table>';
			} else {
				echo '<p class="edd-no-downloads">' . __('You have not purchased any downloads', 'edd') . '</p>';
			}
		return ob_get_clean();
	}
}
add_shortcode('download_history', 'edd_download_history');

/**
 * Purchase History Shortcode
 *
 * Displays a user's purchsae history.
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_purchase_history() {
	global $user_ID, $edd_options;
	
	if(is_user_logged_in()) {
		
		$purchases = edd_get_users_purchases($user_ID);
		
		ob_start();
			if($purchases) { ?>
				<table id="edd_user_history">
					<thead>
						<tr>
							<?php do_action('edd_purchase_history_header_before'); ?>
							<th class="edd_purchase_id_header"><?php _e('Purchase ID', 'edd'); ?></th>
							<th class="edd_purchase_date_header"><?php _e('Date', 'edd'); ?></th>
							<th class="edd_purchase_amount_header"><?php _e('Amount', 'edd'); ?></th>
							<th class="edd_purchased_files_header"><?php _e('Files', 'edd'); ?></th>
							<?php do_action('edd_purchase_history_header_after'); ?>
						</tr>
					</thead>
					<?php foreach($purchases as $purchase) { ?>
						<?php $purchase_data = edd_get_paymeny_meta( $purchase->ID ); ?>
						<?php do_action('edd_purchase_history_body_start', $purchase, $purchase_data); ?>
						<tr class="edd_purchase_row">
							<td>#<?php echo $purchase->ID; ?></td>
							<td><?php echo date(get_option('date_format'), strtotime($purchase->post_date)); ?></td>
							<td><?php echo edd_currency_filter($purchase_data['amount']); ?></td>
							<td>
								<?php
									// show a list of downloadable files
									$downloads = edd_get_downloads_of_purchase($purchase->ID);
									if($downloads) {
										foreach($downloads as $download) {
											$id = isset($purchase_data['cart_details']) ? $download['id'] : $download;
											$price_id = isset($download['options']['price_id']) ? $download['options']['price_id'] : null;
											$download_files = edd_get_download_files( $id, $price_id );
											echo '<div class="edd_purchased_download_name">' . get_the_title($id) . '</div>';
											if( ! edd_no_redownload() ) {
												if($download_files) {
													foreach($download_files as $filekey => $file) {
														$download_url = edd_get_download_file_url($purchase_data['key'], $purchase_data['email'], $filekey, $id);
														echo '<div class="edd_download_file"><a href="' . $download_url . '" class="edd_download_file_link">' . $file['name'] . '</a></div>';
													} 
												} else {
													_e('No downloadable files found.', 'edd');
												}
											}
										}
									}
								?>
							</td>
						</tr>
						<?php do_action('edd_purchase_history_body_end', $purchase, $purchase_data); ?>
					<?php } ?>
				</table>
				<?php
			} else {
				echo '<p class="edd-no-purchases">' . __('You have not made any purchases', 'edd') . '</p>';
			}
		return ob_get_clean();
	}
}
add_shortcode('purchase_history', 'edd_purchase_history');


/**
 * Checkout Form Shortcode
 *
 * Show the checkout form.
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_checkout_form_shortcode($atts, $content = null) {
	return edd_checkout_form();
}
add_shortcode('download_checkout', 'edd_checkout_form_shortcode');


/**
 * Download Cart Shortcode
 *
 * Show the shopping cart.
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_cart_shortcode($atts, $content = null) {
	return edd_shopping_cart();
}
add_shortcode('download_cart', 'edd_cart_shortcode');


/**
 * Login Shortcode
 *
 * Shows the login form.
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_login_form_shortcode($atts, $content = null) {
	
	extract( shortcode_atts( array(
			'redirect' => '',
		), $atts )
	);
	return edd_login_form($redirect);
}
add_shortcode('edd_login', 'edd_login_form_shortcode');


/**
 * Discounts short code
 *
 * Displays a list of all active discounts 
 *
 * @access      private
 * @since       1.0.8.2
 * @return      string
*/

function edd_discounts_shortcode( $atts, $content = null ) {
	
	$discounts = edd_get_discounts();
		
	if( ! $discounts && edd_has_active_discounts() )
		return;
	
	$discounts_list = '<ul id="edd_discounts_list">';
	
	foreach( $discounts as $discount ) {
		
		if( edd_is_discount_valid( $discount['code'] ) ) {
			
			$discounts_list .= '<li class="edd_discount">';
	
				$discounts_list .= '<span class="edd_discount_name">' . $discount['name'] . '</span>';
				$discounts_list .= '<span class="edd_discount_separator"> - </span>';
				$discounts_list .= '<span class="edd_discount_amount">' . edd_format_discount_rate( $discount['type'], $discount['amount'] ) . '</span>';
	
			$discounts_list .= '</li>';
			
		}		
		
	}
	
	$discounts_list .= '</ul>';	
	
	return $discounts_list;
	
}
add_shortcode('download_discounts', 'edd_discounts_shortcode');



/**
 * Purchase Collection Shortcode
 *
 * Displays a collection purchase link for adding all 
 * items in a taxonomy term to the cart.
 *
 * @access      private
 * @since       1.0.6
 * @return      string
*/

function edd_purchase_collection_shortcode($atts, $content = null) {
	extract( shortcode_atts( array(
			'taxonomy' => '',
			'terms' => ''
		), $atts )
	);
	
	return '<a href="' . add_query_arg(array('edd_action' => 'purchase_collection', 'taxonomy' => $taxonomy, 'terms' => $terms)) . '">Purchase All Items</a>';
}
add_shortcode('purchase_collection', 'edd_purchase_collection_shortcode');


/**
 * Downloads Shortcode
 *
 * Incomplete short code for querying downloads.
 *
 * Contributor: Sunny Ratilal
 *
 * @access      private
 * @since       1.0.6
 * @return      string
*/

function edd_downloads_query($atts, $content = null) {

	extract( shortcode_atts( array(
			'category' => '',
			'tags' => '',
			'relation' => 'OR',
			'number' => 10,
			'price' => 'yes',
			'excerpt' => 'yes',
			'full_content' => 'no',
			'buy_button' => 'yes',
			'columns' => 3,
			'thumbnails' => 'true',
			'orderby' => 'post_date',
			'order' => 'DESC'
		), $atts )
	);

	$query = array(
		'post_type' => 'download',
		'posts_per_page' => absint($number),
		'orderby' => $orderby,
		'order' => $order
	);

	switch ( $orderby ) {
		case 'price':
			$orderby = 'meta_value';
			$query['meta_key'] = 'edd_price';
			$query['orderby'] = 'meta_value_num';
		break;

		case 'title':
			$query['orderby'] = 'title';
		break;

		case 'id':
			$query['orderby'] = 'ID';
		break;

		case 'random':
			$query['orderby'] = 'rand';
		break;

		default:
			$query['orderby'] = 'post_date';
		break;
	}

	if ( $tags ) {
		$query['download_tag'] = $tags;
	}
	if ( $category ) {
		$query['download_category'] = $category;
	}

	switch( intval( $columns ) ) :
	
		case 1:
			$column_width = '100%'; break;
		case 2:
			$column_width = '50%'; break;
		case 3:
			$column_width = '33%'; break;
		case 4:
			$column_width = '25%'; break;
		case 5:
			$column_width = '20%'; break;
		case 6:
			$column_width = '16.6%'; break;
	
	endswitch;

	// allow the query to be manipulated by other plugins
	$query = apply_filters('edd_downloads_query', $query);
	
	$downloads = new WP_Query( $query );
	if ( $downloads->have_posts() ) :
		$i = 1;
		ob_start(); ?>
		<div class="edd_downloads_list">
			<?php while ( $downloads->have_posts() ) : $downloads->the_post(); ?>
				<div class="edd_download" id="edd_download_<?php echo get_the_ID(); ?>" style="width: <?php echo $column_width; ?>; float: left;">
					<div class="edd_download_inner">
						<?php 

						do_action( 'edd_download_before' );

						if( 'false' != $thumbnails ) :
							edd_get_template_part( 'shortcode', 'content-image' );
						endif;
						
						edd_get_template_part( 'shortcode', 'content-title' );
						
						if($excerpt == 'yes' && $full_content != 'yes')
							edd_get_template_part( 'shortcode', 'content-excerpt' );
						else if($full_content == 'yes')
							edd_get_template_part( 'shortcode', 'content-full' );
						
						
						if($price == 'yes')
							edd_get_template_part( 'shortcode', 'content-price' );
						
						if($buy_button == 'yes')
							edd_get_template_part( 'shortcode', 'content-cart-button' );

						do_action( 'edd_download_after' ); 

						?>
					</div>
				</div>
				<?php if($i % $columns == 0) { ?><div style="clear:both;"></div><?php } ?>
			<?php $i++; endwhile; ?>
			<?php wp_reset_postdata(); ?>
			<div style="clear:both;"></div>
		</div>
		<?php
		$display = ob_get_clean();
	else:
		$display = __('No downloads found', 'edd');
	endif;
	return $display;
}
add_shortcode('downloads', 'edd_downloads_query');


/**
 * Price Shortcode
 *
 * Shows the price of a download.
 *
 * @access      private
 * @since       1.1.3.3
 * @return      string
*/

function edd_download_price_shortcode($atts, $content = null) {
	
	extract( shortcode_atts( array(
			'id' => NULL,
		), $atts )
	);


	if( is_null( $id ) )
		$id = get_the_ID();

	return edd_price( $id, false );

}
add_shortcode('edd_price', 'edd_download_price_shortcode');
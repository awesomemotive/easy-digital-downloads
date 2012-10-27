<?php
/**
 * Widgets
 *
 * @package     Easy Digital Downloads
 * @subpackage  Widgets
 * @copyright   Copyright (c) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/*
|--------------------------------------------------------------------------
| FRONT-END WIDGETS
|--------------------------------------------------------------------------
|
| - Cart Widget
| - Categories / Tags Widget
| - Purchase History Widget
|
*/

/**
 * Cart Widget
 *
 * Downloads cart widget class.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/
	
class edd_cart_widget extends WP_Widget {

	/** constructor */
	function edd_cart_widget()
	{
		parent::WP_Widget(false, __('Downloads Cart', 'edd'), array('description' => __('Display the downloads shopping cart', 'edd')));
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance )
	{
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$quantity = isset( $instance['quantity'] ) ? $instance['quantity'] : false;

		global $post, $edd_options;

		echo $before_widget;
		if ( $title ) {
			if ( $quantity == 1 ) {
				$quantity = ' - <span class="edd-cart-quantity">' . edd_get_cart_quantity() . '</span>';
			} else {
				$quantity = '';
			}
			echo $before_title . $title . $quantity . $after_title;
		}
		do_action( 'edd_before_cart_widget' );
		edd_shopping_cart( true );
		do_action( 'edd_after_cart_widget' );
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['quantity'] = strip_tags( $new_instance['quantity'] );
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance )
	{
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$quantity = isset( $instance['quantity'] ) ? esc_attr( $instance['quantity'] ) : '';
		?>
	<p>
		<label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'edd'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
			   name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>"/>
	</p>
	<p>
		<label for="<?php echo $this->get_field_id( 'quantity' ); ?>"><?php _e('Show Quantity:', 'edd'); ?></label>
		<input id="<?php echo $this->get_field_id( 'quantity' ); ?>"
			   name="<?php echo $this->get_field_name( 'quantity' ); ?>" type="checkbox"
			   value="1" <?php checked( '1', $quantity ); ?>/>
	</p>
	<?php
	}
}


/**
 * Categories / Tags Widget
 *
 * Downloads categories / tags widget class.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

class edd_categories_tags_widget extends WP_Widget {

	/** constructor */
	function edd_categories_tags_widget()
	{
		parent::WP_Widget( false, __('Downloads Categories / Tags', 'edd'), array( 'description' => __('Display the downloads categories or tags', 'edd') ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance )
	{
		extract( $args );
		$title = apply_filters('widget_title', $instance['title']);
		$tax = $instance['taxonomy'];
		
		global $post, $edd_options;

		echo $before_widget;
		if ( $title ) {
			echo $before_title . $title . $after_title;
		}
		
		do_action( 'edd_before_taxonomy_widget' );
		$terms = get_terms( $tax );

			if ( is_wp_error( $terms ) ) {
				return;
			} else {
				echo "<ul class=\"edd-taxonomy-widget\">\n";
				foreach ( $terms as $term ) {
					echo '<li><a href="' . get_term_link( $term ) . '" title="' . esc_attr( $term->name ) . '" rel="bookmark">' . $term->name . '</a></li>'."\n";
				}
				echo "</ul>\n";
			}
		
		do_action( 'edd_after_taxonomy_widget' );
		echo $after_widget;
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['taxonomy'] = strip_tags( $new_instance['taxonomy'] );
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance )
	{
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		$taxonomy = isset( $instance['taxonomy'] ) ? esc_attr( $instance['taxonomy'] ) : 'download_category';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title:', 'edd'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e('Taxonomy:', 'edd'); ?></label>
			<select name="<?php echo $this->get_field_name( 'taxonomy' ); ?>" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>">
				<option value="download_category" <?php selected( 'download_category', $taxonomy ); ?>><?php _e('Categories', 'edd'); ?></option>
				<option value="download_tag" <?php selected( 'download_tag', $taxonomy ); ?>><?php _e('Tags', 'edd'); ?></option>
			</select>
		</p>
	<?php
	}
}


/**
 * Purchase History Widget
 *
 * Displays a user's purchase history.
 *
 * @access        private
 * @since        1.2
 * @return        void
 */

class edd_purchase_history_widget extends WP_Widget {

	/** constructor */
	function edd_purchase_history_widget()
	{
		parent::WP_Widget( false, __('Purchase History', 'edd'), array( 'description' => __('Display a user\'s purchase history', 'edd') ) );
	}

	/** @see WP_Widget::widget */
	function widget( $args, $instance )
	{
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		global $user_ID, $edd_options;

		if( is_user_logged_in() ) {

			$purchases = edd_get_users_purchases( $user_ID );

			if( $purchases ) {
				echo $before_widget;
				if ( $title ) {
					echo $before_title . $title . $after_title;
				}

				foreach ( $purchases as $purchase ) {
					$purchase_data = get_post_meta( $purchase->ID, '_edd_payment_meta', true );
					$downloads = edd_get_downloads_of_purchase( $purchase->ID );
					if ( $downloads ) {
						foreach ( $downloads as $download ) {
							$id = isset( $purchase_data['cart_details'] ) ? $download['id'] : $download;
							$price_id = isset( $download['options']['price_id'] ) ? $download['options']['price_id'] : null;
							$download_files = edd_get_download_files( $id, $price_id );
							echo '<div class="edd-purchased-widget-purchase edd-purchased-widget-purchase-' . $purchase->ID . '" id="edd-purchased-widget-purchase-' . $id . '">';
								echo '<div class="edd-purchased-widget-purchase-name">' . get_the_title( $id ) . '</div>';
								echo '<ul class="edd-purchased-widget-file-list">';
								if( !edd_no_redownload() ) {
									if( $download_files ) {
										foreach( $download_files as $filekey => $file ) {
											$download_url = edd_get_download_file_url( $purchase_data['key'], $purchase_data['email'], $filekey, $id );
											echo '<li class="edd-purchased-widget-file"><a href="' . $download_url . '" class="edd-purchased-widget-file-link">' .  $file['name'] . '</a></li>';
										}
									} else {
										echo '<li class="edd-purchased-widget-no-file">' . __('No downloadable files found.', 'edd');
									}
								}
								echo '</ul>';
							echo '</div>';
						}
					}
				}
			}
			echo $after_widget;
		}
	}

	/** @see WP_Widget::update */
	function update( $new_instance, $old_instance )
	{
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		return $instance;
	}

	/** @see WP_Widget::form */
	function form( $instance )
	{
		$title = isset( $instance['title'] ) ? esc_attr( $instance['title'] ) : '';
		?>
		<p>
			<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e('Title', 'edd'); ?></label>
			<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
				   name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>"/>
		</p>
	<?php
	}
}


/**
 * Register Widgets
 *
 * Registers the EDD Widgets.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_register_widgets() {
	register_widget( 'edd_cart_widget' );
	register_widget( 'edd_categories_tags_widget' );
	register_widget( 'edd_purchase_history_widget' );
}
add_action('widgets_init', 'edd_register_widgets');


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

	$top_selling_30_days_args = array(
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
		'year'                   => date('Y'),
		'monthnum'               => date('n'),
		'order'                  => 'DESC'
	);

	$top_selling = get_posts( $top_selling_args );
	$top_selling_30_days = get_posts( $top_selling_30_days_args );

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
					<td class="first b"><?php echo edd_get_sales_by_date( null, date('n'), date('Y') ); ?></td>
					<td class="t monthly_sales"><?php echo _n('Sale', 'Sales', edd_get_sales_by_date( null, date('n'), date('Y') ), 'edd'); ?></td>
				</tr>
			</tbody>
		</table>
		<?php if ( $top_selling_30_days ) {
			foreach( $top_selling_30_days as $list ) { ?>
		<p class="best_selling_30_days label_heading"><?php _e('Best Selling in Last 30 Days', 'edd') ?></p>
		<p><span class="best_selling_30_days_label"><?php echo edd_get_download_sales_stats( $list->ID ); ?></span> <a href="<?php echo get_permalink( $list->ID ) ?>"><?php echo get_the_title( $list->ID ); ?></a></p>
		<?php } } ?>
	</div>
	<div class="table table_totals">
		<p class="sub"><?php _e('Totals', 'edd') ?></p>
		<table>
			<tbody>
				<tr class="first">
					<td class="b b-earnings"><?php echo edd_get_total_earnings(); ?></td>
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
					'status'   => 'completed', 
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

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
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_download_shortcode( $atts, $content = null ) {
	global $post, $edd_options;

	extract( shortcode_atts( array(
			'id' 	=> $post->ID,
			'text'	=> isset( $edd_options[ 'add_to_cart_text' ] ) && $edd_options[ 'add_to_cart_text' ] != '' ? $edd_options[ 'add_to_cart_text' ] 	: __( 'Purchase', 'edd' ),
			'style' => isset( $edd_options[ 'button_style' ] ) 	 	? $edd_options[ 'button_style' ] 		: 'button',
			'color' => isset( $edd_options[ 'checkout_color' ] ) 	? $edd_options[ 'checkout_color' ] 		: 'blue',
			'class' => 'edd-submit'
		),
		$atts )
	);

	// edd_get_purchase_link() expects the ID to be download_id since v1.3
	$atts['download_id'] = $atts['id'];

	$download = edd_get_download( $atts['download_id'] );
	
	if( $download ) {
		return edd_get_purchase_link( $atts );
	}
}
add_shortcode( 'purchase_link', 'edd_download_shortcode' );


/**
 * Download History Shortcode
 *
 * Displays a user's download history.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_download_history() {
	
	if(is_user_logged_in()) {
		ob_start();
		edd_get_template_part( 'history', 'downloads' );
		return ob_get_clean();
	}
}
add_shortcode( 'download_history', 'edd_download_history' );

/**
 * Purchase History Shortcode
 *
 * Displays a user's purchsae history.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_purchase_history() {	
	if( is_user_logged_in() ) {
		ob_start();
		edd_get_template_part( 'history', 'purchases' );
		return ob_get_clean();
	}
}
add_shortcode( 'purchase_history', 'edd_purchase_history' );


/**
 * Checkout Form Shortcode
 *
 * Show the checkout form.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_checkout_form_shortcode( $atts, $content = null ) {
	return edd_checkout_form();
}
add_shortcode( 'download_checkout', 'edd_checkout_form_shortcode' );


/**
 * Download Cart Shortcode
 *
 * Show the shopping cart.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_cart_shortcode( $atts, $content = null ) {
	return edd_shopping_cart();
}
add_shortcode( 'download_cart', 'edd_cart_shortcode' );


/**
 * Login Shortcode
 *
 * Shows the login form.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_login_form_shortcode( $atts, $content = null ) {
	
	extract( shortcode_atts( array(
			'redirect' => '',
		), $atts )
	);
	return edd_login_form( $redirect );
}
add_shortcode( 'edd_login', 'edd_login_form_shortcode' );


/**
 * Discounts short code
 *
 * Displays a list of all active discounts 
 *
 * @access      public
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
add_shortcode( 'download_discounts', 'edd_discounts_shortcode' );



/**
 * Purchase Collection Shortcode
 *
 * Displays a collection purchase link for adding all 
 * items in a taxonomy term to the cart.
 *
 * @access      public
 * @since       1.0.6
 * @return      string
*/

function edd_purchase_collection_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
			'taxonomy' => '',
			'terms' => '',
			'link' => __('Purchase All Items', 'edd')
		), $atts )
	);
	
	$link = is_null( $content ) ? $link : $content;

	return '<a href="' . add_query_arg( array( 'edd_action' => 'purchase_collection', 'taxonomy' => $taxonomy, 'terms' => $terms ) ) . '">' . $link . '</a>';
}
add_shortcode( 'purchase_collection', 'edd_purchase_collection_shortcode' );


/**
 * Downloads Shortcode
 *
 * Incomplete short code for querying downloads.
 *
 * Contributor: Sunny Ratilal
 *
 * @access      public
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
		'posts_per_page' => absint( $number ),
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
	$query = apply_filters( 'edd_downloads_query', $query );
	
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
				<?php if( $i % $columns == 0 ) { ?><div style="clear:both;"></div><?php } ?>
			<?php $i++; endwhile; ?>
			<?php wp_reset_postdata(); ?>
			<div style="clear:both;"></div>
		</div>
		<?php
		$display = ob_get_clean();
	else:
		$display = sprintf( _x('No %s found', 'download post type name', 'edd'), edd_get_label_plural() );
	endif;
	return $display;
}
add_shortcode( 'downloads', 'edd_downloads_query' );


/**
 * Price Shortcode
 *
 * Shows the price of a download.
 *
 * @access      public
 * @since       1.1.3.3
 * @return      string
*/

function edd_download_price_shortcode( $atts, $content = null ) {
	
	extract( shortcode_atts( array(
			'id' => NULL,
		), $atts )
	);


	if( is_null( $id ) )
		$id = get_the_ID();

	return edd_price( $id, false );

}
add_shortcode( 'edd_price', 'edd_download_price_shortcode' );
<?php
/**
 * Shortcodes
 *
 * @package     EDD
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Purchase Link Shortcode
 *
 * Retrieves a download and displays the purchase form.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string Fully formatted purchase link
 */
function edd_download_shortcode( $atts, $content = null ) {
	global $post, $edd_options;

	extract( shortcode_atts( array(
			'id' 	=> $post->ID,
			'price' => '1',
			'text'	=> isset( $edd_options[ 'add_to_cart_text' ] )  && $edd_options[ 'add_to_cart_text' ]    != '' ? $edd_options[ 'add_to_cart_text' ] : __( 'Purchase', 'edd' ),
			'style' => isset( $edd_options[ 'button_style' ] ) 	 	? $edd_options[ 'button_style' ] 		: 'button',
			'color' => isset( $edd_options[ 'checkout_color' ] ) 	? $edd_options[ 'checkout_color' ] 		: 'blue',
			'class' => 'edd-submit'
		),
		$atts )
	);

	// Edd_get_purchase_link() expects the ID to be download_id since v1.3
	$atts['download_id'] = $atts['id'];

	$download = edd_get_download( $atts['download_id'] );

	if ( $download ) {
		return edd_get_purchase_link( $atts );
	}
}
add_shortcode( 'purchase_link', 'edd_download_shortcode' );

/**
 * Download History Shortcode
 *
 * Displays a user's download history.
 *
 * @since 1.0
 * @return string
 */
function edd_download_history() {
	if ( is_user_logged_in() ) {
		ob_start();
		// Download history short code was removed with EDD v1.6. It now shows same thing as [purchase_history]
		edd_get_template_part( 'history', 'purchases' );
		return ob_get_clean();
	}
}
add_shortcode( 'download_history', 'edd_download_history' );

/**
 * Purchase History Shortcode
 *
 * Displays a user's purchsae history.
 *
 * @since 1.0
 * @return string
 */
function edd_purchase_history() {
	if ( is_user_logged_in() ) {
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
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
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
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function edd_cart_shortcode( $atts, $content = null ) {
	return edd_shopping_cart();
}
add_shortcode( 'download_cart', 'edd_cart_shortcode' );

/**
 * Login Shortcode
 *
 * Shows a login form allowing users to users to log in. This function simply
 * calls the edd_login_form function to display the login form.
 *
 * @since 1.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @uses edd_login_form()
 * @return string
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
 * Displays a list of all the active discounts. The active discounts can be configured
 * from the Discount Codes admin screen.
 *
 * @since 1.0.8.2
 * @param array $atts Shortcode attributes
 * @param string $content
 * @uses edd_get_discounts()
 * @return string $discounts_lists List of all the active discount codes
 */
function edd_discounts_shortcode( $atts, $content = null ) {
	$discounts = edd_get_discounts();

	if ( ! $discounts && edd_has_active_discounts() )
		return;

	$discounts_list = '<ul id="edd_discounts_list">';

	foreach ( $discounts as $discount ) {
		if ( edd_is_discount_active( $discount->ID ) ) {
			$discounts_list .= '<li class="edd_discount">';

				$discounts_list .= '<span class="edd_discount_name">' . edd_get_discount_code( $discount->ID ) . '</span>';
				$discounts_list .= '<span class="edd_discount_separator"> - </span>';
				$discounts_list .= '<span class="edd_discount_amount">' . edd_format_discount_rate( edd_get_discount_type( $discount->ID ), edd_get_discount_amount( $discount->ID ) ) . '</span>';

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
 * Displays a collection purchase link for adding all items in a taxonomy term
 * to the cart.
 *
 * @since 1.0.6
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
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
 * This shortcodes uses the WordPress Query API to get downloads with the
 * arguments specified when using the shortcode. A list of the arguments
 * can be found from the EDD Dccumentation. The shortcode will take all the
 * paramaters and display the downloads queried in a valid HTML <div> tags.
 *
 * @since 1.0.6
 * @internal Incomplete shortcode
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string $display Output generated from the downloads queried
 */
function edd_downloads_query( $atts, $content = null ) {
	extract( shortcode_atts( array(
			'category'         => '',
			'exclude_category' => '',
			'tags'             => '',
			'exclude_tags'     => '',
			'relation'         => 'AND',
			'number'           => 10,
			'price'            => 'no',
			'excerpt'          => 'yes',
			'full_content'     => 'no',
			'buy_button'       => 'yes',
			'columns'          => 3,
			'thumbnails'       => 'true',
			'orderby'          => 'post_date',
			'order'            => 'DESC',
			'ids'              => ''
		), $atts )
	);

	$query = array(
		'post_type'      => 'download',
		'posts_per_page' => absint( $number ),
		'orderby'        => $orderby,
		'order'          => $order
	);

	switch ( $orderby ) {
		case 'price':
			$orderby           = 'meta_value';
			$query['meta_key'] = 'edd_price';
			$query['orderby']  = 'meta_value_num';
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

	if ( $tags || $category || $exclude_category || $exclude_tags ) {
		$query['tax_query'] = array(
			'relation'     => $relation
		);

		if ( $tags ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'download_tag',
				'terms'    => explode( ',', $tags ),
				'field'    => 'slug'
			);
		}

		if ( $category ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'download_category',
				'terms'    => explode( ',', $category ),
				'field'    => 'slug'
			);
		}

		if ( $exclude_category ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'download_category',
				'terms'    => explode( ',', $exclude_category ),
				'field'    => 'slug',
				'operator' => 'NOT IN',
			);
		}

		if ( $exclude_tags ) {
			$query['tax_query'][] = array(
				'taxonomy' => 'download_tag',
				'terms'    => explode( ',', $exclude_tags ),
				'field'    => 'slug',
				'operator' => 'NOT IN',
			);
		}
	}

	if( ! empty( $ids ) )
		$query['post__in'] = explode( ',', $ids );

	if ( get_query_var( 'paged' ) )
		$query['paged'] = get_query_var('paged');
	else if ( get_query_var( 'page' ) )
		$query['paged'] = get_query_var( 'page' );
	else
		$query['paged'] = 1;

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

	// Allow the query to be manipulated by other plugins
	$query = apply_filters( 'edd_downloads_query', $query );

	$downloads = new WP_Query( $query );
	if ( $downloads->have_posts() ) :
		$i = 1;
		ob_start(); ?>
		<div class="edd_downloads_list">
			<?php while ( $downloads->have_posts() ) : $downloads->the_post(); ?>
				<div itemscope itemtype="http://schema.org/Product" class="edd_download" id="edd_download_<?php echo get_the_ID(); ?>" style="width: <?php echo $column_width; ?>; float: left;">
					<div class="edd_download_inner">
						<?php

						do_action( 'edd_download_before' );

						if ( 'false' != $thumbnails ) :
							edd_get_template_part( 'shortcode', 'content-image' );
						endif;

						edd_get_template_part( 'shortcode', 'content-title' );

						if ( $excerpt == 'yes' && $full_content != 'yes' )
							edd_get_template_part( 'shortcode', 'content-excerpt' );
						else if ( $full_content == 'yes' )
							edd_get_template_part( 'shortcode', 'content-full' );

						if ( $price == 'yes' )
							edd_get_template_part( 'shortcode', 'content-price' );

						if ( $buy_button == 'yes' )
							edd_get_template_part( 'shortcode', 'content-cart-button' );

						do_action( 'edd_download_after' );

						?>
					</div>
				</div>
				<?php if ( $i % $columns == 0 ) { ?><div style="clear:both;"></div><?php } ?>
			<?php $i++; endwhile; ?>

			<div style="clear:both;"></div>

			<div id="edd_download_pagination" class="navigation">
				<?php
				$big = 999999;
				echo paginate_links( array(
					'base'    => str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
					'format'  => '?paged=%#%',
					'current' => max( 1, $query['paged'] ),
					'total'   => $downloads->max_num_pages
				) );
				?>
			</div>
			<?php wp_reset_postdata(); ?>
		</div>
		<?php
		$display = ob_get_clean();
	else:
		$display = sprintf( _x( 'No %s found', 'download post type name', 'edd' ), edd_get_label_plural() );
	endif;

	return apply_filters( 'downloads_shortcode', $display, $atts, $buy_button, $columns, $column_width, $downloads, $excerpt, $full_content, $price, $thumbnails, $query );
}
add_shortcode( 'downloads', 'edd_downloads_query' );

/**
 * Price Shortcode
 *
 * Shows the price of a download.
 *
 * @since 1.1.3.3
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function edd_download_price_shortcode( $atts, $content = null ) {
	extract( shortcode_atts( array(
			'id' => NULL,
		), $atts )
	);

	if ( is_null( $id ) )
		$id = get_the_ID();

	return edd_price( $id, false );
}
add_shortcode( 'edd_price', 'edd_download_price_shortcode' );

/**
 * Receipt Shortcode
 *
 * Shows an order receipt.
 *
 * @since 1.4
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string
 */
function edd_receipt_shortcode( $atts, $content = null ) {
	global $edd_receipt_args;

	$edd_receipt_args = shortcode_atts( array(
		'error'           => __( 'Sorry, trouble retrieving payment receipt.', 'edd' ),
		'price'           => true,
		'discount'        => true,
		'products'        => true,
		'date'            => true,
		'notes'           => true,
		'payment_key'     => true,
		'payment_method'  => true,
		'payment_id'      => true
	), $atts );

	$session = edd_get_purchase_session();
	if ( isset( $_GET[ 'payment_key' ] ) ) {
		$payment_key = urldecode( $_GET[ 'payment_key' ] );
	} else if ( $session ) {
		$payment_key = $session[ 'purchase_key' ];
	}

	// No key found
	if ( ! isset( $payment_key ) )
		return $edd_receipt_args[ 'error' ];

	$edd_receipt_args[ 'id' ] = edd_get_purchase_id_by_key( $payment_key );
	$user = edd_get_payment_meta_user_info( $edd_receipt_args[ 'id' ] );

	// Not the proper user
	if ( ( is_user_logged_in() && $user[ 'id' ] != get_current_user_id() ) || ( $user[ 'id' ] > 0 && ! is_user_logged_in() ) ) {
		return $edd_receipt_args[ 'error' ];
	}

	ob_start();

	edd_get_template_part( 'shortcode', 'receipt' );

	$display = ob_get_clean();

	return $display;
}
add_shortcode( 'edd_receipt', 'edd_receipt_shortcode' );

/**
 * Profile Editor Shortcode
 *
 * Outputs the EDD Profile Editor to allow users to amend their details from the
 * front-end. This function uses the EDD templating system allowing users to
 * override the default profile editor template. The profile editor template is located
 * under templates/profile-editor.php, however, it can be altered by creating a
 * file called profile-editor.php in the edd_template directory in your active theme's
 * folder. Please visit the EDD Documentation for more information on how the
 * templating system is used.
 *
 * @since 1.4
 * @author Sunny Ratilal
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return $display Output generated from the profile editor
 */
function edd_profile_editor_shortcode( $atts, $content = null ) {
	ob_start();

	edd_get_template_part( 'shortcode', 'profile-editor' );

	$display = ob_get_clean();

	return $display;
}
add_shortcode( 'edd_profile_editor', 'edd_profile_editor_shortcode' );

/**
 * Process Profile Updater Form
 *
 * Processes the profile updater form by updating the necessary fields
 *
 * @since 1.4
 * @author Sunny Ratilal
 * @param array $data Data sent from the profile editor
 * @return void
 */
function edd_process_profile_editor_updates( $data ) {
	// Profile field change request
	if ( empty( $_POST['edd_profile_editor_submit'] ) && !is_user_logged_in() )
		return false;

	// Nonce security
	if ( ! wp_verify_nonce( $data['edd_profile_editor_nonce'], 'edd-profile-editor-nonce' ) )
		return false;

	$user_id = get_current_user_id();

	$display_name = sanitize_text_field( $data['edd_display_name'] );
	$first_name   = sanitize_text_field( $data['edd_first_name'] );
	$last_name    = sanitize_text_field( $data['edd_last_name'] );
	$email        = sanitize_email( $data['edd_email'] );

	$userdata = array(
		'ID'           => $user_id,
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'display_name' => $display_name,
		'user_email'   => $email
	);

	// New password
	if ( ! empty( $data['edd_new_user_pass1'] ) ) {
		if ( $data['edd_new_user_pass1'] !== $data['edd_new_user_pass2'] ) {
			edd_set_error( 'password_mismatch', __( 'The passwords you entered do not match. Please try again.', 'edd' ) );
		} else {
			$userdata['user_pass'] = $data['edd_new_user_pass1'];
		}
	}

	// Update the user
	$updated = wp_update_user( $userdata );

	if ( $updated ) {
		do_action( 'edd_user_profile_updated', $user_id, $userdata );
		wp_redirect( add_query_arg( 'updated', 'true', $data['edd_redirect'] ) );
		edd_die();
	}
}
add_action( 'edd_edit_user_profile', 'edd_process_profile_editor_updates' );

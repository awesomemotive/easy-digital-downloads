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

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

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
			'price' => '1',
			'text'	=> isset( $edd_options[ 'add_to_cart_text' ] )  && $edd_options[ 'add_to_cart_text' ]    != '' ? $edd_options[ 'add_to_cart_text' ] : __( 'Purchase', 'edd' ),
			'style' => isset( $edd_options[ 'button_style' ] ) 	 	? $edd_options[ 'button_style' ] 		: 'button',
			'color' => isset( $edd_options[ 'checkout_color' ] ) 	? $edd_options[ 'checkout_color' ] 		: 'blue',
			'class' => 'edd-submit'
		),
		$atts )
	);

	// edd_get_purchase_link() expects the ID to be download_id since v1.3
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
 * @access      public
 * @since       1.0
 * @return      string
*/

function edd_download_history() {
	if ( is_user_logged_in() ) {
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

	if ( ! $discounts && edd_has_active_discounts() )
		return;

	$discounts_list = '<ul id="edd_discounts_list">';

	foreach ( $discounts as $discount ) {
		if ( edd_is_discount_valid( $discount['code'] ) ) {
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
			'order'            => 'DESC'
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
 * @access      public
 * @since       1.4
 * @return      string
 */
function edd_receipt_shortcode( $atts, $content = null ) {
	global $edd_receipt_args;

	$edd_receipt_args = shortcode_atts( array(
		'error'           => __( 'Sorry, trouble retrieving payment receipt.', 'edd' ),
		'price'           => true,
		'discount'        => true,
		'products'        => true,
		'date'            => true,
		'payment_key'     => true,
		'payment_method'  => true,
		'payment_id'      => true
	), $atts );

	$session = edd_get_purchase_session();

	if ( isset( $_GET[ 'purchase_key' ] ) ) {
		$purchase_key = urldecode( $_GET[ 'purchase_key' ] );
	} else if ( $session ) {
		$purchase_key = $session[ 'purchase_key' ];
	}

	// No key found
	if ( ! isset($purchase_key) )
		return $edd_receipt_args[ 'error' ];

	$edd_receipt_args[ 'id' ] = edd_get_purchase_id_by_key( $purchase_key );
	$user = edd_get_payment_meta_user_info( $edd_receipt_args[ 'id' ] );

	// Not the proper user
	if ( $user[ 'id' ] != get_current_user_id() ) {
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
 * Outputs the EDD Profile Editor to allow users to amend their details from the front-end
 *
 * @access      public
 * @since       1.4
 * @author      Sunny Ratilal
 */

function edd_profile_editor_shortcode( $atts, $content = null ) {
	global $current_user;

	ob_start();

	if ( is_user_logged_in() ):

		$user_id = get_current_user_id();
		$first_name = get_user_meta( $user_id, 'first_name', true );
		$last_name = get_user_meta( $user_id, 'last_name', true );
		$display_name = $current_user->display_name;

		if ( isset( $_GET['updated'] ) && $_GET['updated'] == true && ! edd_get_errors() ): ?>
		<p class="edd_success"><strong><?php _e( 'Success', 'edd'); ?>:</strong> <?php _e( 'Your profile has been edited successfully.', 'edd' ); ?></p>
		<?php endif; edd_print_errors(); ?>
		<form id="edd_profile_editor_form" class="edd_form" action="<?php echo edd_get_current_page_url(); ?>" method="post">
			<fieldset>
				<legend><?php _e( 'Change your Name', 'edd' ); ?></legend>
				<p id="edd_profile_name_wrap">
					<label for="edd_first_name"><?php _e( 'First Name', 'edd' ); ?></label>
					<input name="edd_first_name" id="edd_first_name" class="text edd-input" type="text" value="<?php echo $first_name; ?>" />
					<br />
					<label for="edd_last_name"><?php _e( 'Last Name', 'edd' ); ?></label>
					<input name="edd_last_name" id="edd_last_name" class="text edd-input" type="text" value="<?php echo $last_name; ?>" />
				</p>
				<p id="edd_profile_display_name_wrap">
					<label for="edd_display_name"><?php _e( 'Display Name', 'edd' ); ?></label>
					<select name="edd_display_name">
						<?php if ( ! empty( $current_user->first_name ) ): ?>
						<option <?php selected( $display_name, $current_user->first_name ); ?> value="<?php echo $current_user->first_name; ?>"><?php echo $current_user->first_name; ?></option>
						<?php endif; ?>
						<option <?php selected( $display_name, $current_user->user_nicename ); ?> value="<?php echo $current_user->user_nicename; ?>"><?php echo $current_user->user_nicename; ?></option>
						<?php if ( ! empty( $current_user->last_name ) ): ?>
						<option <?php selected( $display_name, $current_user->last_name ); ?> value="<?php echo $current_user->last_name; ?>"><?php echo $current_user->last_name; ?></option>
						<?php endif; ?>
						<?php if ( ! empty( $current_user->first_name ) && ! empty( $current_user->last_name ) ): ?>
						<option <?php selected( $display_name, $current_user->first_name . ' ' . $current_user->last_name ); ?> value="<?php echo $current_user->first_name . ' ' . $current_user->last_name; ?>"><?php echo $current_user->first_name . ' ' . $current_user->last_name; ?></option>
						<option <?php selected( $display_name, $current_user->last_name . ' ' . $current_user->first_name ); ?> value="<?php echo $current_user->last_name . ' ' . $current_user->first_name; ?>"><?php echo $current_user->last_name . ' ' . $current_user->first_name; ?></option>
						<?php endif; ?>
					</select>
				</p>
				<p>
					<label for="edd_email"><?php _e( 'Email Address', 'edd' ); ?></label>
					<input name="edd_email" id="edd_email" class="text edd-input required" type="email" value="<?php echo $current_user->user_email; ?>" />
				</p>
				<legend><?php _e( 'Change your Password', 'edd' ); ?></legend>
				<p id="edd_profile_password_wrap">
					<label for="edd_user_pass"><?php _e( 'New Password', 'edd' ); ?></label>
					<input name="edd_new_user_pass1" id="edd_new_user_pass1" class="password edd-input" type="password"/>
					<br />
					<label for="edd_user_pass"><?php _e( 'Re-enter Password', 'edd' ); ?></label>
					<input name="edd_new_user_pass2" id="edd_new_user_pass2" class="password edd-input" type="password"/>
				</p>
				<p class="edd_password_change_notice"><?php _e( 'Please note after changing your password, you must log back in.', 'edd' ); ?></p>
				<p id="edd_profile_submit_wrap">
					<input type="hidden" name="edd_profile_editor_nonce" value="<?php echo wp_create_nonce( 'edd-profile-editor-nonce' ); ?>"/>
					<input type="hidden" name="edd_action" value="edit_user_profile" />
					<input type="hidden" name="edd_redirect" value="<?php echo esc_url( edd_get_current_page_url() ); ?>" />
					<input name="edd_profile_editor_submit" id="edd_profile_editor_submit" type="submit" class="edd_submit" value="<?php _e( 'Save Changes', 'edd' ); ?>"/>
				</p>
			</fieldset>
		</form><!-- #edd_profile_editor_form -->
		<?php
	else:
		echo '<p>' . __( 'You need to login to edit your profile.', 'edd' ) . '</p>';
		wp_login_form( array(
			'echo'           => true,
			'redirect'       => edd_get_current_page_url(),
			'form_id'        => 'edd_login_form',
			'label_username' => __( 'Username', 'edd' ),
			'label_password' => __( 'Password', 'edd' ),
			'label_remember' => __( 'Remember me?', 'edd' ),
			'label_log_in'   => __( 'Log In', 'edd' ),
			'id_username'    => 'edd_user_login',
			'id_password'    => 'edd_user_pass',
			'id_remember'    => 'edd_remember_me',
			'id_submit'      => 'edd_login_form_submit',
			'remember'       => true
		) );
	endif;

	$display = ob_get_clean();

	return $display;
}
add_shortcode( 'edd_profile_editor', 'edd_profile_editor_shortcode' );


/**
 * Process Profile Updater Form
 *
 * Processes the profile updater form by updating the necessary fields
 *
 * @access      private
 * @since       1.4
 * @author      Sunny Ratilal
*/

function edd_process_profile_editor_updates( $data ) {
	if ( $_SERVER['REQUEST_METHOD'] == 'POST' && isset( $data['edd_profile_editor_submit'] ) && wp_verify_nonce( $data['edd_profile_editor_nonce'], 'edd-profile-editor-nonce' ) && is_user_logged_in() ) {
		$user_id = get_current_user_id();

		if ( ! empty( $data['edd_first_name'] ) || ! empty( $data['edd_last_name'] ) ) {
			$first_name = sanitize_text_field( $data['edd_first_name'] );
			$last_name =  sanitize_text_field( $data['edd_last_name'] );
		}

		if ( ! empty( $data['edd_display_name'] ) ) {
			$display_name = sanitize_text_field( $data['edd_display_name'] );
		}

		if ( ! empty( $data['edd_email'] ) ) {
			$email = sanitize_email( $data['edd_email'] );
		}

		if ( ! empty( $data['edd_new_user_pass1'] ) && ! empty( $data['edd_new_user_pass2'] ) ) {
			if ( $data['edd_new_user_pass1'] !== $data['edd_new_user_pass2'] ) {
				edd_set_error( 'password_mismatch', __( 'The passwords you entered do not match. Please try again.', 'edd' ) );
			} else {
				wp_set_password( $data['edd_new_user_pass1'], $user_id );
				$updated = true;
			}
		}

		// Update user
		$updated = wp_update_user( array(
			'ID'           => $user_id,
			'first_name'   => $first_name,
			'last_name'    => $last_name,
			'display_name' => $display_name,
			'user_email'   => $email
		) );

		if( $updated ) {
			wp_redirect( add_query_arg( 'updated', 'true', $data['edd_redirect'] ) );
			exit;
		}
	} else {
		wp_die( __( 'Security check failed. Please try again.', 'edd' ), __( 'Security Check Failed', 'edd' ) );
		exit;
	}
}
add_action( 'edd_edit_user_profile', 'edd_process_profile_editor_updates' );
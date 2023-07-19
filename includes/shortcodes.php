<?php
/**
 * Shortcodes
 *
 * @package     EDD
 * @subpackage  Shortcodes
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

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
	global $post;

	$post_id = is_object( $post ) ? $post->ID : 0;

	$atts = shortcode_atts( array(
		'id'       => $post_id,
		'price_id' => isset( $atts['price_id'] ) ? $atts['price_id'] : false,
		'sku'      => '',
		'price'    => '1',
		'direct'   => '0',
		'text'     => '',
		'style'    => edd_get_option( 'button_style', 'button' ),
		'color'    => edd_get_button_color_class(),
		'class'    => 'edd-submit',
		'form_id'  => '',
	),
	$atts, 'purchase_link' );

	// Override text only if not provided / empty
	if ( ! $atts['text'] ) {
		if( $atts['direct'] == '1' || $atts['direct'] == 'true' ) {
			$atts['text'] = edd_get_option( 'buy_now_text', __( 'Buy Now', 'easy-digital-downloads' ) );
		} else {
			$atts['text'] = edd_get_option( 'add_to_cart_text', __( 'Purchase', 'easy-digital-downloads' ) );
		}
	}

	if( ! empty( $atts['sku'] ) ) {

		$download = edd_get_download_by( 'sku', $atts['sku'] );

		if ( $download ) {
			$atts['download_id'] = $download->ID;
		}

	} elseif( isset( $atts['id'] ) ) {

		// Edd_get_purchase_link() expects the ID to be download_id since v1.3
		$atts['download_id'] = $atts['id'];

		$download = edd_get_download( $atts['download_id'] );

	}

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

		if( ! edd_user_pending_verification() ) {

			edd_get_template_part( 'history', 'downloads' );

		} else {

			edd_get_template_part( 'account', 'pending' );

		}

		return ob_get_clean();
	}
}
add_shortcode( 'download_history', 'edd_download_history' );

/**
 * Purchase History Shortcode
 *
 * Displays a user's purchase history.
 *
 * @since 1.0
 * @return string
 */
function edd_purchase_history() {
	ob_start();

	if( ! edd_user_pending_verification() ) {

		edd_get_template_part( 'history', 'purchases' );

	} else {

		edd_get_template_part( 'account', 'pending' );

	}

	return ob_get_clean();
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
	$redirect = '';

	extract( shortcode_atts( array(
		'redirect' => $redirect
	), $atts, 'edd_login' ) );

	if ( empty( $redirect ) ) {
		$login_redirect_page = edd_get_option( 'login_redirect_page', '' );

		if ( ! empty( $login_redirect_page ) ) {
			$redirect = get_permalink( $login_redirect_page );
		}
	}

	if ( empty( $redirect ) ) {
		$purchase_history = edd_get_option( 'purchase_history_page', 0 );

		if ( ! empty( $purchase_history ) ) {
			$redirect = get_permalink( $purchase_history );
		}
	}

	if ( empty( $redirect ) ) {
		$redirect = home_url();
	}

	return edd_login_form( $redirect );
}
add_shortcode( 'edd_login', 'edd_login_form_shortcode' );

/**
 * Register Shortcode
 *
 * Shows a registration form allowing users to users to register for the site
 *
 * @since 2.0
 * @param array $atts Shortcode attributes
 * @param string $content
 * @uses edd_register_form()
 * @return string
 */
function edd_register_form_shortcode( $atts, $content = null ) {
	$redirect         = home_url();
	$purchase_history = edd_get_option( 'purchase_history_page', 0 );

	if ( ! empty( $purchase_history ) ) {
		$redirect = get_permalink( $purchase_history );
	}

	extract( shortcode_atts( array(
		'redirect' => $redirect
	), $atts, 'edd_register' ) );

	return edd_register_form( $redirect );
}
add_shortcode( 'edd_register', 'edd_register_form_shortcode' );

/**
 * Discounts shortcode
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

	$discounts_list = '<ul id="edd_discounts_list">';

	if ( ! empty( $discounts ) && edd_has_active_discounts() ) {

		foreach ( $discounts as $discount ) {

			if ( edd_is_discount_active( $discount->id ) ) {
				$discounts_list .= '<li class="edd_discount">';
					$discounts_list .= '<span class="edd_discount_name">' . edd_get_discount_code( $discount->id ) . '</span>';
					$discounts_list .= '<span class="edd_discount_separator"> - </span>';
					$discounts_list .= '<span class="edd_discount_amount">' . edd_format_discount_rate( edd_get_discount_type( $discount->id ), edd_get_discount_amount( $discount->id ) ) . '</span>';
				$discounts_list .= '</li>';
			}
		}

	} else {
		$discounts_list .= '<li class="edd_discount">' . __( 'No discounts found', 'easy-digital-downloads' ) . '</li>';
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
		'terms'    => '',
		'text'     => __( 'Purchase All Items', 'easy-digital-downloads' ),
		'style'    => edd_get_option( 'button_style', 'button' ),
		'color'    => edd_get_button_color_class(),
		'class'    => 'edd-submit',
	), $atts, 'purchase_collection' ) );

	$button_display = implode( ' ', array_filter( array( $style, $color, $class ) ) );

	return '<a href="' . esc_url( add_query_arg( array( 'edd_action' => 'purchase_collection', 'taxonomy' => sanitize_key( $taxonomy ), 'terms' => sanitize_key( $terms ) ) ) ) . '" class="' . esc_attr( $button_display ) . '">' . esc_html( $text ) . '</a>';
}
add_shortcode( 'purchase_collection', 'edd_purchase_collection_shortcode' );

/**
 * Downloads Shortcode
 *
 * This shortcodes uses the WordPress Query API to get downloads with the
 * arguments specified when using the shortcode. A list of the arguments
 * can be found from the EDD Documentation. The shortcode will take all the
 * parameters and display the downloads queried in a valid HTML <div> tags.
 *
 * @since 1.0.6
 * @internal Incomplete shortcode
 * @param array $atts Shortcode attributes
 * @param string $content
 * @return string $display Output generated from the downloads queried
 */
function edd_downloads_query( $atts, $content = null ) {
	$atts = shortcode_atts( array(
		'category'         => '',
		'exclude_category' => '',
		'tags'             => '',
		'exclude_tags'     => '',
		'author'           => false,
		'relation'         => 'OR',
		'number'           => 9,
		'price'            => 'no',
		'excerpt'          => 'yes',
		'full_content'     => 'no',
		'buy_button'       => 'yes',
		'columns'          => 3,
		'thumbnails'       => 'true',
		'orderby'          => 'post_date',
		'order'            => 'DESC',
		'ids'              => '',
		'class'            => '',
		'pagination'       => 'true',
	), $atts, 'downloads' );

	$query = array(
		'post_type' => 'download',
		'orderby'   => $atts['orderby'],
		'order'     => $atts['order']
	);

	if ( filter_var( $atts['pagination'], FILTER_VALIDATE_BOOLEAN ) || ( ! filter_var( $atts['pagination'], FILTER_VALIDATE_BOOLEAN ) && $atts[ 'number' ] ) ) {

		$query['posts_per_page'] = (int) $atts['number'];

		if ( $query['posts_per_page'] < 0 ) {
			$query['posts_per_page'] = abs( $query['posts_per_page'] );
		}
	} else {
		$query['nopaging'] = true;
	}

	if( 'random' == $atts['orderby'] ) {
		$atts['pagination'] = false;
	}

	switch ( $atts['orderby'] ) {
		case 'price':
			$atts['orderby']   = 'meta_value';
			$query['meta_key'] = 'edd_price';
			$query['orderby']  = 'meta_value_num';
		break;

		case 'sales':
			$atts['orderby']   = 'meta_value';
			$query['meta_key'] = '_edd_download_sales';
			$query['orderby']  = 'meta_value_num';
		break;

		case 'earnings':
			$atts['orderby']   = 'meta_value';
			$query['meta_key'] = '_edd_download_earnings';
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

		case 'post__in':
			$query['orderby'] = 'post__in';
		break;

		default:
			$query['orderby'] = 'post_date';
		break;
	}

	if ( $atts['tags'] || $atts['category'] || $atts['exclude_category'] || $atts['exclude_tags'] ) {

		$query['tax_query'] = array(
			'relation' => $atts['relation']
		);

		if ( $atts['tags'] ) {

			$tag_list = explode( ',', $atts['tags'] );

			foreach( $tag_list as $tag ) {

				$t_id  = (int) $tag;
				$is_id = is_int( $t_id ) && ! empty( $t_id );

				if( $is_id ) {

					$term_id = $tag;

				} else {

					$term = get_term_by( 'slug', $tag, 'download_tag' );

					if( ! $term ) {
						continue;
					}

					$term_id = $term->term_id;
				}

				$query['tax_query'][] = array(
					'taxonomy' => 'download_tag',
					'field'    => 'term_id',
					'terms'    => $term_id
				);
			}

		}

		if ( $atts['category'] ) {

			$categories = explode( ',', $atts['category'] );

			foreach( $categories as $category ) {

				$t_id  = (int) $category;
				$is_id = is_int( $t_id ) && ! empty( $t_id );

				if( $is_id ) {

					$term_id = $category;

				} else {

					$term = get_term_by( 'slug', $category, 'download_category' );

					if( ! $term ) {
						continue;
					}

					$term_id = $term->term_id;

				}

				$query['tax_query'][] = array(
					'taxonomy' => 'download_category',
					'field'    => 'term_id',
					'terms'    => $term_id,
				);

			}

		}

		if ( $atts['exclude_category'] ) {

			$categories = explode( ',', $atts['exclude_category'] );

			foreach( $categories as $category ) {

				$t_id  = (int) $category;
				$is_id = is_int( $t_id ) && ! empty( $t_id );

				if( $is_id ) {

					$term_id = $category;

				} else {

					$term = get_term_by( 'slug', $category, 'download_category' );

					if( ! $term ) {
						continue;
					}

					$term_id = $term->term_id;
				}

				$query['tax_query'][] = array(
					'taxonomy' => 'download_category',
					'field'    => 'term_id',
					'terms'    => $term_id,
					'operator' => 'NOT IN'
				);
			}

		}

		if ( $atts['exclude_tags'] ) {

			$tag_list = explode( ',', $atts['exclude_tags'] );

			foreach( $tag_list as $tag ) {

				$t_id  = (int) $tag;
				$is_id = is_int( $t_id ) && ! empty( $t_id );

				if( $is_id ) {

					$term_id = $tag;

				} else {

					$term = get_term_by( 'slug', $tag, 'download_tag' );

					if( ! $term ) {
						continue;
					}

					$term_id = $term->term_id;
				}

				$query['tax_query'][] = array(
					'taxonomy' => 'download_tag',
					'field'    => 'term_id',
					'terms'    => $term_id,
					'operator' => 'NOT IN'
				);

			}

		}
	}

	if ( $atts['exclude_tags'] || $atts['exclude_category'] ) {
		$query['tax_query']['relation'] = 'AND';
	}

	if ( $atts['author'] ) {
		$authors = explode( ',', $atts['author'] );
		if ( ! empty( $authors ) ) {
			$author_ids = array();
			$author_names = array();

			foreach ( $authors as $author ) {
				if ( is_numeric( $author ) ) {
					$author_ids[] = $author;
				} else {
					$user = get_user_by( 'login', $author );
					if ( $user ) {
						$author_ids[] = $user->ID;
					}
				}
			}

			if ( ! empty( $author_ids ) ) {
				$author_ids      = array_unique( array_map( 'absint', $author_ids ) );
				$query['author'] = implode( ',', $author_ids );
			}
		}
	}

	if( ! empty( $atts['ids'] ) ) {
		$query['post__in'] = explode( ',', $atts['ids'] );
	}

	if ( get_query_var( 'paged' ) ) {
		$query['paged'] = get_query_var('paged');
	} else if ( get_query_var( 'page' ) ) {
		$query['paged'] = get_query_var( 'page' );
	} else {
		$query['paged'] = 1;
	}

	// Allow the query to be manipulated by other plugins
	$query = apply_filters( 'edd_downloads_query', $query, $atts );

	$downloads = new WP_Query( $query );

	do_action( 'edd_downloads_list_before', $atts );

	// Ensure buttons are not appended to content.
	remove_filter( 'the_content', 'edd_after_download_content' );

	ob_start();


	if ( $downloads->have_posts() ) :
		$i = 1;
		$columns_class   = array( 'edd_download_columns_' . $atts['columns'] );
		$custom_classes  = array_filter( explode( ',', $atts['class'] ) );
		$wrapper_classes = array_unique( array_merge( $columns_class, $custom_classes ) );
		$wrapper_classes = implode( ' ', $wrapper_classes );
	?>

		<div class="edd_downloads_list <?php echo apply_filters( 'edd_downloads_list_wrapper_class', $wrapper_classes, $atts ); ?>">

			<?php do_action( 'edd_downloads_list_top', $atts, $downloads ); ?>

			<?php while ( $downloads->have_posts() ) : $downloads->the_post(); ?>
				<?php do_action( 'edd_download_shortcode_item', $atts, $i ); ?>
			<?php $i++; endwhile; ?>

			<?php wp_reset_postdata(); ?>

			<?php do_action( 'edd_downloads_list_bottom', $atts ); ?>

		</div>

		<?php

	else :
		/* translators: plural download label */
		printf( _x( 'No %s found', 'download post type name', 'easy-digital-downloads' ), esc_html( edd_get_label_plural() ) );
	endif;

	do_action( 'edd_downloads_list_after', $atts, $downloads, $query );

	$display = ob_get_clean();

	// Ensure buttons are appended to content.
	add_filter( 'the_content', 'edd_after_download_content' );

	return apply_filters( 'downloads_shortcode', $display, $atts, $atts['buy_button'], $atts['columns'], '', $downloads, $atts['excerpt'], $atts['full_content'], $atts['price'], $atts['thumbnails'], $query );
}
add_shortcode( 'downloads', 'edd_downloads_query' );
add_shortcode( 'edd_downloads', 'edd_downloads_query' );

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
		'id'       => null,
		'price_id' => false,
	), $atts, 'edd_price' ) );

	if ( is_null( $id ) ) {
		$id = get_the_ID();
	}

	return edd_price( $id, false, $price_id );
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
		'error'          => __( 'Sorry, trouble retrieving order receipt.', 'easy-digital-downloads' ),
		'price'          => true,
		'discount'       => true,
		'products'       => true,
		'date'           => true,
		'notes'          => true,
		'payment_key'    => false,
		'payment_method' => true,
		'payment_id'     => true,
	), $atts, 'edd_receipt' );

	$session = edd_get_purchase_session();

	if ( isset( $_GET['payment_key'] ) ) {
		$payment_key = urldecode( $_GET['payment_key'] );
	} elseif ( ! empty( $_GET['order'] ) && ! empty( $_GET['id'] ) ) {
		$payment_key = edd_get_payment_key( absint( $_GET['id'] ) );
	} elseif ( $session ) {
		$payment_key = $session['purchase_key'];
	} elseif ( $edd_receipt_args['payment_key'] ) {
		$payment_key = $edd_receipt_args['payment_key'];
	}

	// No key found
	if ( ! isset( $payment_key ) ) {
		return '<p class="edd-alert edd-alert-error">' . $edd_receipt_args['error'] . '</p>';
	}

	$order         = edd_get_order_by( 'payment_key', $payment_key );
	$user_can_view = edd_can_view_receipt( $order );

	// Key was provided, but user is logged out. Offer them the ability to login and view the receipt
	if ( ! $user_can_view && ! empty( $payment_key ) && ! is_user_logged_in() && ! edd_is_guest_payment( $order ) ) {
		global $edd_login_redirect;
		$edd_login_redirect = edd_get_receipt_page_uri( $order->id );

		ob_start();

		echo '<p class="edd-alert edd-alert-warn">' . __( 'You must be logged in to view this payment receipt.', 'easy-digital-downloads' ) . '</p>';
		edd_get_template_part( 'shortcode', 'login' );

		$login_form = ob_get_clean();

		return $login_form;
	}

	$user_can_view = apply_filters( 'edd_user_can_view_receipt', $user_can_view, $edd_receipt_args );

	// If this was a guest checkout and the purchase session is empty, output a relevant error message
	if ( empty( $session ) && ! is_user_logged_in() && ! $user_can_view ) {
		return '<p class="edd-alert edd-alert-error">' . apply_filters( 'edd_receipt_guest_error_message', __( 'Receipt could not be retrieved, your purchase session has expired.', 'easy-digital-downloads' ) ) . '</p>';
	}

	/*
	 * Check if the user has permission to view the receipt
	 *
	 * If user is logged in, user ID is compared to user ID of ID stored in payment meta
	 *
	 * Or if user is logged out and purchase was made as a guest, the purchase session is checked for
	 *
	 * Or if user is logged in and the user can view sensitive shop data
	 */

	if ( ! $user_can_view ) {
		return '<p class="edd-alert edd-alert-error">' . $edd_receipt_args['error'] . '</p>';
	}

	ob_start();

	edd_get_template_part( 'shortcode', 'receipt' );

	$display = ob_get_clean();

	return $display;
}
add_shortcode( 'edd_receipt', 'edd_receipt_shortcode' );

/**
 * Render the profile editor shortcode.
 *
 * @since 1.4
 *
 * @param null $atts    Unused parameter.
 * @param null $content Unused parameter.
 *
 * @return string Shortcode template.
 */
function edd_profile_editor_shortcode( $atts = null, $content = null ) {
	ob_start();

	if ( ! edd_user_pending_verification() ) {
		edd_get_template_part( 'shortcode', 'profile-editor' );
	} else {
		edd_get_template_part( 'account', 'pending' );
	}

	$display = ob_get_clean();

	return $display;
}
add_shortcode( 'edd_profile_editor', 'edd_profile_editor_shortcode' );

/**
 * Process profile updates.
 *
 * @since 1.4
 * @since 3.0 Updated to use new custom tables.
 *
 * @param array $data Data sent from the profile editor.
 * @return bool False on error.
 */
function edd_process_profile_editor_updates( $data ) {

	// Profile field change request.
	if ( empty( $data['edd_profile_editor_submit'] ) && ! is_user_logged_in() ) {
		return false;
	}

	// Pending users can't edit their profile.
	if ( edd_user_pending_verification() ) {
		return false;
	}

	// Verify nonce.
	if ( empty( $data['edd_profile_editor_nonce'] ) || ! wp_verify_nonce( $data['edd_profile_editor_nonce'], 'edd-profile-editor-nonce' ) ) {
		return false;
	}

	$user_id       = get_current_user_id();
	$old_user_data = get_userdata( $user_id );

	// Fetch customer record.
	$customer = edd_get_customer_by( 'user_id', $user_id );
	if ( ! empty( $customer->user_id ) && $customer->user_id != $user_id ) { // phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
		edd_set_error( 'customer_mismatch', __( 'Your profile could not be updated. Please contact a site administrator.', 'easy-digital-downloads' ) );
	}

	$display_name = isset( $data['edd_display_name'] )    ? sanitize_text_field( $data['edd_display_name'] )    : $old_user_data->display_name;
	$first_name   = isset( $data['edd_first_name'] )      ? sanitize_text_field( $data['edd_first_name'] )      : $old_user_data->first_name;
	$last_name    = isset( $data['edd_last_name'] )       ? sanitize_text_field( $data['edd_last_name'] )       : $old_user_data->last_name;
	$email        = isset( $data['edd_email'] )           ? sanitize_email( $data['edd_email'] )                : $old_user_data->user_email;
	$line1        = isset( $data['edd_address_line1'] )   ? sanitize_text_field( $data['edd_address_line1'] )   : '';
	$line2        = isset( $data['edd_address_line2'] )   ? sanitize_text_field( $data['edd_address_line2'] )   : '';
	$city         = isset( $data['edd_address_city'] )    ? sanitize_text_field( $data['edd_address_city'] )    : '';
	$state        = isset( $data['edd_address_state'] )   ? sanitize_text_field( $data['edd_address_state'] )   : '';
	$zip          = isset( $data['edd_address_zip'] )     ? sanitize_text_field( $data['edd_address_zip'] )     : '';
	$country      = isset( $data['edd_address_country'] ) ? sanitize_text_field( $data['edd_address_country'] ) : '';

	$userdata = array(
		'ID'           => $user_id,
		'first_name'   => $first_name,
		'last_name'    => $last_name,
		'display_name' => $display_name,
		'user_email'   => $email,
	);

	$address = array(
		'line1'   => $line1,
		'line2'   => $line2,
		'city'    => $city,
		'state'   => $state,
		'zip'     => $zip,
		'country' => $country,
	);

	do_action( 'edd_pre_update_user_profile', $user_id, $userdata );

	// New password
	if ( ! empty( $data['edd_new_user_pass1'] ) ) {
		if ( $data['edd_new_user_pass1'] !== $data['edd_new_user_pass2'] ) {
			edd_set_error( 'password_mismatch', __( 'The passwords you entered do not match. Please try again.', 'easy-digital-downloads' ) );
		} else {
			$userdata['user_pass'] = $data['edd_new_user_pass1'];
		}
	}

	// Make sure the new email doesn't belong to another user.
	if ( $email !== $old_user_data->user_email ) {

		// Make sure the new email is valid.
		if ( ! is_email( $email ) ) {
			edd_set_error( 'email_invalid', __( 'The email you entered is invalid. Please enter a valid email.', 'easy-digital-downloads' ) );
		}

		$customers = edd_get_customers(
			array(
				'email'           => $email,
				'user_id__not_in' => array( $user_id ),
			)
		);
		// Make sure the new email doesn't belong to another user.
		if ( email_exists( $email ) || ! empty( $customers ) ) {
			edd_set_error( 'email_exists', __( 'This email address is not available.', 'easy-digital-downloads' ) );
		}
	}

	// Check for errors.
	$errors = edd_get_errors();

	// Send back to the profile editor if there are errors.
	if ( ! empty( $errors ) ) {
		if ( ! empty( $data['edd_redirect'] ) ) {
			edd_redirect( $data['edd_redirect'] );
		}
		return false;
	}

	// Update user.
	$updated = wp_update_user( $userdata );

	// If the current user does not have an associated customer record, create one so that all of the customer's data is stored.
	if ( ! $customer && $updated ) {
		$customer_id = edd_add_customer(
			array(
				'user_id' => $updated,
				'email'   => $email,
			)
		);

		$customer = edd_get_customer_by( 'id', $customer_id );
	}

	// Try to update customer data.
	if ( $customer ) {

		// Update the primary address.
		$customer_address_id = edd_get_customer_addresses( array(
			'customer_id' => $customer->id,
			'type'        => 'billing',
			'is_primary'  => 1,
			'number'      => 1,
			'fields'      => 'ids',
		) );

		// Try updating the address if it exists.
		if ( ! empty( $customer_address_id ) ) {
			$customer_address_id = $customer_address_id[0];

			edd_update_customer_address( $customer_address_id, array(
				'name'        => stripslashes( $first_name . ' ' . $last_name ),
				'address'     => $address['line1'],
				'address2'    => $address['line2'],
				'city'        => $address['city'],
				'country'     => $address['country'],
				'region'      => $address['state'],
				'postal_code' => $address['zip'],
				'country'     => $address['country']
			) );

		// Add a customer address.
		} else {
			edd_maybe_add_customer_address(
				$customer->id,
				array(
					'name'        => stripslashes( $first_name . ' ' . $last_name ),
					'type'        => 'billing',
					'address'     => $address['line1'],
					'address2'    => $address['line2'],
					'city'        => $address['city'],
					'country'     => $address['country'],
					'region'      => $address['state'],
					'postal_code' => $address['zip'],
					'country'     => $address['country'],
					'is_primary'  => true,
				)
			);
		}

		if ( $customer->email === $email || ( is_array( $customer->emails ) && in_array( $email, $customer->emails ) ) ) {
			$customer->set_primary_email( $email );
		}

		$update_args = array(
			'name'  => stripslashes( $first_name . ' ' . $last_name ),
		);

		$customer->update( $update_args );
	}

	if ( $updated ) {
		do_action( 'edd_user_profile_updated', $user_id, $userdata );

		edd_redirect( add_query_arg( 'updated', 'true', $data['edd_redirect'] ) );
	}
}
add_action( 'edd_edit_user_profile', 'edd_process_profile_editor_updates' );

/**
 * Process the 'remove' URL on the profile editor when customers wish to remove an email address.
 *
 * @since  2.6
 * @param array $data The array of data passed from the profile editor.
 * @return void
 */
function edd_process_profile_editor_remove_email( $data ) {
	if ( ! is_user_logged_in() ) {
		return;
	}

	// Pending users can't edit their profile
	if ( edd_user_pending_verification() ) {
		return;
	}

	// Nonce security
	if ( ! wp_verify_nonce( $data['_wpnonce'], 'edd-remove-customer-email' ) ) {
		return;
	}

	if ( empty( $data['email'] ) || ! is_email( $data['email'] ) ) {
		return;
	}

	$user_id  = get_current_user_id();
	$customer = new EDD_Customer( $user_id, true );

	if ( $customer->user_id == $user_id && $customer->remove_email( $data['email'] ) ) {

		$url = add_query_arg( 'updated', true, $data['redirect'] );

		$user       = wp_get_current_user();
		$user_login = ! empty( $user->user_login ) ? $user->user_login : edd_get_bot_name();
		/* translators: 1. email address; 2. user who removed email. */
		$customer_note = sprintf( __( 'Email address %1$s removed by %2$s', 'easy-digital-downloads' ), sanitize_email( $data['email'] ), $user_login );
		$customer->add_note( $customer_note );

	} else {
		edd_set_error( 'profile-remove-email-failure', __( 'Error removing email address from profile. Please try again later.', 'easy-digital-downloads' ) );
		$url = $data['redirect'];
	}

	edd_redirect( $url );
}
add_action( 'edd_profile-remove-email', 'edd_process_profile_editor_remove_email' );

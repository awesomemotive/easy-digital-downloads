<?php
/**
 * Template Functions
 *
 * @package     Easy Digital Downloads
 * @subpackage  Template Functions
 * @copyright   Copyright ( c ) 2012, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0 
*/


/**
 * Append Purchase Link
 *
 * Automatically appends the purchase link to download content, if enabled.
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_append_purchase_link( $download_id ) {
	
	if ( ! get_post_meta( $download_id, '_edd_hide_purchase_link', true ) ) {			
		echo edd_get_purchase_link( $download_id );
	}
		
}
add_action( 'edd_after_download_content', 'edd_append_purchase_link' );


/**
 * Get Purchase Link
 *
 * Returns the purchase link.
 *
 * @access      public
 * @since       1.0 
 * @return      string
*/

function edd_get_purchase_link( $download_id = null, $link_text = null, $style = null, $color = null, $class = '' ) {

	global $edd_options, $post, $user_ID;

	if ( ! isset( $edd_options['purchase_page'] ) || $edd_options['purchase_page'] == 0 ) {
		edd_set_error( 'set_checkout', sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-settings' ) ) );
		edd_print_errors();
		return false;
	}
	
	$page = get_permalink( $post->ID ); // current page
	$link_args = array( 'download_id' => $download_id, 'edd_action' => 'add_to_cart' );
	$link = add_query_arg( $link_args, $page );
	$checkout_url = edd_get_checkout_uri();
	$variable_pricing = edd_has_variable_prices( $download_id );
	
	if ( is_null( $link_text ) ) {
		$link_text = isset( $edd_options['add_to_cart_text'] ) ? $edd_options['add_to_cart_text'] : __( 'Purchase', 'edd' );
	}
	
	if ( is_null( $style ) ) {
		$style = isset( $edd_options['button_style'] ) ? $edd_options['button_style'] : 'button';
	}
	
	if ( is_null( $color ) ) {		
		$color = isset( $edd_options['checkout_color'] ) ? $edd_options['checkout_color'] : 'blue';
	}
	
	$purchase_form = '<form id="edd_purchase_' . $download_id . '" class="edd_download_purchase_form" method="POST">';
		
		if ( $variable_pricing ) {
			$prices = edd_get_variable_prices( $download_id );
			$purchase_form .= '<div class="edd_price_options">';
				if ( $prices ) {
					foreach( $prices as $key => $price ) {
						$checked = '';
						
						if ( $key == 0 ) {
							$checked = 'checked="checked"';
						}
												
						$purchase_form .= sprintf( '<input type="radio" %1$s name="edd_options[price_id]" id="%2$s" class="%3$s" value="%4$s"/>&nbsp;',
							$checked,
							esc_attr( 'edd_price_option_' . $download_id . '_' . $key ),
							esc_attr( 'edd_price_option_' . $download_id ),
							esc_attr( $key )
						);

						$purchase_form .= sprintf( '<label for="%1$s">%2$s</label><br/>',
							esc_attr( 'edd_price_option_' . $download_id . '_' . $key ),
							esc_html( $price['name'] . ' - ' . edd_currency_filter( $price['amount'] ) )
						);
						
					}
				}
			$purchase_form .= '</div><!--end .edd_price_options-->';
		}
		
		$purchase_form .= '<div class="edd_purchase_submit_wrapper">';
		
			$data_variable = $variable_pricing ? ' data-variable-price="yes"' : '';
			
			if ( edd_item_in_cart( $download_id ) ) {
				$button_display = 'style="display:none;"';
				$checkout_display = '';
			} else {
				$button_display = '';
				$checkout_display = 'style="display:none;"';
			}
			
			if ( $style == 'button' ) {
				
				$purchase_button = sprintf( '<span class="%1$s" %2$s>',
					esc_attr( 'edd_button edd_add_to_cart_wrap edd_' . $color ),
					$button_display
				);
					$purchase_button .= '<span class="edd_button_outer">';
						$purchase_button .= '<span class="edd_button_inner">';
							$purchase_button .= sprintf( '<input type="submit" class="%1$s" name="edd_purchase_download" value="%2$s" data-action="edd_add_to_cart" data-download-id="%3$s" %4$s/>',
								esc_attr( 'edd_button_text edd-submit edd-add-to-cart ' . $class ),
								esc_attr( $link_text ),
								esc_attr( $download_id ),
								$data_variable
							 );
						$purchase_button .= '</span>';
					$purchase_button .= '</span>';
				$purchase_button .= '</span>';
				
				$checkout_link = sprintf( '<a href="%1$s" class="%2$s" %3$s>',
					esc_url( $checkout_url ),
					esc_attr( 'edd_go_to_checkout edd_button edd_' . $color ),
					$checkout_display
				);
				 	$checkout_link .= '<span class="edd_button_outer"><span class="edd_button_inner">';
						$checkout_link .= '<span class="edd_button_text"><span>' . __( 'Checkout', 'edd' ) . '</span></span>';
					$checkout_link .= '</span></span>';
				$checkout_link .= '</a>';
				
				$purchase_form .= $purchase_button . $checkout_link;
				
			} else {
				
				$purchase_text = sprintf( '<input type="submit" class="%1$s" name="edd_purchase_download" value="%2$s" data-action="edd_add_to_cart" data-download-id="%3$s" %4$s %5$s/>', 
					esc_attr( 'edd_submit_plain edd-add-to-cart ' . $class ),
					esc_attr( $link_text ),
					esc_attr( $download_id ),
					esc_attr( $data_variable ),
					$button_display
				);
				
				$checkout_link = sprintf( '<a href="%1$s" class="%2$s" %3$s>', 
					esc_url( $checkout_url ),
					esc_attr( 'edd_go_to_checkout edd_button edd_' . $color ),
					$checkout_display
				);
				 	$checkout_link .= __( 'Checkout', 'edd' );
				$checkout_link .= '</a>';
				
				$purchase_form .= $purchase_text . $checkout_link;
			}
			if ( edd_is_ajax_enabled() ) {
				$purchase_form .= sprintf( '<div class="edd-cart-ajax-alert"><img src="%1$s" class="edd-cart-ajax" style="display: none;"/>', 
					esc_url( EDD_PLUGIN_URL . 'includes/images/loading.gif' )
				);
				$purchase_form .= '&nbsp;<span style="display:none;" class="edd-cart-added-alert">' . __( 'added to your cart', 'edd' ) . '</span></div>';
			}
	
		$purchase_form .= '</div><!--end .edd_purchase_submit_wrapper-->';	
		$purchase_form .= '<input type="hidden" name="download_id" value="' . esc_attr( $download_id ) . '">';
		$purchase_form .= '<input type="hidden" name="edd_action" value="add_to_cart">';
	$purchase_form .= '</form><!--end #edd_purchase_' . esc_html( $download_id ) . '-->';
		
	return apply_filters( 'edd_purchase_download_form', $purchase_form, $download_id, $link_text, $style, $color, $class );
	
}


/**
 * After Download Content
 *
 * Adds an action to the end of download post content 
 * that can be hooked to by other functions
 *
 * @access      private
 * @since       1.0.8
 * @param       $content string the the_content field of the download object
 * @return      $content string the content with any additional data attached
*/

function edd_after_download_content( $content ) {
	
	global $post;
	
	if ( $post->post_type == 'download' && is_singular() && is_main_query() ) {
		ob_start();
		do_action( 'edd_after_download_content', $post->ID );
		$content .= ob_get_clean();
	}
	
	return $content;
	
}
add_filter( 'the_content', 'edd_after_download_content' );

/**
 * Filter Success Page Content
 *
 * Applies filters to the success page content.
 *
 * @access      private
 * @since       1.0 
 * @return      string
*/

function edd_filter_success_page_content( $content ) {
	
	global $edd_options;
	
	if ( isset( $edd_options['success_page'] ) && isset( $_GET['payment-confirmation'] ) && is_page( $edd_options['success_page'] ) ) {
		
		if ( has_filter( 'edd_payment_confirm_' . $_GET['payment-confirmation'] ) ) {
			$content = apply_filters( 'edd_payment_confirm_' . $_GET['payment-confirmation'], $content );
		}

	}

	if ( isset( $edd_options['success_page'] ) && is_page( $edd_options['success_page'] ) ) {

		if ( isset( $edd_options['show_links_on_success'] ) ) {

			// show download links to non logged-in users
			if ( ( $purchase_data = edd_get_purchase_session() ) ) {

				$content .= edd_get_purchase_download_links( $purchase_data );

			}

		}

	}

	return $content;
	
}
add_filter( 'the_content', 'edd_filter_success_page_content' );


/**
 * Get Button Colors
 *
 * Returns an array of button colors.
 *
 * @access      public
 * @since       1.0 
 * @return      array
*/

function edd_get_button_colors() {
	
	$colors = array( 
		'gray'      => __( 'Gray', 'edd' ), 
		'pink'      => __( 'Pink', 'edd' ), 
		'blue'      => __( 'Blue', 'edd' ), 
		'green'     => __( 'Green', 'edd' ), 
		'teal'      => __( 'Teal', 'edd' ), 
		'black'     => __( 'Black', 'edd' ), 
		'dark gray' => __( 'Dark Gray', 'edd' ), 
		'orange'    => __( 'Orange', 'edd' ), 
		'purple'    => __( 'Purple', 'edd' ), 
		'slate'     => __( 'Slate', 'edd' )
	);
	
	return apply_filters( 'edd_button_colors', $colors );

}


/**
 * Get Button Styles
 *
 * Returns an array of button styles.
 *
 * @access      public
 * @since       1.2.2
 * @return      array
*/

function edd_get_button_styles() {
	
	$styles = array( 
		'button'	=> __( 'Button', 'edd' ), 
		'plain'     => __( 'Plain Text', 'edd' )
	);
	
	return apply_filters( 'edd_button_styles', $styles );

}


/**
 * Show Has Purchased Item Message
 *
 * Prints a notice when user has already purchased the item.
 *
 * @access      private
 * @since       1.0 
 * @return      void
*/

function edd_show_has_purchased_item_message( $download_id ) {
	
	global $user_ID;
	
	if ( edd_has_user_purchased( $user_ID, $download_id ) ) {
		echo '<p class="edd_has_purchased">' . __( 'You have already purchased this item, but you may purchase it again.', 'edd' ) . '</p>';
	}
	
}
add_action( 'edd_after_download_content', 'edd_show_has_purchased_item_message' );


/**
 * Default formatting for download excerpts
 *
 * This excerpt is primarily used in the [downloads] short code
 *
 * @access      private
 * @since       1.0.8.4
 * @return      string
*/

function edd_downloads_default_excerpt( $excerpt ) {
	
	return do_shortcode( wpautop( $excerpt ) );

}
add_filter( 'edd_downloads_excerpt', 'edd_downloads_default_excerpt' );


/**
 * Default formatting for full download content
 *
 * This is primarily used in the [downloads] short code
 *
 * @access      private
 * @since       1.0.8.4
 * @return      string
*/

function edd_downloads_default_content( $content ) {

	return do_shortcode( wpautop( $content ) );

}
add_filter( 'edd_downloads_content', 'edd_downloads_default_content' );


/**
 * Gets the download links for each item purchased
 *
 * @access      private
 * @since       1.1.5
 * @return      string
*/

function edd_get_purchase_download_links( $purchase_data ) {

	$links = '<ul class="edd_download_links">';
	foreach( $purchase_data['downloads'] as $download ) {

		$links .= '<li>';
			$links .= '<h3 class="edd_download_link_title">' . esc_html( get_the_title( $download['id'] ) ) . '</h3>';
			$price_id = isset( $download['options'] ) && isset( $download['options']['price_id'] ) ? $download['options']['price_id'] : null;
			$files = edd_get_download_files( $download['id'], $price_id );
			if ( is_array( $files ) ) {
				foreach( $files as $filekey => $file ) {
					$links .= '<div class="edd_download_link_file">';
						$links .= '<a href="' . esc_url( edd_get_download_file_url( $purchase_data['purchase_key'], $purchase_data['user_email'], $filekey, $download['id'] ) ) . '">';
							$links .= esc_html( $file['name'] );
						$links .= '</a>';
					$links .= '</div>';
				}
			}
		$links .= '</li>';

	}
	$links .= '</ul>';

	return $links;
}


/**
 * Returns the path to the EDD templates directory
 *
 * @access      private
 * @since       1.2
 * @return      string
*/

function edd_get_templates_dir() {
	return EDD_PLUGIN_DIR . 'includes/templates';
}


/**
 * Retrieves a template part
 *
 * @since v1.2
 *
 * Taken from bbPress
 *
 * @param string $slug
 * @param string $name Optional. Default null
 * @uses edd_locate_template()
 * @uses load_template()
 * @uses get_template_part()
 */

function edd_get_template_part( $slug, $name = null, $load = true ) {

	// Execute code for this part
	do_action( 'get_template_part_' . $slug, $slug, $name );

	// Setup possible parts
	$templates = array();
	if ( isset( $name ) )
		$templates[] = $slug . '-' . $name . '.php';
	$templates[] = $slug . '.php';

	// Allow template parst to be filtered
	$templates = apply_filters( 'edd_get_template_part', $templates, $slug, $name );

	// Return the part that is found
	return edd_locate_template( $templates, $load, false );

}


/**
 * Retrieve the name of the highest priority template file that exists.
 *
 * Searches in the STYLESHEETPATH before TEMPLATEPATH so that themes which
 * inherit from a parent theme can just overload one file. If the template is
 * not found in either of those, it looks in the theme-compat folder last.
 *
 * Taken from bbPress
 *
 * @since v1.2
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true.
 *                            Has no effect if $load is false.
 * @return string The template filename if one is located.
 */
function edd_locate_template( $template_names, $load = false, $require_once = true ) {

	// No file found yet
	$located = false;

	// Try to find a template file
	foreach ( (array) $template_names as $template_name ) {

		// Continue if template is empty
		if ( empty( $template_name ) )
			continue;

		// Trim off any slashes from the template name
		$template_name = ltrim( $template_name, '/' );

		// Check child theme first
		if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'edd_templates/' . $template_name ) ) {
			$located = trailingslashit( get_stylesheet_directory() ) . 'edd_templates/' . $template_name;
			break;

		// Check parent theme next
		} elseif ( file_exists( trailingslashit( get_template_directory() ) . 'edd_templates/' . $template_name ) ) {
			$located = trailingslashit( get_template_directory() ) . 'edd_templates/' . $template_name;
			break;

		// Check theme compatibility last
		} elseif ( file_exists( trailingslashit( edd_get_templates_dir() ) . $template_name ) ) {
			$located = trailingslashit( edd_get_templates_dir() ) . $template_name;
			break;
		}
	}

	if ( ( true == $load ) && !empty( $located ) )
		load_template( $located, $require_once );

	return $located;
}
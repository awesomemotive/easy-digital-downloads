<?php
/**
 * Template Functions
 *
 * @package     EDD
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Append Purchase Link
 *
 * Automatically appends the purchase link to download content, if enabled.
 *
 * @since 1.0
 * @param int $download_id Download ID
 * @return void
 */

function edd_append_purchase_link( $download_id ) {
	if ( ! get_post_meta( $download_id, '_edd_hide_purchase_link', true ) ) {
		echo edd_get_purchase_link( array( 'download_id' => $download_id ) );
	}
}
add_action( 'edd_after_download_content', 'edd_append_purchase_link' );


/**
 * Get Purchase Link
 *
 * Builds a Purchase link for a specified download based on arguments passed.
 * This function is used all over EDD to generate the Purchase or Add to Cart
 * buttons. If no arguments are passed, the function uses the defaults that have
 * been set by the plugin. The Purchase link is built for simple and variable
 * pricing and filters are available throughout the function to override
 * certain elements of the function.
 *
 * $download_id = null, $link_text = null, $style = null, $color = null, $class = null
 *
 * @since 1.0
 * @param array $args Arguments for display
 * @return string $purchase_form
 */
function edd_get_purchase_link( $args = array() ) {
	global $edd_options, $post;

	if ( ! isset( $edd_options['purchase_page'] ) || $edd_options['purchase_page'] == 0 ) {
		edd_set_error( 'set_checkout', sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-settings' ) ) );
		edd_print_errors();
		return false;
	}

	$defaults = apply_filters( 'edd_purchase_link_defaults', array(
		'download_id' => $post->ID,
		'price'       => (bool) true,
		'text'        => ! empty( $edd_options[ 'add_to_cart_text' ] ) ? $edd_options[ 'add_to_cart_text' ] : __( 'Purchase', 'edd' ),
		'style'       => isset( $edd_options[ 'button_style' ] ) 	   ? $edd_options[ 'button_style' ]     : 'button',
		'color'       => isset( $edd_options[ 'checkout_color' ] ) 	   ? $edd_options[ 'checkout_color' ] 	: 'blue',
		'class'       => 'edd-submit'
	) );

	$args = wp_parse_args( $args, $defaults );

	$variable_pricing = edd_has_variable_prices( $args['download_id'] );
	$data_variable    = $variable_pricing ? ' data-variable-price=yes' : 'data-variable-price=no';
	$type             = edd_single_price_option_mode( $args['download_id'] ) ? 'data-price-mode=multi' : 'data-price-mode=single';

	if ( $args['price'] && $args['price'] !== 'no' && ! $variable_pricing ) {
		$price = edd_get_download_price( $args['download_id'] );
		$args['text'] = edd_currency_filter( edd_format_amount( $price ) ) . '&nbsp;&ndash;&nbsp;' . $args['text'];
	}

	if ( edd_item_in_cart( $args['download_id'] ) && ! $variable_pricing ) {
		$button_display   = 'style="display:none;"';
		$checkout_display = '';
	} else {
		$button_display   = '';
		$checkout_display = 'style="display:none;"';
	}

	ob_start();
?>
	<!--dynamic-cached-content-->
	<form id="edd_purchase_<?php echo $args['download_id']; ?>" class="edd_download_purchase_form" method="post">

		<?php do_action( 'edd_purchase_link_top', $args['download_id'], $args['price'] ); ?>

		<div class="edd_purchase_submit_wrapper">
			<?php
				printf(
					'<input type="submit" class="edd-add-to-cart %1$s" name="edd_purchase_download" value="%2$s" data-action="edd_add_to_cart" data-download-id="%3$s" %4$s %5$s %6$s/>',
					implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
					esc_attr( $args['text'] ),
					esc_attr( $args['download_id'] ),
					esc_attr( $data_variable ),
					esc_attr( $type ),
					$button_display
				);

				printf(
					'<a href="%1$s" class="%2$s %3$s" %4$s>' . __( 'Checkout', 'edd' ) . '</a>',
					esc_url( edd_get_checkout_uri() ),
					esc_attr( 'edd_go_to_checkout' ),
					implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) ),
					$checkout_display
				);
			?>

			<?php if ( edd_is_ajax_enabled() ) : ?>
				<span class="edd-cart-ajax-alert">
					<img alt="<?php _e( 'Loading', 'edd' ); ?>" src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/loading.gif' ); ?>" class="edd-cart-ajax" style="display: none;" />
					<span class="edd-cart-added-alert" style="display: none;">&mdash;
						<?php printf(
								__( 'Item successfully added to your %scart%s.', 'edd' ),
								'<a href="' . esc_url( edd_get_checkout_uri() ) . '" title="' . __( 'Go to Checkout', 'edd' ) . '">',
								'</a>'
							);
						?>
					</span>
				</span>
			<?php endif; ?>
		</div><!--end .edd_purchase_submit_wrapper-->

		<input type="hidden" name="download_id" value="<?php echo esc_attr( $args['download_id'] ); ?>">
		<input type="hidden" name="edd_action" value="add_to_cart">

		<?php do_action( 'edd_purchase_link_end', $args['download_id'] ); ?>

	</form><!--end #edd_purchase_<?php echo esc_attr( $args['download_id'] ); ?>-->
	<!--/dynamic-cached-content-->
<?php
	$purchase_form = ob_get_clean();

	return apply_filters( 'edd_purchase_download_form', $purchase_form, $args );
}

/**
 * Variable price output
 *
 * Outputs variable pricing options for each download or a specified downloads in a list.
 * The output generated can be overriden by the filters provided or by removing
 * the action and adding your own custom action.
 *
 * @since 1.2.3
 * @param int $download_id Download ID
 * @param bool $show_price
 * @return void
 */
function edd_purchase_variable_pricing( $download_id = 0, $show_price = true ) {
	$variable_pricing = edd_has_variable_prices( $download_id );

	if ( ! $variable_pricing || empty( $show_price ) )
		return;

	$prices = apply_filters( 'edd_purchase_variable_prices', edd_get_variable_prices( $download_id ), $download_id );

	$type   = edd_single_price_option_mode( $download_id ) ? 'checkbox' : 'radio';

	do_action( 'edd_before_price_options', $download_id ); ?>
	<div class="edd_price_options">
		<ul>
			<?php
			if ( $prices ) :
				foreach ( $prices as $key => $price ) :
					echo '<li id="edd_price_option_' . $download_id . '_' . sanitize_key( $price['name'] ) . '">';
					printf(
						'<label for="%3$s"><input type="%2$s" %1$s name="edd_options[price_id][]" id="%3$s" class="%4$s" value="%5$s" %7$s/> %6$s</label>',
						checked( 0, $key, false ),
						$type,
						esc_attr( 'edd_price_option_' . $download_id . '_' . $key ),
						esc_attr( 'edd_price_option_' . $download_id ),
						esc_attr( $key ),
						'<span class="edd_price_option_name">' . esc_html( $price['name'] ) . '</span><span class="edd_price_option_sep">&nbsp;&ndash;&nbsp;</span><span class="edd_price_option_price">' . edd_currency_filter( edd_format_amount( $price[ 'amount' ] ) ) . '</span>',
						checked( isset( $_GET['price_option'] ), $key, false )
					);
					do_action( 'edd_after_price_option', $key, $price, $download_id );
					echo '</li>';
				endforeach;
			endif;
			do_action( 'edd_after_price_options_list', $download_id, $prices, $type );
			?>
		</ul>
	</div><!--end .edd_price_options-->
<?php
	do_action( 'edd_after_price_options', $download_id );
}
add_action( 'edd_purchase_link_top', 'edd_purchase_variable_pricing', 10, 2 );

/**
 * Before Download Content
 *
 * Adds an action to the begining of download post content that can be hooked to
 * by other functions.
 *
 * @since 1.0.8
 * @global $post
 * @param $content string The the_content field of the download object
 * @return $content string the content with any additional data attached
 */
function edd_before_download_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'download' && is_singular() && is_main_query() ) {
		ob_start();
		$content .= ob_get_clean();
		do_action( 'edd_before_download_content', $post->ID );
	}

	return $content;
}
add_filter( 'the_content', 'edd_before_download_content' );

/**
 * After Download Content
 *
 * Adds an action to the end of download post content that can be hooked to by
 * other functions.
 *
 * @since 1.0.8
 * @global $post
 * @param $content string The the_content field of the download object
 * @return $content string the content with any additional data attached
 */
function edd_after_download_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'download' && is_singular() && is_main_query() ) {
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
 * @since 1.0
 * @param string $content Content before filters
 * @return string $content Filtered content
 */
function edd_filter_success_page_content( $content ) {
	global $edd_options;

	if ( isset( $edd_options['success_page'] ) && isset( $_GET['payment-confirmation'] ) && is_page( $edd_options['success_page'] ) ) {
		if ( has_filter( 'edd_payment_confirm_' . $_GET['payment-confirmation'] ) ) {
			$content = apply_filters( 'edd_payment_confirm_' . $_GET['payment-confirmation'], $content );
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
 * @since 1.0
 * @return array $colors Button colors
 */
function edd_get_button_colors() {
	$colors = array(
		'gray'      => __( 'Gray', 'edd' ),
		'blue'      => __( 'Blue', 'edd' ),
		'green'     => __( 'Green', 'edd' ),
		'yellow'    => __( 'Yellow', 'edd' ),
		'dark-gray' => __( 'Dark Gray', 'edd' ),
	);

	return apply_filters( 'edd_button_colors', $colors );
}

/**
 * Get Button Styles
 *
 * Returns an array of button styles.
 *
 * @since 1.2.2
 * @return array $styles Button styles
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
 * @since 1.0
 * @global $user_ID
 * @param int $download_id Download ID
 * @return void
 */
function edd_show_has_purchased_item_message( $download_id ) {
	global $user_ID;

	if ( edd_has_user_purchased( $user_ID, $download_id ) ) {
		$alert = '<p class="edd_has_purchased">' . __( 'You have already purchased this item, but you may purchase it again.', 'edd' ) . '</p>';
		echo apply_filters( 'edd_show_has_purchased_item_message', $alert );
	}
}
add_action( 'edd_after_download_content', 'edd_show_has_purchased_item_message' );

/**
 * Default formatting for download excerpts
 *
 * This excerpt is primarily used in the [downloads] short code
 *
 * @since 1.0.8.4
 * @param string $excerpt Content before filterting
 * @return string $excerpt Content after filterting
 * @return string
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
 * @since 1.0.8.4
 * @param string $content Content before filterting
 * @return string $content Content after filterting
 */
function edd_downloads_default_content( $content ) {
	return do_shortcode( wpautop( $content ) );
}
add_filter( 'edd_downloads_content', 'edd_downloads_default_content' );

/**
 * Gets the download links for each item purchased
 *
 * @since 1.1.5
 * @param array $purchase_data Purchase data
 * @return string
 */
function edd_get_purchase_download_links( $purchase_data ) {
	if ( ! is_array( $purchase_data['downloads'] ) )
		return '<div class="edd-error">' . __( 'No downloads found', 'edd' ) . '</div>';

	$links = '<ul class="edd_download_links">';

	foreach ( $purchase_data['downloads'] as $download ) {
		$links .= '<li>';
			$links .= '<h3 class="edd_download_link_title">' . esc_html( get_the_title( $download['id'] ) ) . '</h3>';
			$price_id = isset( $download['options'] ) && isset( $download['options']['price_id'] ) ? $download['options']['price_id'] : null;
			$files = edd_get_download_files( $download['id'], $price_id );
			if ( is_array( $files ) ) {
				foreach ( $files as $filekey => $file ) {
					$links .= '<div class="edd_download_link_file">';
						$links .= '<a href="' . esc_url( edd_get_download_file_url( $purchase_data['purchase_key'], $purchase_data['user_email'], $filekey, $download['id'], $price_id ) ) . '">';
							if ( isset( $file['name'] ) )
								$links .= esc_html( $file['name'] );
							else
								$links .= esc_html( $file['file'] );
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
 * @since 1.2
 * @return string
 */
function edd_get_templates_dir() {
	return EDD_PLUGIN_DIR . 'templates';
}

/**
 * Returns the URL to the EDD templates directory
 *
 * @since 1.3.2.1
 * @return string
 */
function edd_get_templates_url() {
	return EDD_PLUGIN_URL . 'templates';
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
 *
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
 * @since 1.2
 *
 * @param string|array $template_names Template file(s) to search for, in order.
 * @param bool $load If true the template file will be loaded if it is found.
 * @param bool $require_once Whether to require_once or require. Default true.
 *   Has no effect if $load is false.
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

	if ( ( true == $load ) && ! empty( $located ) )
		load_template( $located, $require_once );

	return $located;
}

/**
 * Add Microdata to download titles
 *
 * @since 1.5
 * @author Sunny Ratilal
 * @param string $title Post Title
 * @param int $id Post ID
 * @return string $title New title
 */
function edd_microdata_title( $title, $id = 0 ) {
	if ( is_singular( 'download' ) && 'download' == get_post_type( intval( $id ) ) ) {
		$title = '<span itemprop="name">' . $title . '</span>';
	}

	return $title;
}
add_filter( 'the_title', 'edd_microdata_title', 10, 2 );

/**
 * Add Microdata to download description
 *
 * @since 1.5
 * @author Sunny Ratilal
 * @param string $title Post Title
 * @param int $id Post ID
 * @return string $title New title
 */
function edd_microdata_wrapper( $content ) {
	global $post;
	if ( $post && $post->post_type == 'download' && is_singular() && is_main_query() ) {
		$content = apply_filters( 'edd_microdata_wrapper', '<div itemscope itemtype="http://schema.org/Product" itemprop="description">' . $content . '</div>' );
	}
	return $content;
}
add_filter( 'the_content', 'edd_microdata_wrapper', 10 );

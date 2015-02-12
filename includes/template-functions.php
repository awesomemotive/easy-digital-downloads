<?php
/**
 * Template Functions
 *
 * @package     EDD
 * @subpackage  Functions/Templates
 * @copyright   Copyright (c) 2015, Pippin Williamson
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
	global $edd_options, $post, $edd_displayed_form_ids;

	if ( ! isset( $edd_options['purchase_page'] ) || $edd_options['purchase_page'] == 0 ) {
		edd_set_error( 'set_checkout', sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'edd' ), admin_url( 'edit.php?post_type=download&page=edd-settings' ) ) );
		edd_print_errors();
		return false;
	}

	$post_id = is_object( $post ) ? $post->ID : 0;

	$defaults = apply_filters( 'edd_purchase_link_defaults', array(
		'download_id' => $post_id,
		'price'       => (bool) true,
		'price_id'    => isset( $args['price_id'] ) ? $args['price_id'] : false,
		'direct'      => edd_get_download_button_behavior( $post_id ) == 'direct' ? true : false,
		'text'        => ! empty( $edd_options[ 'add_to_cart_text' ] ) ? $edd_options[ 'add_to_cart_text' ] : __( 'Purchase', 'edd' ),
		'style'       => isset( $edd_options[ 'button_style' ] ) 	   ? $edd_options[ 'button_style' ]     : 'button',
		'color'       => isset( $edd_options[ 'checkout_color' ] ) 	   ? $edd_options[ 'checkout_color' ] 	: 'blue',
		'class'       => 'edd-submit'
	) );

	$args = wp_parse_args( $args, $defaults );

	$download = new EDD_Download( $args['download_id'] );

	if( empty( $download->ID ) ) {
		return false;
	}

	if( 'publish' !== $download->post_status && ! current_user_can( 'edit_product', $download->ID ) ) {
		return false; // Product not published or user doesn't have permission to view drafts
	}

	// Override color if color == inherit
	$args['color'] = ( $args['color'] == 'inherit' ) ? '' : $args['color'];

	$variable_pricing = $download->has_variable_prices();
	$data_variable    = $variable_pricing ? ' data-variable-price="yes"' : 'data-variable-price="no"';
	$type             = $download->is_single_price_mode() ? 'data-price-mode=multi' : 'data-price-mode=single';

	if ( $args['price'] && $args['price'] !== 'no' ) {

		if ( $variable_pricing && false !== $args['price_id'] ) {

			$price_id = $args['price_id'];
			$prices   = $download->prices;

			$price = isset( $prices[$price_id] ) ? $prices[$price_id]['amount'] : false;

		} elseif ( ! $variable_pricing ) {

			$price = $download->price;

		}
	}

	$data_price       = isset( $price ) && false !== $price ? 'data-price="' . $price . '"' : 'data-price="' . 0 . '"';

	$button_text = ! empty( $args['text'] ) ? '&nbsp;&ndash;&nbsp;' . $args['text'] : '';

	if ( isset( $price ) && false !== $price ) {

		if ( 0 == $price ) {
			$args['text'] = __( 'Free', 'edd' ) . $button_text;
		} else {
			$args['text'] = edd_currency_filter( edd_format_amount( $price ) ) . $button_text;
		}

	}

	if ( edd_item_in_cart( $download->ID ) && ( ! $variable_pricing || ! $download->is_single_price_mode() ) ) {
		$button_display   = 'style="display:none;"';
		$checkout_display = '';
	} else {
		$button_display   = '';
		$checkout_display = 'style="display:none;"';
	}

	// Collect any form IDs we've displayed already so we can avoid duplicate IDs
	if ( isset( $edd_displayed_form_ids[ $download->ID ] ) ) {
		$edd_displayed_form_ids[ $download->ID ]++;
	} else {
		$edd_displayed_form_ids[ $download->ID ] = 1;
	}

	$form_id = ! empty( $args['form_id'] ) ? $args['form_id'] : 'edd_purchase_' . $download->ID;

	// If we've already generated a form ID for this download ID, apped -#
	if ( $edd_displayed_form_ids[ $download->ID ] > 1 ) {
		$form_id .= '-' . $edd_displayed_form_ids[ $download->ID ];
	}

	$args = apply_filters( 'edd_purchase_link_args', $args );

	ob_start();
?>
	<form id="<?php echo $form_id; ?>" class="edd_download_purchase_form edd_purchase_<?php echo absint( $download->ID ); ?>" method="post">

		<?php do_action( 'edd_purchase_link_top', $download->ID, $args ); ?>

		<div class="edd_purchase_submit_wrapper">
			<?php
			$class = implode( ' ', array( $args['style'], $args['color'], trim( $args['class'] ) ) );

			if ( ! edd_is_ajax_disabled() ) {

				echo '<a href="#" class="edd-add-to-cart ' . esc_attr( $class ) . '" data-action="edd_add_to_cart" data-download-id="' . esc_attr( $download->ID ) . '" ' . $data_variable . ' ' . $type . ' ' . $data_price . ' ' . $button_display . '><span class="edd-add-to-cart-label">' . $args['text'] . '</span> <span class="edd-loading"><i class="edd-icon-spinner edd-icon-spin"></i></span></a>';

			}

			echo '<input type="submit" class="edd-add-to-cart edd-no-js ' . esc_attr( $class ) . '" name="edd_purchase_download" value="' . esc_attr( $args['text'] ) . '" data-action="edd_add_to_cart" data-download-id="' . esc_attr( $download->ID ) . '" ' . $data_variable . ' ' . $type . ' ' . $button_display . '/>';
			echo '<a href="' . esc_url( edd_get_checkout_uri() ) . '" class="edd_go_to_checkout ' . esc_attr( $class ) . '" ' . $checkout_display . '>' . __( 'Checkout', 'edd' ) . '</a>';
			?>

			<?php if ( ! edd_is_ajax_disabled() ) : ?>
				<span class="edd-cart-ajax-alert">
					<span class="edd-cart-added-alert" style="display: none;">
						<?php printf(
								'<i class="edd-icon-ok"></i> ' . __( 'Added to cart', 'edd' ),
								'<a href="' . esc_url( edd_get_checkout_uri() ) . '" title="' . __( 'Go to Checkout', 'edd' ) . '">',
								'</a>'
							);
						?>
					</span>
				</span>
			<?php endif; ?>
			<?php if( ! $download->is_free( $args['price_id'] ) ): ?>
				<?php if ( edd_display_tax_rate() && edd_prices_include_tax() ) {
					echo '<span class="edd_purchase_tax_rate">' . sprintf( __( 'Includes %1$s&#37; tax', 'edd' ), edd_get_tax_rate() * 100 ) . '</span>';
				} elseif ( edd_display_tax_rate() && ! edd_prices_include_tax() ) {
					echo '<span class="edd_purchase_tax_rate">' . sprintf( __( 'Excluding %1$s&#37; tax', 'edd' ), edd_get_tax_rate() * 100 ) . '</span>';
				} ?>
			<?php endif; ?>
		</div><!--end .edd_purchase_submit_wrapper-->

		<input type="hidden" name="download_id" value="<?php echo esc_attr( $download->ID ); ?>">
		<?php if ( $variable_pricing && isset( $price_id ) && isset( $prices[$price_id] ) ): ?>
			<input type="hidden" name="edd_options[price_id][]" id="edd_price_option_<?php echo $download->ID; ?>_1" class="edd_price_option_<?php echo $download->ID; ?>" value="<?php echo $price_id; ?>">
		<?php endif; ?>
		<?php if( ! empty( $args['direct'] ) && ! $download->is_free( $args['price_id'] ) ) { ?>
			<input type="hidden" name="edd_action" class="edd_action_input" value="straight_to_gateway">
		<?php } else { ?>
			<input type="hidden" name="edd_action" class="edd_action_input" value="add_to_cart">
		<?php } ?>

		<?php do_action( 'edd_purchase_link_end', $download->ID, $args ); ?>

	</form><!--end #<?php echo esc_attr( $form_id ); ?>-->
<?php
	$purchase_form = ob_get_clean();


	return apply_filters( 'edd_purchase_download_form', $purchase_form, $args );
}

/**
 * Variable price output
 *
 * Outputs variable pricing options for each download or a specified downloads in a list.
 * The output generated can be overridden by the filters provided or by removing
 * the action and adding your own custom action.
 *
 * @since 1.2.3
 * @param int $download_id Download ID
 * @return void
 */
function edd_purchase_variable_pricing( $download_id = 0, $args = array() ) {
	global $edd_options;

	$variable_pricing = edd_has_variable_prices( $download_id );
	$prices = apply_filters( 'edd_purchase_variable_prices', edd_get_variable_prices( $download_id ), $download_id );

	if ( ! $variable_pricing || ( false !== $args['price_id'] && isset( $prices[$args['price_id']] ) ) ) {
		return;
	}

	if ( edd_item_in_cart( $download_id ) && ! edd_single_price_option_mode( $download_id ) ) {
		return;
	}

	$type = edd_single_price_option_mode( $download_id ) ? 'checkbox' : 'radio';
	$mode = edd_single_price_option_mode( $download_id ) ? 'multi' : 'single';

	do_action( 'edd_before_price_options', $download_id ); ?>
	<div class="edd_price_options edd_<?php echo esc_attr( $mode ); ?>_mode">
		<ul>
			<?php
			if ( $prices ) :
				$checked_key = isset( $_GET['price_option'] ) ? absint( $_GET['price_option'] ) : edd_get_default_variable_price( $download_id );
				foreach ( $prices as $key => $price ) :
					echo '<li id="edd_price_option_' . $download_id . '_' . sanitize_key( $price['name'] ) . '" itemprop="offers" itemscope itemtype="http://schema.org/Offer">';
						echo '<label for="'	. esc_attr( 'edd_price_option_' . $download_id . '_' . $key ) . '">';
							echo '<input type="' . $type . '" ' . checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $key ), $key, false ) . ' name="edd_options[price_id][]" id="' . esc_attr( 'edd_price_option_' . $download_id . '_' . $key ) . '" class="' . esc_attr( 'edd_price_option_' . $download_id ) . '" value="' . esc_attr( $key ) . '" data-price="' . edd_get_price_option_amount( $download_id, $key ) .'"/>&nbsp;';
							echo '<span class="edd_price_option_name" itemprop="description">' . esc_html( $price['name'] ) . '</span><span class="edd_price_option_sep">&nbsp;&ndash;&nbsp;</span><span class="edd_price_option_price" itemprop="price">' . edd_currency_filter( edd_format_amount( $price[ 'amount' ] ) ) . '</span>';
						echo '</label>';
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
 * Display the quantity field for a variable price when multi-purchase mode is enabled
 *
 * @since 2.2
 * @param int $download_id Download ID
 * @param array $args Argument array
 * @return void
 */
function edd_download_purchase_form_quantity_field( $download_id = 0, $args = array() ) {

	if ( ! edd_item_quantities_enabled() ) {
		return;
	}

	if ( edd_item_in_cart( $download_id ) && ! edd_has_variable_prices( $download_id ) ) {
		return;
	}

	if ( edd_single_price_option_mode( $download_id ) && edd_has_variable_prices( $download_id ) && ! edd_item_in_cart( $download_id ) ) {
		return;
	}

	if ( edd_single_price_option_mode( $download_id ) && edd_has_variable_prices( $download_id ) && edd_item_in_cart( $download_id ) ) {
		return;
	}

	if ( ! edd_single_price_option_mode( $download_id ) && edd_has_variable_prices( $download_id ) && edd_item_in_cart( $download_id ) ) {
		return;
	}

	echo '<div class="edd_download_quantity_wrapper">';
		echo '<input type="number" min="1" step="1" name="edd_download_quantity" class="edd-input edd-item-quantity" value="1" />';
	echo '</div>';

}
add_action( 'edd_purchase_link_top', 'edd_download_purchase_form_quantity_field', 10, 2 );

/**
 * Display the quantity field for a variable price when multi-purchase mode is enabled
 *
 * @since 2.2
 * @param int $key Price ID
 * @param array $price price option array
 * @param int $download_id Download ID
 * @return void
 */
function edd_variable_price_quantity_field( $key, $price, $download_id ) {

	if( ! edd_item_quantities_enabled() ) {
		return;
	}

	if( ! edd_single_price_option_mode() ) {
		return;
	}

	echo '<div class="edd_download_quantity_wrapper edd_download_quantity_price_option_' . sanitize_key( $price['name'] ) . '">';
		echo '<span class="edd_price_option_sep">&nbsp;x&nbsp;</span>';
		echo '<input type="number" min="1" step="1" name="edd_download_quantity_' . esc_attr( $key ) . '" class="edd-input edd-item-quantity" value="1" />';
	echo '</div>';
}
add_action( 'edd_after_price_option', 'edd_variable_price_quantity_field', 10, 3 );

/**
 * Before Download Content
 *
 * Adds an action to the beginning of download post content that can be hooked to
 * by other functions.
 *
 * @since 1.0.8
 * @global $post
 *
 * @param $content The the_content field of the download object
 * @return string the content with any additional data attached
 */
function edd_before_download_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() && !post_password_required() ) {
		ob_start();
		do_action( 'edd_before_download_content', $post->ID );
		$content = ob_get_clean() . $content;
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
 *
 * @param $content The the_content field of the download object
 * @return string the content with any additional data attached
 */
function edd_after_download_content( $content ) {
	global $post;

	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() && !post_password_required() ) {
		ob_start();
		do_action( 'edd_after_download_content', $post->ID );
		$content .= ob_get_clean();
	}

	return $content;
}
add_filter( 'the_content', 'edd_after_download_content' );

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
		'white'     => array(
			'label' => __( 'White', 'edd' ),
			'hex'   => '#ffffff'
		),
		'gray'      => array(
			'label' => __( 'Gray', 'edd' ),
			'hex'   => '#f0f0f0'
		),
		'blue'      => array(
			'label' => __( 'Blue', 'edd' ),
			'hex'   => '#428bca'
		),
		'red'       => array(
			'label' => __( 'Red', 'edd' ),
			'hex'   => '#d9534f'
		),
		'green'     => array(
			'label' => __( 'Green', 'edd' ),
			'hex'   => '#5cb85c'
		),
		'yellow'    => array(
			'label' => __( 'Yellow', 'edd' ),
			'hex'   => '#f0ad4e'
		),
		'orange'    => array(
			'label' => __( 'Orange', 'edd' ),
			'hex'   => '#ed9c28'
		),
		'dark-gray' => array(
			'label' => __( 'Dark Gray', 'edd' ),
			'hex'   => '#363636'
		),
		'inherit'	=> array(
			'label' => __( 'Inherit', 'edd' ),
			'hex'   => ''
		)
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
 * Default formatting for download excerpts
 *
 * This excerpt is primarily used in the [downloads] short code
 *
 * @since 1.0.8.4
 * @param string $excerpt Content before filtering
 * @return string $excerpt Content after filtering
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
 * @param string $content Content before filtering
 * @return string $content Content after filtering
 */
function edd_downloads_default_content( $content ) {
	return do_shortcode( wpautop( $content ) );
}
add_filter( 'edd_downloads_content', 'edd_downloads_default_content' );

/**
 * Gets the download links for each item purchased
 *
 * @since 1.1.5
 * @param int $payment_id The ID of the payment to retrieve download links for
 * @return string
 */
function edd_get_purchase_download_links( $payment_id = 0 ) {

	$downloads   = edd_get_payment_meta_cart_details( $payment_id, true );
	$payment_key = edd_get_payment_key( $payment_id );
	$email       = edd_get_payment_user_email( $payment_id );
	$links       = '<ul class="edd_download_links">';

	foreach ( $downloads as $download ) {
		$links .= '<li>';
			$links .= '<h3 class="edd_download_link_title">' . esc_html( get_the_title( $download['id'] ) ) . '</h3>';
			$price_id = isset( $download['options'] ) && isset( $download['options']['price_id'] ) ? $download['options']['price_id'] : null;
			$files    = edd_get_download_files( $download['id'], $price_id );
			if ( is_array( $files ) ) {
				foreach ( $files as $filekey => $file ) {
					$links .= '<div class="edd_download_link_file">';
						$links .= '<a href="' . esc_url( edd_get_download_file_url( $payment_key, $email, $filekey, $download['id'], $price_id ) ) . '">';
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
 * @param bool   $load
 *
 * @return string
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

	// Allow template parts to be filtered
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

		// try locating this template file by looping through the template paths
		foreach( edd_get_theme_template_paths() as $template_path ) {

			if( file_exists( $template_path . $template_name ) ) {
				$located = $template_path . $template_name;
				break;
			}
		}

		if( $located ) {
			break;
		}
	}

	if ( ( true == $load ) && ! empty( $located ) )
		load_template( $located, $require_once );

	return $located;
}

/**
 * Returns a list of paths to check for template locations
 *
 * @since 1.8.5
 * @return mixed|void
 */
function edd_get_theme_template_paths() {

	$template_dir = edd_get_theme_template_dir_name();

	$file_paths = array(
		1 => trailingslashit( get_stylesheet_directory() ) . $template_dir,
		10 => trailingslashit( get_template_directory() ) . $template_dir,
		100 => edd_get_templates_dir()
	);

	$file_paths = apply_filters( 'edd_template_paths', $file_paths );

	// sort the file paths based on priority
	ksort( $file_paths, SORT_NUMERIC );

	return array_map( 'trailingslashit', $file_paths );
}

/**
 * Returns the template directory name.
 *
 * Themes can filter this by using the edd_templates_dir filter.
 *
 * @since 1.6.2
 * @return string
*/
function edd_get_theme_template_dir_name() {
	return trailingslashit( apply_filters( 'edd_templates_dir', 'edd_templates' ) );
}

/**
 * Should we add schema.org microdata?
 *
 * @since 1.7
 * @return bool
 */
function edd_add_schema_microdata() {
	// Don't modify anything until after wp_head() is called
	$ret = did_action( 'wp_head' );
	return apply_filters( 'edd_add_schema_microdata', $ret );
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

	if( ! edd_add_schema_microdata() ) {
		return $title;
	}

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
 *
 * @param $content
 * @return mixed|void New title
 */
function edd_microdata_wrapper( $content ) {
	global $post;

	if( ! edd_add_schema_microdata() ) {
		return $content;
	}

	if ( $post && $post->post_type == 'download' && is_singular() && is_main_query() ) {
		$content = apply_filters( 'edd_microdata_wrapper', '<div itemscope itemtype="http://schema.org/Product" itemprop="description">' . $content . '</div>' );
	}
	return $content;
}
add_filter( 'the_content', 'edd_microdata_wrapper', 10 );

/**
 * Add no-index and no-follow to EDD checkout and purchase confirmation pages
 *
 * @since 2.0
 *
 * @return void
 */
function edd_checkout_meta_tags() {

	$pages   = array();
	$pages[] = edd_get_option( 'success_page' );
	$pages[] = edd_get_option( 'failure_page' );
	$pages[] = edd_get_option( 'purchase_history_page' );

	if( ! edd_is_checkout() && ! is_page( $pages ) ) {
		return;
	}

	echo '<meta name="robots" content="noindex,nofollow" />' . "\n";
}
add_action( 'wp_head', 'edd_checkout_meta_tags' );

/**
 * Adds EDD Version to the <head> tag
 *
 * @since 1.4.2
 * @return void
*/
function edd_version_in_header(){
	echo '<meta name="generator" content="Easy Digital Downloads v' . EDD_VERSION . '" />' . "\n";
}
add_action( 'wp_head', 'edd_version_in_header' );

/**
 * Determines if we're currently on the Purchase History page.
 *
 * @since 2.1
 * @return bool True if on the Purchase History page, false otherwise.
 */
function edd_is_purchase_history_page() {
	global $edd_options;
	$ret = isset( $edd_options['purchase_history_page'] ) ? is_page( $edd_options['purchase_history_page'] ) : false;
	return apply_filters( 'edd_is_purchase_history_page', $ret );
}

/**
 * Adds body classes for EDD pages
 *
 * @since 2.1
 * @param array $classes current classes
 * @return array Modified array of classes
 */
function edd_add_body_classes( $class ) {
	$classes = (array) $class;

	if( edd_is_checkout() ) {
		$classes[] = 'edd-checkout';
		$classes[] = 'edd-page';
	}

	if( edd_is_success_page() ) {
		$classes[] = 'edd-success';
		$classes[] = 'edd-page';
	}

	if( edd_is_failed_transaction_page() ) {
		$classes[] = 'edd-failed-transaction';
		$classes[] = 'edd-page';
	}

	if( edd_is_purchase_history_page() ) {
		$classes[] = 'edd-purchase-history';
		$classes[] = 'edd-page';
	}

	if( edd_is_test_mode() ) {
		$classes[] = 'edd-test-mode';
		$classes[] = 'edd-page';
	}

	return array_unique( $classes );
}
add_filter( 'body_class', 'edd_add_body_classes' );

/**
 * Adds post classes for downloads
 *
 * @since 2.1
 * @param array $classes Current classes
 * @param string|array $class
 * @param int $post_id The ID of the current post
 * @return array Modified array of classes
 */
function edd_add_download_post_classes( $classes, $class = '', $post_id = false ) {
	if( ! $post_id || get_post_type( $post_id ) !== 'download' || is_admin() ) {
		return $classes;
	}

	$download = edd_get_download( $post_id );

	if( $download ) {
		$classes[] = 'edd-download';

		// Add category slugs
		$categories = get_the_terms( $post_id, 'download_category' );
		if( ! empty( $categories ) ) {
			foreach( $categories as $key => $value ) {
				$classes[] = 'edd-download-cat-' . $value->slug;
			}
		}

		// Add tag slugs
		$tags = get_the_terms( $post_id, 'download_tag' );
		if( ! empty( $tags ) ) {
			foreach( $tags as $key => $value ) {
				$classes[] = 'edd-download-tag-' . $value->slug;
			}
		}

		// Add edd-download
		if( is_singular( 'download' ) ) {
			$classes[] = 'edd-download';
		}
	}

	return $classes;
}
add_filter( 'post_class', 'edd_add_download_post_classes', 20, 3 );

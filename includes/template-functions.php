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
	global $post, $edd_displayed_form_ids;

	$purchase_page = edd_get_option( 'purchase_page', false );
	if ( ! $purchase_page || $purchase_page == 0 ) {

		global $no_checkout_error_displayed;
		if ( ! is_null( $no_checkout_error_displayed ) ) {
			return false;
		}


		edd_set_error( 'set_checkout', sprintf( __( 'No checkout page has been configured. Visit <a href="%s">Settings</a> to set one.', 'easy-digital-downloads' ), admin_url( 'edit.php?post_type=download&page=edd-settings' ) ) );
		edd_print_errors();

		$no_checkout_error_displayed = true;

		return false;

	}

	$post_id = is_object( $post ) ? $post->ID : 0;
	$button_behavior = edd_get_download_button_behavior( $post_id );

	$defaults = apply_filters( 'edd_purchase_link_defaults', array(
		'download_id' => $post_id,
		'price'       => (bool) true,
		'price_id'    => isset( $args['price_id'] ) ? $args['price_id'] : false,
		'direct'      => $button_behavior == 'direct' ? true : false,
		'text'        => $button_behavior == 'direct' ? edd_get_option( 'buy_now_text', __( 'Buy Now', 'easy-digital-downloads' ) ) : edd_get_option( 'add_to_cart_text', __( 'Purchase', 'easy-digital-downloads' ) ),
		'style'       => edd_get_option( 'button_style', 'button' ),
		'color'       => edd_get_option( 'checkout_color', 'blue' ),
		'class'       => 'edd-submit'
	) );

	$args = wp_parse_args( $args, $defaults );

	// Override the straight_to_gateway if the shop doesn't support it
	if ( ! edd_shop_supports_buy_now() ) {
		$args['direct'] = false;
	}

	$download = new EDD_Download( $args['download_id'] );

	if( empty( $download->ID ) ) {
		return false;
	}

	if( 'publish' !== $download->post_status && ! current_user_can( 'edit_product', $download->ID ) ) {
		return false; // Product not published or user doesn't have permission to view drafts
	}

	// Override color if color == inherit
	$args['color'] = ( $args['color'] == 'inherit' ) ? '' : $args['color'];

	$options          = array();
	$variable_pricing = $download->has_variable_prices();
	$data_variable    = $variable_pricing ? ' data-variable-price="yes"' : 'data-variable-price="no"';
	$type             = $download->is_single_price_mode() ? 'data-price-mode=multi' : 'data-price-mode=single';

	$show_price       = $args['price'] && $args['price'] !== 'no';
	$data_price_value = 0;
	$price            = false;

	if ( $variable_pricing && false !== $args['price_id'] ) {

		$price_id            = $args['price_id'];
		$prices              = $download->prices;
		$options['price_id'] = $args['price_id'];
		$found_price         = isset( $prices[$price_id] ) ? $prices[$price_id]['amount'] : false;

		$data_price_value    = $found_price;

		if ( $show_price ) {
			$price = $found_price;
		}

	} elseif ( ! $variable_pricing ) {

		$data_price_value = $download->price;

		if ( $show_price ) {
			$price = $download->price;
		}

	}

	$data_price  = 'data-price="' . $data_price_value . '"';

	$button_text = ! empty( $args['text'] ) ? '&nbsp;&ndash;&nbsp;' . $args['text'] : '';

	if ( false !== $price ) {

		if ( 0 == $price ) {
			$args['text'] = __( 'Free', 'easy-digital-downloads' ) . $button_text;
		} else {
			$args['text'] = edd_currency_filter( edd_format_amount( $price ) ) . $button_text;
		}

	}

	if ( edd_item_in_cart( $download->ID, $options ) && ( ! $variable_pricing || ! $download->is_single_price_mode() ) ) {
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

	// If we've already generated a form ID for this download ID, append -#
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

				echo '<a href="#" class="edd-add-to-cart ' . esc_attr( $class ) . '" data-action="edd_add_to_cart" data-download-id="' . esc_attr( $download->ID ) . '" ' . $data_variable . ' ' . $type . ' ' . $data_price . ' ' . $button_display . '><span class="edd-add-to-cart-label">' . $args['text'] . '</span> <span class="edd-loading" aria-label="' . esc_attr__( 'Loading', 'easy-digital-downloads' ) . '"></span></a>';

			}

			echo '<input type="submit" class="edd-add-to-cart edd-no-js ' . esc_attr( $class ) . '" name="edd_purchase_download" value="' . esc_attr( $args['text'] ) . '" data-action="edd_add_to_cart" data-download-id="' . esc_attr( $download->ID ) . '" ' . $data_variable . ' ' . $type . ' ' . $button_display . '/>';
			echo '<a href="' . esc_url( edd_get_checkout_uri() ) . '" class="edd_go_to_checkout ' . esc_attr( $class ) . '" ' . $checkout_display . '>' . __( 'Checkout', 'easy-digital-downloads' ) . '</a>';
			?>

			<?php if ( ! edd_is_ajax_disabled() ) : ?>
				<span class="edd-cart-ajax-alert" aria-live="assertive">
					<span class="edd-cart-added-alert" style="display: none;">
						<svg class="edd-icon edd-icon-check" xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 28 28" aria-hidden="true">
							<path d="M26.11 8.844c0 .39-.157.78-.44 1.062L12.234 23.344c-.28.28-.672.438-1.062.438s-.78-.156-1.06-.438l-7.782-7.78c-.28-.282-.438-.673-.438-1.063s.156-.78.438-1.06l2.125-2.126c.28-.28.672-.438 1.062-.438s.78.156 1.062.438l4.594 4.61L21.42 5.656c.282-.28.673-.438 1.063-.438s.78.155 1.062.437l2.125 2.125c.28.28.438.672.438 1.062z"/>
						</svg>
						<?php echo __( 'Added to cart', 'easy-digital-downloads' ); ?>
					</span>
				</span>
			<?php endif; ?>
			<?php if( ! $download->is_free( $args['price_id'] ) && ! edd_download_is_tax_exclusive( $download->ID ) ): ?>
				<?php if ( edd_display_tax_rate() && edd_prices_include_tax() ) {
					echo '<span class="edd_purchase_tax_rate">' . sprintf( __( 'Includes %1$s&#37; tax', 'easy-digital-downloads' ), edd_get_tax_rate() * 100 ) . '</span>';
				} elseif ( edd_display_tax_rate() && ! edd_prices_include_tax() ) {
					echo '<span class="edd_purchase_tax_rate">' . sprintf( __( 'Excluding %1$s&#37; tax', 'easy-digital-downloads' ), edd_get_tax_rate() * 100 ) . '</span>';
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

		<?php if( apply_filters( 'edd_download_redirect_to_checkout', edd_straight_to_checkout(), $download->ID, $args ) ) : ?>
			<input type="hidden" name="edd_redirect_to_checkout" id="edd_redirect_to_checkout" value="1">
		<?php endif; ?>

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
	global $edd_displayed_form_ids;

	// If we've already generated a form ID for this download ID, append -#
	$form_id = '';
	if ( $edd_displayed_form_ids[ $download_id ] > 1 ) {
		$form_id .= '-' . $edd_displayed_form_ids[ $download_id ];
	}

	$variable_pricing = edd_has_variable_prices( $download_id );

	if ( ! $variable_pricing ) {
		return;
	}

	$prices = apply_filters( 'edd_purchase_variable_prices', edd_get_variable_prices( $download_id ), $download_id );

	// If the price_id passed is found in the variable prices, do not display all variable prices.
	if ( false !== $args['price_id'] && isset( $prices[ $args['price_id'] ] ) ) {
		return;
	}

	$type   = edd_single_price_option_mode( $download_id ) ? 'checkbox' : 'radio';
	$mode   = edd_single_price_option_mode( $download_id ) ? 'multi' : 'single';
	$schema = edd_add_schema_microdata() ? ' itemprop="offers" itemscope itemtype="http://schema.org/Offer"' : '';

	// Filter the class names for the edd_price_options div
	$css_classes_array = apply_filters( 'edd_price_options_classes', array(
		'edd_price_options',
		'edd_' . esc_attr( $mode ) . '_mode'
	), $download_id );

	// Sanitize those class names and form them into a string
	$css_classes_string = implode( array_map( 'sanitize_html_class', $css_classes_array ), ' ' );

	if ( edd_item_in_cart( $download_id ) && ! edd_single_price_option_mode( $download_id ) ) {
		return;
	}

	do_action( 'edd_before_price_options', $download_id ); ?>
	<div class="<?php echo esc_attr( rtrim( $css_classes_string ) ); ?>">
		<ul>
			<?php
			if ( $prices ) :
				$checked_key = isset( $_GET['price_option'] ) ? absint( $_GET['price_option'] ) : edd_get_default_variable_price( $download_id );
				foreach ( $prices as $key => $price ) :
					echo '<li id="edd_price_option_' . $download_id . '_' . sanitize_key( $price['name'] ) . $form_id . '"' . $schema . '>';
						echo '<label for="' . esc_attr( 'edd_price_option_' . $download_id . '_' . $key . $form_id ) . '">';
							echo '<input type="' . $type . '" ' . checked( apply_filters( 'edd_price_option_checked', $checked_key, $download_id, $key ), $key, false ) . ' name="edd_options[price_id][]" id="' . esc_attr( 'edd_price_option_' . $download_id . '_' . $key . $form_id ) . '" class="' . esc_attr( 'edd_price_option_' . $download_id ) . '" value="' . esc_attr( $key ) . '" data-price="' . edd_get_price_option_amount( $download_id, $key ) .'"/>&nbsp;';

							$item_prop = edd_add_schema_microdata() ? ' itemprop="description"' : '';

							// Construct the default price output.
							$price_output = '<span class="edd_price_option_name"' . $item_prop . '>' . esc_html( $price['name'] ) . '</span><span class="edd_price_option_sep">&nbsp;&ndash;&nbsp;</span><span class="edd_price_option_price">' . edd_currency_filter( edd_format_amount( $price['amount'] ) ) . '</span>';

							// Filter the default price output
							$price_output = apply_filters( 'edd_price_option_output', $price_output, $download_id, $key, $price, $form_id, $item_prop );

							// Output the filtered price output
							echo $price_output;

							if( edd_add_schema_microdata() ) {
								echo '<meta itemprop="price" content="' . esc_attr( $price['amount'] ) .'" />';
								echo '<meta itemprop="priceCurrency" content="' . esc_attr( edd_get_currency() ) .'" />';
							}

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
 * Output schema markup for single price products.
 *
 * @since  2.6.14
 * @param  int $download_id The download being output.
 * @return void
 */
function edd_purchase_link_single_pricing_schema( $download_id = 0, $args = array() ) {

	// Bail if the product has variable pricing, or if we aren't showing schema data.
	if ( edd_has_variable_prices( $download_id ) || ! edd_add_schema_microdata() ) {
		return;
	}

	// Grab the information we need.
	$download = new EDD_Download( $download_id );
	?>
	<span itemprop="offers" itemscope itemtype="http://schema.org/Offer">
		<meta itemprop="price" content="<?php esc_attr_e( $download->price ); ?>" />
		<meta itemprop="priceCurrency" content="<?php esc_attr_e( edd_get_currency() ); ?>" />
	</span>
	<?php
}
add_action( 'edd_purchase_link_top', 'edd_purchase_link_single_pricing_schema', 10, 2 );

/**
 * Display the quantity field for a variable price when multi-purchase mode is enabled
 *
 * @since 2.2
 * @param int $download_id Download ID
 * @param array $args Argument array
 * @return void
 */
function edd_download_purchase_form_quantity_field( $download_id = 0, $args = array() ) {

	$options = array();
	if( false !== $args['price_id'] ) {
		$options['price_id'] = $args['price_id'];
	}

	if ( ! edd_item_quantities_enabled() || edd_download_quantities_disabled( $download_id ) ) {
		return;
	}

	if ( edd_item_in_cart( $download_id ) && ! edd_has_variable_prices( $download_id ) ) {
		return;
	}

	if ( edd_single_price_option_mode( $download_id ) && edd_has_variable_prices( $download_id ) && ! edd_item_in_cart( $download_id, $options ) ) {
		return;
	}

	if ( edd_single_price_option_mode( $download_id ) && edd_has_variable_prices( $download_id ) && edd_item_in_cart( $download_id, $options ) ) {
		return;
	}

	if ( ! edd_single_price_option_mode( $download_id ) && edd_has_variable_prices( $download_id ) && edd_item_in_cart( $download_id, $options ) ) {
		return;
	}

	ob_start();
?>
	<div class="edd_download_quantity_wrapper">
		<input type="number" min="1" step="1" name="edd_download_quantity" class="edd-input edd-item-quantity" value="1" />
	</div>
<?php
	$quantity_input = ob_get_clean();

	echo apply_filters( 'edd_purchase_form_quantity_input', $quantity_input, $download_id, $args );
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

	if( ! edd_item_quantities_enabled() || edd_download_quantities_disabled( $download_id ) ) {
		return;
	}

	if( ! edd_single_price_option_mode( $download_id ) ) {
		return;
	}

	ob_start();
?>
	<div class="edd_download_quantity_wrapper edd_download_quantity_price_option_<?php echo sanitize_key( $price['name'] ) ?>">
		<span class="edd_price_option_sep">&nbsp;x&nbsp;</span>
		<input type="number" min="1" step="1" name="edd_download_quantity_<?php echo esc_attr( $key ) ?>" class="edd-input edd-item-quantity" value="1" />
	</div>
<?php
	$quantity_input = ob_get_clean();

	echo apply_filters( 'edd_purchase_form_variation_quantity_input', $quantity_input, $download_id, $key, $price );
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
			'label' => __( 'White', 'easy-digital-downloads' ),
			'hex'   => '#ffffff'
		),
		'gray'      => array(
			'label' => __( 'Gray', 'easy-digital-downloads' ),
			'hex'   => '#f0f0f0'
		),
		'blue'      => array(
			'label' => __( 'Blue', 'easy-digital-downloads' ),
			'hex'   => '#428bca'
		),
		'red'       => array(
			'label' => __( 'Red', 'easy-digital-downloads' ),
			'hex'   => '#d9534f'
		),
		'green'     => array(
			'label' => __( 'Green', 'easy-digital-downloads' ),
			'hex'   => '#5cb85c'
		),
		'yellow'    => array(
			'label' => __( 'Yellow', 'easy-digital-downloads' ),
			'hex'   => '#f0ad4e'
		),
		'orange'    => array(
			'label' => __( 'Orange', 'easy-digital-downloads' ),
			'hex'   => '#ed9c28'
		),
		'dark-gray' => array(
			'label' => __( 'Dark Gray', 'easy-digital-downloads' ),
			'hex'   => '#363636'
		),
		'inherit'	=> array(
			'label' => __( 'Inherit', 'easy-digital-downloads' ),
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
		'button'	=> __( 'Button', 'easy-digital-downloads' ),
		'plain'     => __( 'Plain Text', 'easy-digital-downloads' )
	);

	return apply_filters( 'edd_button_styles', $styles );
}

/**
 * Default formatting for download excerpts
 *
 * This excerpt is primarily used in the [downloads] shortcode
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
 * This is primarily used in the [downloads] shortcode
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
						$links .= edd_get_file_name( $file );
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

	$load_template = apply_filters( 'edd_allow_template_part_' . $slug . '_' . $name, true );
	if ( false === $load_template ) {
		return '';
	}

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
 * Only allow the pending verification message to display once
 * @since 2.7.8
 * @param $load_template
 *
 * @return bool
 */
function edd_load_verification_template_once( $load_template ) {
	static $account_pending_loaded;
	if ( ! is_null( $account_pending_loaded ) ) {
		return false;
	}

	$account_pending_loaded = true;
	return $load_template;
}
add_filter( 'edd_allow_template_part_account_pending', 'edd_load_verification_template_once', 10, 1 );

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
	$ret = (bool)did_action( 'wp_head' );
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
	global $post;

	if( ! edd_add_schema_microdata() || ! is_object( $post ) ) {
		return $title;
	}

	if ( $post->ID == $id && is_singular( 'download' ) && 'download' == get_post_type( intval( $id ) ) ) {
		$title = '<span itemprop="name">' . $title . '</span>';
	}

	return $title;
}
add_filter( 'the_title', 'edd_microdata_title', 10, 2 );

/**
 * Start Microdata to wrapper download
 *
 * @since 2.3
 * @author Chris Klosowski
 *
 * @return void
 */
function edd_microdata_wrapper_open( $query ) {
	global $post;

	static $microdata_open = NULL;

	if( ! edd_add_schema_microdata() || true === $microdata_open || ! is_object( $query ) ) {
		return;
	}

	if ( $query && ! empty( $query->query['post_type'] ) && $query->query['post_type'] == 'download' && is_singular( 'download' ) && $query->is_main_query() ) {
		$microdata_open = true;
		echo '<span itemscope itemtype="http://schema.org/Product">';
	}

}
add_action( 'loop_start', 'edd_microdata_wrapper_open', 10 );

/**
 * End Microdata to wrapper download
 *
 * @since 2.3
 * @author Chris Klosowski
 *
 * @return void
 */
function edd_microdata_wrapper_close() {
	global $post;

	static $microdata_close = NULL;

	if( ! edd_add_schema_microdata() || true === $microdata_close || ! is_object( $post ) ) {
		return;
	}

	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() ) {
		$microdata_close = true;
		echo '</span>';
	}
}
add_action( 'loop_end', 'edd_microdata_wrapper_close', 10 );

/**
 * Add Microdata to download description
 *
 * @since 1.5
 * @author Sunny Ratilal
 *
 * @param $content
 * @return mixed|void New title
 */
function edd_microdata_description( $content ) {
	global $post;

	static $microdata_description = NULL;

	if( ! edd_add_schema_microdata() || true === $microdata_description || ! is_object( $post ) ) {
		return $content;
	}

	if ( $post && $post->post_type == 'download' && is_singular( 'download' ) && is_main_query() ) {
		$microdata_description = true;
		$content = apply_filters( 'edd_microdata_wrapper', '<div itemprop="description">' . $content . '</div>' );
	}
	return $content;
}
add_filter( 'the_content', 'edd_microdata_description', 10 );

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

	echo '<meta name="edd-chosen-gateway" content="' . edd_get_chosen_gateway() . '"/>' . "\n";
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
	$ret = edd_get_option( 'purchase_history_page', false );
	$ret = $ret ? is_page( $ret ) : false;
	return apply_filters( 'edd_is_purchase_history_page', $ret );
}

/**
 * Adds body classes for EDD pages
 *
 * @since 2.1
 * @param array $class current classes
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

/**
 * Adds Download product price to oembed display
 *
 * @since 2.6
 * @return void
 */
function edd_add_oembed_price() {

	if( 'download' !== get_post_type( get_the_ID() ) ) {
		return;
	}

	$show = ! get_post_meta( get_the_ID(), '_edd_hide_purchase_link', true );

	if ( apply_filters( 'edd_show_oembed_purchase_links', $show ) ) {
		echo '<style>.wp-embed-edd-price { margin: 20px 0 0 0; }</style>';
		echo '<div class="wp-embed-edd-price">';
			if ( edd_has_variable_prices( get_the_ID() ) ) {
				echo edd_price_range( get_the_ID() );
			} else {
				edd_price( get_the_ID(), true );
			}

		echo '</div>';
	}
}
add_action( 'embed_content', 'edd_add_oembed_price' );

/**
 * Remove comments button for download embeds
 *
 * @since 2.6
 * @return  void
 */
function edd_remove_embed_comments_button() {
	global $post;

	$hide_comments = apply_filters( 'edd_embed_hide_comments', true, $post );

	if ( ! empty( $post ) && $post->post_type == 'download' && true === $hide_comments ) {
		remove_action( 'embed_content_meta', 'print_embed_comments_button' );
	}
}
add_action( 'embed_content_meta', 'edd_remove_embed_comments_button', 5 );

/**
 * Get a fully formatted title of a bundle item
 *
 * @since 2.7
 *
 * @param array $bundle_item Bundle item.
 * @return string Bundle item title.
 */
function edd_get_bundle_item_title( $bundle_item ) {
	$bundle_item_pieces = explode( '_', $bundle_item );
	$bundle_item_id = $bundle_item_pieces[0];
	$bundle_price_id = isset( $bundle_item_pieces[1] ) ? $bundle_item_pieces[1] : null;

	$prices = edd_get_variable_prices( $bundle_item_id );
	$bundle_title = get_the_title( $bundle_item_id );

	if ( null !== $bundle_price_id ) {
		$bundle_title .= ' - ' . $prices[ $bundle_price_id ]['name'];
	}

	return $bundle_title;
}

/**
 * Retrieve the ID of an item in a bundle.
 *
 * @since 2.7
 *
 * @param array $bundle_item Bundle item.
 * @return string Bundle item ID.
 */
function edd_get_bundle_item_id( $bundle_item ) {
	$bundle_item_pieces = explode( '_', $bundle_item );
	$bundle_item_id = $bundle_item_pieces[0];
	return $bundle_item_id;
}

/**
 * Retrieve the price ID of a bundle item.
 *
 * @since 2.7
 *
 * @param array $bundle_item Bundle item.
 * @return string Bundle item ID.
 */
function edd_get_bundle_item_price_id( $bundle_item ) {
	$bundle_item_pieces = explode( '_', $bundle_item );
	$bundle_item_id = $bundle_item_pieces[0];
	$bundle_price_id = isset( $bundle_item_pieces[1] ) ? $bundle_item_pieces[1] : null;

	return $bundle_price_id;
}

/**
 * Load a template file for a single download item.
 *
 * This is a wrapper function for backwards compatibility so the
 * shortcode's attributes can be passed to the template file via
 * a global variable.
 *
 * @since 2.8.0
 *
 * @param array $atts The [downloads] shortcode attributes.
 * @param int   $i The current item count.
 */
function edd_download_shortcode_item( $atts, $i ) {
	global $edd_download_shortcode_item_atts, $edd_download_shortcode_item_i;

	/**
	 * The variables are registered as part of the global scope so the template can access them.
	 */
	$edd_download_shortcode_item_atts = $atts;
	$edd_download_shortcode_item_i = $i;

	edd_get_template_part( 'shortcode', 'download' );
}
add_action( 'edd_download_shortcode_item', 'edd_download_shortcode_item', 10, 2 );

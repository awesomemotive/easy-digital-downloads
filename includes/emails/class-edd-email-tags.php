<?php
/**
 * Easy Digital Downloads API for creating Email template tags
 *
 * Email tags are wrapped in { }
 *
 * A few examples:
 *
 * {download_list}
 * {name}
 * {sitename}
 *
 *
 * To replace tags in content, use: edd_do_email_tags( $content, payment_id );
 *
 * To add tags, use: edd_add_email_tag( $tag, $description, $func ). Be sure to wrap edd_add_email_tag()
 * in a function hooked to the 'edd_add_email_tags' action
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 * @author      Barry Kooij
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class EDD_Email_Template_Tags {

	/**
	 * Container for storing all tags
	 *
	 * @since 1.9
	 */
	private $tags = array();

	/**
	 * Payment ID
	 *
	 * @since 1.9
	 */
	private $payment_id;

	/**
	 * Add an email tag
	 *
	 * @since 1.9
	 *
	 * @param string   $tag  Email tag to be replace in email
	 * @param callable $func Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) ) {
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	}

	/**
	 * Remove an email tag
	 *
	 * @since 1.9
	 *
	 * @param string $tag Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	}

	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since 1.9
	 *
	 * @param string $tag Email tag that will be searched
	 *
	 * @return bool
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}

	/**
	 * Returns a list of all email tags
	 *
	 * @since 1.9
	 *
	 * @return array
	 */
	public function get_tags() {
		return $this->tags;
	}

	/**
	 * Search content for email tags and filter email tags through their hooks
	 *
	 * @param string $content Content to search for email tags
	 * @param int $payment_id The payment id
	 *
	 * @since 1.9
	 *
	 * @return string Content with email tags filtered out.
	 */
	public function do_tags( $content, $payment_id ) {

		// Check if there is atleast one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->payment_id = $payment_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->payment_id = null;

		return $new_content;
	}

	/**
	 * Do a specific tag, this function should not be used. Please use edd_do_email_tags instead.
	 *
	 * @since 1.9
	 *
	 * @param $m message
	 *
	 * @return mixed
	 */
	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[$tag]['func'], $this->payment_id, $tag );
	}

}

/**
 * Add an email tag
 *
 * @since 1.9
 *
 * @param string   $tag  Email tag to be replace in email
 * @param callable $func Hook to run when email tag is found
 */
function edd_add_email_tag( $tag, $description, $func ) {
	EDD()->email_tags->add( $tag, $description, $func );
}

/**
 * Remove an email tag
 *
 * @since 1.9
 *
 * @param string $tag Email tag to remove hook from
 */
function edd_remove_email_tag( $tag ) {
	EDD()->email_tags->remove( $tag );
}

/**
 * Check if $tag is a registered email tag
 *
 * @since 1.9
 *
 * @param string $tag Email tag that will be searched
 *
 * @return bool
 */
function edd_email_tag_exists( $tag ) {
	return EDD()->email_tags->email_tag_exists( $tag );
}

/**
 * Get all email tags
 *
 * @since 1.9
 *
 * @return array
 */
function edd_get_email_tags() {
	return EDD()->email_tags->get_tags();
}

/**
 * Get a formatted HTML list of all available email tags
 *
 * @since 1.9
 *
 * @return string
 */
function edd_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = (array) edd_get_email_tags();

	// Check
	if ( count( $email_tags ) > 0 ) {

		// Loop
		foreach ( $email_tags as $email_tag ) {

			// Add email tag to list
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br/>';

		}

	}

	// Return the list
	return $list;
}

/**
 * Search content for email tags and filter email tags through their hooks
 *
 * @param string $content Content to search for email tags
 * @param int $payment_id The payment id
 *
 * @since 1.9
 *
 * @return string Content with email tags filtered out.
 */
function edd_do_email_tags( $content, $payment_id ) {

	// Replace all tags
	$content = EDD()->email_tags->do_tags( $content, $payment_id );

	// Maintaining backwards compatibility
	$content = apply_filters( 'edd_email_template_tags', $content, edd_get_payment_meta( $payment_id ), $payment_id );

	// Return content
	return $content;
}

/**
 * Load email tags
 *
 * @since 1.9
 */
function edd_load_email_tags() {
	do_action( 'edd_add_email_tags' );
}
add_action( 'init', 'edd_load_email_tags', -999 );

/**
 * Add default EDD email template tags
 *
 * @since 1.9
 */
function edd_setup_email_tags() {

	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'download_list',
			'description' => __( 'A list of download links for each download purchased', 'easy-digital-downloads' ),
			'function'    => 'text/html' == EDD()->emails->get_content_type() ? 'edd_email_tag_download_list' : 'edd_email_tag_download_list_plain'
		),
		array(
			'tag'         => 'file_urls',
			'description' => __( 'A plain-text list of download URLs for each download purchased', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_file_urls'
		),
		array(
			'tag'         => 'name',
			'description' => __( "The buyer's first name", 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_first_name'
		),
		array(
			'tag'         => 'fullname',
			'description' => __( "The buyer's full name, first and last", 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_fullname'
		),
		array(
			'tag'         => 'username',
			'description' => __( "The buyer's user name on the site, if they registered an account", 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_username'
		),
		array(
			'tag'         => 'user_email',
			'description' => __( "The buyer's email address", 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_user_email'
		),
		array(
			'tag'         => 'billing_address',
			'description' => __( 'The buyer\'s billing address', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_billing_address'
		),
		array(
			'tag'         => 'date',
			'description' => __( 'The date of the purchase', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_date'
		),
		array(
			'tag'         => 'subtotal',
			'description' => __( 'The price of the purchase before taxes', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_subtotal'
		),
		array(
			'tag'         => 'tax',
			'description' => __( 'The taxed amount of the purchase', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_tax'
		),
		array(
			'tag'         => 'price',
			'description' => __( 'The total price of the purchase', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_price'
		),
		array(
			'tag'         => 'payment_id',
			'description' => __( 'The unique ID number for this purchase', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_payment_id'
		),
		array(
			'tag'         => 'receipt_id',
			'description' => __( 'The unique ID number for this purchase receipt', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_receipt_id'
		),
		array(
			'tag'         => 'payment_method',
			'description' => __( 'The method of payment used for this purchase', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_payment_method'
		),
		array(
			'tag'         => 'sitename',
			'description' => __( 'Your site name', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_sitename'
		),
		array(
			'tag'         => 'receipt_link',
			'description' => __( 'Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly.', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_receipt_link'
		),
		array(
			'tag'         => 'discount_codes',
			'description' => __( 'Adds a list of any discount codes applied to this purchase', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_discount_codes'
		),
		array(
			'tag'         => 'ip_address',
			'description' => __( 'The buyer\'s IP Address', 'easy-digital-downloads' ),
			'function'    => 'edd_email_tag_ip_address'
		)
	);

	// Apply edd_email_tags filter
	$email_tags = apply_filters( 'edd_email_tags', $email_tags );

	// Add email tags
	foreach ( $email_tags as $email_tag ) {
		edd_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}

}
add_action( 'edd_add_email_tags', 'edd_setup_email_tags' );

/**
 * Email template tag: download_list
 * A list of download links for each download purchased
 *
 * @param int $payment_id
 *
 * @return string download_list
 */
function edd_email_tag_download_list( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );

	$payment_data  = $payment->get_meta();
	$download_list = '<ul>';
	$cart_items    = $payment->cart_details;
	$email         = $payment->email;

	if ( $cart_items ) {
		$show_names = apply_filters( 'edd_email_show_names', true );
		$show_links = apply_filters( 'edd_email_show_links', true );

		foreach ( $cart_items as $item ) {

			if ( edd_use_skus() ) {
				$sku = edd_get_download_sku( $item['id'] );
			}

			if ( edd_item_quantities_enabled() ) {
				$quantity = $item['quantity'];
			}

			$price_id = edd_get_cart_item_price_id( $item );
			if ( $show_names ) {

				$title = '<strong>' . get_the_title( $item['id'] ) . '</strong>';

				if ( ! empty( $quantity ) && $quantity > 1 ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'Quantity', 'easy-digital-downloads' ) . ': ' . $quantity;
				}

				if ( ! empty( $sku ) ) {
					$title .= "&nbsp;&ndash;&nbsp;" . __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
				}

				if( ! empty( $price_id ) && 0 !== $price_id ){
					$title .= "&nbsp;&ndash;&nbsp;" . edd_get_price_option_name( $item['id'], $price_id, $payment_id );
				}

				$download_list .= '<li>' . apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id ) . '<br/>';
			}

			$files = edd_get_download_files( $item['id'], $price_id );

			if ( ! empty( $files ) ) {

				foreach ( $files as $filekey => $file ) {

					if ( $show_links ) {
						$download_list .= '<div>';
							$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
							$download_list .= '<a href="' . esc_url_raw( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
							$download_list .= '</div>';
					} else {
						$download_list .= '<div>';
							$download_list .= edd_get_file_name( $file );
						$download_list .= '</div>';
					}

				}

			} elseif ( edd_is_bundled_product( $item['id'] ) ) {

				$bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item['id'], $price_id ), $item, $payment_id, 'download_list' );

				foreach ( $bundled_products as $bundle_item ) {

					$download_list .= '<div class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

					$download_files = edd_get_download_files( edd_get_bundle_item_id( $bundle_item ), edd_get_bundle_item_price_id( $bundle_item ) );

					foreach ( $download_files as $filekey => $file ) {
						if ( $show_links ) {
							$download_list .= '<div>';
							$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
							$download_list .= '<a href="' . esc_url( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
							$download_list .= '</div>';
						} else {
							$download_list .= '<div>';
							$download_list .= edd_get_file_name( $file );
							$download_list .= '</div>';
						}
					}
				}

			} else {

				$no_downloads_message = apply_filters( 'edd_receipt_no_files_found_text', __( 'No downloadable files found.', 'easy-digital-downloads' ), $item['id'] );
				$no_downloads_message = apply_filters( 'edd_email_receipt_no_downloads_message', $no_downloads_message, $item['id'], $price_id, $payment_id );

				if ( ! empty( $no_downloads_message ) ){
					$download_list .= '<div>';
						$download_list .= $no_downloads_message;
					$download_list .= '</div>';
				}
			}


			if ( '' != edd_get_product_notes( $item['id'] ) ) {
				$download_list .= ' &mdash; <small>' . edd_get_product_notes( $item['id'] ) . '</small>';
			}


			if ( $show_names ) {
				$download_list .= '</li>';
			}
		}
	}
	$download_list .= '</ul>';

	return $download_list;
}

/**
 * Email template tag: download_list
 * A list of download links for each download purchased in plaintext
 *
 * @since 2.1.1
 * @param int $payment_id
 *
 * @return string download_list
 */
function edd_email_tag_download_list_plain( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );

	$payment_data  = $payment->get_meta();
	$cart_items    = $payment->cart_details;
	$email         = $payment->email;
	$download_list = '';

	if ( $cart_items ) {
		$show_names = apply_filters( 'edd_email_show_names', true );
		$show_links = apply_filters( 'edd_email_show_links', true );

		foreach ( $cart_items as $item ) {

			if ( edd_use_skus() ) {
				$sku = edd_get_download_sku( $item['id'] );
			}

			if ( edd_item_quantities_enabled() ) {
				$quantity = $item['quantity'];
			}

			$price_id = edd_get_cart_item_price_id( $item );
			if ( $show_names ) {

				$title = get_the_title( $item['id'] );

				if ( ! empty( $quantity ) && $quantity > 1 ) {
					$title .= __( 'Quantity', 'easy-digital-downloads' ) . ': ' . $quantity;
				}

				if ( ! empty( $sku ) ) {
					$title .= __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
				}

				if ( $price_id !== null ) {
					$title .= edd_get_price_option_name( $item['id'], $price_id, $payment_id );
				}

				$download_list .= "\n";

				$download_list .= apply_filters( 'edd_email_receipt_download_title', $title, $item, $price_id, $payment_id )  . "\n";
			}

			$files = edd_get_download_files( $item['id'], $price_id );

			if ( ! empty( $files ) ) {

				foreach ( $files as $filekey => $file ) {
					if( $show_links ) {
						$download_list .= "\n";
						$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );
						$download_list .= edd_get_file_name( $file ) . ': ' . $file_url . "\n";
					} else {
						$download_list .= "\n";
						$download_list .= edd_get_file_name( $file ) . "\n";
					}
				}

			} elseif ( edd_is_bundled_product( $item['id'] ) ) {

				$bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item['id'] ), $item, $payment_id, 'download_list' );

				foreach ( $bundled_products as $bundle_item ) {

					$download_list .= '<div class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

					$files = edd_get_download_files( $bundle_item );

					foreach ( $files as $filekey => $file ) {
						if( $show_links ) {
							$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
							$download_list .= edd_get_file_name( $file ) . ': ' . $file_url . "\n";
						} else {
							$download_list .= edd_get_file_name( $file ) . "\n";
						}
					}
				}
			}


			if ( '' != edd_get_product_notes( $item['id'] ) ) {
				$download_list .= "\n";
				$download_list .= edd_get_product_notes( $item['id'] ) . "\n";
			}
		}
	}

	return $download_list;
}

/**
 * Email template tag: file_urls
 * A plain-text list of download URLs for each download purchased
 *
 * @param int $payment_id
 *
 * @return string $file_urls
 */
function edd_email_tag_file_urls( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );

	$payment_data = $payment->get_meta();
	$file_urls    = '';
	$cart_items   = $payment->cart_details;
	$email        = $payment->email;

	foreach ( $cart_items as $item ) {

		$price_id = edd_get_cart_item_price_id( $item );
		$files    = edd_get_download_files( $item['id'], $price_id );

		if ( $files ) {
			foreach ( $files as $filekey => $file ) {
				$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id );

				$file_urls .= esc_html( $file_url ) . '<br/>';
			}
		}
		elseif ( edd_is_bundled_product( $item['id'] ) ) {

			$bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item['id'] ), $item, $payment_id, 'file_urls' );

			foreach ( $bundled_products as $bundle_item ) {

				$files = edd_get_download_files( $bundle_item );
				foreach ( $files as $filekey => $file ) {
					$file_url = edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id );
					$file_urls .= esc_html( $file_url ) . '<br/>';
				}

			}
		}

	}

	return $file_urls;
}

/**
 * Email template tag: name
 * The buyer's first name
 *
 * @param int $payment_id
 *
 * @return string name
 */
function edd_email_tag_first_name( $payment_id ) {
	$payment   = new EDD_Payment( $payment_id );
	$user_info = $payment->user_info;

	if( empty( $user_info) ) {
		return '';
	}

	$email_name   = edd_get_email_names( $user_info, $payment );

	return $email_name['name'];
}

/**
 * Email template tag: fullname
 * The buyer's full name, first and last
 *
 * @param int $payment_id
 *
 * @return string fullname
 */
function edd_email_tag_fullname( $payment_id ) {
	$payment   = new EDD_Payment( $payment_id );
	$user_info = $payment->user_info;

	if( empty( $user_info ) ) {
		return '';
	}

	$email_name   = edd_get_email_names( $user_info, $payment );
	return $email_name['fullname'];
}

/**
 * Email template tag: username
 * The buyer's user name on the site, if they registered an account
 *
 * @param int $payment_id
 *
 * @return string username
 */
function edd_email_tag_username( $payment_id ) {
	$payment   = new EDD_Payment( $payment_id );
	$user_info = $payment->user_info;

	if( empty( $user_info ) ) {
		return '';
	}

	$email_name   = edd_get_email_names( $user_info, $payment );
	return $email_name['username'];
}

/**
 * Email template tag: user_email
 * The buyer's email address
 *
 * @param int $payment_id
 *
 * @return string user_email
 */
function edd_email_tag_user_email( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );

	return $payment->email;
}

/**
 * Email template tag: billing_address
 * The buyer's billing address
 *
 * @param int $payment_id
 *
 * @return string billing_address
 */
function edd_email_tag_billing_address( $payment_id ) {

	$user_info    = edd_get_payment_meta_user_info( $payment_id );
	$user_address = ! empty( $user_info['address'] ) ? $user_info['address'] : array( 'line1' => '', 'line2' => '', 'city' => '', 'country' => '', 'state' => '', 'zip' => '' );

	$return = $user_address['line1'] . "\n";
	if( ! empty( $user_address['line2'] ) ) {
		$return .= $user_address['line2'] . "\n";
	}
	$return .= $user_address['city'] . ' ' . $user_address['zip'] . ' ' . $user_address['state'] . "\n";
	$return .= $user_address['country'];

	return $return;
}

/**
 * Email template tag: date
 * Date of purchase
 *
 * @param int $payment_id
 *
 * @return string date
 */
function edd_email_tag_date( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	return date_i18n( get_option( 'date_format' ), strtotime( $payment->date ) );
}

/**
 * Email template tag: subtotal
 * Price of purchase before taxes
 *
 * @param int $payment_id
 *
 * @return string subtotal
 */
function edd_email_tag_subtotal( $payment_id ) {
	$payment  = new EDD_Payment( $payment_id );
	$subtotal = edd_currency_filter( edd_format_amount( $payment->subtotal ), $payment->currency );
	return html_entity_decode( $subtotal, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: tax
 * The taxed amount of the purchase
 *
 * @param int $payment_id
 *
 * @return string tax
 */
function edd_email_tag_tax( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	$tax     = edd_currency_filter( edd_format_amount( $payment->tax ), $payment->currency );
	return html_entity_decode( $tax, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: price
 * The total price of the purchase
 *
 * @param int $payment_id
 *
 * @return string price
 */
function edd_email_tag_price( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	$price   = edd_currency_filter( edd_format_amount( $payment->total ), $payment->currency );
	return html_entity_decode( $price, ENT_COMPAT, 'UTF-8' );
}

/**
 * Email template tag: payment_id
 * The unique ID number for this purchase
 *
 * @param int $payment_id
 *
 * @return int payment_id
 */
function edd_email_tag_payment_id( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	return $payment->number;
}

/**
 * Email template tag: receipt_id
 * The unique ID number for this purchase receipt
 *
 * @param int $payment_id
 *
 * @return string receipt_id
 */
function edd_email_tag_receipt_id( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	return $payment->key;
}

/**
 * Email template tag: payment_method
 * The method of payment used for this purchase
 *
 * @param int $payment_id
 *
 * @return string gateway
 */
function edd_email_tag_payment_method( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	return edd_get_gateway_checkout_label( $payment->gateway );
}

/**
 * Email template tag: sitename
 * Your site name
 *
 * @param int $payment_id
 *
 * @return string sitename
 */
function edd_email_tag_sitename( $payment_id ) {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}

/**
 * Email template tag: receipt_link
 * Adds a link so users can view their receipt directly on your website if they are unable to view it in the browser correctly
 *
 * @param $payment_id int
 *
 * @return string receipt_link
 */
function edd_email_tag_receipt_link( $payment_id ) {
	$receipt_url = esc_url( add_query_arg( array(
		'payment_key' => edd_get_payment_key( $payment_id ),
		'edd_action'  => 'view_receipt'
	), home_url() ) );
	$formatted   = sprintf( __( '%1$sView it in your browser %2$s', 'easy-digital-downloads' ), '<a href="' . $receipt_url . '">', '&raquo;</a>' );

	if ( edd_get_option( 'email_template' ) !== 'none' ) {
		return $formatted;
	} else {
		return $receipt_url;
	}
}

/**
 * Email template tag: discount_codes
 * Adds a list of any discount codes applied to this purchase
 *
 * @since  2.0
 * @param int $payment_id
 * @return string $discount_codes
 */
function edd_email_tag_discount_codes( $payment_id ) {
	$user_info = edd_get_payment_meta_user_info( $payment_id );

	$discount_codes = '';

	if( isset( $user_info['discount'] ) && $user_info['discount'] !== 'none' ) {
		$discount_codes = $user_info['discount'];
	}

	return $discount_codes;
}

/**
 * Email template tag: IP address
 * IP address of the customer
 *
 * @since  2.3
 * @param int $payment_id
 * @return string IP address
 */
function edd_email_tag_ip_address( $payment_id ) {
	$payment = new EDD_Payment( $payment_id );
	return $payment->ip;
}

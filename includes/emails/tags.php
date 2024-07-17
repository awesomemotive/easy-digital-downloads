<?php
/**
 * Email template tag helpers.
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
 * To add tags, use: edd_add_email_tag( $tag, $description, $func, $label ). Be sure to wrap edd_add_email_tag()
 * in a function hooked to the 'edd_add_email_tags' action
 *
 * @package     EDD
 * @subpackage  Email
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.0
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Add an email tag.
 *
 * @since 1.9
 * @since 3.0 Added $label parameter.
 *
 * @param string     $tag         Email tag to be replace in email.
 * @param string     $description Tag description.
 * @param string     $func        Callback function to run when email tag is found.
 * @param string     $label       Tag label.
 * @param null|array $contexts    Contexts in which the tag can be used. If null, the tag will default to order.
 * @param null|array $recipients  Recipients for which the tag can be used. If null, the tag will default to all.
 */
function edd_add_email_tag( $tag = '', $description = '', $func = '', $label = '', $contexts = null, $recipients = null ) {
	EDD()->email_tags->add( $tag, $description, $func, $label, $contexts, $recipients );
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
 * @param string $tag       Email tag that will be searched.
 * @param array  $context   Context in which the email is being sent.
 * @param string $recipient The recipient of the email.
 * @return bool
 */
function edd_email_tag_exists( $tag, $context = '', $recipient = '' ) {
	return EDD()->email_tags->email_tag_exists( $tag, $context, $recipient );
}

/**
 * Get all email tags
 *
 * @since 1.9
 * @param string $context   Context in which the email is being sent.
 * @param string $recipient The recipient of the email.
 * @return array
 */
function edd_get_email_tags( $context = '', $recipient = '' ) {
	return EDD()->email_tags->get( $context, $recipient );
}

/**
 * Get a formatted HTML list of all available email tags.
 *
 * @since 1.9
 *
 * @return string
 */
function edd_get_emails_tags_list() {

	// Begin with empty list.
	$list = '';

	// Get all tags.
	$email_tags = (array) edd_get_email_tags();

	// Check.
	if ( count( $email_tags ) > 0 ) {

		// Loop.
		foreach ( $email_tags as $email_tag ) {

			// Add email tag to list.
			$list .= '<code>{' . $email_tag['tag'] . '}</code> - ' . $email_tag['description'] . '<br/>';
		}
	}

	// Return the list.
	return $list;
}

/**
 * Search content for email tags and filter email tags through their hooks.
 *
 * @since 1.9
 * @since 3.0 Renamed `$payment_id` parameter to `$order_id`.
 *            Set default value of `$order_id` to 0.
 *            Set default value of `$content` to empty string.
 *
 * @param string                         $content      Content to search for email tags.
 * @param int                            $order_id     Object ID.
 * @param object                         $email_object This could be an order, a user, license, subscription, etc.
 * @param string|\EDD\Emails\Types\Email $context      Context in which the email is being sent. This should match the object if provided.
 *
 * @return string Content with email tags filtered out.
 */
function edd_do_email_tags( $content = '', $order_id = 0, $email_object = null, $context = 'order' ) {

	// Replace all tags.
	$content = EDD()->email_tags->do_tags( $content, $order_id, $email_object, $context );

	if ( 'order' === $context && false !== has_filter( 'edd_email_template_tags' ) ) {
		// Maintaining backwards compatibility.
		$content = apply_filters( 'edd_email_template_tags', $content, edd_get_payment_meta( $order_id ), $order_id );
	}

	return $content;
}

/**
 * Load email tags.
 *
 * @since 1.9
 */
function edd_load_email_tags() {
	do_action( 'edd_add_email_tags' );
}
add_action( 'init', 'edd_load_email_tags', -999 );

/**
 * Add default EDD email template tags.
 *
 * @since 1.9
 */
function edd_setup_email_tags() {
	$tags = new EDD\Emails\Tags\Registry();
	$tags->register();
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
function edd_email_tag_download_list( $payment_id, $order = null ) {
	if ( ! $order ) {
		$order = edd_get_order( $payment_id );
	}
	if ( ! $order ) {
		return '';
	}

	$download_list = '<ul>';
	$needs_notes   = array();

	if ( $order->get_items() ) {
		$show_names = apply_filters( 'edd_email_show_names', true );
		$show_links = apply_filters( 'edd_email_show_links', true );

		foreach ( $order->get_items() as $item ) {

			if ( edd_use_skus() ) {
				$sku = edd_get_download_sku( $item->product_id );
			}

			if ( edd_item_quantities_enabled() ) {
				$quantity = $item->quantity;
			}

			if ( $show_names ) {

				$title = '<strong>' . $item->product_name . '</strong>';

				if ( ! empty( $quantity ) && $quantity > 1 ) {
					$title .= '&nbsp;&ndash;&nbsp;' . __( 'Quantity', 'easy-digital-downloads' ) . ': ' . $quantity;
				}

				if ( ! empty( $sku ) ) {
					$title .= '&nbsp;&ndash;&nbsp;' . __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
				}

				if ( has_filter( 'edd_email_receipt_download_title' ) ) {
					$payment    = edd_get_payment( $payment_id );
					$cart_items = $payment->cart_details;
					$title      = apply_filters(
						'edd_email_receipt_download_title',
						$title,
						$cart_items[ $item->cart_index ],
						$item->price_id,
						$payment_id
					);
				}
				$download_list .= '<li>' . $title . '<br/>';
			}

			$files = edd_get_download_files( $item->product_id, $item->price_id );

			if ( ! empty( $files ) ) {

				foreach ( $files as $filekey => $file ) {

					if ( $show_links ) {
						$download_list .= '<div>';
						$file_url       = edd_get_download_file_url( $order, $order->email, $filekey, $item->product_id, $item->price_id );
						$download_list .= '<a href="' . esc_url_raw( $file_url ) . '">' . edd_get_file_name( $file ) . '</a>';
						$download_list .= '</div>';
					} else {
						$download_list .= '<div>';
						$download_list .= edd_get_file_name( $file );
						$download_list .= '</div>';
					}
				}
			} elseif ( edd_is_bundled_product( $item->product_id ) ) {

				$bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item->product_id, $item->price_id ), $item, $payment_id, 'download_list' );

				foreach ( $bundled_products as $bundle_item ) {

					$download_list .= '<div class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

					$bundle_item_id       = edd_get_bundle_item_id( $bundle_item );
					$bundle_item_price_id = edd_get_bundle_item_price_id( $bundle_item );
					$download_files       = edd_get_download_files( $bundle_item_id, $bundle_item_price_id );

					foreach ( $download_files as $filekey => $file ) {
						if ( $show_links ) {
							$download_list .= '<div>';
							$file_url       = edd_get_download_file_url( $order, $order->email, $filekey, $bundle_item_id, $bundle_item_price_id );
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

				$no_downloads_message = apply_filters( 'edd_receipt_no_files_found_text', __( 'No downloadable files found.', 'easy-digital-downloads' ), $item->product_id );
				$no_downloads_message = apply_filters( 'edd_email_receipt_no_downloads_message', $no_downloads_message, $item->product_id, $item->price_id, $payment_id );

				if ( ! empty( $no_downloads_message ) ) {
					$download_list     .= '<div>';
						$download_list .= $no_downloads_message;
					$download_list     .= '</div>';
				}
			}

			if ( ! array_key_exists( $item->product_id, $needs_notes ) ) {
				$item_notes = edd_get_product_notes( $item->product_id );
				if ( $item_notes ) {
					$needs_notes[ $item->product_id ] = array(
						'item_name'  => get_the_title( $item->product_id ),
						'item_notes' => $item_notes,
					);
				}
			}

			if ( $show_names ) {
				$download_list .= '</li>';
			}
		}
	}
	$download_list .= '</ul>';

	// Remove any empty values.
	$needs_notes = array_filter( $needs_notes );
	if ( ! empty( $needs_notes ) ) {
		$download_list .= __( 'Additional information about your purchase:', 'easy-digital-downloads' );

		$download_list .= '<ul>';
		foreach ( $needs_notes as $note ) {
			$download_list .= '<li>' . $note['item_name'] . "\n" . '<small>' . $note['item_notes'] . '</small></li>';
		}
		$download_list .= '</ul>';
	}

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
	$order   = edd_get_order( $payment_id );

	$payment_data  = $payment->get_meta();
	$cart_items    = $payment->cart_details;
	$download_list = '';

	if ( $order->get_items() ) {
		$show_names = apply_filters( 'edd_email_show_names', true );
		$show_links = apply_filters( 'edd_email_show_links', true );

		foreach ( $order->get_items() as $item ) {

			if ( edd_use_skus() ) {
				$sku = edd_get_download_sku( $item->product_id );
			}

			if ( edd_item_quantities_enabled() ) {
				$quantity = $item->quantity;
			}

			if ( $show_names ) {

				$title = $item->product_name;

				if ( ! empty( $quantity ) && $quantity > 1 ) {
					$title .= __( 'Quantity', 'easy-digital-downloads' ) . ': ' . $quantity;
				}

				if ( ! empty( $sku ) ) {
					$title .= __( 'SKU', 'easy-digital-downloads' ) . ': ' . $sku;
				}

				$download_list .= "\n";

				$download_list .= apply_filters( 'edd_email_receipt_download_title', $title, $cart_items[ $item->cart_index ], $item->price_id, $payment_id ) . "\n";
			}

			$files = edd_get_download_files( $item->product_id, $item->price_id );

			if ( ! empty( $files ) ) {

				foreach ( $files as $filekey => $file ) {
					if ( $show_links ) {
						$download_list .= "\n";
						$file_url       = edd_get_download_file_url( $order, $order->email, $filekey, $item->product_id, $item->price_id );
						$download_list .= edd_get_file_name( $file ) . ': ' . $file_url . "\n";
					} else {
						$download_list .= "\n";
						$download_list .= edd_get_file_name( $file ) . "\n";
					}
				}
			} elseif ( edd_is_bundled_product( $item->product_id ) ) {

				$bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item->product_id ), $cart_items[ $item->cart_index ], $payment_id, 'download_list' );

				foreach ( $bundled_products as $bundle_item ) {

					$download_list .= '<div class="edd_bundled_product"><strong>' . get_the_title( $bundle_item ) . '</strong></div>';

					$files = edd_get_download_files( $bundle_item );

					foreach ( $files as $filekey => $file ) {
						if ( $show_links ) {
							$file_url       = edd_get_download_file_url( $order, $order->email, $filekey, $bundle_item, $item->price_id );
							$download_list .= edd_get_file_name( $file ) . ': ' . $file_url . "\n";
						} else {
							$download_list .= edd_get_file_name( $file ) . "\n";
						}
					}
				}
			}

			if ( '' != edd_get_product_notes( $item->product_id ) ) {
				$download_list .= "\n";
				$download_list .= edd_get_product_notes( $item->product_id ) . "\n";
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
	$show_links   = apply_filters( 'edd_email_show_links', true );

	foreach ( $cart_items as $item ) {

		$price_id = edd_get_cart_item_price_id( $item );
		$files    = edd_get_download_files( $item['id'], $price_id );

		if ( $files ) {
			foreach ( $files as $filekey => $file ) {
				$file_url   = $show_links ? edd_get_download_file_url( $payment_data['key'], $email, $filekey, $item['id'], $price_id ) : '#';
				$file_urls .= esc_html( $file_url ) . '<br/>';
			}
		} elseif ( edd_is_bundled_product( $item['id'] ) ) {

			$bundled_products = apply_filters( 'edd_email_tag_bundled_products', edd_get_bundled_products( $item['id'] ), $item, $payment_id, 'file_urls' );

			foreach ( $bundled_products as $bundle_item ) {

				$files = edd_get_download_files( $bundle_item );
				foreach ( $files as $filekey => $file ) {
					$file_url   = $show_links ? edd_get_download_file_url( $payment_data['key'], $email, $filekey, $bundle_item, $price_id ) : '#';
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
function edd_email_tag_first_name( $payment_id, $email_object = null, $context = 'order' ) {
	$context = EDD()->email_tags->get_context( $context );
	if ( 'user' === $context ) {
		if ( ! $email_object instanceof WP_User ) {
			$email_object = new WP_User( $payment_id );
		}

		return ! empty( $email_object->first_name ) ? $email_object->first_name : '';
	}
	$payment   = new EDD_Payment( $payment_id );
	$user_info = $payment->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name = edd_get_email_names( $user_info, $payment );

	return $email_name['name'];
}

/**
 * Email template tag: fullname
 * The buyer's full name, first and last
 *
 * @param int        $payment_id   The ID of the object of the email.
 * @param null|mixed $email_object The object of the email.
 * @param string     $context      The context of the email.
 *
 * @return string fullname
 */
function edd_email_tag_fullname( $payment_id, $email_object = null, $context = 'order' ) {
	$context = EDD()->email_tags->get_context( $context );
	if ( 'user' === $context ) {
		if ( ! $email_object instanceof WP_User ) {
			$email_object = new WP_User( $payment_id );
		}

		return ! empty( $email_object->display_name ) ? $email_object->display_name : '';
	}

	if ( ! $email_object instanceof EDD\Orders\Order ) {
		$email_object = edd_get_order( $payment_id );
	}

	$customer = edd_get_customer( $email_object->customer_id );

	return ! empty( $customer->name ) ? $customer->name : '';
}

/**
 * Email template tag: username
 * The buyer's user name on the site, if they registered an account
 *
 * @param int        $object_id    The object ID. This can be an order ID or user ID.
 * @param null|mixed $email_object The object of the email.
 * @param string     $context      The context of the email.
 * @return string username
 */
function edd_email_tag_username( $object_id, $email_object = null, $context = 'order' ) {
	$context = EDD()->email_tags->get_context( $context );
	if ( 'user' === $context ) {
		if ( ! $email_object instanceof WP_User ) {
			$email_object = new WP_User( $object_id );
		}

		return ! empty( $email_object->user_login ) ? $email_object->user_login : '';
	}

	$payment   = new EDD_Payment( $object_id );
	$user_info = $payment->user_info;

	if ( empty( $user_info ) ) {
		return '';
	}

	$email_name = edd_get_email_names( $user_info, $payment );

	return $email_name['username'];
}

/**
 * Email template tag: user_email
 * The buyer's email address
 *
 * @param int        $object_id  The object ID. This can be an order ID or user ID.
 * @param null|mixed $email_object     The object of the email.
 * @param string     $context    The context of the email.
 * @return string user_email
 */
function edd_email_tag_user_email( $object_id, $email_object = null, $context = 'order' ) {
	$context = EDD()->email_tags->get_context( $context );
	if ( ! in_array( $context, array( 'order', 'user' ), true ) ) {
		return '';
	}
	if ( 'user' === $context ) {
		if ( ! $email_object instanceof WP_User ) {
			$email_object = new WP_User( $object_id );
		}

		return ! empty( $email_object->user_email ) ? $email_object->user_email : '';
	}

	$order = edd_get_order( $object_id );

	return $order->email;
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
	$user_address = ! empty( $user_info['address'] ) ? $user_info['address'] : array(
		'line1'   => '',
		'line2'   => '',
		'city'    => '',
		'country' => '',
		'state'   => '',
		'zip'     => '',
	);

	$return = $user_address['line1'] . "\n";
	if ( ! empty( $user_address['line2'] ) ) {
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
 * @param int        $payment_id   The ID of the object of the email.
 * @param null|mixed $email_object The object of the email.
 *
 * @return string date
 */
function edd_email_tag_date( $payment_id, $email_object = null ) {
	if ( ! $email_object instanceof EDD\Orders\Order ) {
		$email_object = edd_get_order( $payment_id );
	}

	return date_i18n( get_option( 'date_format' ), strtotime( $email_object->date_created ) );
}

/**
 * Email template tag: subtotal
 * Price of purchase before taxes
 *
 * @param int        $payment_id   The ID of the object of the email.
 * @param null|mixed $email_object The object of the email.
 *
 * @return string subtotal
 */
function edd_email_tag_subtotal( $payment_id, $email_object = null ) {
	if ( ! $email_object instanceof EDD\Orders\Order ) {
		$email_object = edd_get_order( $payment_id );
	}
	$subtotal = edd_currency_filter( edd_format_amount( $email_object->subtotal ), $email_object->currency );

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
function edd_email_tag_tax( $payment_id, $email_object = null ) {
	if ( ! $email_object instanceof EDD\Orders\Order ) {
		$email_object = edd_get_order( $payment_id );
	}
	$tax = edd_currency_filter( edd_format_amount( $email_object->tax ), $email_object->currency );

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
function edd_email_tag_price( $payment_id, $email_object = null ) {
	if ( ! $email_object instanceof EDD\Orders\Order ) {
		$email_object = edd_get_order( $payment_id );
	}
	$price = edd_currency_filter( edd_format_amount( $email_object->total ), $email_object->currency );

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
function edd_email_tag_payment_id( $payment_id, $email_object = null ) {
	if ( ! $email_object instanceof EDD\Orders\Order ) {
		$email_object = edd_get_order( $payment_id );
	}

	return $email_object->get_number();
}

/**
 * Email template tag: receipt_id
 * The unique ID number for this purchase receipt
 *
 * @param int $payment_id
 *
 * @return string receipt_id
 */
function edd_email_tag_receipt_id( $payment_id, $email_object = null ) {
	if ( ! $email_object instanceof EDD\Orders\Order ) {
		$email_object = edd_get_order( $payment_id );
	}

	return $email_object->payment_key;
}

/**
 * Email template tag: payment_method
 * The method of payment used for this purchase
 *
 * @param int $payment_id
 *
 * @return string gateway
 */
function edd_email_tag_payment_method( $payment_id, $email_object = null ) {
	if ( ! $email_object instanceof EDD\Orders\Order ) {
		$email_object = edd_get_order( $payment_id );
	}

	return edd_get_gateway_checkout_label( $email_object->gateway );
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
 * Email template tag: receipt
 *
 * Adds a link to the user's receipt page on the website.
 *
 * @param int $order_id
 * @return string
 */
function edd_email_tag_receipt( $order_id ) {

	return sprintf(
		'<a href="%s">%s</a>',
		esc_url( edd_get_receipt_page_uri( $order_id ) ),
		__( 'View Receipt', 'easy-digital-downloads' )
	);
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
	$receipt_url = esc_url(
		add_query_arg(
			array(
				'payment_key' => urlencode( edd_get_payment_key( $payment_id ) ),
				'edd_action'  => 'view_receipt',
			),
			home_url()
		)
	);
	if ( 'none' === edd_get_option( 'email_template' ) ) {
		return $receipt_url;
	}

	/* translators: 1: opening anchor tag, 2: closing anchor tag */
	return sprintf( __( '%1$sView it in your browser %2$s', 'easy-digital-downloads' ), '<a href="' . esc_url_raw( $receipt_url ) . '">', '&raquo;</a>' );
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

	if ( isset( $user_info['discount'] ) && $user_info['discount'] !== 'none' ) {
		$discount_codes = $user_info['discount'];
	}

	return $discount_codes;
}

/**
 * Email template tag: IP address
 * IP address of the customer
 *
 * @since  2.3
 * @param int $email_object_id
 * @return string IP address
 */
function edd_email_tag_ip_address( $email_object_id, $email_object = null, $context = 'order' ) {
	$context = EDD()->email_tags->get_context( $context );
	if ( 'user' === $context && ! is_null( $email_object ) ) {
		return $_SERVER['REMOTE_ADDR'];
	}

	if ( ! $email_object instanceof EDD\Orders\Order ) {
		$email_object = edd_get_order( $payment_id );
	}

	return $email_object->ip;
}

/**
 * Get various correctly formatted names used in emails
 *
 * @since 1.9
 * @since 3.2.0 - Moved to the tags.php file, as it is exclusively is used for email tags, even in extensions.
 *
 * @param $user_info
 * @param $payment   EDD_Payment for getting the names
 *
 * @return array $email_names
 */
function edd_get_email_names( $user_info, $payment = false ) {
	$email_names             = array();
	$email_names['fullname'] = '';

	if ( $payment instanceof EDD_Payment ) {

		$email_names['name']     = $payment->email;
		$email_names['username'] = $payment->email;
		if ( $payment->user_id > 0 ) {

			$user_data               = get_userdata( $payment->user_id );
			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			if ( ! empty( $user_data->user_login ) ) {
				$email_names['username'] = $user_data->user_login;
			}
		} elseif ( ! empty( $payment->first_name ) ) {

			$email_names['name']     = $payment->first_name;
			$email_names['fullname'] = trim( $payment->first_name . ' ' . $payment->last_name );
			$email_names['username'] = $payment->first_name;

		}
	} else {

		if ( is_serialized( $user_info ) ) {

			preg_match( '/[oO]\s*:\s*\d+\s*:\s*"\s*(?!(?i)(stdClass))/', $user_info, $matches );
			if ( ! empty( $matches ) ) {
				return array(
					'name'     => '',
					'fullname' => '',
					'username' => '',
				);
			} else {
				$user_info = maybe_unserialize( $user_info );
			}
		}

		if ( isset( $user_info['id'] ) && $user_info['id'] > 0 && isset( $user_info['first_name'] ) ) {
			$user_data               = get_userdata( $user_info['id'] );
			$email_names['name']     = $user_info['first_name'];
			$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username'] = $user_data->user_login;
		} elseif ( isset( $user_info['first_name'] ) ) {
			$email_names['name']     = $user_info['first_name'];
			$email_names['fullname'] = $user_info['first_name'] . ' ' . $user_info['last_name'];
			$email_names['username'] = $user_info['first_name'];
		} else {
			$email_names['name']     = $user_info['email'];
			$email_names['username'] = $user_info['email'];
		}
	}

	return $email_names;
}

<?php

namespace EDD\Emails\Tags;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Tags
 *
 * @since 3.3.0
 * @package EDD\Emails
 */
class Registry {

	/**
	 * Render instance.
	 *
	 * @since 3.3.0
	 * @var Render
	 */
	private $render;

	/**
	 * Tags constructor.
	 *
	 * @since 3.3.0
	 */
	public function __construct() {
		$this->render = new Render();
	}

	/**
	 * Registers the email tags.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function register() {
		$email_tags = $this->get_tags();

		// Add email tags.
		foreach ( $email_tags as $email_tag ) {
			$label      = isset( $email_tag['label'] ) ? $email_tag['label'] : '';
			$contexts   = isset( $email_tag['contexts'] ) ? $email_tag['contexts'] : null;
			$recipients = isset( $email_tag['recipients'] ) ? $email_tag['recipients'] : null;
			edd_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'], $label, $contexts, $recipients );
		}
	}

	/**
	 * Retrieves the email tags.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private function get_tags() {
		$email_tags = array(
			array(
				'tag'         => 'download_list',
				'label'       => __( 'Download List', 'easy-digital-downloads' ),
				'description' => __( 'A list of download links for each download purchased.', 'easy-digital-downloads' ),
				'function'    => 'text/html' === EDD()->emails->get_content_type()
					? 'edd_email_tag_download_list'
					: 'edd_email_tag_download_list_plain',
				'contexts'    => array( 'order' ),
			),
			array(
				'tag'         => 'file_urls',
				'label'       => __( 'File URLs', 'easy-digital-downloads' ),
				'description' => __( 'A plain-text list of download URLs for each download purchased.', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_file_urls',
				'contexts'    => array( 'order' ),
			),
			array(
				'tag'         => 'name',
				'label'       => __( 'First Name', 'easy-digital-downloads' ),
				'description' => __( "The buyer's (or user's) first name.", 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_first_name',
				'contexts'    => array( 'user', 'order', 'refund' ),
			),
			array(
				'tag'         => 'fullname',
				'label'       => __( 'Full Name', 'easy-digital-downloads' ),
				'description' => __( "The buyer's (or user's) full name: first and last.", 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_fullname',
				'contexts'    => array( 'user', 'order', 'refund' ),
			),
			array(
				'tag'         => 'username',
				'label'       => __( 'Username', 'easy-digital-downloads' ),
				'description' => __( "The buyer's (or user's) user name on the site, if they registered an account.", 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_username',
				'contexts'    => array( 'user', 'order', 'refund' ),
			),
			array(
				'tag'         => 'user_email',
				'label'       => __( 'Email', 'easy-digital-downloads' ),
				'description' => __( "The buyer's (or user's) email address.", 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_user_email',
				'contexts'    => array( 'user', 'order', 'refund' ),
			),
			array(
				'tag'         => 'billing_address',
				'label'       => __( 'Billing Address', 'easy-digital-downloads' ),
				'description' => __( "The buyer's billing address.", 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_billing_address',
				'contexts'    => array( 'order' ),
			),
			array(
				'tag'         => 'date',
				'label'       => __( 'Purchase Date', 'easy-digital-downloads' ),
				'description' => __( 'The date of the purchase.', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_date',
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'subtotal',
				'label'       => __( 'Subtotal', 'easy-digital-downloads' ),
				'description' => __( 'The price of the purchase before taxes.', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_subtotal',
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'tax',
				'label'       => __( 'Tax', 'easy-digital-downloads' ),
				'description' => __( 'The taxed amount of the purchase', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_tax',
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'fees_total',
				'label'       => __( 'Fees Total', 'easy-digital-downloads' ),
				'description' => __( 'The total fees on the order, formatted with currency.', 'easy-digital-downloads' ),
				'function'    => array( $this->render, 'fees_total' ),
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'fees_list',
				'label'       => __( 'Fees List', 'easy-digital-downloads' ),
				'description' => __( 'A list of all fees on the order, with amounts.', 'easy-digital-downloads' ),
				'function'    => array( $this->render, 'fees_list' ),
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'price',
				'label'       => __( 'Price', 'easy-digital-downloads' ),
				'description' => __( 'The total price of the purchase', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_price',
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'payment_id',
				'label'       => __( 'Payment ID', 'easy-digital-downloads' ),
				'description' => __( 'The unique identifier for this purchase.', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_payment_id',
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'receipt_id',
				'label'       => __( 'Receipt ID', 'easy-digital-downloads' ),
				'description' => __( 'The unique identifier for the receipt of this purchase.', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_receipt_id',
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'payment_method',
				'label'       => __( 'Payment Method', 'easy-digital-downloads' ),
				'description' => __( 'The method of payment used for this purchase.', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_payment_method',
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'sitename',
				'label'       => __( 'Site Name', 'easy-digital-downloads' ),
				'description' => __( 'Your site name.', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_sitename',
				'contexts'    => array(),
			),
			array(
				'tag'         => 'receipt',
				'label'       => __( 'Receipt', 'easy-digital-downloads' ),
				'description' => __( 'Links to the EDD success page with the text "View Receipt".', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_receipt',
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'receipt_link',
				'label'       => __( 'Receipt Link', 'easy-digital-downloads' ),
				'description' => __( 'Adds a link so users can view their receipt directly on a simplified page on your site if they are unable to view it in the browser correctly.', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_receipt_link',
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'discount_codes',
				'label'       => __( 'Discount Codes', 'easy-digital-downloads' ),
				'description' => __( 'Adds a list of any discount codes applied to this purchase.', 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_discount_codes',
				'contexts'    => array( 'order', 'refund' ),
			),
			array(
				'tag'         => 'ip_address',
				'label'       => __( 'IP Address', 'easy-digital-downloads' ),
				'description' => __( "The buyer's IP Address.", 'easy-digital-downloads' ),
				'function'    => 'edd_email_tag_ip_address',
				'contexts'    => array( 'order', 'refund', 'user' ),
			),
			array(
				'tag'         => 'login_link',
				'label'       => __( 'Login Link', 'easy-digital-downloads' ),
				'description' => __( 'The link to log into the site.', 'easy-digital-downloads' ),
				'function'    => array( $this->render, 'login_link' ),
				'contexts'    => array(),
			),
			array(
				'tag'         => 'refund_link',
				'label'       => __( 'Refund Link', 'easy-digital-downloads' ),
				'description' => __( 'The link to refund record in the EDD admin.', 'easy-digital-downloads' ),
				'function'    => array( $this->render, 'refund_link' ),
				'contexts'    => array( 'refund' ),
				'recipients'  => array( 'admin' ),
			),
			array(
				'tag'         => 'order_details_link',
				'label'       => __( 'Order Details Link', 'easy-digital-downloads' ),
				'description' => __( 'The link to the order details page in the EDD admin.', 'easy-digital-downloads' ),
				'function'    => array( $this->render, 'order_details_link' ),
				'contexts'    => array( 'order' ),
				'recipients'  => array( 'admin' ),
			),
			array(
				'tag'         => 'transaction_id',
				'label'       => __( 'Transaction ID', 'easy-digital-downloads' ),
				'description' => __( 'The merchant transaction ID for this order. This is for admin emails only.', 'easy-digital-downloads' ),
				'function'    => array( $this->render, 'transaction_id' ),
				'contexts'    => array( 'order' ),
				'recipients'  => array( 'admin' ),
			),
			array(
				'tag'         => 'password_link',
				'label'       => __( 'Password Reset Link', 'easy-digital-downloads' ),
				'description' => __( "The link to set the user's password. In an order receipt, this will only be included for the user's first purchase.", 'easy-digital-downloads' ),
				'function'    => array( $this->render, 'password_link' ),
				'contexts'    => array( 'order', 'user' ),
			),
			array(
				'tag'         => 'refund_amount',
				'label'       => __( 'Refund Amount', 'easy-digital-downloads' ),
				'description' => __( 'The amount that was refunded to the customer.', 'easy-digital-downloads' ),
				'function'    => array( $this->render, 'refund_amount' ),
				'contexts'    => array( 'refund' ),
			),
			array(
				'tag'         => 'refund_id',
				'label'       => __( 'Refund ID', 'easy-digital-downloads' ),
				'description' => __( 'The unique identifier for this refund.', 'easy-digital-downloads' ),
				'function'    => array( $this->render, 'refund_id' ),
				'contexts'    => array( 'refund' ),
			),
		);

		// Apply edd_email_tags filter.
		return apply_filters( 'edd_email_tags', $email_tags );
	}
}

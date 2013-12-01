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
 * To add tags, use: edd_add_email_tag( $tag, $description, $func ). Be sure to hook into 'edd_email_tags'
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2013, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.x
 * @author      Barry Kooij
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class EDD_Email_Template_Tags {

	// Instance
	private static $instance = null;

	// Container for storing all tags
	private $tags;

	// Payment ID
	private $payment_id;

	/**
	 * Method to get instance
	 *
	 * @return EDD_Email_Template_Tags
	 */
	public function get_instance() {
		if ( self::$instance === null ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Add hook for email tag
	 *
	 * @since 1.x
	 *
	 * @param string   $tag  Email tag to be replace in email
	 * @param callable $func Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if ( is_callable( $func ) )
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
	}

	/**
	 * Remove hook for email tag
	 *
	 * @since 1.x
	 *
	 * @param string $tag Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	}

	/**
	 * Whether a registered email tag exists names $tag
	 *
	 * @since 1.x
	 *
	 * @param string $tag Email tag that will be searched
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}

	/**
	 * Search content for email tags and filter email tags through their hooks
	 *
	 * @param string $content Content to search for email tags
	 *
	 * @return string Content with email tags filtered out.
	 */
	public function do_tags( $content, $payment_id ) {

		// Check if there is atleast one tag added
		if ( empty( $this->tags ) || ! is_array( $this->tags ) )
			return $content;

		$this->payment_id = $payment_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->payment_id = null;

		return $new_content;
	}

	public function do_tag( $m ) {

		// Get tag
		$tag = $m[1];

		// Return tag if tag not set
		if ( ! self::email_tag_exists( $tag ) )
			return $m[0];

		return call_user_func( $this->tags[$tag]['func'], $this->payment_id, $tag );
	}

}

/**
 * Functions
 */
function edd_add_email_tag( $tag, $description, $func ) {
	EDD_Email_Template_Tags::get_instance()->add( $tag, $description, $func );
}

function edd_remove_email_tag( $tag ) {
	EDD_Email_Template_Tags::get_instance()->remove( $tag );
}

function edd_email_tag_exists( $tag ) {
	return EDD_Email_Template_Tags::get_instance()->email_tag_exists( $tag );
}

function edd_do_email_tags( $content, $payment_id ) {

	// Replace all tags
	$content = EDD_Email_Template_Tags::get_instance()->do_tags( $content, $payment_id );

	// Maintaining backwards compatibility
	$content = apply_filters( 'edd_email_template_tags', $content, edd_get_payment_meta( $payment_id ), $payment_id );

	// Return content
	return $content;
}

/**
 * Load email tags
 */
function edd_load_email_tags() {
	do_action( 'edd_add_email_tags' );
}

add_action( 'init', 'edd_load_email_tags' );
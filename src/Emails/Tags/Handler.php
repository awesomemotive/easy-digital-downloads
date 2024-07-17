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
 * To replace tags in content, use: edd_do_email_tags( $content, object_id );
 *
 * To add tags, use: edd_add_email_tag( $tag, $description, $func ). Be sure to wrap edd_add_email_tag()
 * in a function hooked to the 'edd_add_email_tags' action
 *
 * @package     EDD
 * @subpackage  Emails
 * @copyright   Copyright (c) 2018, Easy Digital Downloads, LLC
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.9
 */

namespace EDD\Emails\Tags;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Handler
 *
 * @since 1.9
 */
class Handler {

	/**
	 * Container for storing all tags
	 *
	 * @since 1.9
	 * @var array
	 */
	private $tags = array();

	/**
	 * The object ID. Originally this was an order ID, but it can be any object ID.
	 *
	 * @since 1.9
	 * @var int
	 */
	private $object_id;

	/**
	 * Object
	 *
	 * @since 3.3.0
	 * @var object
	 */
	private $object;

	/**
	 * Email
	 *
	 * @since 3.3.0
	 * @var \EDD\Emails\Types\Email
	 */
	private $email;

	/**
	 * Context
	 *
	 * @since 3.3.0
	 * @var string
	 */
	private $context = '';

	/**
	 * Add an email tag
	 *
	 * @since 1.9
	 *
	 * @param string   $tag         Email tag to be replace in email.
	 * @param string   $description Description of the tag.
	 * @param callable $func        Hook to run when email tag is found.
	 * @param string   $label       Human readable tag label.
	 * @param array    $contexts    The contexts in which the email tag can be used. Added in 3.3.0.
	 * @param array    $recipients  The recipients for which the email tag can be used. Added in 3.3.0.
	 */
	public function add( $tag, $description, $func, $label = null, $contexts = null, $recipients = null ) {
		if ( is_null( $contexts ) || ! is_array( $contexts ) ) {
			$contexts = array( 'order' );
		}
		if ( ! is_callable( $func ) ) {
			return;
		}
		$tag_data = array(
			'tag'         => $tag,
			'label'       => ! empty( $label ) ? $label : ucwords( str_replace( '_', ' ', $tag ) ),
			'description' => $description,
			'func'        => $func,
			'contexts'    => $contexts,
			'recipients'  => $recipients,
		);

		$this->tags[ $this->get_unique_tag_key( $tag_data ) ] = $tag_data;
	}

	/**
	 * Remove an email tag
	 *
	 * @since 1.9
	 *
	 * @param string $tag Email tag to remove hook from.
	 */
	public function remove( $tag ) {
		if ( ! array_key_exists( $tag, $this->tags ) ) {
			$tag_name = $this->get_tag_by_name( $tag );
			if ( $tag_name ) {
				$tag = $this->get_unique_tag_key( $tag_name );
			}
		}

		unset( $this->tags[ $tag ] );
	}

	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since 1.9
	 *
	 * @param string $tag       Email tag key that will be searched.
	 * @param string $context   The context to get tags for.
	 * @param string $recipient The recipient to get tags for.
	 * @return bool
	 */
	public function email_tag_exists( $tag, $context = '', $recipient = '' ) {
		$tags = $this->get( $context, $recipient );
		if ( array_key_exists( $tag, $tags ) ) {
			return true;
		}

		$tag_by_name = $this->get_tag_by_name( $tag );

		return ! empty( $tag_by_name ) && array_key_exists( $this->get_unique_tag_key( $tag_by_name ), $tags );
	}

	/**
	 * Get all email tags
	 *
	 * @since 3.3.0
	 *
	 * @param string $context   The context to get tags for.
	 * @param string $recipient The recipient to get tags for.
	 * @return array
	 */
	public function get( $context = '', $recipient = '' ) {
		$tags = (array) $this->tags;
		if ( empty( $tags ) ) {
			return $tags;
		}
		if ( empty( $context ) && empty( $recipient ) ) {
			return $tags;
		}
		foreach ( $tags as $data ) {
			if (
				! empty( $context ) && ! empty( $data['contexts'] ) && ! in_array( $context, $data['contexts'], true ) ||
				! empty( $recipient ) && ! empty( $data['recipients'] ) && ! in_array( $recipient, $data['recipients'], true )
			) {
				unset( $tags[ $this->get_unique_tag_key( $data ) ] );
			}
		}

		return $tags;
	}

	/**
	 * Search content for email tags and filter email tags through their hooks
	 *
	 * @param string $content          Content to search for email tags.
	 * @param int    $object_id        The object ID. Originally this was an order ID, but it can be any object ID.
	 * @param object $email_object     The email object. This could be an order, license, user, etc.
	 * @param string $context_or_email The context or email object (\EDD\Emails\Types\Email).
	 *
	 * @since 1.9
	 * @since 3.3.0 Added $email_object and $context_or_email parameters.
	 *
	 * @return string Content with email tags filtered out.
	 */
	public function do_tags( $content, $object_id, $email_object = null, $context_or_email = '' ) {

		if ( $context_or_email instanceof \EDD\Emails\Types\Email ) {
			$this->email = $context_or_email;
		}
		$context = $this->get_context( $context_or_email );

		// Check if there is at least one tag added.
		if ( empty( $this->get( $context ) ) ) {
			return $content;
		}

		$this->object_id = $object_id;
		$this->object    = $email_object;
		$this->context   = $context;

		$new_content = $this->handle_content( $content );

		$this->object_id = null;
		$this->object    = null;
		$this->email     = null;
		$this->context   = '';

		return $new_content;
	}

	/**
	 * Handles the content of the email.
	 *
	 * @since 3.3.0
	 * @param string $content The content of the email.
	 * @return string
	 */
	private function handle_content( $content ) {
		$content = preg_replace_callback( '/{([A-Za-z0-9\-\_]+)}/s', array( $this, 'do_tag' ), $content );

		/**
		 * Apply filters to the email content.
		 *
		 * This function applies the 'edd_email_content_tags' filter to the provided content, along with additional parameters.
		 *
		 * @param string $content   The email content to be filtered.
		 * @param int    $object_id The ID of the object associated with the email.
		 * @param object $object    The object associated with the email.
		 * @param string $email     The email being sent.
		 * @param string $context   The context in which the email is being sent.
		 * @return string The filtered email content.
		 */
		return apply_filters( 'edd_email_content_tags', $content, $this->object_id, $this->object, $this->email, $this->context );
	}

	/**
	 * Do a specific tag, this function should not be used directly. Please use edd_do_email_tags instead.
	 *
	 * @since 1.9
	 *
	 * @param array $m Array of matches from preg_replace_callback.
	 * @return string
	 */
	public function do_tag( $m ) {

		// Get tag by name.
		$tag = $this->get_tag_by_name( $m[1], $this->context );
		if ( ! $tag ) {
			return $m[0];
		}

		$parameter = ! empty( $this->email ) ? $this->email : $this->context;

		return $this->can_do_tag( $tag ) ?
			call_user_func( $tag['func'], $this->object_id, $this->object, $parameter ) :
			$m[0];
	}

	/**
	 * Retrieves the context for the given email or context.
	 *
	 * @since 3.3.0
	 * @param string|array $email_or_context The email or context for which to retrieve the context.
	 * @return array The context for the given email or context.
	 */
	public function get_context( $email_or_context ) {
		if ( $email_or_context instanceof \EDD\Emails\Types\Email ) {
			return $email_or_context->get_context();
		}

		return ! empty( $email_or_context ) ? $email_or_context : 'order';
	}

	/**
	 * Check if a tag can be processed.
	 *
	 * @since 3.3.0
	 *
	 * @param array $tag_data Tag to check.
	 * @return bool
	 */
	private function can_do_tag( $tag_data ) {
		if ( ! is_callable( $tag_data['func'] ) ) {
			return false;
		}

		if ( ! empty( $tag_data['contexts'] ) && ! in_array( $this->context, $tag_data['contexts'], true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get a tag by name.
	 *
	 * @since 3.3.0
	 *
	 * @param string $name Name of tag to get.
	 * @return array|bool
	 */
	private function get_tag_by_name( $name, $context = '' ) {
		$tags = $this->get();
		foreach ( $tags as $tag ) {
			if ( $tag['tag'] !== $name ) {
				continue;
			}
			$context_matches = true;
			if ( ! empty( $tag['contexts'] ) && ! empty( $context ) ) {
				$context_matches = in_array( $context, $tag['contexts'], true );
			}
			if ( $context_matches ) {
				return $tag;
			}
		}

		return false;
	}

	/**
	 * Get a unique key for a tag.
	 *
	 * @since 3.3.0
	 *
	 * @param array $tag Tag to get unique key for.
	 * @return string
	 */
	private function get_unique_tag_key( $tag ) {
		if ( empty( $tag ) ) {
			return false;
		}

		return ! empty( $tag['contexts'] ) ? $tag['tag'] . '_' . reset( $tag['contexts'] ) : $tag['tag'];
	}

	/**
	 * Returns a list of all email tags.
	 * This function is deprecated, please use get() instead.
	 * This has been retained for compatibility with classes which extend this class.
	 *
	 * @since 1.9
	 * @deprecated 3.3.0 Use get() instead.
	 * @return array
	 */
	public function get_tags() {
		_edd_deprecated_function( __METHOD__, '3.3.0', 'get()' );

		return $this->get();
	}
}

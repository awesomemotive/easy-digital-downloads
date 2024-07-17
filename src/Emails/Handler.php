<?php

namespace EDD\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Class Handler
 *
 * @since 3.3.0
 * @package EDD\Emails
 */
class Handler implements SubscriberInterface {

	/**
	 * Gets the events to subscribe to.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'init'                          => array( 'register', 3 ),
		);
	}

	/**
	 * Register the EDD core emails.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function register() {
		if ( ! edd_has_upgrade_completed( 'edd_emails_registered' ) ) {
			$emails = new \EDD\Database\Tables\Emails();
			$emails->maybe_upgrade();
			$meta = new \EDD\Database\Tables\EmailMeta();
			$meta->maybe_upgrade();
		}
		foreach ( Registry::get_emails() as $key => $email_class ) {
			Registry::register( $key, $email_class );
		}
	}
}

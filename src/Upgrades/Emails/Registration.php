<?php

namespace EDD\Upgrades\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\Emails\Templates\Registry;

/**
 * Class Registration
 *
 * @since 3.3.0
 * @package EDD\Upgrades\Emails
 */
class Registration implements SubscriberInterface {

	/**
	 * The name of the upgrade.
	 *
	 * @var string
	 */
	protected static $upgrade_name = 'edd_emails_registered';

	/**
	 * Get the events to subscribe to.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		if ( edd_has_upgrade_completed( self::$upgrade_name ) ) {
			return array();
		}

		return array(
			'shutdown' => array( 'install' ),
		);
	}

	/**
	 * Install the email templates.
	 *
	 * @since  3.3.0
	 * @return void
	 */
	public function install() {
		$registry = new Registry();
		foreach ( $registry->get_emails() as $email_id => $email_class ) {
			$email = $registry->make_email_class( $email_class, array( $email_id ) );
			$email->install();
		}
		edd_set_upgrade_complete( self::$upgrade_name );
	}
}

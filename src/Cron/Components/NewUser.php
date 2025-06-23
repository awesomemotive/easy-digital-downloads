<?php
/**
 * Handles the NewUser cron events.
 *
 * @package EDD\Cron\Components
 * @copyright   Copyright (c) 2025, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.4.0
 */

namespace EDD\Cron\Components;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

use EDD\Emails\Registry;

/**
 * NewUser Class for Cron Events.
 *
 * @since 3.4.0
 */
class NewUser extends Component {

	/**
	 * The component ID.
	 *
	 * @var string
	 */
	protected static $id = 'new_user_emails';

	/**
	 * Register the event to run.
	 *
	 * @since 3.4.0
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_send_new_user_email' => 'send_emails',
		);
	}

	/**
	 * Sends the new user emails.
	 *
	 * @param int $user_id The user ID.
	 * @return void
	 */
	public function send_emails( $user_id ) {
		$user_data = get_userdata( $user_id );
		$user_data = (array) $user_data->data;

		$user_email = Registry::get( 'new_user', array( $user_id, $user_data ) );
		$user_email->send();
		$admin_email = Registry::get( 'new_user_admin', array( $user_id, $user_data ) );
		$admin_email->send();
	}
}

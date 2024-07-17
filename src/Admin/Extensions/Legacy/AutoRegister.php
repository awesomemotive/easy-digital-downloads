<?php

namespace EDD\Admin\Extensions\Legacy;

defined( 'ABSPATH' ) || exit;

/**
 * Class AutoRegister
 *
 * @since 3.3.0
 * @package EDD\Admin\Extensions\Legacy
 */
class AutoRegister {

	/**
	 * Update core settings based on Auto Register.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	public function update() {
		// If the logged in only setting is already set, do nothing.
		if ( ! empty( edd_get_option( 'logged_in_only', false ) ) ) {
			return;
		}
		edd_update_option( 'logged_in_only', 'auto' );
		$this->update_user_email();
		$this->update_admin_email();
	}

	/**
	 * Update the user email.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	private function update_user_email() {
		$template = edd_get_email_registry()->get_email_by_id( 'new_user' );
		if ( ! $template ) {
			return;
		}
		$email = $template->get_email();
		if ( ! $email ) {
			return;
		}
		$data = array();
		// Even though the data is in the email object, we are looking for the old options so we don't overwrite them if they were customized.
		if ( empty( edd_get_option( 'edd_new_user_body' ) ) ) {
			$data['content'] = $this->get_new_user_message();
		}
		if ( empty( edd_get_option( 'edd_new_user_subject' ) ) ) {
			$data['subject'] = $this->get_new_user_subject();
		}
		if ( ! empty( edd_get_option( 'edd_auto_register_disable_user_email' ) ) ) {
			$data['status'] = 0;
		}
		if ( ! empty( $data ) ) {
			edd_update_email( $email->id, $data );
		}
		edd_delete_option( 'edd_auto_register_disable_user_email' );
	}

	/**
	 * Update the admin email.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	private function update_admin_email() {
		$template = edd_get_email_registry()->get_email_by_id( 'new_user_admin' );
		if ( ! $template ) {
			return;
		}
		$email = $template->get_email();
		if ( ! $email ) {
			return;
		}
		$data = array();
		// Even though the data is in the email object, we are looking for the old options so we don't overwrite them if they were customized.
		if ( empty( edd_get_option( 'edd_new_user_admin_body' ) ) ) {
			$data['content'] = $this->get_new_user_admin_message();
		}
		if ( empty( edd_get_option( 'edd_new_user_admin_subject' ) ) ) {
			$data['subject'] = $this->get_new_user_admin_subject();
		}
		if ( ! empty( edd_get_option( 'edd_auto_register_disable_admin_email' ) ) ) {
			$data['status'] = 0;
		}
		if ( ! empty( $data ) ) {
			edd_update_email( $email->id, $data );
		}
		edd_delete_option( 'edd_auto_register_disable_admin_email' );
	}

	/**
	 * Gets the new user message content from Auto Register.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_new_user_message() {
		/* translators: %s: Email tag that will be replaced with the customer name */
		$message  = sprintf( _x( 'Dear %s,', 'Context: This is an email tag (placeholder) that will be replaced at the time of sending the email', 'easy-digital-downloads' ), '{name}' ) . "\n\n";
		$message .= __( 'Below are your login details:', 'easy-digital-downloads' ) . "\n\n";
		/* translators: %s: Email tag that will be replaced with the customer username */
		$message .= sprintf( __( 'Your Username: %s', 'easy-digital-downloads' ), '{username}' ) . "\r\n\r\n";
		$message .= '{password_link}' . "\r\n\r\n";
		$message .= '{login_link}' . "\r\n";

		/**
		 * Optionally filters the email message.
		 *
		 * @param string  $message
		 * @param string  $first_name
		 * @param WP_User $user
		 * @param string  $password
		 */
		return apply_filters( 'edd_auto_register_email_body', $message, false, false, false );
	}

	/**
	 * Gets the new user subject from Auto Register.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_new_user_subject() {
		return apply_filters(
			'edd_auto_register_email_subject',
			sprintf(
				/* translators: %s: Site name email tag */
				__( '[%s] Login Details', 'easy-digital-downloads' ),
				'{sitename}'
			)
		);
	}

	/**
	 * Gets the new user admin message from Auto Register.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_new_user_admin_message() {
		/* translators: %s: Email tag that will be replaced with the Site Name */
		$message = sprintf( _x( 'New user registration on your site %s:', 'Used to insert the {sitename} email tag into default email content.', 'easy-digital-downloads' ), '{sitename}' ) . "\r\n\r\n";
		/* translators: %s: Username email tag */
		$message .= sprintf( _x( 'Username: %s', 'Used to insert the {username} email tag into default email content.', 'easy-digital-downloads' ), '{username}' ) . "\r\n\r\n";
		/* translators: %s: User email email tag */
		$message .= sprintf( _x( 'Email: %s', 'Used to insert the {user_email} email tag into default email content.', 'easy-digital-downloads' ), '{user_email}' ) . "\r\n";

		return $message;
	}

	/**
	 * Gets the new user admin subject from Auto Register.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_new_user_admin_subject() {
		/* translators: %s: Email tag that will be replaced with the Site Name */
		return sprintf( _x( '[%s] New User Registration', 'Context: This is an email tag (placeholder) that will be replaced at the time of sending the email', 'easy-digital-downloads' ), '{sitename}' );
	}
}

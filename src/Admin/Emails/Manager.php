<?php

namespace EDD\Admin\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;
use EDD\Utils\Exception;

/**
 * Class Manager
 *
 * @since 3.3.0
 * @package EDD\Admin\Emails
 */
class Manager implements SubscriberInterface {

	/**
	 * Gets the events to subscribe to.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_save_email_settings'             => 'save',
			'wp_ajax_edd_update_email_status'     => 'update_status',
			'edd_flyout_docs_link'                => 'update_docs_link',
			'edd_email_editor_top'                => 'description',
			'wp_ajax_edd_reset_email'             => 'reset',
			'edd_render_settings_emails_sections' => 'remove_sections',
		);
	}

	/**
	 * Saves the email settings.
	 *
	 * @since 3.3.0
	 * @param array $data Data.
	 * @throws Exception If the email cannot be saved due to permissions, verification, or missing data.
	 */
	public function save( $data ) {
		try {
			if ( empty( $data['edd_save_email_nonce'] ) || ! wp_verify_nonce( $data['edd_save_email_nonce'], 'edd_save_email' ) ) {
				throw new Exception( __( 'Nonce verification failed.', 'easy-digital-downloads' ) );
			}

			if ( ! current_user_can( 'manage_shop_settings' ) ) {
				throw new Exception( __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) );
			}

			if ( empty( $data['email_id'] ) ) {
				throw new Exception( __( 'Missing email ID.', 'easy-digital-downloads' ) );
			}

			$email_saved = $this->save_email( $data );
			$message     = empty( $email_saved['success'] ) ? 'email-not-saved' : 'email-saved';
			if ( ! empty( $email_saved['message'] ) ) {
				$message = $email_saved['message'];
			}

			edd_redirect(
				edd_get_admin_url(
					array(
						'page'        => 'edd-emails',
						'edd-message' => $message,
						'email'       => $email_saved['email_id'],
					)
				)
			);
		} catch ( Exception $e ) {
			wp_die( $e->getMessage() );
		}
	}

	/**
	 * Updates the status of an email via ajax.
	 *
	 * @since 3.3.0
	 */
	public function update_status() {

		$email_id = filter_input( INPUT_POST, 'email_id', FILTER_SANITIZE_SPECIAL_CHARS );
		// Check for the email ID.
		if ( empty( $email_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing email ID.', 'easy-digital-downloads' ) ) );
		}

		// Validate the nonce.
		if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'edd_update_email' ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid nonce.', 'easy-digital-downloads' ) ) );
		}

		// Check for permissions.
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) ) );
		}

		// Get the email.
		try {
			$registry = edd_get_email_registry();
			$email    = $registry->get_email_by_id( $email_id );
		} catch ( Exception $e ) {
			wp_send_json_error( array( 'message' => $e->getMessage() ) );
		}

		if ( ! $email->can_edit( 'status' ) ) {
			wp_send_json_error( array( 'message' => __( 'This email status cannot be changed.', 'easy-digital-downloads' ) ) );
		}

		$success = $this->set_status( $email );
		if ( $success ) {
			wp_send_json_success( array( 'success' => $success ) );
		}

		wp_send_json_error( array( 'message' => __( 'This email status could not be changed.', 'easy-digital-downloads' ) ) );
	}

	/**
	 * Updates the docs link for the flyout menu.
	 *
	 * @since 3.3.0
	 * @param string $link The link.
	 * @return string
	 */
	public function update_docs_link( $link ) {
		if ( 'edd-emails' === filter_input( INPUT_GET, 'page', FILTER_SANITIZE_SPECIAL_CHARS ) ) {
			$link = 'https://easydigitaldownloads.com/docs/emails/';

			if ( 'email_summaries' === filter_input( INPUT_GET, 'tab', FILTER_SANITIZE_SPECIAL_CHARS ) ) {
				$link .= '#summaries';
			}
		}

		return $link;
	}

	/**
	 * Outputs the email description for the editor.
	 *
	 * @since 3.3.0
	 * @param \EDD\Emails\Email $email The email.
	 */
	public function description( $email ) {
		?>
		<div class="edd-email-editor__description">
			<?php
			$description  = $email->get_description();
			$required_tag = $email->required_tag;
			if ( ! empty( $required_tag ) ) {
				$description .= '<br>';
				$description .= sprintf(
					/* translators: 1: opening strong tag, 2: closing string tag, 3: required tag */
					__( '%1$sImportant:%2$s The %3$s template tag must remain in this email. Do not delete it.', 'easy-digital-downloads' ),
					'<strong>',
					'</strong>',
					'<code>{' . $required_tag . '}</code>'
				);
				EDD()->email_tags->remove( $required_tag );
			}
			echo wpautop( wp_kses_post( $description ) );
			?>
		</div>
		<?php
	}

	/**
	 * Gets a new email ID.
	 *
	 * @since 3.3.0
	 * @param string $prefix   The email ID prefix.
	 * @param string $temp_id  The temporary email ID (usually something like `license_new`).
	 * @return string|null
	 */
	public static function get_new_id( string $prefix, string $temp_id ) {
		$email_exists = true;
		$email_id     = null;
		$max_tries    = 5;

		// Restrict prefix to alphanumeric characters.
		$prefix = preg_replace( '/[\W]/', '', $prefix );

		do {
			$id           = md5( $prefix . $temp_id . wp_rand() );
			$id           = substr( $id, 0, 5 );
			$email_id     = "{$prefix}_{$id}";
			$email_id     = substr( $email_id, 0, 32 );
			$email_exists = edd_get_email( $email_id );
			--$max_tries;
		} while ( $email_exists && $max_tries > 0 );

		return $email_id;
	}

	/**
	 * Resets the email content via ajax.
	 *
	 * @since 3.3.0
	 */
	public function reset() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to perform this action.', 'easy-digital-downloads' ) ) );
		}
		$nonce = filter_input( INPUT_POST, 'nonce', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( empty( $nonce ) || ! wp_verify_nonce( $nonce, 'edd_update_email' ) ) {
			wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'easy-digital-downloads' ) ) );
		}
		$email_id = filter_input( INPUT_POST, 'email_id', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( empty( $email_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Missing email ID.', 'easy-digital-downloads' ) ) );
		}
		$email_template = edd_get_email_registry()->get_email_by_id( $email_id );
		if ( ! $email_template ) {
			wp_send_json_error( array( 'message' => __( 'Invalid email ID.', 'easy-digital-downloads' ) ) );
		}

		wp_send_json_success( array( 'content' => wpautop( $email_template->get_default( 'content' ) ) ) );
	}

	/**
	 * Sets the status of the email.
	 *
	 * @since 3.3.0
	 * @param \EDD\Emails\Templates\EmailTemplate $email_template The email template.
	 * @return bool
	 */
	private function set_status( $email_template ) {
		$action = filter_input( INPUT_POST, 'button', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! empty( $email_template->email->id ) ) {
			return edd_update_email(
				$email_template->email->id,
				array(
					'status' => 'enable' === $action,
				)
			);
		}

		return $email_template->__set( 'status', 'enable' === $action );
	}

	/**
	 * Saves the email.
	 * This method runs after the user capabilities and nonce have been checked.
	 * Extensions can short-circuit this method by returning a value from the edd_email_templates_save_email filter.
	 *
	 * @since 3.3.0
	 * @param array $data The email form data.
	 * @return array The result of the save.
	 */
	private function save_email( $data ) {
		$email_id       = $data['email_id'];
		$registry       = edd_get_email_registry();
		$email_template = $registry->get_email_by_id( $email_id );

		if ( ! $email_template ) {
			return array(
				'success'  => false,
				'email_id' => $email_id,
			);
		}

		/**
		 * Allow plugins to save the email using their own logic.
		 * If this filter returns anything other than false, the email will not be saved with the default logic.
		 *
		 * @since 3.3.0
		 * @param string                              $email_id       The email ID.
		 * @param \EDD\Emails\Templates\EmailTemplate $email_template The email being saved.
		 * @param array                               $data           The data being saved.
		 */
		$email_id = apply_filters( 'edd_email_manager_save_email_id', $email_id, $email_template, $data );
		if ( empty( $email_id ) ) {
			return array(
				'success'  => false,
				'email_id' => $email_id,
			);
		}

		$updated_data = array();
		$email        = $email_template->get_email();
		foreach ( $this->get_filtered_form_data( $data ) as $key => $value ) {
			if ( ! $email_template->can_edit( $key ) && ! array_key_exists( $key, $email_template->meta ) ) {
				if ( property_exists( $email, $key ) && ! is_null( $email->{$key} ) ) {
					$updated_data[ $key ] = $email->{$key};
				} elseif ( property_exists( $email_template, $key ) && ! is_null( $email_template->{$key} ) ) {
					$updated_data[ $key ] = $email_template->{$key};
				}
				continue;
			}

			$updated_data[ $key ] = $this->sanitize( $value, $key );
		}

		$success = false;
		if ( empty( $updated_data ) ) {
			return array(
				'success'  => $success,
				'email_id' => $email_id,
			);
		}

		$required_tag = $email_template->required_tag;
		if (
			! empty( $required_tag ) &&
			! empty( $updated_data['content'] ) &&
			false === strpos( $updated_data['content'], "{{$required_tag}}" )
			) {
			return array(
				'success'  => false,
				'email_id' => $email_id,
				'message'  => 'required-content-missing',
			);
		}

		$updated_data['email_id'] = $email_id;

		if ( ! empty( $email->id ) ) {
			$success = edd_update_email( $email->id, $updated_data );
		} else {
			$id = edd_add_email( $updated_data );
			if ( $id ) {
				$email    = edd_get_email_by( 'id', $id );
				$email_id = $email->email_id;
				$success  = true;
			}
		}
		$this->update_recipients( $email->id, $data );

		return array(
			'success'  => $success,
			'email_id' => $email_id,
		);
	}

	/**
	 * Gets the filtered form data.
	 *
	 * @since 3.3.0
	 * @param array $data The form data.
	 * @return array
	 */
	private function get_filtered_form_data( $data ) {
		$skipped_fields = array( 'email_id', 'edd-action', 'edd_save_email_nonce', '_wp_http_referer', 'submit' );

		return array_diff_key( $data, array_flip( $skipped_fields ) );
	}

	/**
	 * Sanitizes the value.
	 *
	 * @since 3.3.0
	 * @param mixed  $value The value.
	 * @param string $key   The key.
	 * @return mixed
	 */
	private function sanitize( $value, $key ) {
		if ( 'status' === $key ) {
			return (int) (bool) $value;
		}

		if ( in_array( $key, array( 'heading', 'subject' ), true ) ) {
			return sanitize_text_field( $value );
		}

		return is_array( $value ) ? array_map( 'wp_kses_post', $value ) : wp_kses_post( $value );
	}

	/**
	 * Updates the recipients for admin emails.
	 * This method runs after the email has been saved.
	 *
	 * @since 3.3.0
	 * @param int   $email_id The email ID.
	 * @param array $data     The data.
	 */
	private function update_recipients( $email_id, $data ) {
		if ( empty( $data['admin_recipient'] ) ) {
			edd_delete_email_meta( $email_id, 'recipients' );
			return;
		}

		if ( 'default' === $data['admin_recipient'] ) {
			edd_update_email_meta( $email_id, 'recipients', 'admin' );
			return;
		}

		if ( 'custom' === $data['admin_recipient'] ) {
			$recipients = $this->sanitize_recipients( $data['recipients'] );
			if ( ! empty( $recipients ) ) {
				edd_update_email_meta( $email_id, 'recipients', $recipients );
				return;
			}
		}

		edd_delete_email_meta( $email_id, 'recipients' );
	}

	/**
	 * Sanitizes the recipients.
	 *
	 * @since 3.3.0
	 * @param string $recipients The recipients.
	 * @return string
	 */
	private function sanitize_recipients( $recipients ) {
		$recipients = sanitize_textarea_field( $recipients );
		$recipients = explode( "\n", $recipients );
		foreach ( $recipients as $key => $recipient ) {
			$recipients[ $key ] = sanitize_email( $recipient );
		}
		$recipients = array_filter( $recipients );

		return implode( "\n", $recipients );
	}

	/**
	 * Removes the email summaries section from being displayed in the EDD Settings > Emails tab.
	 *
	 * @since 3.3.0
	 * @param array $sections The sections.
	 * @return array
	 */
	public function remove_sections( $sections ) {
		unset( $sections['email_summaries'] );

		return $sections;
	}
}

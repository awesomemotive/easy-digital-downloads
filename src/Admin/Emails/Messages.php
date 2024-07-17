<?php

namespace EDD\Admin\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Class Messages
 *
 * @since 3.3.0
 * @package EDD\Admin\Emails
 */
class Messages implements SubscriberInterface {

	/**
	 * Gets the events to subscribe to.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'edd_email_editor_top' => array( 'notices', 5 ),
			'admin_notices'        => 'notices',
		);
	}

	/**
	 * Admin notices.
	 *
	 * @since 3.3.0
	 * @return  void
	 */
	public function notices() {
		if ( ! edd_is_admin_page( 'emails' ) ) {
			return;
		}
		if ( ! empty( $_GET['email'] ) && 'admin_notices' === current_action() ) {
			return;
		}
		$notice = $this->get_notice();
		if ( ! $notice ) {
			return;
		}

		$args = wp_parse_args(
			$notice,
			array(
				'id'      => 'edd-email-notice',
				'message' => '',
				'class'   => 'updated',
			)
		);
		if ( empty( $args['message'] ) ) {
			return;
		}

		?>
		<div class="notice <?php echo esc_attr( $args['class'] ); ?> edd-email-notice" id="<?php echo esc_attr( $args['id'] ); ?>">
			<p><?php echo esc_html( $args['message'] ); ?></p>
		</div>
		<?php
	}

	/**
	 * Gets the notice to display.
	 *
	 * @return array|bool
	 */
	private function get_notice() {
		$message = filter_input( INPUT_GET, 'edd-message', FILTER_SANITIZE_SPECIAL_CHARS );
		if ( ! $message ) {
			return false;
		}

		$messages = array(
			'required-content-missing' => array(
				'id'      => 'edd-email-required-content-missing',
				'message' => __( 'Your email could not be saved because it is missing required content.', 'easy-digital-downloads' ),
				'class'   => 'error',
			),
			'test-email-sent'          => array(
				'id'      => 'edd-test-email-sent',
				'message' => __( 'The test email was sent successfully.', 'easy-digital-downloads' ),
			),
			'test-email-failed'        => array(
				'id'      => 'edd-test-email-failed',
				'message' => __( 'The test email could not be sent. Please check your email settings and try again.', 'easy-digital-downloads' ),
				'class'   => 'error',
			),
			'email-added'              => array(
				'id'      => 'email-added',
				'message' => __( 'The email was successfully added.', 'easy-digital-downloads' ),
			),
			'email-add-failed'         => array(
				'id'      => 'email-add-failed',
				'message' => __( 'The email could not be added.', 'easy-digital-downloads' ),
				'class'   => 'error',
			),
			'email-deleted'            => array(
				'id'      => 'email-deleted',
				'message' => __( 'The email was successfully deleted.', 'easy-digital-downloads' ),
			),
			'email-delete-failed'      => array(
				'id'      => 'email-delete-failed',
				'message' => __( 'The email could not be deleted.', 'easy-digital-downloads' ),
				'class'   => 'error',
			),
		);

		return isset( $messages[ $message ] ) ? $messages[ $message ] : false;
	}
}

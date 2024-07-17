<?php

namespace EDD\Emails\Templates\Traits;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Trait Actions
 *
 * @since 3.3.0
 * @package EDD\Emails\Traits
 */
trait Actions {

	/**
	 * Retrieves the URL to view/edit this email.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	public function get_edit_url() {
		return edd_get_admin_url(
			array(
				'page'  => 'edd-emails',
				'email' => $this->email_id,
			)
		);
	}

	/**
	 * Retrieves the row actions for this email.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public function get_row_actions() {
		$row_actions = array(
			'edit' => array(
				'url'  => $this->get_edit_url(),
				'text' => __( 'Edit', 'easy-digital-downloads' ),
			),
		);

		if ( $this->can_preview() ) {
			$row_actions['view'] = array(
				'url'    => wp_nonce_url(
					add_query_arg(
						array(
							'edd_action' => 'preview_email',
							'email'      => $this->email_id,
						),
						home_url()
					),
					'edd-preview-email'
				),
				'text'   => __( 'Preview', 'easy-digital-downloads' ),
				'target' => '_blank',
			);
		}
		if ( $this->can_test() ) {
			$row_actions['test'] = array(
				'url'  => wp_nonce_url(
					add_query_arg(
						array(
							'edd-action' => 'send_test_email',
							'email'      => $this->email_id,
						)
					),
					'edd-test-email'
				),
				'text' => __( 'Send Test', 'easy-digital-downloads' ),
			);
		}

		return $row_actions;
	}
}

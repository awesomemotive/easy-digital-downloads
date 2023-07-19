<?php
/**
 * Easy Digital Downloads Privacy Settings
 *
 * @package EDD
 * @subpackage  Settings
 * @copyright   Copyright (c) 2023, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since 3.1.4
 */
namespace EDD\Admin\Settings\Tabs;

defined( 'ABSPATH' ) || exit;

class Privacy extends Tab {

	/**
	 * Get the ID for this tab.
	 *
	 * @since 3.1.4
	 * @return string
	 */
	protected $id = 'privacy';

	/**
	 * Register the settings for this tab.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	protected function register() {
		return array(
			'main'         => array(
				''                                => array(
					'id'            => 'privacy_settings',
					'name'          => '<h3>' . __( 'Privacy Policy', 'easy-digital-downloads' ) . '</h3>',
					'desc'          => '',
					'type'          => 'header',
					'tooltip_title' => __( 'Privacy Policy Settings', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'Depending on legal and regulatory requirements, it may be necessary for your site to show a checkbox for agreement to a privacy policy.', 'easy-digital-downloads' ),
				),
				'show_agree_to_privacy_policy'    => array(
					'id'    => 'show_agree_to_privacy_policy',
					'name'  => __( 'Agreement', 'easy-digital-downloads' ),
					'check' => __( 'Check this box to show an "Agree to Privacy Policy" checkbox on checkout.', 'easy-digital-downloads' ),
					'desc'  => __( 'Customers must agree to your privacy policy before purchasing.', 'easy-digital-downloads' ),
					'type'  => 'checkbox_description',
				),
				'agree_privacy_label'             => array(
					'id'          => 'privacy_agree_label',
					'name'        => __( 'Agreement Label', 'easy-digital-downloads' ),
					'desc'        => __( 'Label for the "Agree to Privacy Policy" checkbox.', 'easy-digital-downloads' ),
					'type'        => 'text',
					'placeholder' => __( 'I agree to the privacy policy', 'easy-digital-downloads' ),
					'size'        => 'regular',
				),
				'show_privacy_policy_on_checkout' => array(
					'id'    => 'show_privacy_policy_on_checkout',
					'name'  => __( 'Privacy Policy on Checkout', 'easy-digital-downloads' ),
					'check' => __( 'Display the entire Privacy Policy at checkout.', 'easy-digital-downloads' ) . ' <a href="' . esc_url( admin_url( 'options-privacy.php' ) ) . '">' . __( 'Set your Privacy Policy here', 'easy-digital-downloads' ) . '</a>.',
					'desc'  =>
						__( 'Display your Privacy Policy on checkout.', 'easy-digital-downloads' ) . ' <a href="' . esc_url( admin_url( 'options-privacy.php' ) ) . '">' . __( 'Set your Privacy Policy here', 'easy-digital-downloads' ) . '</a>.' .
						/* translators: %1$s: Opening anchor tag, %2$s: Closing anchor tag. */
						'<p>' . sprintf( __( 'Need help creating a Privacy Policy? We recommend %1$sTermageddon%2$s.', 'easy-digital-downloads' ), '<a href="https://termageddon.com/i/easy-digital-downloads-edd-termageddon-promotion/" target="_blank" rel="noopener noreferrer">', '</a>' ) . '</p>',
					'type'  => 'checkbox',
				),
			),
			'site_terms'   => array(
				''                    => array(
					'id'            => 'terms_settings',
					'name'          => '<h3>' . __( 'Terms & Agreements', 'easy-digital-downloads' ) . '</h3>',
					'desc'          => '',
					'type'          => 'header',
					'tooltip_title' => __( 'Terms & Agreements Settings', 'easy-digital-downloads' ),
					'tooltip_desc'  => __( 'Depending on legal and regulatory requirements, it may be necessary for your site to show checkbox for agreement to terms.', 'easy-digital-downloads' ),
				),
				'show_agree_to_terms' => array(
					'id'    => 'show_agree_to_terms',
					'name'  => __( 'Agreement', 'easy-digital-downloads' ),
					'check' => __( 'Check this box to show an "Agree to Terms" checkbox on checkout.', 'easy-digital-downloads' ),
					'desc'  =>
						__( 'Check this to show an agree to terms on checkout that users must agree to before purchasing.', 'easy-digital-downloads' ) .
						'<p>' .
						sprintf(
							/* translators: 1: Opening anchor tag, 2: Closing anchor tag. */
							__( 'Need help creating a Terms of Agreement? We recommend using %1$sTermageddon%2$s.', 'easy-digital-downloads' ),
							'<a href="https://termageddon.com/i/easy-digital-downloads-edd-termageddon-promotion/" target="_blank" rel="noopener noreferrer">',
							'</a>'
						) .
						'</p>',
					'type'  => 'checkbox_description',
				),
				'agree_label'         => array(
					'id'          => 'agree_label',
					'name'        => __( 'Agreement Label', 'easy-digital-downloads' ),
					'desc'        => __( 'Label for the "Agree to Terms" checkbox.', 'easy-digital-downloads' ),
					'placeholder' => __( 'I agree to the terms', 'easy-digital-downloads' ),
					'type'        => 'text',
					'size'        => 'regular',
				),
				'agree_text'          => array(
					'id'   => 'agree_text',
					'name' => __( 'Agreement Text', 'easy-digital-downloads' ),
					'type' => 'rich_editor',
				),
			),
			'export_erase' => $this->get_export_erase(),
		);
	}

	/**
	 * Get the export and erase settings.
	 *
	 * @since 3.1.4
	 * @return array
	 */
	private function get_export_erase() {
		$export_erase = array(
			array(
				'id'            => 'payment_privacy_status_action_header',
				'name'          => '<h3>' . __( 'Order Statuses', 'easy-digital-downloads' ) . '</h3>',
				'type'          => 'header',
				'desc'          => __( 'When a user requests to be anonymized or removed from a site, these are the actions that will be taken on payments associated with their customer, by status.', 'easy-digital-downloads' ),
				'tooltip_title' => __( 'What settings should I use?', 'easy-digital-downloads' ),
				'tooltip_desc'  => __( 'By default, Easy Digital Downloads sets suggested actions based on the Payment Status. These are purely recommendations, and you may need to change them to suit your store\'s needs. If you are unsure, you can safely leave these settings as is.', 'easy-digital-downloads' ),
			),
			array(
				'id'   => 'payment_privacy_status_action_text',
				'name' => __( 'Rules', 'easy-digital-downloads' ),
				'type' => 'descriptive_text',
				'desc' => __( 'When a user wants their order history anonymized or removed, the following rules will be used:', 'easy-digital-downloads' ),
			),
		);

		$options          = array(
			'none'      => __( 'Do Nothing', 'easy-digital-downloads' ),
			'anonymize' => __( 'Anonymize', 'easy-digital-downloads' ),
			'delete'    => __( 'Delete', 'easy-digital-downloads' ),
		);
		$payment_statuses = edd_get_payment_statuses();

		// Add Privacy settings for statuses
		foreach ( $payment_statuses as $status => $label ) {

			$export_erase[] = array(
				'id'      => "payment_privacy_status_action_{$status}",
				'name'    => $label,
				'desc'    => '',
				'type'    => 'select',
				'std'     => $this->get_action( $status ),
				'options' => $options,
			);
		}

		return $export_erase;
	}

	/**
	 * Get the default action to take for a given status.
	 *
	 * @since 3.1.4
	 * @param string $status The status to get the action for.
	 * @return string
	 */
	private function get_action( $status ) {
		switch ( $status ) {
			case 'complete':
			case 'refunded':
			case 'revoked':
				$action = 'anonymize';
				break;

			case 'failed':
			case 'abandoned':
				$action = 'delete';
				break;

			case 'pending':
			case 'processing':
			default:
				$action = 'none';
				break;
		}

		return $action;
	}
}

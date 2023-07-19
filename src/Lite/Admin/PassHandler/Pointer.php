<?php
/**
 * Class for adding a pointer notice for users who have an active pass, but not a pro license.
 *
 * @since 3.1.1
 */
namespace EDD\Lite\Admin\PassHandler;

use \EDD\EventManagement\SubscriberInterface;

class Pointer implements SubscriberInterface {

	public static function get_subscribed_events() {
		return array(
			'admin_menu'    => 'add_menu_item_class',
			'user_register' => 'dismiss_pointers_for_new_users',
			'edd_pointers'  => 'pointers',
		);
	}

	/**
	 * Add class to the Onboarding Wizard subpage menu item.
	 *
	 * @since 3.1.1
	 */
	public function add_menu_item_class() {
		new \EDD\Admin\Menu\LinkClass( 'edd-settings', 'edd-settings__menu-item' );
	}

	/**
	 * Maybe show an admin pointer showing a message about the new menu locations.
	 *
	 * @since 3.1.1
	 * @return void
	 */
	public function pointers( $pointers ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return $pointers;
		}
		if ( ! $this->has_pass_no_license() ) {
			return $pointers;
		}

		// Add pointers that need to be registered when we are not on an EDD Admin screen.
		if ( ! edd_is_admin_page( 'download' ) ) {
			$pointers[] = array(
				'pointer_id' => 'edd_activate_pass_non_edd_setting_page',
				'target'     => '#menu-posts-download',
				'options'    => array(
					'content'  => $this->get_default_pass_upgrade_content(),
					'position' => array(
						'edge'  => 'left',
						'align' => 'middle',
					),
				),
			);
		}

		// Add pointers that need to be registered when we are on an EDD Admin Page, but not a settings screen.
		$screen = get_current_screen();
		if ( 'dashboard' !== $screen->id && edd_is_admin_page() && ! edd_is_admin_page( 'settings' ) ) {
			$pointers[] = array(
				'pointer_id' => 'edd_activate_pass_edd_setting_page',
				'target'     => '.edd-settings__menu-item:not(.current)',
				'options'    => array(
					'content'  => $this->get_default_pass_upgrade_content(),
					'position' => array(
						'edge'  => 'left',
						'align' => 'middle',
					),
				),
			);
		}

		// Add this pointer on the general EDD settings screen.
		if ( edd_is_admin_page( 'settings', 'general' ) ) {
			$pointers[] = array(
				'pointer_id' => 'edd_activate_pass_button',
				'target'     => '.edd-pass-handler__action',
				'options'    => array(
					'content'  => sprintf(
						'<h3>%s</h3><p>%s</p>',
						__( 'Install the Pro Version!', 'easy-digital-downloads' ),
						__( 'We see you already have an active pass. Click here to verify your license key and we\'ll connect you to install Easy Digital Downloads (Pro).', 'easy-digital-downloads' )
					),
					'position' => array(
						'edge'  => 'bottom',
						'align' => 'left',
					),
				),
			);
		}

		return $pointers;
	}

	/**
	 * Gets the dismissed_wp_pointers user meta.
	 *
	 * @since 3.1.1
	 * @param int $user_id THe current user ID.
	 * @return array
	 */
	public function get_user_dismissals( $user_id ) {
		return explode( ',', (string) get_user_meta( $user_id, 'dismissed_wp_pointers', true ) );
	}

	/**
	 * Dismisses the pointer notices for new users.
	 *
	 * @since 3.1.1
	 * @param int $user_id The new user ID.
	 * @return void
	 */
	public function dismiss_pointers_for_new_users( $user_id ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( $this->has_pass_no_license() ) {
			return;
		}

		$dismissals = $this->get_user_dismissals( $user_id );

		if ( ! in_array( 'edd_activate_pass', $dismissals, true ) ) {
			$dismissals[] = 'edd_activate_pass';
		}

		if ( ! in_array( 'edd_activate_pass_button', $dismissals, true ) ) {
			$dismissals[] = 'edd_activate_pass_button';
		}

		if ( ! in_array( 'edd_activate_pass_non_edd_setting_page', $dismissals, true ) ) {
			$dismissals[] = 'edd_activate_pass_non_edd_setting_page';
		}

		update_user_meta( $user_id, 'dismissed_wp_pointers', implode( ',', array_filter( $dismissals ) ) );
	}

	/**
	 * Checks whether the site has an active pass, but hasn't entered the pro license key yet.
	 *
	 * @since 3.1.1
	 * @return bool
	 */
	private function has_pass_no_license() {
		$pro_license = new \EDD\Licensing\License( 'pro' );
		if ( ! empty( $pro_license->key ) ) {
			return false;
		}
		$pass_manager = new \EDD\Admin\Pass_Manager();

		return ! empty( $pass_manager->highest_license_key );
	}

	/**
	 * Gets the default notice content for users with passes.
	 *
	 * @since 3.1.1.2
	 * @return string
	 */
	private function get_default_pass_upgrade_content() {
		$settings_url = edd_get_admin_url(
			array(
				'page' => 'edd-settings',
			)
		);

		return sprintf(
			'<h3>%s</h3><p>%s</p>',
			__( 'You\'re eligible to install EDD (Pro)!', 'easy-digital-downloads' ),
			sprintf(
				/* translators: 1. opening anchor tag; 2. closing anchor tag */
				__( 'Good news! With your pass subscription, you can install the Pro version of Easy Digital Downloads. %1$sVisit the settings page%2$s to verify your license and access Pro only features.', 'easy-digital-downloads' ),
				'<a href="' . esc_url( $settings_url ) . '">',
				'</a>'
			)
		);
	}
}

<?php
/**
 * Email Marketing
 *
 * Manages automatic installation/activation for email marketing extensions.
 *
 * @package     EDD
 * @subpackage  EmailMarketing
 * @copyright   Copyright (c) 2021, Easy Digital Downloads
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       2.11.x
 */
namespace EDD\Admin\Settings;

use \EDD\Admin\Extensions\Extension;

class EmailMarketing extends Extension {

	public function __construct() {
		add_filter( 'edd_settings_sections_marketing', array( $this, 'add_section' ) );
		add_action( 'edd_settings_tab_top_marketing_email_marketing', array( $this, 'field' ) );

		parent::__construct();
	}

	/**
	 * Gets the configuration for the newsletter extensions.
	 * Returns data for all email marketing extensions or just the one matching the item ID.
	 *
	 * @since 2.11.x
	 * @param int $item_id Optional. The product ID.
	 * @return array
	 */
	protected function get_configuration( $item_id = false ) {
		$extensions = include 'list-email-marketing.php';

		return $item_id ? $extensions[ $item_id ] : $extensions;
	}

	/**
	 * Adds an email marketing section to the Marketing tab.
	 *
	 * @since 2.11.x
	 * @param array $sections
	 * @return array
	 */
	public function add_section( $sections ) {
		if ( $this->is_activated() ) {
			return $sections;
		}

		$sections['email_marketing'] = __( 'Email Marketing', 'easy-digital-downloads' );

		return $sections;
	}

	/**
	 * Adds the email marketing extensions as cards.
	 *
	 * @since 2.11.x
	 * @return void
	 */
	public function field() {
		$config = $this->get_configuration();
		?>
		<div class="edd-extension-manager__card-group">
			<?php
			foreach ( $config as $item_id => $extension ) {
				$this->do_single_extension_card( $item_id );
			}
			?>
		</div>
		<style>p.submit{display:none;}</style>
		<?php
	}

	/**
	 * Overrides the body array sent to the Products API.
	 *
	 * @since 2.11.x
	 * @return array
	 */
	protected function get_api_body() {
		return array(
			'tag' => 1578,
		);
	}

	/**
	 * Whether any email marketing extension is active.
	 *
	 * @since 2.11.x
	 *
	 * @return bool True if any email marketing extension is active.
	 */
	protected function is_activated() {
		$config = $this->get_configuration();
		foreach ( $config as $extension ) {
			if ( is_plugin_active( $extension['basename'] ) ) {
				return true;
			}
		}

		return false;
	}
}

new EmailMarketing();

<?php
/**
 * Show a notice on the emails screen.
 */

namespace EDD\Admin\Promos\Notices;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

/**
 * Class Emails
 *
 * @since 3.3.0
 * @package EDD\Admin\Promos\Notices
 */
class Emails extends Notice {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'admin_print_footer_scripts-download_page_edd-emails';

	/**
	 * The priority for the display hook.
	 */
	const DISPLAY_PRIORITY = 5;

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'overlay';

	/**
	 * The extensions class.
	 *
	 * @var \EDD\Admin\Extensions\Emails
	 */
	private $extensions;

	/**
	 * Displays the notice content.
	 *
	 * @return void
	 */
	protected function _display() {
		?>
		<h2><?php esc_html_e( 'Need More Emails?', 'easy-digital-downloads' ); ?></h2>
		<p>
			<?php esc_html_e( 'Super charge your eCommerce store with these extensions which support custom emails:', 'easy-digital-downloads' ); ?>
		</p>
		<div class="edd-extension-manager__card-group">
			<?php $this->do_cards(); ?>
		</div>
		<div class="edd-extension-manager__actions">
			<?php
			$this->do_action_buttons();
			?>
		</div>
		<?php
	}

	/**
	 * Duration (in seconds) that the notice is dismissed for.
	 * `0` means it's dismissed permanently.
	 *
	 * @return int
	 */
	public static function dismiss_duration() {
		return 1;
	}

	/**
	 * Gets the notice content for AJAX requests.
	 *
	 * @return string
	 */
	public function get_ajax_content() {
		if ( ! $this->_should_display() ) {
			return '';
		}
		$product_id = filter_input( INPUT_GET, 'product_id', FILTER_SANITIZE_NUMBER_INT );
		if ( empty( $product_id ) ) {
			return '';
		}
		ob_start();
		?>
		<h2><?php esc_html_e( 'Need More Emails?', 'easy-digital-downloads' ); ?></h2>
		<p>
			<?php esc_html_e( 'Super charge your ecommerce store with custom emails:', 'easy-digital-downloads' ); ?>
		</p>
		<div class="edd-extension-manager__single-card">
			<?php
			$extensions = $this->get_extensions_class();
			$extensions->do_single_extension_card( absint( $product_id ) );
			?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * @inheritDoc
	 * @since 3.3.0
	 * @return bool
	 */
	protected function _should_display() {
		if ( ! current_user_can( 'manage_shop_settings' ) ) {
			return false;
		}
		if ( ! empty( $_GET['email'] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Outputs the action buttons for the modal.
	 *
	 * @since 3.3.0
	 */
	protected function do_action_buttons() {
		$upgrade_link = edd_get_admin_url(
			array(
				'page'   => 'edd-addons',
				'filter' => 'email-management',
			)
		);
		?>
		<a href="<?php echo esc_attr( $upgrade_link ); ?>" class="button button-secondary">
			<?php esc_html_e( 'Learn More', 'easy-digital-downloads' ); ?>
		</a>
		<?php
	}

	/**
	 * Gets the extensions class.
	 *
	 * @return EDD\Admin\Extensions\Emails
	 */
	protected function get_extensions_class() {
		if ( is_null( $this->extensions ) ) {
			$email_class      = edd_get_namespace( 'Admin\\Extensions\\Emails' );
			$this->extensions = new $email_class();
		}

		return $this->extensions;
	}

	/**
	 * Outputs the extension cards.
	 *
	 * @since 3.3.0
	 */
	protected function do_cards() {
		$extensions = $this->get_extensions_class();
		foreach ( $extensions->get_product_data() as $item_id => $product ) {
			$extensions->do_single_extension_card( $item_id );
		}
	}
}

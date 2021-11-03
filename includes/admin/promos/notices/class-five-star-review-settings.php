<?php
/**
 * Settings Review Notice
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 * @since     2.11.4
 */

namespace EDD\Admin\Promos\Notices;

class Five_Star_Review_Settings extends Five_Star_Review_Dashboard {

	/**
	 * Action hook for displaying the notice.
	 */
	const DISPLAY_HOOK = 'admin_notices';

	/**
	 * Type of promotional notice.
	 */
	const TYPE = 'admin-notice';

	/**
	 * The current screen.
	 *
	 * @var string
	 */
	protected $screen = 'plugin-settings-page';

	/**
	 * Display the notice.
	 * This extends the parent method because the container classes are different.
	 *
	 * @since 2.11.4
	 * @return void
	 */
	public function display() {
		?>
		<div
			id="edd-admin-notice-<?php echo esc_attr( $this->get_id() ); ?>"
			class="notice notice-info edd-admin-notice-<?php echo esc_attr( sanitize_html_class( static::TYPE ) ); ?> edd-promo-notice"
			data-nonce="<?php echo esc_attr( wp_create_nonce( 'edd-dismiss-notice-' . $this->get_id() ) ); ?>"
			data-id="<?php echo esc_attr( $this->get_id() ); ?>"
			data-lifespan="<?php echo esc_attr( static::dismiss_duration() ); ?>"
		>
			<?php
			parent::_display();
			?>
		</div>
		<?php
	}

	/**
	 * Whether the notice should display.
	 * This extends the general method as this notice should only display on EDD settings screens.
	 *
	 * @since 2.11.4
	 * @return bool
	 */
	protected function _should_display() {
		if ( ! edd_is_admin_page( 'settings' ) ) {
			return false;
		}
		return parent::_should_display();
	}
}

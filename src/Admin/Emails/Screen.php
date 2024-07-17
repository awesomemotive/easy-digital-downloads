<?php

namespace EDD\Admin\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Utils\Exception;
use EDD\Admin\Menu\SecondaryNavigation;

/**
 * Class Screen
 *
 * @since 3.3.0
 * @package EDD\Admin\Emails
 */
class Screen {

	/**
	 * Renders the emails screen.
	 *
	 * @since 3.3.0
	 */
	public static function render() {

		self::enqueue();
		if ( ! empty( $_GET['email'] ) ) {
			self::render_email_editor();
			return;
		}

		$navigation = new SecondaryNavigation(
			array(
				'general'         => __( 'Emails', 'easy-digital-downloads' ),
				'settings'        => __( 'Settings', 'easy-digital-downloads' ),
				'email_summaries' => __( 'Email Reports', 'easy-digital-downloads' ),
				'logs'            => __( 'Logs', 'easy-digital-downloads' ),
			),
			'edd-emails'
		);
		$navigation->render();
		?>

		<div class="wrap wrap-emails">
			<hr class="wp-header-end">
			<?php
			$current_tab = self::get_current_tab();
			if ( $current_tab && array_key_exists( $current_tab, $navigation->tabs ) ) {
				if ( 'logs' === $current_tab ) {
					self::render_logs();
					return;
				}

				self::render_settings();
				return;
			}

			self::render_table();
			?>
		</div>
		<?php
	}

	/**
	 * Enqueues the scripts and styles.
	 *
	 * @since 3.3.0
	 */
	private static function enqueue() {
		if ( empty( $_GET['email'] ) ) {
			$script = 'edd-admin-emails-list-table';
		} else {
			$script = 'edd-admin-emails-editor';
		}
		wp_enqueue_script( $script );
		wp_enqueue_style( 'edd-admin-emails' );
		wp_localize_script(
			$script,
			'EDDAdminEmails',
			array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( 'edd_update_email' ),
				'debug'   => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG,
				'link'    => edd_get_admin_url( array( 'page' => 'edd-emails' ) ),
			)
		);
	}

	/**
	 * Renders the email editor.
	 *
	 * @since 3.3.0
	 * @throws Exception
	 */
	private static function render_email_editor() {
		try {
			if ( empty( $_GET['email'] ) ) {
				throw new Exception( __( 'Missing email ID.', 'easy-digital-downloads' ) );
			}

			$registry = edd_get_email_registry();
			$email    = $registry->get_email_by_id( sanitize_text_field( $_GET['email'] ) );

			if ( ! $email ) {
				throw new Exception( __( 'Invalid email ID.', 'easy-digital-downloads' ) );
			}
		} catch ( Exception $e ) {
			wp_die( $e->getMessage() );
		}

		require_once EDD_PLUGIN_DIR . 'includes/admin/views/email-editor/editor.php';
	}

	/**
	 * Renders the email table.
	 *
	 * @since 3.3.0
	 */
	private static function render_table() {
		$table = new ListTable(
			array(
				'singular' => 'email_template',
				'plural'   => 'email_templates',
				'ajax'     => false,
			)
		);
		$table->prepare_items();
		$table->display();
	}

	/**
	 * Renders the email settings.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	private static function render_settings() {
		wp_enqueue_script( 'edd-admin-settings' );
		$tab = self::get_current_tab();
		if ( 'settings' === $tab ) {
			$tab = 'main';
		}
		?>

		<div class="edd-settings-content">
			<form method="post" action="options.php" class="edd-settings-form">
				<?php
				settings_fields( 'edd_settings' );
				do_action( 'edd_settings_tab_top', 'emails' );
				do_action( "edd_settings_tab_top_emails_{$tab}" );
				do_settings_sections( "edd_settings_emails_{$tab}" );
				do_action( "edd_settings_tab_bottom_emails_{$tab}" );
				do_action( 'edd_settings_tab_bottom', 'emails' );
				submit_button();
				?>
				<input type="hidden" name="edd_tab_override" value="emails" />
				<input type="hidden" name="edd_section_override" value="<?php echo esc_attr( $tab ); ?>" />
			</form>
		</div>
		<?php
	}

	/**
	 * Renders the email logs table.
	 *
	 * @since 3.3.0
	 * @return void
	 */
	private static function render_logs() {
		require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
		require_once EDD_PLUGIN_DIR . 'includes/admin/reporting/class-base-logs-list-table.php';

		$logs_table = new LogsTable();
		$logs_table->prepare_items();
		$logs_table->display();
	}

	/**
	 * Get the current tab.
	 *
	 * @since 3.3.0
	 * @return string|false
	 */
	private static function get_current_tab() {
		return isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : false;
	}
}

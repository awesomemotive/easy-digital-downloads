<?php

namespace EDD\Admin\Menu;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Class Header
 *
 * @package EDD\Admin\Menu
 */
class Header implements SubscriberInterface {

	/**
	 * Get the events that this class is subscribed to.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	public static function get_subscribed_events() {
		return array(
			'admin_notices' => array( 'render', 1 ),
		);
	}

	/**
	 * Render the admin header.
	 *
	 * @since 3.3.0
	 */
	public function render() {
		if ( ! $this->can_render() ) {
			return;
		}
		$number_notifications = EDD()->notifications->countActiveNotifications();

		$is_single_view = $this->is_single_view();
		$page_title     = $this->get_page_title();
		if ( ! empty( $page_title ) && empty( $is_single_view ) ) {
			$this->print_style_script();
		}
		?>

		<div id="edd-header" class="edd-header">
			<div id="edd-header-wrapper">
				<span id="edd-header-branding">
					<img class="edd-header-logo" alt="" src="<?php echo esc_url( EDD_PLUGIN_URL . 'assets/images/logo-edd-dark.svg' ); ?>" />
				</span>

				<?php if ( ! empty( $page_title ) ) : ?>
					<span class="edd-header-page-title-wrap">
						<span class="edd-header-separator">/</span>
						<?php $element = true === $is_single_view ? 'span' : 'h1'; ?>
						<<?php echo esc_attr( $element ); ?> class="edd-header-page-title"><?php echo esc_html( $page_title ); ?></<?php echo esc_attr( $element ); ?>>
					</span>
				<?php endif; ?>

				<div id="edd-header-actions">
					<button
						id="edd-notification-button"
						class="edd-round edd-hidden"
						x-data
						x-init="function() {
							if ( 'undefined' !== typeof $store.eddNotifications ) {
								$el.classList.remove( 'edd-hidden' );
								$store.eddNotifications.numberActiveNotifications = <?php echo esc_js( $number_notifications ); ?>
							}
						}"
						@click="$store.eddNotifications.openPanel()"
					>
						<span
							class="edd-round edd-number<?php echo 0 === $number_notifications ? ' edd-hidden' : ''; ?>"
							x-show="$store.eddNotifications.numberActiveNotifications > 0"
						>
							<?php
							echo wp_kses(
								sprintf(
								/* translators: %1$s number of notifications; %2$s opening span tag; %3$s closing span tag */
									__( '%1$s %2$sunread notifications%3$s', 'easy-digital-downloads' ),
									'<span x-text="$store.eddNotifications.numberActiveNotifications"></span>',
									'<span class="screen-reader-text">',
									'</span>'
								),
								array(
									'span' => array(
										'class'  => true,
										'x-text' => true,
									),
								)
							);
							?>
						</span>

						<svg viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg" class="edd-notifications-icon"><path fill-rule="evenodd" clip-rule="evenodd" d="M15.8333 2.5H4.16667C3.25 2.5 2.5 3.25 2.5 4.16667V15.8333C2.5 16.75 3.24167 17.5 4.16667 17.5H15.8333C16.75 17.5 17.5 16.75 17.5 15.8333V4.16667C17.5 3.25 16.75 2.5 15.8333 2.5ZM15.8333 15.8333H4.16667V13.3333H7.13333C7.70833 14.325 8.775 15 10.0083 15C11.2417 15 12.3 14.325 12.8833 13.3333H15.8333V15.8333ZM11.675 11.6667H15.8333V4.16667H4.16667V11.6667H8.34167C8.34167 12.5833 9.09167 13.3333 10.0083 13.3333C10.925 13.3333 11.675 12.5833 11.675 11.6667Z" fill="currentColor"></path></svg>
					</button>
				</div>
			</div>
		</div>
		<?php

		// Maybe display product navigation.
		$this->maybe_do_product_navigation();

		add_action(
			'admin_footer',
			function () {
				require_once EDD_PLUGIN_DIR . 'includes/admin/views/notifications.php';
			}
		);
	}

	/**
	 * Check if the header can be rendered.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private function can_render() {
		if ( ! edd_is_admin_page( '', '', false ) ) {
			return false;
		}
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : false;
		if ( $screen && $screen->is_block_editor() ) {
			return false;
		}

		return true;
	}

	/**
	 * Gets the page title.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private function get_page_title() {
		$current_page = ! empty( $_GET['page'] ) ? $_GET['page'] : '';

		$page_title = __( 'Downloads', 'easy-digital-downloads' );
		switch ( $current_page ) {
			case 'edd-settings':
				$page_title = __( 'Settings', 'easy-digital-downloads' );
				break;
			case 'edd-reports':
				$page_title = __( 'Reports', 'easy-digital-downloads' );
				break;
			case 'edd-payment-history':
				$page_title = __( 'Orders', 'easy-digital-downloads' );
				break;
			case 'edd-discounts':
				$page_title = __( 'Discounts', 'easy-digital-downloads' );
				break;
			case 'edd-customers':
				$page_title = __( 'Customers', 'easy-digital-downloads' );
				break;
			case 'edd-tools':
				$page_title = __( 'Tools', 'easy-digital-downloads' );
				break;
			case 'edd-emails':
				$page_title = __( 'Emails', 'easy-digital-downloads' );
				break;
			case 'edd-addons':
				$page_title = __( 'View Extensions', 'easy-digital-downloads' );
				if ( edd_is_pro() ) {
					$page_title = __( 'Manage Extensions', 'easy-digital-downloads' );
				}
				break;
			default:
				if ( ! empty( $current_page ) ) {
					$page_title = ucfirst( str_replace( array( 'edd-', 'fes-' ), '', $current_page ) );
				} elseif ( ! empty( $_GET['post_type'] ) ) {
					$post_type  = get_post_type_object( $_GET['post_type'] );
					$page_title = $post_type->labels->name;
				}
				break;
		}

		return apply_filters( 'edd_settings_page_title', $page_title, $current_page, $this->is_single_view() );
	}

	/**
	 * Check if the current view is a single view.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private function is_single_view() {
		return (bool) apply_filters( 'edd_admin_is_single_view', ! empty( $_GET['view'] ) && ! isset( $_GET['s'] ) );
	}

	/**
	 * Print the style and script.
	 *
	 * @since 3.3.0
	 */
	private function print_style_script() {
		?>
		<style>
			.wrap > h1,
			.wrap h1.wp-heading-inline:not(.button),
			a.page-title-action:not(.button) {
				display: none !important;
			}
		</style>
		<script>
			jQuery(document).ready(function($){
				const coreAddNew = $( '.page-title-action' );
				const eddAddNew  = $( '.add-new-h2' );

				if ( coreAddNew.length ) {
					coreAddNew.appendTo( '.edd-header-page-title-wrap' ).addClass( 'button' ).show();
				}

				if ( eddAddNew.length ) {
					eddAddNew.appendTo( '.edd-header-page-title-wrap' ).addClass( 'button' ).show();
				}
			});
		</script>
		<?php
	}

	/**
	 * Maybe display product tabs.
	 * This navigation is handled differently than the rest of the admin navigation
	 * because we do not have control over the download/taxonomy screens.
	 *
	 * @since 3.3.0
	 */
	private function maybe_do_product_navigation() {
		if ( $this->can_do_product_tabs() ) {
			edd_display_product_tabs();
		}
	}

	/**
	 * Check if we can display the product navigation.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private function can_do_product_tabs() {
		$screen = get_current_screen();
		if ( 'download' !== $screen->post_type ) {
			return false;
		}
		if ( 'edit' === $screen->base ) {
			return true;
		}

		$taxonomy = filter_input( INPUT_GET, 'taxonomy', FILTER_SANITIZE_SPECIAL_CHARS );

		// Bail if not viewing a taxonomy.
		if ( empty( $taxonomy ) ) {
			return false;
		}

		return in_array( $taxonomy, get_object_taxonomies( 'download' ), true );
	}
}

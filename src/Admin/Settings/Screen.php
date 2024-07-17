<?php

namespace EDD\Admin\Settings;

defined( 'ABSPATH' ) || exit;

use EDD\Admin\Menu\SecondaryNavigation;

/**
 * Class Screen
 *
 * @since 3.3.0
 * @package EDD\Admin\Settings
 */
class Screen {

	/**
	 * The array of settings.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	private static $settings;

	/**
	 * The array of tabs.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	private static $tabs;

	/**
	 * The active tab.
	 *
	 * @since 3.3.0
	 * @var string
	 */
	private static $active_tab;

	/**
	 * The array of sections.
	 *
	 * @since 3.3.0
	 * @var array
	 */
	private static $sections;

	/**
	 * Renders the settings screen.
	 *
	 * @since 3.3.0
	 */
	public static function render() {
		// Enqueue scripts.
		wp_enqueue_script( 'edd-admin-settings' );

		$active_tab = self::get_active_tab();
		$section    = self::get_active_section();

		// Ensure all the sections are set on the static property.
		self::get_sections();

		// Default values.
		$override = false;

		// Unset 'main' if it's empty and default to the first non-empty if it's the chosen section.
		if ( false === self::section_has_main_settings() ) {
			unset( self::$sections['main'] );

			if ( 'main' === $section ) {
				$all_settings = self::get_settings();
				foreach ( self::$sections as $section_key => $section_title ) {
					if ( ! empty( $all_settings[ $active_tab ][ $section_key ] ) ) {
						$section  = $section_key;
						$override = true;
						break;
					}
				}
			}
		}

		// Primary nav.
		self::primary_navigation( self::get_tabs(), $active_tab );
		// Secondary nav.
		self::secondary_navigation( $active_tab, $section, self::$sections );
		?>
		<div class="wrap <?php echo 'wrap-' . esc_attr( $active_tab ); ?>">
			<?php self::form( $active_tab, $section, $override ); ?>
		</div>
		<?php
	}

	/**
	 * Output the options page form and fields for this tab & section
	 *
	 * @since 3.3.0
	 *
	 * @param string  $active_tab The active tab.
	 * @param string  $section    The active section.
	 * @param boolean $override   Whether or not to override the view.
	 */
	public static function form( $active_tab = '', $section = '', $override = false ) {

		// Setup the action & section suffix.
		$suffix = ! empty( $section )
			? $active_tab . '_' . $section
			: $active_tab . '_main';

		// Allow filtering of the classes.
		$classes = apply_filters( 'edd_settings_wrap_classes', array( 'edd-settings-wrap', 'wp-clearfix' ) );
		$classes = array_map( 'sanitize_html_class', $classes );
		$classes = implode( ' ', $classes );
		?>

		<div class="<?php echo $classes; ?>">
			<div class="edd-settings-content">
				<form method="post" action="options.php" class="edd-settings-form">
					<?php

					settings_fields( 'edd_settings' );

					if ( 'main' === $section ) {
						do_action( 'edd_settings_tab_top', $active_tab );
					}

					do_action( 'edd_settings_tab_top_' . $suffix );
					do_settings_sections( 'edd_settings_' . $suffix );
					do_action( 'edd_settings_tab_bottom_' . $suffix );

					// For backwards compatibility.
					if ( 'main' === $section ) {
						do_action( 'edd_settings_tab_bottom', $active_tab );
					}

					// If the main section was empty and we overrode the view with the
					// next subsection, prepare the section for saving.
					if ( true === $override ) {
						?>
						<input type="hidden" name="edd_section_override" value="<?php echo esc_attr( $section ); ?>" />
						<?php
					}

					submit_button();
					?>
				</form>
			</div>
		</div>
		<?php
	}

	/**
	 * Output the primary options page navigation
	 *
	 * @since 3.0
	 *
	 * @param array  $tabs       All available tabs.
	 * @param string $active_tab Current active tab. Deprecated 3.3.0.
	 */
	public static function primary_navigation( $tabs, $active_tab = '' ) {
		$navigation = new SecondaryNavigation(
			$tabs,
			'edd-settings'
		);
		$navigation->render();
	}

	/**
	 * Output the secondary options page navigation
	 *
	 * @since 3.3.0
	 *
	 * @param string $active_tab The active tab.
	 * @param string $section    The active section.
	 * @param array  $sections   The available sections.
	 */
	public static function secondary_navigation( $active_tab = '', $section = '', $sections = array() ) {

		// Back compat for section'less tabs (Licenses, etc...).
		if ( empty( $sections ) ) {
			$section  = 'main';
			$sections = array(
				'main' => __( 'General', 'easy-digital-downloads' ),
			);
		}

		if ( count( $sections ) < 2 && 'main' === $section ) {
			return;
		}

		?>
		<div class="edd-sub-nav__wrapper">
			<ul class="edd-sub-nav">
				<?php

				// Loop through sections.
				foreach ( $sections as $section_id => $section_name ) {

					// Tab & Section.
					$tab_url = add_query_arg(
						array(
							'post_type' => 'download',
							'page'      => 'edd-settings',
							'tab'       => $active_tab,
							'section'   => $section_id,
						),
						edd_get_admin_base_url()
					);

					// Settings not updated.
					$tab_url = remove_query_arg( 'settings-updated', $tab_url );

					// Class for link.
					$class = ( $section === $section_id )
						? 'current'
						: '';

					printf(
						'<li class="%1$s"><a href="%2$s">%3$s</a></li>',
						esc_attr( $class ),
						esc_url( $tab_url ),
						esc_html( $section_name )
					);
				}
				?>
			</ul>
		</div>
		<?php
	}

	/**
	 * Gets the registered settings.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private static function get_settings() {
		if ( is_null( self::$settings ) ) {
			self::$settings = edd_get_registered_settings();
		}

		return self::$settings;
	}

	/**
	 * Gets the registered tabs.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private static function get_tabs() {
		if ( is_null( self::$tabs ) ) {
			$tabs = edd_get_settings_tabs();
			$tabs = empty( $tabs ) ? array() : $tabs;

			$settings = self::get_settings();
			foreach ( array_keys( $tabs ) as $tab ) {
				if ( empty( $settings[ $tab ] ) ) {
					unset( $tabs[ $tab ] );
				}
				if ( 'emails' === $tab ) {
					$tabs_to_ignore = array( 'main', 'purchase_receipts', 'sale_notifications', 'email_summaries' );
					$remaining_tabs = array_diff( array_keys( $settings['emails'] ), $tabs_to_ignore );
					if ( empty( $remaining_tabs ) ) {
						unset( $tabs['emails'] );
					}
				}
			}

			self::$tabs = $tabs;
		}

		return self::$tabs;
	}

	/**
	 * Gets the registered sections.
	 *
	 * @since 3.3.0
	 * @return array
	 */
	private static function get_sections() {
		if ( is_null( self::$sections ) ) {
			$sections = edd_get_settings_tab_sections( self::get_active_tab() );
			$sections = empty( $sections ) ? array() : $sections;

			/**
			 * Filters the sections for the active tab.
			 *
			 * This allows us to remove or add sections if necessary.
			 *
			 * @since 3.3.0
			 *
			 * @param array $sections The sections for the active tab.
			 */
			$sections = apply_filters( 'edd_render_settings_' . self::get_active_tab() . '_sections', $sections );

			self::$sections = $sections;
		}

		return self::$sections;
	}

	/**
	 * Gets the active tab.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private static function get_active_tab() {
		if ( is_null( self::$active_tab ) ) {
			$active_tab = 'general';
			if ( isset( $_GET['tab'] ) && array_key_exists( $_GET['tab'], self::get_tabs() ) ) {
				$active_tab = sanitize_text_field( $_GET['tab'] );
			}
			self::$active_tab = $active_tab;
		}

		return self::$active_tab;
	}

	/**
	 * Gets the active section.
	 *
	 * @since 3.3.0
	 * @return string
	 */
	private static function get_active_section() {
		$active_section = 'main';
		$sections       = self::get_sections();
		if ( ! empty( $_GET['section'] ) && ! empty( $sections[ $_GET['section'] ] ) ) {
			$active_section = sanitize_text_field( $_GET['section'] );
		}

		return $active_section;
	}

	/**
	 * Checks if the current section has main settings.
	 *
	 * @since 3.3.0
	 * @return bool
	 */
	private static function section_has_main_settings() {

		$active_tab = self::get_active_tab();

		// We are handling emails differently now, so we need to override the view.
		if ( 'emails' === $active_tab ) {
			return false;
		}

		$settings = self::get_settings();
		// If there is a main section, return true.
		if ( ! empty( $settings[ $active_tab ]['main'] ) ) {
			return true;
		}

		$sections = self::get_sections();
		$has_main = false;
		// Check for old non-sectioned settings (see #4211 and #5171).
		foreach ( $settings[ $active_tab ] as $sid => $stitle ) {
			if ( is_string( $sid ) && ! empty( $sections ) && array_key_exists( $sid, $sections ) ) {
				continue;
			}
			$has_main = true;
			break;
		}

		return $has_main;
	}
}

<?php
/**
 * Email editor style formats: button above editor and TinyMCE integration.
 *
 * @package EDD\Admin\Emails
 * @copyright Copyright (c) 2026, Sandhills Development, LLC
 * @license https://opensource.org/licenses/GPL-2.0 GNU Public License
 * @since 3.6.5
 */

namespace EDD\Admin\Emails;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\EventManagement\SubscriberInterface;

/**
 * Registers email editor style formats via a filter and renders a "Format" button
 * above the editor that opens a modal. Also injects the same formats into TinyMCE.
 *
 * @since 3.6.5
 * @package EDD\Admin\Emails
 */
class Styles implements SubscriberInterface {

	/**
	 * Current email object when on the email editor screen.
	 *
	 * @var \EDD\Emails\Templates\Base|null
	 */
	private $current_email = null;

	/**
	 * Gets the subscribed events.
	 *
	 * @since 3.6.5
	 * @return array
	 */
	public static function get_subscribed_events(): array {
		return array(
			'edd_email_editor_top' => array( 'setup', 5, 1 ),
		);
	}

	/**
	 * Sets up the style format UI and TinyMCE when the email editor is shown.
	 *
	 * @since 3.6.5
	 * @param \EDD\Emails\Templates\Base $email The email object.
	 */
	public function setup( $email ) {
		$this->current_email = $email;

		add_action( 'media_buttons', array( $this, 'render_button' ), 10 );
		add_filter( 'tiny_mce_before_init', array( $this, 'add_styles_tinymce' ), 10, 1 );
		add_filter( 'mce_buttons_2', array( $this, 'add_style_button' ), 10, 1 );
	}

	/**
	 * Adds the styleselect button to the TinyMCE toolbar when we have formats.
	 *
	 * @since 3.6.5
	 * @param array $buttons TinyMCE buttons row 2.
	 * @return array
	 */
	public function add_style_button( $buttons ) {
		if ( ! $this->current_email || ! $this->current_email->supports_html() ) {
			return $buttons;
		}

		$formats = $this->get_style_formats( $this->current_email );
		if ( empty( $formats ) ) {
			return $buttons;
		}

		array_unshift( $buttons, 'styleselect' );
		return $buttons;
	}

	/**
	 * Injects style formats into TinyMCE init from the filter.
	 *
	 * @since 3.6.5
	 * @param array $init_array TinyMCE initialization settings.
	 * @return array
	 */
	public function add_styles_tinymce( $init_array ) {
		if ( ! $this->current_email || ! $this->current_email->supports_html() ) {
			return $init_array;
		}

		$formats = $this->get_normalized_formats( $this->current_email );
		if ( empty( $formats ) ) {
			return $init_array;
		}

		$init_array['style_formats'] = wp_json_encode( $formats );

		return $init_array;
	}

	/**
	 * Renders the "Format" button and modal above the editor when formats are registered.
	 *
	 * @since 3.6.5
	 */
	public function render_button() {
		if ( ! $this->current_email || ! $this->current_email->supports_html() ) {
			return;
		}

		$formats = $this->get_normalized_formats( $this->current_email );
		if ( empty( $formats ) ) {
			return;
		}

		wp_enqueue_script( 'edd-admin-email-styles', edd_get_assets_url( 'js/admin/' ) . 'emails-styles.js', array(), edd_admin_get_script_version(), true );
		wp_localize_script(
			'edd-admin-email-styles',
			'eddEmailStyles',
			array(
				'formats'         => $formats,
				'editorId'        => 'edd-email-content',
				'linkPlaceholder' => __( 'Link', 'easy-digital-downloads' ),
			)
		);

		?>
		<button type="button" class="button edd-email-styles-inserter" data-dialog-id="edd-email-style-format-dialog" aria-label="<?php esc_attr_e( 'Insert style format', 'easy-digital-downloads' ); ?>">
			<span class="wp-media-buttons-icon dashicons dashicons-art"></span>
			<?php esc_html_e( 'Format', 'easy-digital-downloads' ); ?>
		</button>
		<?php
		$this->render_modal( $formats );
	}

	/**
	 * Outputs the modal dialog content listing format options (native dialog element, same pattern as Cart Recovery modal).
	 *
	 * @since 3.6.5
	 * @param array $formats Normalized format definitions (with name key).
	 */
	private function render_modal( array $formats ) {
		?>
		<dialog id="edd-email-style-format-dialog" class="edd-modal edd-modal--email-styles" aria-labelledby="edd-email-style-format-dialog__title">
			<div class="edd-modal__header">
				<h2 id="edd-email-style-format-dialog__title"><?php esc_html_e( 'Insert Style', 'easy-digital-downloads' ); ?></h2>
				<button type="button" class="edd-modal__close" aria-label="<?php esc_attr_e( 'Close', 'easy-digital-downloads' ); ?>">
					<span class="dashicons dashicons-no-alt"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Close', 'easy-digital-downloads' ); ?></span>
				</button>
			</div>
			<div class="edd-modal__content">
				<ul class="edd-email-styles__list">
					<?php foreach ( $formats as $format ) : ?>
					<li class="edd-email-styles__item">
						<button type="button" class="edd-email-styles__button" data-format-name="<?php echo esc_attr( $format['name'] ); ?>">
							<?php
							echo '<strong>' . esc_html( $format['title'] ) . '</strong>';
							if ( ! empty( $format['description'] ) ) {
								echo '<div class="edd-email-styles__description">' . esc_html( $format['description'] ) . '</div>';
							}
							?>
						</button>
					</li>
					<?php endforeach; ?>
				</ul>
			</div>
		</dialog>
		<?php
	}

	/**
	 * Gets style formats for the current email via the filter.
	 *
	 * @since 3.6.5
	 * @param \EDD\Emails\Templates\Base $email Email object.
	 * @return array List of format definitions (title, block or selector, classes, styles, etc.).
	 */
	private function get_style_formats( $email ) {
		$colors  = \EDD\Utils\Colors::get_button_colors();
		$formats = array(
			'button' => array(
				'title'       => __( 'Button', 'easy-digital-downloads' ),
				'description' => __( 'Show a link as a button.', 'easy-digital-downloads' ),
				'selector'    => 'a',
				'classes'     => 'edd-button',
				'styles'      => array(
					'display'          => 'inline-block',
					'padding'          => '12px 24px',
					'background-color' => $colors['buttonColor'],
					'color'            => $colors['buttonTextColor'],
					'text-decoration'  => 'none',
					'border-radius'    => '4px',
					'font-weight'      => 'bold',
				),
			),
		);

		/**
		 * Filters the style formats for the current email.
		 *
		 * @since 3.6.5
		 * @param array $formats List of format definitions (title, block or selector, classes, styles, etc.).
		 * @param \EDD\Emails\Templates\Base $email Email object.
		 * @return array List of format definitions (title, block or selector, classes, styles, etc.).
		 */
		return apply_filters( 'edd_email_editor_style_formats', $formats, $email );
	}

	/**
	 * Gets normalized formats for the current email.
	 *
	 * @since 3.6.5
	 * @param \EDD\Emails\Templates\Base $email Email object.
	 * @return array List of format definitions (title, block or selector, classes, styles, etc.).
	 */
	private function get_normalized_formats( $email ) {
		$formats = $this->get_style_formats( $email );
		if ( empty( $formats ) ) {
			return array();
		}

		return $this->normalize_formats_for_tinymce( $formats );
	}

	/**
	 * Normalizes format definitions for TinyMCE: ensures each has a stable "name" for formatter.apply().
	 *
	 * @since 3.6.5
	 * @param array $formats Raw format definitions from the filter.
	 * @return array Format definitions with "name" key added where missing.
	 */
	private function normalize_formats_for_tinymce( array $formats ) {
		$normalized = array();
		$used_names = array();

		foreach ( $formats as $format ) {
			$item = $format;

			if ( empty( $item['name'] ) ) {
				$item['name'] = $this->get_stable_format_name( $item, $used_names );
				$used_names[] = $item['name'];
			}

			$normalized[] = $item;
		}

		return $normalized;
	}

	/**
	 * Generates a stable format name from title or classes for TinyMCE formatter.
	 *
	 * @since 3.6.5
	 * @param array $format     Single format definition.
	 * @param array $used_names Names already assigned to avoid collisions.
	 * @return string Sanitized name unique among used names.
	 */
	private function get_stable_format_name( array $format, array $used_names ) {
		if ( ! empty( $format['classes'] ) ) {
			$class = is_array( $format['classes'] ) ? $format['classes'][0] : $format['classes'];
			$name  = sanitize_key( str_replace( array( ' ', '__' ), array( '_', '_' ), $class ) );
		} else {
			$title = isset( $format['title'] ) ? $format['title'] : 'format';
			$name  = sanitize_key( str_replace( ' ', '_', $title ) );
		}

		$base = $name;
		$i    = 0;
		while ( in_array( $name, $used_names, true ) ) {
			$name = $base . '_' . ( ++$i );
		}

		return $name;
	}
}

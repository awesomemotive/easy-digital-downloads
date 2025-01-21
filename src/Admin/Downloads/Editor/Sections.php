<?php
/**
 * Sections Class
 *
 * @package   EDD\Admin\Downloads\Editor
 * @copyright Copyright (c) 2024, Sandhills Development, LLC
 * @license   https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since     3.3.6
 */

namespace EDD\Admin\Downloads\Editor;

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit; // @codeCoverageIgnore

use EDD\Admin\Sections as Base;

/**
 * Main class for creating a vertically tabbed UI
 *
 * @since 3.3.6
 */
class Sections extends Base {

	/**
	 * Section ID
	 *
	 * @since 3.3.6
	 *
	 * @var array
	 */
	protected $id = 'edd_download_editor__';

	/**
	 * ID of the currently selected section
	 *
	 * @since 3.3.6
	 *
	 * @var string
	 */
	public $current_section = 'details';

	/**
	 * Output the contents
	 *
	 * @since 3.3.6
	 */
	public function display() {
		ob_start(); ?>

		<div class="edd-sections-wrap edd-download-editor__sections">
			<div class="edd-vertical-sections use-js meta-box">
				<ul class="section-nav" role="tablist">
					<?php echo $this->get_all_section_links(); ?>
				</ul>

				<div class="section-wrap">
					<?php echo $this->get_all_section_contents(); ?>
				</div>
			</div>
			<?php
			$this->nonce_field();

			if ( ! empty( $this->item ) ) :
				?>

				<input type="hidden" name="edd-item-id" value="<?php echo esc_attr( $this->item->id ); ?>" />

			<?php endif; ?>
		</div>

		<?php

		// Output current buffer.
		echo ob_get_clean();
	}

	/**
	 * Get all section links
	 *
	 * @since 3.3.6
	 *
	 * @return string
	 */
	protected function get_all_section_links() {
		ob_start();

		$repeatable = '';
		foreach ( $this->sections as $section ) :
			echo $this->get_section_link( $section ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			if ( empty( $repeatable ) ) {
				$repeatable = $section->repeatable_button;
			}
		endforeach;

		echo $repeatable; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped

		// Return current buffer.
		return ob_get_clean();
	}

	/**
	 * Gets a section link.
	 *
	 * @since 3.3.6
	 * @param object $section The section to get a link for.
	 */
	public function get_section_link( $section, $doing_ajax = false ) {
		static $dynamic;
		ob_start();
		$id      = $this->id . $section->id;
		$classes = array(
			'section-title',
		);
		if ( $this->is_current_section( $section->id ) ) {
			$classes[] = 'section-title--is-active';
		}

		if ( ! empty( $section->dynamic ) ) {
			$classes[] = 'section-title--is-dynamic';
			if ( ! $doing_ajax && ! $this->is_current_section( $section->id ) ) {
				$classes[] = 'edd-hidden';
			}
		}
		?>

		<li
			id="<?php echo esc_attr( $id ); ?>-nav-item"
			class="<?php echo esc_attr( implode( ' ', $classes ) ); ?>"
			role="tab"
			aria-controls="<?php echo esc_attr( $id ); ?>"
			<?php echo $this->get_section_data_attributes( $section ); ?>
		>
			<a href="<?php echo esc_url( '#' . $id ); ?>">
				<?php if ( $section->icon ) : ?>
					<span class="dashicons dashicons-<?php echo esc_attr( $section->icon ); ?>"></span>
				<?php else : ?>
					<span class="section-title__indicator"><?php echo absint( preg_replace( '/[^0-9]/', '', $section->id ) ); ?></span>
				<?php endif; ?>
				<span class="label">
					<?php echo $section->label; // Allow HTML. ?>
				</span>
				<?php $this->get_order_handles( $section ); ?>
			</a>
		</li>
		<?php

		return ob_get_clean();
	}

	/**
	 * Gets a section content.
	 *
	 * @since 3.3.6
	 * @param object $section The section to get content for.
	 */
	public function get_section_content( $section ) {
		$selected = ! $this->is_current_section( $section->id )
			? 'style="display: none;"'
			: '';

		$classes = $section->classes;
		if ( $section->dynamic ) {
			$classes[] = 'section-content--is-dynamic';
		}

		ob_start();
		?>

		<div id="<?php echo esc_attr( $this->id . $section->id ); ?>" class="<?php echo implode( ' ', $classes ); ?>" <?php echo $selected; ?>>
			<?php

			// Callback or action.
			if ( ! empty( $section->callback ) ) {
				if ( is_callable( $section->callback ) ) {
					call_user_func( $section->callback, $this->item );
				} elseif ( is_array( $section->callback ) && is_callable( $section->callback[0] ) ) {
					$parameters = array();
					if ( isset( $section->callback[1] ) ) {
						$parameters = $section->callback[1];
					}
					call_user_func_array( $section->callback[0], $parameters );
				} else {
					esc_html_e( 'Invalid section', 'easy-digital-downloads' );
				}
			} else {
				die;
				do_action( 'edd_' . $section->id . 'section_contents', $this );
			}

			?>
		</div>
		<?php

		return ob_get_clean();
	}

	/**
	 * Get all section contents.
	 *
	 * @since 3.3.6
	 * @return string
	 */
	protected function get_all_section_contents() {
		// Bail if no sections.
		if ( empty( $this->sections ) ) {
			return;
		}

		// Loop through sections.
		foreach ( $this->sections as $section ) :
			echo $this->get_section_content( $section ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		endforeach;
	}

	/**
	 * Is a section the current section?
	 *
	 * @since 3.3.6
	 * @param string $section_id The section ID to check.
	 * @return bool
	 */
	protected function is_current_section( $section_id = '' ) {
		return (bool) ( 'details' === $section_id );
	}

	/**
	 * Get the order handles for a section.
	 *
	 * @since 3.3.6
	 * @param object $section The section to get order handles for.
	 */
	private function get_order_handles( $section ) {
		if ( empty( $section->dynamic ) ) {
			return;
		}
		?>
		<div class="edd__handle-actions-order edd-section-title__handle-actions hide-if-no-js">
			<button type="button" class="edd__handle-actions edd__handle-actions-order--higher" aria-disabled="false" aria-describedby="<?php echo esc_attr( $section->id ); ?>-edd__handle-actions-order--higher-description">
				<span class="screen-reader-text"><?php esc_html_e( 'Move up', 'easy-digital-downloads' ); ?></span>
				<span class="dashicons dashicons-arrow-up-alt2" aria-hidden="true"></span>
			</button>
			<span class="hidden" id="<?php echo esc_attr( $section->id ); ?>-edd__handle-actions-order--higher-description">
				<?php
				/* translators: %s: Section label */
				printf( esc_html__( 'Move %s up', 'easy-digital-downloads' ), $section->label );
				?>
			</span>
			<button type="button" class="edd__handle-actions edd__handle-actions-order--lower" aria-disabled="false" aria-describedby="<?php echo esc_attr( $section->id ); ?>-edd__handle-actions-order--lower-description">
				<span class="screen-reader-text"><?php esc_html_e( 'Move down', 'easy-digital-downloads' ); ?></span>
				<span class="dashicons dashicons-arrow-down-alt2" aria-hidden="true"></span>
			</button>
			<span class="hidden" id="<?php echo esc_attr( $section->id ); ?>-edd__handle-actions-order--lower-description">
				<?php
				/* translators: %s: Section label */
				printf( esc_html__( 'Move %s down', 'easy-digital-downloads' ), $section->label );
				?>
			</span>
		</div>
		<?php
	}

	/**
	 * Get the data attributes for a section.
	 *
	 * @param stdClass $section The section data.
	 * @return string
	 */
	private function get_section_data_attributes( $section ) {
		$data_attributes = array();
		if ( ! empty( $section->requires ) ) {
			if ( is_string( $section->requires ) ) {
				$data_attributes[] = 'data-edd-requires-' . esc_attr( $section->requires ) . '="true"';
			} elseif ( is_array( $section->requires ) ) {
				foreach ( $section->requires as $requirement => $value ) {
					$data_attributes[] = 'data-edd-requires-' . esc_attr( $requirement ) . '="' . esc_attr( $value ) . '"';
				}
			}
		}
		if ( ! empty( $section->supports ) ) {
			foreach ( $section->supports as $key => $values ) {
				$data_attributes[] = 'data-edd-supports-' . esc_attr( $key ) . '="' . implode( ',', array_map( 'esc_attr', $values ) ) . '"';
			}
		}

		return ! empty( $data_attributes ) ? implode( ' ', $data_attributes ) : '';
	}
}

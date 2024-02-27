<?php

namespace EDD\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Class Tooltip
 *
 * @since 3.2.8
 * @package EDD\HTML
 */
class Tooltip extends Base {

	/**
	 * Gets the HTML for the tooltip.
	 *
	 * @since 3.2.8
	 * @return string
	 */
	public function get() {
		// If there are no items passed, return an empty string.
		if ( empty( $this->args['content'] ) ) {
			return '';
		}

		$tooltip_content  = $this->get_title_markup();
		$tooltip_content .= $this->separate_title_content( $tooltip_content );
		$tooltip_content .= $this->args['content'];

		// Return the icon for the tooltip, with the tooltip content added as the title attribute.
		return sprintf(
			'<span class="%1$s" title="%2$s"></span>',
			$this->get_css_class_string(),
			$tooltip_content
		);
	}

	/**
	 * Gets the default arguments for the element.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	protected function defaults() {
		return array(
			'title'      => '',
			'content'    => '',
			'dashicon'   => 'dashicons-editor-help',
			'line_break' => false,
		);
	}

	/**
	 * Gets the base classes for an element which cannot be overwritten.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	protected function get_base_classes(): array {
		return array( 'edd-help-tip', 'dashicons', $this->args['dashicon'] );
	}

	/**
	 * Gets the HTML for the title of the tooltip.
	 *
	 * @since 3.2.7
	 * @return string
	 */
	protected function get_title_markup() {
		// Ensure the title only contains allowed HTML tags and trim it up.
		$this->args['title'] = trim(
			wp_kses(
				$this->args['title'],
				array(
					'em'     => array(),
					'strong' => array(),
				)
			)
		);

		// After sanitizing, if the title is empty, return an empty string.
		if ( empty( $this->args['title'] ) ) {
			return '';
		}

		return sprintf(
			'<span class=\'title\'>%s</span>',
			$this->args['title']
		);
	}

	/**
	 * Adds padding to the content if the title is not empty.
	 *
	 * @since 3.2.8
	 * @param string $content The content to add padding to.
	 * @return string
	 */
	private function separate_title_content( $content ) {
		if ( empty( $content ) ) {
			return '';
		}

		if ( ! empty( $this->args['line_break'] ) ) {
			return '<br>';
		}

		// Check if the title ends with a punctuation mark. If not, add a colon.
		$last_character = substr( $this->args['title'], -1 );
		$separator      = ' ';
		if ( ! in_array( $last_character, $this->get_final_punctuation(), true ) ) {
			$separator = ':' . $separator;
		}

		return $separator;
	}

	/**
	 * Gets the final punctuation marks.
	 *
	 * @since 3.2.8
	 * @return array
	 */
	private function get_final_punctuation() {
		return array( '.', '!', '?', ':' );
	}
}

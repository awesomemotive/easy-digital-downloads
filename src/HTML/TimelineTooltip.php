<?php
/**
 * Timeline Tooltip
 *
 * Generates the markup for a tooltip that displays a timeline of events.
 *
 * @since 3.2.7
 *
 * @package EDD\HTML
 * @subpackage EDD\HTML\TimelineTooltip
 */

namespace EDD\HTML;

defined( 'ABSPATH' ) || exit;

/**
 * Class TimelineTooltip
 *
 * We're labeling this as a 'final' class as we are in the process of refactoring the HTML class
 * system, and we don't want anyone extending this class in anticipation of the upcoming changes.
 *
 * When we work to refactor we'll likely introduce a ToolTip class that this class can extend.
 *
 * @since 3.2.7
 */
final class TimelineTooltip extends Tooltip {

	/**
	 * Gets the HTML for the tooltip.
	 *
	 * @since 3.2.7
	 * @return array
	 */
	protected function defaults() {
		return array(
			'title'         => '',
			'items'         => array(),
			'max_items'     => 5,
			'slice_from'    => 'start',
			'more_position' => 'bottom',
			'dashicon'      => 'dashicons-clock',
		);
	}

	/**
	 * Gets the HTML for the tooltip.
	 *
	 * @since 3.2.7
	 * @return string
	 */
	public function get() {
		// If there are no items passed, return an empty string.
		if ( empty( $this->args['items'] ) ) {
			return '';
		}

		$title      = $this->get_title_markup();
		$opening_ul = sprintf(
			'<ul class=\'%1$s\'>', // As this is content added to a title attribute, we have to use single quotes here.
			$this->array_to_css_string( array( 'timeline' ) )
		);

		$list_item_markup = '';

		// Loop over the items, and append them to the content string.
		foreach ( $this->parse_list_items() as $list_item ) {
			$list_item_markup .= sprintf(
				'<li>%s</li>', // As this is content added to a title attribute, we have to use single quotes here.
				$list_item
			);
		}

		$closing_ul = '</ul>';

		// Build the tooltip content string.
		$tooltip_content = sprintf(
			'%1$s%2$s%3$s%4$s',
			$title,
			$opening_ul,
			$list_item_markup,
			$closing_ul
		);

		// Return the icon for the tooltip, with the tooltip content added as the title attribute.
		return sprintf(
			'<span class="%1$s" title="%2$s"></span>',
			$this->get_css_class_string(),
			$tooltip_content
		);
	}

	/**
	 * Gets the list items for the tooltip.
	 *
	 * @since 3.2.7
	 *
	 * @return array
	 */
	private function parse_list_items() {
		$items         = $this->args['items'];
		$total_items   = count( $items );
		$max_items     = $this->args['max_items'];
		$more_position = $this->args['more_position'];

		// Reduce the number of items in the array to the max number of items.
		if ( false !== $max_items ) {
			$items = 'start' === $this->args['slice_from'] ?
				array_slice( $items, 0, $max_items ) :
				array_slice( $items, -( $max_items ) );
		}

		// Initialize the list items array.
		$list_items = array();

		foreach ( $items as $item ) {
			// If the item is numeric (a timestamp) convert it to a date string.
			if ( is_numeric( $item ) ) {
				$item = edd_date_i18n( $item, get_option( 'date_format' ) . ' H:i:s' ) . ' ' . edd_get_timezone_abbr();
			}

			$list_items[] = esc_html( $item );
		}

		if (
			false !== $max_items && // If there is a max number of items.
			false !== $more_position && // .. and if the more position is not false.
			$total_items > $max_items // ...and if the total number of items is greater than the max number of items.
		) {
			$more_count = $total_items - $max_items;

			$more_items = sprintf(
				// translators: %s: number of additional items that are not being displayed.
				__( '%s More', 'easy-digital-downloads' ),
				$more_count > 10 ?
					'10+' :
					$more_count
			);

			// If at the top, use array_unshift, otherwise add to the array at the bottom.
			if ( 'top' === $more_position ) {
				array_unshift( $list_items, $more_items );
			} else {
				$list_items[] = $more_items;
			}
		}

		return $list_items;
	}
}

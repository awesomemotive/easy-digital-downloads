/* global eddEmailTagsInserter, tb_remove, tb_position, send_to_editor, _, window, document */

/**
 * Internal dependencies.
 */
import { searchItems } from './utils.js';

/**
 * Make tags clickable and send them to the email content (wp_editor()).
 */
function setupEmailTags() {
	// Find all of the buttons.
	const insertButtons = document.querySelectorAll( '.edd-email-tags-list-button' );
	if ( ! insertButtons ) {
		return;
	}

	/**
	 * Listen for clicks on tag buttons.
	 *
	 * @param {object} node Button node.
	 */
	_.each( insertButtons, function( node ) {
		/**
		 * Listen for clicks on tag buttons.
		 */
		node.addEventListener( 'click', function() {
			// Close Thickbox.
			tb_remove();

			window.send_to_editor( node.dataset.to_insert );
		} );
	} );
}

/**
 * Filter tags.
 */
function filterEmailTags() {
	const filterInput = document.querySelector( '.edd-email-tags-filter-search' );
	const tagItems = document.querySelectorAll( '.edd-email-tags-list-item' );

	if ( ! filterInput ) {
		return;
	}

	filterInput.addEventListener( 'keyup', function( event ) {
		const searchTerm = event.target.value;
		const foundTags = searchItems( eddEmailTagsInserter.items, searchTerm );

		_.each( tagItems, function( node ) {
			const found = _.findWhere( foundTags, { tag: node.dataset.tag } );

			node.style.display = ! found ? 'none' : 'block';
		} );
	} );
}

/**
 * DOM ready.
 */
document.addEventListener( 'DOMContentLoaded', function() {
	// Resize Thickbox when media button is clicked.
	const mediaButton = document.querySelector( '.edd-email-tags-inserter' );
	if ( ! mediaButton ) {
		return;
	}

	mediaButton.addEventListener( 'click', tb_position );

	// Clickable tags.
	setupEmailTags();

	// Filterable tags.
	filterEmailTags();
} );

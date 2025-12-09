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
 * Tags search event setup.
 */
function setupEmailTagSearch() {
	const filterInput = document.querySelector( '.edd-email-tags-filter-search' );
	if ( ! filterInput ) {
		return;
	}

	filterInput.addEventListener( 'input', function( event ) {
		filterEmailTags( event.target.value );
	} );
}

/**
 * Tags navigation event setup.
 */
function setupEmailTagNavigation() {
	document.addEventListener( 'keydown', function( event ) {
		const key = event.key || event.keyCode;
		if ( ! key ) {
			return;
		}

		if ( key === 'ArrowDown' || key === 40 ) {
			handleNavigation( 'down' );
			return;
		}

		if ( key === 'ArrowUp' || key === 38 ) {
			handleNavigation( 'up' );
		}
	} );
}

/**
 * Focus on the search input when the tags inserter is opened.
 */
function setupEmailTagSearchFocus() {
	const tagInserter = document.querySelector( '.edd-email-tags-inserter' );
	if ( ! tagInserter ) {
		return;
	}

	tagInserter.addEventListener( 'click', () => {
		setTimeout( () => {
			const filterInput = document.querySelector( '.edd-email-tags-filter-search' );
			if ( filterInput ) {
				filterInput.value = '';
				filterInput.dispatchEvent( new Event( 'input' ) );
				filterInput.focus();
			}
		}, 10 );
	} );
}

/**
 * Filter tags.
 *
 * @param {string} term Tags search term.
 */
function filterEmailTags( term = '' ) {

	const tagItems = document.querySelectorAll( '.edd-email-tags-list-item' );
	const foundTags = searchItems( eddEmailTagsInserter.items, term );

	_.each( tagItems, function( node ) {
		const found = _.findWhere( foundTags, { tag: node.dataset.tag } );

		node.style.display = ! found ? 'none' : 'block';
	} );
}

/**
 * Handle navigation between tags and search input.
 *
 * @param {string} direction Navigation direction.
 */
function handleNavigation( direction ) {
	const tagItems = document.querySelectorAll( '.edd-email-tags-list-item' );
	if ( ! tagItems.length ) {
		return;
	}

	const searchInputSelector = '.edd-email-tags-filter-search';
	const tagButtonSelector = '.edd-email-tags-list-button';

	const currentElement = document.activeElement;
	if ( ! currentElement || ( ! currentElement.matches( searchInputSelector ) && ! currentElement.matches( tagButtonSelector ) ) ) {
		return;
	}

	let nextTag;
	let isVisible = false;

	// skip the search input navigation from the loop.
	if ( currentElement.matches( searchInputSelector ) ) {
		if ( direction === 'up' ) {
			return;
		}
		nextTag = _.find( tagItems, ( node ) => node.style.display !== 'none' );
		isVisible = true;
	} else {
		nextTag = currentElement.parentElement;
	}

	// find a visible tag.
	while ( ! isVisible ) {
		nextTag = direction === 'down' ? nextTag.nextElementSibling : nextTag.previousElementSibling;
		isVisible = ! nextTag || nextTag.style.display !== 'none';
	}

	if ( ! nextTag ) {
		// focus on the search input when no previous tags found.
		if ( direction === 'up' ) {
			document.querySelector( searchInputSelector ).focus();
		}
		return;
	}

	nextTag.querySelector( tagButtonSelector ).focus();
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

	// Search tags.
	setupEmailTagSearch();

	// Search tags navigation.
	setupEmailTagNavigation();

	// Search tags input focus.
	setupEmailTagSearchFocus();
} );

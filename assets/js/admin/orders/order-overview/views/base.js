/* global _, $ */

/**
 * WordPress dependencies
 */
import { focus } from '@wordpress/dom';

/**
 * Internal dependencies
 */
import { getChosenVars } from 'utils/chosen.js';

// Set noconflict when using Lodash (@wordpress packages) and Underscores.
// @todo Find a better place to set this up. Webpack?
window.lodash = _.noConflict();

/**
 * Base
 *
 * Supplies additional functionality and helpers beyond
 * what is provided by `wp.Backbone.View`.
 *
 * - Maintains focus and caret positioning on rendering.
 * - Extends events via `addEvents()`.
 *
 * @since 3.0
 *
 * @class Base
 * @augments wp.Backbone.View
 */
export const Base = wp.Backbone.View.extend( {
	/**
	 * Defines base events to help maintain focus and caret position.
	 *
	 * @since 3.0
	 */
	events: {
		'keydown input': 'handleTabBehavior',
		'keydown textarea': 'handleTabBehavior',

		'focus input': 'onFocus',
		'focus textarea': 'onFocus',
		'focus select': 'onFocus',

		'change input': 'onChange',
		'change textarea': 'onChange',
		'change select': 'onChange',
		'input input': 'onChange',
		'input textarea': 'onChange',
		'input select': 'onChange',
	},

	/**
	 * Sets up additional properties.
	 *
	 * @since 3.0
	 */
	preinitialize() {
		this.focusedEl = null;
		this.focusedElCaretPos = 0;

		wp.Backbone.View.prototype.preinitialize.apply( this, arguments );
	},

	/**
	 * Merges additional events with existing events.
	 *
	 * @since 3.0
	 *
	 * @param {Object} events Hash of events to add.
	 */
	addEvents( events ) {
		this.delegateEvents( {
			...this.events,
			...events,
		} );
	},

	/**
	 * Moves the focus when dealing with tabbing.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Keydown event.
	 */
	handleTabBehavior( e ) {
		const { keyCode, shiftKey, target } = e;

		// 9 = TAB
		if ( 9 !== keyCode ) {
			return;
		}

		const tabbables = focus.tabbable.find( this.el );

		if ( ! tabbables.length ) {
			return;
		}

		const firstTabbable = tabbables[ 0 ];
		const lastTabbable = tabbables[ tabbables.length - 1 ];
		let toFocus;

		if ( shiftKey && target === firstTabbable ) {
			toFocus = lastTabbable;
		} else if ( ! shiftKey && target === lastTabbable ) {
			toFocus = firstTabbable;
		} else if ( shiftKey ) {
			toFocus = focus.tabbable.findPrevious( target );
		} else {
			toFocus = focus.tabbable.findNext( target );
		}

		if ( 'undefined' !== typeof toFocus ) {
			this.focusedEl = toFocus;
			this.focusedElCaretPos = toFocus.value.length;
		} else {
			this.focusedEl = null;
			this.focusedElCaretPos = 0;
		}
	},

	/**
	 * Tracks the current element when focusing.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event.
	 */
	onFocus( e ) {
		this.focusedEl = e.target;
	},

	/**
	 * Tracks the current cursor position when editing.
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event.
	 */
	onChange( e ) {
		const { target, keyCode } = e;

		// 9 = TAB
		if ( undefined !== typeof keyCode && 9 === keyCode ) {
			return;
		}

		try {
			if ( target.selectionStart ) {
				this.focusedElCaretPos = target.selectionStart;
			}
		} catch ( error ) {
			this.focusedElCaretPos = target.value.length;
		}
	},

	/**
	 * Prepares data to be used in `render` method.
	 *
	 * @since 3.0
	 *
	 * @see wp.Backbone.View
	 * @see https://github.com/WordPress/WordPress/blob/master/wp-includes/js/wp-backbone.js
	 *
	 * @return {Object} The data for this view.
	 */
	prepare() {
		return this.model
			? {
					...this.model.toJSON(),
					state: this.model.get( 'state' ).toJSON(),
			  }
			: {};
	},

	/**
	 * Adds additional handling after initial render.
	 *
	 * @since 3.0
	 */
	render() {
		wp.Backbone.View.prototype.render.apply( this, arguments );

		this.initializeSelects();
		this.setFocus();

		return this;
	},

	/**
	 * Reinitializes special <select> fields.
	 *
	 * @since 3.0
	 */
	initializeSelects() {
		const selects = this.el.querySelectorAll( '.edd-select-chosen' );

		// Reinialize Chosen.js
		_.each( selects, ( el ) => {
			$( el ).chosen( {
				...getChosenVars( $( el ) ),
				width: '100%',
			} );
		} );
	},

	/**
	 * Sets the focus and caret position.
	 *
	 * @since 3.0
	 */
	setFocus() {
		const { el, focusedEl, focusedElCaretPos } = this;

		// Do nothing extra if nothing is focused.
		if ( null === focusedEl || 'undefined' === typeof focusedEl ) {
			return;
		}

		// Convert full element in to a usable selector.
		// We can't search for the actual HTMLElement because
		// the DOM has since changed.
		let selector = null;

		if ( '' !== focusedEl.id ) {
			selector = `#${ focusedEl.id }`;
		} else if ( '' !== focusedEl.name ) {
			selector = `[name="${ focusedEl.name }"]`;
		} else if ( focusedEl.classList.length > 0 ) {
			selector = `.${ [ ...focusedEl.classList ].join( '.' ) }`;
		}

		// Do nothing if we can't generate a selector.
		if ( null === selector ) {
			return;
		}

		// Focus element.
		const elToFocus = el.querySelector( selector );

		if ( ! elToFocus ) {
			return;
		}

		elToFocus.focus();

		// Attempt to set the caret position.
		try {
			if ( elToFocus.setSelectionRange ) {
				elToFocus.setSelectionRange(
					focusedElCaretPos,
					focusedElCaretPos
				);
			}
		} catch ( error ) {}
	},
} );

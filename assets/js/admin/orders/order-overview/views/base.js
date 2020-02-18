/* global wp */

/**
 * WordPress dependencies
 */
import { focus } from '@wordpress/dom';
import { TAB } from '@wordpress/keycodes';

// Set noconflict when using Lodash (@wordpress packages) and Underscores.
// @todo Find a better place to set this up. Webpack?
window.lodash = _.noConflict();

/**
 * Base View
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
export const Base = wp.Backbone.View.extend( /** Lends Base.prototype */ {
	/**
	 * Defines base events to help maintain focus and caret.
	 *
	 * @since 3.0
	 */
	events: {
		'keydown input': 'handleTabBehavior',
		'keydown textarea': 'handleTabBehavior',

		'change input': 'onChange',
		'change textarea': 'onChange',
		'change select': 'onChange',
	},

	/**
	 * Sets up additional properties.
	 *
	 * @since 3.0
	 */
	preinitialize() {
		this.focusedEl = null;
		this.focusedElCaretPos = 0;

		wp.Backbone.View.prototype.preinitialize.apply( this );
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
		const {
			keyCode,
			shiftKey,
			target,
			preventDefault,
		} = e;

		if ( TAB !== keyCode ) {
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

		if ( 'undefined' !== typeof toFocus ){
			this.focusedEl = toFocus;
			this.focusedElCartetPos = toFocus.value.length;
		} else {
			this.focusedEl = null;
			this.focusedElCartetPos = 0;
		}
	},

	/**
	 * Maintains focus when modifying an <input /> or <textarea />
	 *
	 * @since 3.0
	 *
	 * @param {Object} e Change event.
	 */
	onChange( e ) {
		const {
			target,
			keyCode,
		} = e;

		this.focusedEl = target;

		// Attempt to find the caret position.
		if ( target.selectionStart ) {
			try {
				this.focusedElCaretPos = target.selectionStart;
			} catch ( error ) {
				this.focusedElCaretPos = target.value.length;
			}
		}
	},

	/**
	 * Restores focus and caret position after render.
	 *
	 * @since 3.0
	 */
	render() {
		wp.Backbone.View.prototype.render.apply( this );

		const {
			focusedEl,
			focusedElCaretPos,
		} = this;

		// Do nothing extra if nothing is focused.
		if ( null === focusedEl ) {
			return;
		}

		// Convert full element in to a usable selector.
		// We can't search for the actual HTMLElement because
		// the DOM has since changed.
		let selector = '' !== focusedEl.id
			? `#${ focusedEl.id }`
			: `[name="${ focusedEl.name }"]`;

		// Focus element.
		const elToFocus = this.el.querySelector( selector );
		elToFocus.focus();

		// Attempt to set the caret position.
		try {
			if ( elToFocus.setSelectionRange ) {
				elToFocus.setSelectionRange(
					focusedElCaretPos,
					focusedElCaretPos,
				);
			}
		} catch ( error ) {}

		return this;
	},
} );

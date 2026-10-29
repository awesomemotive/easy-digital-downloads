/* global TomSelect, ajaxurl, globalThis */

const chosenVars = {
	disable_search_threshold: 13,
	search_contains: true,
	inherit_select_classes: true,
};

/**
 * Convert an AJAX search result into a Tom Select option.
 *
 * Every key besides id and name is carried through, since individual search
 * handlers add their own fields for option templates to render.
 *
 * @param {Object} item Single result from an EDD search AJAX handler.
 * @return {Object} Tom Select option.
 */
const buildOption = ( item ) => {
	const option = { value: String( item.id ), text: item.name };

	Object.keys( item ).forEach( ( key ) => {
		if ( 'id' !== key && 'name' !== key ) {
			option[ key ] = item[ key ];
		}
	} );

	return option;
};

/**
 * Determine the variables used to initialize a select element.
 *
 * For elements with a data-search-type attribute, AJAX search options
 * (load, loadThrottle, shouldLoad) are included so Tom Select can apply
 * its debounce during setup rather than requiring a separate call.
 *
 * @param {HTMLSelectElement} el Native select element.
 * @return {Object} Options object.
 */
export const getChosenVars = ( el ) => {
	// edd_vars is localized onto edd-admin-scripts, which this bundle does not
	// depend on, so read it when a select is initialized.
	const eddVars = globalThis.edd_vars ?? {};
	const inputVars = {
		...chosenVars,
		placeholder_text_single: eddVars.one_option,
		placeholder_text_multiple: eddVars.one_or_more_option,
		no_results_text: eddVars.no_results_text,
	};

	const searchType = el?.dataset?.searchType;

	if ( searchType ) {
		// Fields with a search type start with no options and load via AJAX,
		// so the search input must always be shown regardless of option count.
		delete inputVars.disable_search_threshold;

		if ( 'no_ajax' !== searchType ) {
			const action = 'edd_' + searchType + '_search';
			const minLength = ( 'edd_download_search' === action ) ? 4 : 1;

			// Collect all data-search-* attributes and convert to snake_case query params.
			const searchData = { action };
			Object.keys( el.dataset )
				.filter( ( key ) => key.startsWith( 'search' ) && key !== 'searchType' && key !== 'searchPlaceholder' )
				.forEach( ( key ) => {
					// Convert camelCase key (e.g. searchNoBundles) to snake_case (no_bundles).
					const paramName = key.replace( 'search', '' )
						.replace( /([A-Z])/g, '_$1' )
						.toLowerCase()
						.replace( /^_/, '' );
					searchData[ paramName ] = el.dataset[ key ];
				} );

			// Track which option values were returned by the most recent AJAX
			// response so the score function can always show them. The set is
			// cleared when the search query changes, ensuring stale remote
			// results don't persist across different searches.
			const remoteValues = new Set();
			let lastScoredQuery = null;

			inputVars.shouldLoad = ( query ) => query.length >= minLength;
			inputVars.loadThrottle = 521;
			inputVars.load = function( query, callback ) {
				const params = new URLSearchParams( { ...searchData, s: query } );
				fetch( ajaxurl + '?' + params.toString() )
					.then( ( response ) => response.json() )
					.then( ( data ) => {
						const items   = data && 'object' === typeof data ? Object.values( data ) : [];
						const options = items.map( buildOption );

						for ( const option of options ) {
							remoteValues.add( option.value );
						}

						callback( options );
					} )
					.catch( () => callback() );
			};

			// Server-returned results must always be visible even when the
			// search query doesn't match the display text (e.g. searching by
			// email when the option shows a display name). Pre-populated
			// options use default local scoring so they filter as expected.
			inputVars.score = function( query ) {
				if ( query !== lastScoredQuery ) {
					remoteValues.clear();
					lastScoredQuery = query;
				}
				const defaultScoreFn = this.getScoreFunction( query );
				return function( item ) {
					if ( remoteValues.has( String( item.value ) ) ) {
						return 1;
					}
					return defaultScoreFn( item );
				};
			};
		}
	}

	return { ...inputVars };
};

/**
 * Build a Tom Select configuration object from a Chosen-style options object.
 *
 * Used by both initChosen (internal clean path) and the jQuery.fn.chosen shim
 * (legacy compatibility path) so config translation stays in one place.
 *
 * @param {HTMLSelectElement} el      Native select element.
 * @param {Object}            options Chosen-style options (e.g. from getChosenVars).
 * @return {Object} Tom Select configuration object.
 */
export function buildTomSelectConfig( el, options ) {
	const isMultiple  = el.multiple;
	const config      = {
		copyClassesToDropdown: false,
		closeAfterSelect: ! isMultiple,
	};

	if ( isMultiple ) {
		config.plugins = [ 'remove_button' ];
	}

	// Placeholder text — prefer data-placeholder attribute on the element.
	const dataPlaceholder = el.dataset?.placeholder || '';
	if ( dataPlaceholder ) {
		config.placeholder = dataPlaceholder;
	} else if ( isMultiple && options.placeholder_text_multiple ) {
		config.placeholder = options.placeholder_text_multiple;
	} else if ( ! isMultiple && options.placeholder_text_single ) {
		config.placeholder = options.placeholder_text_single;
	}

	// no_results_text.
	if ( options.no_results_text ) {
		config.render = config.render || {};
		config.render.no_results = ( data, escape ) => {
			return `<div class="no-results">${ escape( options.no_results_text ) }</div>`;
		};
	}

	// AJAX search: pass load, loadThrottle, and shouldLoad so Tom Select
	// applies its debounce during setup() rather than requiring post-init config.
	if ( options.load ) {
		config.load        = options.load;
		config.loadThrottle = options.loadThrottle ?? 521;
	}

	if ( options.score ) {
		config.score = options.score;
	}

	if ( options.shouldLoad ) {
		config.shouldLoad = options.shouldLoad;
	}

	// inherit_select_classes: copy the select's classes to the Tom Select wrapper.
	if ( options.inherit_select_classes !== false ) {
		config.wrapperClass = `ts-wrapper ${ el.className || '' }`;
	}

	// Single-select: clear the textbox when the control receives focus so that
	// typing replaces the displayed selection text rather than appending to it.
	if ( ! isMultiple ) {
		config.onFocus = function() {
			this.setTextboxValue( '' );
		};
	}

	return config;
}

/**
 * Add Chosen-compatible CSS classes and a legacy label span to a Tom Select instance.
 *
 * External code (including extensions) may query `.chosen-single span` to read
 * the selected value's display text. This ensures that contract is honoured
 * regardless of whether the element was initialized via initChosen or the shim.
 *
 * @param {Object}  instance   Tom Select instance.
 * @param {boolean} isMultiple Whether the select allows multiple selections.
 * @return {void}
 */
export function addCompatClasses( instance, isMultiple ) {
	const { wrapper, dropdown, control } = instance;

	if ( wrapper ) {
		wrapper.classList.add( 'chosen-container', isMultiple ? 'chosen-container-multi' : 'chosen-container-single' );
	}

	if ( dropdown ) {
		dropdown.classList.add( 'chosen-drop' );
	}

	if ( control ) {
		control.classList.add( isMultiple ? 'chosen-choices' : 'chosen-single' );

		if ( ! isMultiple ) {
			const span = document.createElement( 'span' );
			span.className = 'chosen-legacy';
			const currentValue = instance.getValue();
			if ( currentValue && instance.options[ currentValue ] ) {
				span.textContent = instance.options[ currentValue ].text || '';
			}
			control.insertBefore( span, control.firstChild );

			// Keep the span in sync as the selection changes.
			instance.on( 'change', ( value ) => {
				const opt = value ? instance.options[ value ] : null;
				span.textContent = opt ? ( opt.text || '' ) : '';
			} );
		}
	}
}

/**
 * Initialize Tom Select on a select element.
 *
 * Accepts a native HTMLSelectElement. For legacy code that passes a jQuery
 * object, the jQuery.fn.chosen shim in chosen-compat.entry.js should be used.
 *
 * @param {HTMLSelectElement} el          Native select element.
 * @param {Object}            extraConfig Optional Tom Select config overrides merged on top of the defaults.
 * @return {void}
 */
export const initChosen = ( el, extraConfig = {} ) => {
	if ( ! el ) {
		return;
	}

	const nativeEl = el instanceof HTMLSelectElement ? el : el[ 0 ];
	if ( ! nativeEl ) {
		return;
	}

	if ( nativeEl.tomselect ) {
		return;
	}

	const config = { ...buildTomSelectConfig( nativeEl, getChosenVars( nativeEl ) ), ...extraConfig };
	const instance = new TomSelect( nativeEl, config );
	addCompatClasses( instance, nativeEl.multiple );
};

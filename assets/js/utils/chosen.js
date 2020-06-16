/* global jQuery, edd_vars */

export const chosenVars = {
	disable_search_threshold: 13,
	search_contains: true,
	inherit_select_classes: true,
	single_backstroke_delete: false,
	placeholder_text_single: edd_vars.one_option,
	placeholder_text_multiple: edd_vars.one_or_more_option,
	no_results_text: edd_vars.no_results_text,
};

/**
 * Determine the variables used to initialie Chosen on an element.
 *
 * @param {Object} el select element.
 * @return {Object} Variables for Chosen.
 */
export const getChosenVars = ( el ) => {
	if ( ! el instanceof jQuery ) {
		el = jQuery( el );
	}

	let inputVars = chosenVars;

	// Ensure <select data-search-type="download"> or similar can use search always.
	// These types of fields start with no options and are updated via AJAX.
	if ( el.data( 'search-type' ) ) {
		delete inputVars.disable_search_threshold;
	}

	return {
		...inputVars,
		width: el.css( 'width' ),
	};
}

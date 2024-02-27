/**
 * Attach tooltips
 *
 * @param {string} selector
 */
export const edd_attach_tooltips = function( selector ) {
	selector.tooltip( {
		content: function() {
			return $( this ).prop( 'title' );
		},
		tooltipClass: 'edd-ui-tooltip',
		position: {
			my: 'bottom',
			at: 'top-10',
			collision: 'flipfit',
		},
		hide: {
			duration: 200,
		},
		show: {
			duration: 200,
		},
	} );
};

jQuery( document ).ready( function( $ ) {
	edd_attach_tooltips( $( '.edd-help-tip' ) );
} );

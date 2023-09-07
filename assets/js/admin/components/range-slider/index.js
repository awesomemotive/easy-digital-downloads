/**
 * Range Slider Init
 *
 * @param {string} selector
 */
export const edd_init_range_slider = function( selector ) {

	const min = selector.data( 'min' ) || 0;
	const max = selector.data( 'max' ) || 100;
	const value = selector.data( 'value' ) || 0;

	const updateSliderInputValue = ( e, ui ) => {
		selector.siblings( '.edd-range__input' ).val( ui.value );
	};

	selector.slider({
		min,
		max,
		value,
		range: 'min',
		animate: true,
		slide: updateSliderInputValue,
		change: updateSliderInputValue,
		create: () => {
			selector.siblings( '.edd-range__input' ).on( 'input change', function() {
				selector.slider( 'value', $( this ).val() );
			});
		}
	});
};

jQuery( document ).ready( function( $ ) {
	$( '.edd-range__slider' ).each( function() {
		edd_init_range_slider( $( this ) );
	});
} );

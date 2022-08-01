/**
 * Date picker
 *
 * This juggles a few CSS classes to avoid styling collisions with other
 * third-party plugins.
 */
jQuery( document ).ready( function( $ ) {
	const edd_datepicker = $( 'input.edd_datepicker' );

	if ( edd_datepicker.length > 0 ) {
		edd_datepicker

		// Disable autocomplete to avoid it covering the calendar
			.attr( 'autocomplete', 'off' )

		// Invoke the datepickers
			.datepicker( {
				dateFormat: edd_vars.date_picker_format,
				beforeShow: function() {
					$( '#ui-datepicker-div' )
						.removeClass( 'ui-datepicker' )
						.addClass( 'edd-datepicker' );
				},
			} );
	}
} );

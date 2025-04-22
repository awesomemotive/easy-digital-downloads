; ( function ( document, $ ) {
	'use strict';
	// Initialize phone input when page loads.
	const input = document.querySelector( '.edd-input__phone' );
	if ( input ) {
		initIntlTelInput( input );
	}

	// Initialize phone input when gateway loads.
	$( document.body ).on( 'edd_gateway_loaded', function () {
		const input = document.querySelector( '.edd-input__phone' );
		if ( input ) {
			initIntlTelInput( input );
		}
	} );

	function initIntlTelInput ( input ) {
		var data = {
			formatOnDisplay: true,
			utilsScript: EDDIntlTelInput.utils,
			nationalMode: true,
		}
		if ( input.dataset.country ) {
			data.initialCountry = input.dataset.country;
		}

		// Initialize the phone input.
		var iti = window.intlTelInput( input, data );

		// If there's an existing value, force format it after utils are loaded
		if ( input.value ) {
			// Check if utils script is loaded and format the number
			var handleUtilsLoaded = function () {
				if ( window.intlTelInputUtils ) {
					// Get the formatted value and set it
					var currentNumber = iti.getNumber();
					if ( currentNumber ) {
						iti.setNumber( currentNumber );
					}
				} else {
					// If utils not loaded yet, wait a bit and try again
					setTimeout( handleUtilsLoaded, 100 );
				}
			};

			// Start checking for utils loaded
			handleUtilsLoaded();
		}

		// Set up country change listener after initialization
		const countrySelect = document.querySelector( 'select.edd_countries_filter' );
		if ( countrySelect ) {
			countrySelect.addEventListener( 'change', function () {
				if ( iti ) {
					iti.setCountry( this.value );
				}
			} );
		}

		return iti;
	}
} )( document, jQuery );


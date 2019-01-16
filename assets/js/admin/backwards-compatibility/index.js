/* global edd_backcompat_vars */

/**
 * Developer Notice: The contents of this JavaScript file are not to be relied on in any future versions of EDD
 * These exist as a backwards compatibility measure for https://github.com/easydigitaldownloads/easy-digital-downloads/issues/2704
 */
jQuery( document ).ready( function( $ ) {
	// Adjust location of setting labels for settings in the new containers created below (back compat)
	$( document.body ).find( '.edd-custom-price-option-sections .edd-legacy-setting-label' ).each( function() {
		$( this ).prependTo( $( this ).nextAll( 'span:not(:has(>.edd-legacy-setting-label))' ).first() );
	} );

	// Build HTML containers for existing price option settings (back compat)
	$( document.body ).find( '.edd-custom-price-option-sections' ).each( function() {
		$( this ).find( '[class*="purchase_limit"]' ).wrapAll( '<div class="edd-purchase-limit-price-option-settings-legacy edd-custom-price-option-section"></div>' );
		$( this ).find( '[class*="shipping"]' ).wrapAll( '<div class="edd-simple-shipping-price-option-settings-legacy edd-custom-price-option-section" style="display: none;"></div>' );
		$( this ).find( '[class*="sl-"]' ).wrapAll( '<div class="edd-sl-price-option-settings-legacy edd-custom-price-option-section"></div>' );
		$( this ).find( '[class*="edd-recurring-"]' ).wrapAll( '<div class="edd-recurring-price-option-settings-legacy edd-custom-price-option-section"></div>' );
	} );

	// only display Simple Shipping/Software Licensing sections if enabled (back compat)
	$( document.body ).find( '#edd_enable_shipping', '#edd_license_enabled' ).each( function() {
		const variable_pricing = $( '#edd_variable_pricing' ).is( ':checked' );
		const ss_checked = $( '#edd_enable_shipping' ).is( ':checked' );
		const ss_section = $( '.edd-simple-shipping-price-option-settings-legacy' );
		const sl_checked = $( '#edd_license_enabled' ).is( ':checked' );
		const sl_section = $( '.edd-sl-price-option-settings-legacy' );
		if ( variable_pricing ) {
			if ( ss_checked ) {
				ss_section.show();
			} else {
				ss_section.hide();
			}
			if ( sl_checked ) {
				sl_section.show();
			} else {
				sl_section.hide();
			}
		}
	} );
	$( '#edd_enable_shipping' ).on( 'change', function() {
		const enabled = $( this ).is( ':checked' );
		const section = $( '.edd-simple-shipping-price-option-settings-legacy' );
		if ( enabled ) {
			section.show();
		} else {
			section.hide();
		}
	} );
	$( '#edd_license_enabled' ).on( 'change', function() {
		const enabled = $( this ).is( ':checked' );
		const section = $( '.edd-sl-price-option-settings-legacy' );
		if ( enabled ) {
			section.show();
		} else {
			section.hide();
		}
	} );

	// Create section titles for newly created HTML containers (back compat)
	$( document.body ).find( '.edd-purchase-limit-price-option-settings-legacy' ).each( function() {
		$( this ).prepend( '<span class="edd-custom-price-option-section-title">' + edd_backcompat_vars.purchase_limit_settings + '</span>' );
	} );
	$( document.body ).find( '.edd-simple-shipping-price-option-settings-legacy' ).each( function() {
		$( this ).prepend( '<span class="edd-custom-price-option-section-title">' + edd_backcompat_vars.simple_shipping_settings + '</span>' );
	} );
	$( document.body ).find( '.edd-sl-price-option-settings-legacy' ).each( function() {
		$( this ).prepend( '<span class="edd-custom-price-option-section-title">' + edd_backcompat_vars.software_licensing_settings + '</span>' );
	} );
	$( document.body ).find( '.edd-recurring-price-option-settings-legacy' ).each( function() {
		$( this ).prepend( '<span class="edd-custom-price-option-section-title">' + edd_backcompat_vars.recurring_payments_settings + '</span>' );
	} );
} );

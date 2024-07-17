import { getChosenVars } from 'utils/chosen.js';

jQuery( document ).ready( function ( $ ) {
	$( '.edd_countries_filter' ).on( 'change', function () {
		const select = $( this ),
			state_field = $( '.edd_regions_filter' ),
			data = {
				action: 'edd_get_shop_states',
				country: select.val(),
				nonce: select.data( 'nonce' ),
				field_name: state_field.attr( 'name' ),
				field_id: state_field.attr( 'id' ),
				field_classes: 'edd_regions_filter',
			};

		$.post( ajaxurl, data, function ( response ) {

			// hot fix for settings page
			if ( $( 'body' ).hasClass( 'download_page_edd-settings' ) ) {
				// only on these 2 scenarios we have to setup the field
				if ( ( 'nostates' === response && state_field.is( 'select' ) ) || ( 'nostates' !== response && state_field.is( 'input' ) ) ) {
					let attributes = {};
					$.each(
						state_field.get(0)?.attributes || [],
						( i, attr ) => {
							if ( ! [ 'style', 'type'].includes( attr.name ) ) {
								attributes[ attr.name ] = attr.value;
							}
						}
					)

					const parent = state_field.parent();
					let newStateField = '';

					if ( state_field.is( 'select' ) ) {
						state_field.chosen( 'destroy' );
						newStateField = $( '<input />' ).attr( { ...attributes, ...{ type: 'text', placeholder: edd_vars.enter_region } } );
					} else {
						newStateField = $( response ).attr( { ...attributes, ...{ 'data-placeholder': edd_vars.select_region } } ).addClass( 'edd-select-chosen' );
					}

					state_field.remove();
					parent.prepend( newStateField );
					$( 'select.edd_regions_filter' ).chosen( { ...getChosenVars( newStateField ) } );
					return;
				}
			}

			$( 'select.edd_regions_filter' ).find( 'option:gt(0)' ).remove();

			if ( 'nostates' !== response ) {
				$( response ).find( 'option:gt(0)' ).appendTo( 'select.edd_regions_filter' );
			}

			$( 'select.edd_regions_filter' ).trigger( 'chosen:updated' );
		} );

		return false;
	} );
} );

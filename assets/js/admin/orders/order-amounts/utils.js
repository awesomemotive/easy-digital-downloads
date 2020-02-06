/* global $ */

/**
 * Updates the values in the "Order Amounts" metabox.
 *
 * @note This only updates the UI and does not affect server-side processing.
 *
 * @since 3.0.0
 */
export function updateAmounts() {
	let subtotal = 0,
		discounts = 0,
		adjustments = 0,
		tax = 0,
		total = 0;

	$( '.orderitems tbody tr:not(.no-items)' ).each( function() {
		let row = $( this ),
			item_amount,
			item_quantity = 1,
			item_tax = 0,
			item_total;

		item_amount = parseFloat( row.find( '.amount input' ).val() );

		if ( row.find( '.quantity' ).length ) {
			item_quantity = parseFloat( row.find( '.quantity input' ).val() );
		}

		subtotal += item_amount * item_quantity;

		if ( row.find( '.tax' ).length ) {
			item_tax = parseFloat( row.find( '.tax input' ).val() );

			if ( ! isNaN( item_tax ) && ! edd_vars.taxes_included ) {
				item_amount += item_tax;
				tax += item_tax;
			}
		}

		item_total = item_amount * item_quantity;

		total += item_total;
	} );

	$( '.orderadjustments tbody tr:not(.no-items)' ).each( function() {
		let row = $( this ),
			type,
			amount = 0;

		type = row.data( 'adjustment' );

		switch ( type ) {
			case 'credit':
				amount = parseFloat( row.find( 'input.credit-amount', row ).val() );
				adjustments += amount;
				total -= amount;
				break;
			case 'discount':
				amount = parseFloat( row.find( 'input.discount-amount', row ).val() );

				if ( 'percent' === row.find( 'input.discount-type' ).val() ) {
					$( '.orderitems tbody tr:not(.no-items)' ).each( function() {
						let item_amount = $( this ).find( '.amount .value' ).text(),
							quantity = 1;

						if ( $( this ).find( '.quantity' ).length ) {
							quantity = parseFloat( $( this ).find( '.quantity' ).text() );
						}

						item_amount *= quantity;

						const reduction = parseFloat( ( item_amount / 100 ) * amount );

						if ( $( this ).find( '.tax' ).length ) {
							const item_tax = parseFloat( $( this ).find( '.tax .value' ).text() ),
								item_tax_reduction = parseFloat( item_tax / 100 * amount );

							tax -= item_tax_reduction;
							total -= item_tax_reduction;
						}

						discounts += reduction;
						total -= reduction;
					} );
				} else {
					adjustments += amount;
					total -= amount;
				}

				break;
		}
	} );

	if ( isNaN( total ) ) {
		total = 0;
	}

	if ( isNaN( subtotal ) ) {
		subtotal = 0;
	}

	if ( isNaN( tax ) ) {
		tax = 0;
	}

	if ( isNaN( discounts ) ) {
		discounts = 0;
	}

	if ( isNaN( adjustments ) ) {
		adjustments = 0;
	}

	$( '.edd-order-subtotal .value' ).html( subtotal.toFixed( edd_vars.currency_decimals ) );
	$( '.edd-order-discounts .value' ).html( discounts.toFixed( edd_vars.currency_decimals ) );
	$( '.edd-order-adjustments .value' ).html( adjustments.toFixed( edd_vars.currency_decimals ) );
	$( '.edd-order-taxes .value' ).html( tax.toFixed( edd_vars.currency_decimals ) );
	$( '.edd-order-total .value' ).html( total.toFixed( edd_vars.currency_decimals ) );
}

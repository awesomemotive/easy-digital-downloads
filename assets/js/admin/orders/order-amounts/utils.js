/* global $ */

/**
 * Internal dependencies
 */
import { Currency } from 'utils';

/**
 * Updates the values in the "Order Amounts" metabox.
 *
 * @note This only updates the UI and does not affect server-side processing.
 *
 * @since 3.0.0
 */
export function updateAmounts() {
	const currency = new Currency;

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

		item_amount = currency.unformat( row.find( '.amount input' ).val() );

		if ( row.find( '.quantity' ).length ) {
			item_quantity = parseFloat( row.find( '.quantity input' ).val() );
		}

		subtotal += item_amount * item_quantity;

		if ( row.find( '.tax' ).length ) {
			item_tax = currency.unformat( row.find( '.tax input' ).val() );

			if ( ! isNaN( item_tax ) && ! edd_vars.taxes_included ) {
				item_amount += item_tax;
				tax += item_tax;
			}
		}

		item_total = subtotal + tax;

		total += item_total;
	} );

	$( '.orderadjustments tbody tr:not(.no-items)' ).each( function() {
		let row = $( this ),
			type,
			amount = 0;

		type = row.data( 'adjustment' );
		amount = currency.unformat( row.find( '.column-amount .value', row ).text() );

		switch ( type ) {
			case 'credit':
				adjustments += amount;
				total -= amount;
				break;
			case 'discount':
				if ( 'percent' === row.find( 'input.discount-type' ).val() ) {
					$( '.orderitems tbody tr:not(.no-items)' ).each( function() {
						const reduction = parseFloat( ( amount / 100 ) * subtotal );

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

	$( '.edd-order-subtotal .value' ).html( currency.formatCurrency( subtotal ) );
	$( '.edd-order-discounts .value' ).html( currency.formatCurrency( discounts ) );
	$( '.edd-order-adjustments .value' ).html( currency.formatCurrency( adjustments ) );
	$( '.edd-order-taxes .value' ).html( currency.formatCurrency( tax ) );
	$( '.edd-order-total .value' ).html( currency.formatCurrency( total ) );
}

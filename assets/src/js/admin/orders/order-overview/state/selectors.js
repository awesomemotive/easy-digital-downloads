/**
 * Pure selector functions that derive order overview values from state.
 * All functions accept the full state object and return a derived value.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

/**
 * Gets the order's tax location and rate.
 *
 * @param {Object} state Order overview state.
 * @return {Object|null} Country, region, rate and inclusive, or null when the order is not taxed.
 */
export function getTaxConfig( state ) {
	return state.hasTax && 'object' === typeof state.hasTax ? state.hasTax : null;
}

/**
 * Formats a tax location and rate for display, e.g. "US: CA – 7.25%".
 *
 * @param {Object} taxConfig Tax config from getTaxConfig().
 * @return {string} The label, or just the rate when no country is set.
 */
export function formatTaxLocation( taxConfig ) {
	if ( ! taxConfig.country ) {
		return `${ taxConfig.rate }%`;
	}
	const region = taxConfig.region ? `: ${ taxConfig.region }` : '';
	return `${ taxConfig.country }${ region } – ${ taxConfig.rate }%`;
}

export function getTaxCountry( state ) {
	return getTaxConfig( state )?.country ?? '';
}

export function getTaxRegion( state ) {
	return getTaxConfig( state )?.region ?? '';
}

export function hasInclusiveTax( state ) {
	return !! getTaxConfig( state )?.inclusive;
}

export function getItemTax( item, state ) {
	if ( false === state.isAdding ) {
		return item.tax;
	}
	const { number } = state.formatters;
	return number.unformat( number.format( item.tax ) );
}

export function getItemDiscountAmount( item ) {
	const discounts = ( item.adjustments ?? [] ).filter( ( a ) => 'discount' === a.type );
	if ( 0 === discounts.length ) {
		return item.discount;
	}
	return discounts.reduce( ( sum, d ) => sum + ( +d.subtotal ), 0 );
}

export function getItemSubtotal( item, state, includeTax = false ) {
	if ( false === state.isAdding ) {
		return item.subtotal;
	}
	if ( hasInclusiveTax( state ) && false === includeTax ) {
		return item.subtotal - getItemTax( item, state );
	}
	return item.subtotal;
}

export function getItemTotal( item, state ) {
	if ( false === state.isAdding ) {
		return item.total;
	}
	const { number } = state.formatters;
	const total = hasInclusiveTax( state )
		? item.subtotal - getItemDiscountAmount( item )
		: ( item.subtotal - getItemDiscountAmount( item ) ) + getItemTax( item, state );
	return number.unformat( number.format( total ) );
}

export function getSubtotal( state, includeTax = false ) {
	if ( false === state.isAdding ) {
		return state.order.subtotal;
	}
	return state.items.reduce( ( sum, item ) => sum + ( +getItemSubtotal( item, state, includeTax ) ), 0 );
}

export function getDiscount( state ) {
	if ( false === state.isAdding ) {
		return state.order.discount;
	}
	const discounts = state.adjustments.filter( ( a ) => 'discount' === a.type );
	return discounts.reduce( ( sum, adj ) => sum + ( +getAdjustmentAmount( adj, state ) ), 0 );
}

export function getFeesTax( state ) {
	if ( false === state.isAdding ) {
		return state.order.tax;
	}
	const fees = state.adjustments.filter( ( a ) => 'fee' === a.type );
	return fees.reduce( ( sum, adj ) => sum + ( +adj.tax ), 0 );
}

export function getTax( state ) {
	if ( false === state.isAdding ) {
		return state.order.tax;
	}
	const itemsTax = state.items.reduce( ( sum, item ) => sum + ( +getItemTax( item, state ) ), 0 );
	return itemsTax + getFeesTax( state );
}

export function getTotal( state ) {
	if ( false === state.isAdding ) {
		return state.order.total;
	}
	const includeTaxInSubtotal = true;
	const adjustedSubtotal = state.adjustments.reduce( ( sum, adj ) => {
		if ( [ 'discount', 'credit' ].includes( adj.type ) ) {
			return sum - ( +getAdjustmentAmount( adj, state ) );
		}
		return sum + ( +adj.subtotal );
	}, getSubtotal( state, includeTaxInSubtotal ) );

	const { number } = state.formatters;
	const total = hasInclusiveTax( state )
		? adjustedSubtotal + getFeesTax( state )
		: adjustedSubtotal + getTax( state );
	return number.unformat( number.format( total ) );
}

export function getAdjustmentAmount( adj, state ) {
	if ( 'discount' !== adj.type ) {
		return adj.subtotal;
	}
	// For discount type: sum item-level discount adjustments matching this typeId.
	if ( false === state.isAdding ) {
		return adj.subtotal;
	}
	const { number } = state.formatters;
	let amount = 0;
	state.items.forEach( ( item ) => {
		const match = ( item.adjustments ?? [] ).find( ( a ) => a.typeId === adj.typeId );
		if ( match ) {
			amount += number.unformat( number.format( match.subtotal ) );
		}
	} );
	return amount;
}

export function getAdjustmentTotal( adj ) {
	return ( +adj.subtotal ) + ( +adj.tax );
}

export function hasTaxableItems( state ) {
	const items = state.items;
	const fees = state.adjustments.filter( ( a ) => 'fee' === a.type );
	return items.length > 0 || fees.length > 0;
}

export function hasManualAdjustment( state ) {
	return state.items.some( ( item ) => item._isAdjustingManually );
}

export function getOrderItemForAdjustment( adj, state ) {
	if ( 'order_item' !== adj.objectType ) {
		return null;
	}
	return state.items.find( ( item ) => {
		return ( item.adjustments ?? [] ).some( ( a ) => a.objectId === adj.objectId );
	} ) ?? null;
}

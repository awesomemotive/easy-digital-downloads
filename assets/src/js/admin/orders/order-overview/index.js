/* global eddAdminOrderOverview */
import { createRoot } from '@wordpress/element';
import { Currency, NumberFormat } from '@easy-digital-downloads/currency';
import OrderOverviewApp from './OrderOverviewApp.js';

import './_refund.js';

( () => {
	const root = document.getElementById( 'edd-order-overview' );

	if ( ! root || ! globalThis.eddAdminOrderOverview ) {
		return;
	}

	const {
		isAdding,
		isRefund,
		hasTax,
		hasQuantity,
		hasDiscounts,
		order,
		items       = [],
		adjustments = [],
		refunds     = [],
	} = eddAdminOrderOverview;

	const normalizeItems = ( rawItems ) =>
		rawItems.map( ( item ) => ( {
			...item,
			adjustments: item.adjustments ?? [],
		} ) );

	const config = {
		isAdding:     '1' === String( isAdding ),
		isRefund:     '1' === String( isRefund ),
		hasTax:       hasTax ?? 'none',
		hasQuantity:  '1' === String( hasQuantity ),
		hasDiscounts: '1' === String( hasDiscounts ),
		order:        order ?? {},
		formatters: {
			currency: new Currency( {
				currency:       order?.currency ?? '',
				currencySymbol: order?.currencySymbol ?? '',
			} ),
			number: new NumberFormat(),
		},
		items:       normalizeItems( items ),
		adjustments: adjustments,
		refunds:     refunds,
	};

	createRoot( root ).render( <OrderOverviewApp config={ config } /> );
} )();

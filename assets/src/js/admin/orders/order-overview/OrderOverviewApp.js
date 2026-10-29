/* global eddAdminOrderOverview */

/**
 * Root component for the order overview.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useReducer, useEffect, useRef, useState } from '@wordpress/element';
import { OrderOverviewContext } from './state/context.js';
import { reducer, initialState } from './state/reducer.js';
import OrderSummary from './components/OrderSummary.js';
import OrderActions from './components/OrderActions.js';

/**
 * Root component for the order overview.
 *
 * Exposes a global eddOrderOverview bridge so address.js, which runs
 * outside React, can dispatch SET_TAX.
 *
 * @param {Object} props
 * @param {Object} props.config  Normalized eddAdminOrderOverview data.
 * @return {Element}
 */
const OrderOverviewApp = ( { config } ) => {
	const [ state, dispatch ] = useReducer( reducer, {
		...initialState,
		isAdding:     config.isAdding,
		isRefund:     config.isRefund,
		hasTax:       config.hasTax,
		hasQuantity:  config.hasQuantity,
		hasDiscounts: config.hasDiscounts,
		order:        config.order,
		formatters:   config.formatters,
		items:        config.items,
		adjustments:  config.adjustments,
		refunds:      config.refunds,
	} );

	const [ isSaveDeferred, setSaveDeferred ] = useState( false );
	const amountsErrorRef = useRef( null );

	useEffect( () => {
		globalThis.eddOrderOverview = {
			dispatch,
			getItemCount: () => state.items.length,
			getHasTax:    () => state.hasTax,
			getIsAdding:  () => state.isAdding,
		};
	}, [ state.items.length, state.hasTax, state.isAdding ] ); // eslint-disable-line react-hooks/exhaustive-deps

	// Form validation: check items count on submit.
	useEffect( () => {
		const noItemErrorEl    = document.getElementById( 'edd-add-order-no-items-error' );
		const noCustomerEl     = document.getElementById( 'edd-add-order-customer-error' );
		const assignCustomerEl = document.getElementById( 'customer_id' );
		const newEmailEl       = document.getElementById( 'edd_new_customer_email' );

		if ( ! noItemErrorEl ) {
			return;
		}

		// Clear item error whenever an item is present.
		if ( state.items.length > 0 ) {
			noItemErrorEl.style.display = 'none';
		}

		function handleSubmit( event ) {
			let hasError = false;

			// Posted amounts are only consistent once a recalculation has succeeded.
			if ( state.isFetching ) {
				setSaveDeferred( true );
				hasError = true;
			} else if ( state.hasAmountsError ) {
				amountsErrorRef.current?.focus();
				hasError = true;
			}

			if ( 0 === state.items.length ) {
				noItemErrorEl.style.display = 'block';
				hasError = true;
			} else {
				noItemErrorEl.style.display = 'none';
			}

			if ( noCustomerEl && assignCustomerEl ) {
				const noEmail = newEmailEl ? '' === newEmailEl.value : true;
				if ( '0' === assignCustomerEl.value && noEmail ) {
					noCustomerEl.style.display = 'block';
					hasError = true;
				} else {
					noCustomerEl.style.display = 'none';
				}
			}

			if ( hasError ) {
				event.preventDefault();
			}
		}

		const forms = [ 'edd-add-order-form', 'edd-edit-order-form' ]
			.map( ( id ) => document.getElementById( id ) )
			.filter( Boolean );

		forms.forEach( ( f ) => f.addEventListener( 'submit', handleSubmit ) );
		return () => forms.forEach( ( f ) => f.removeEventListener( 'submit', handleSubmit ) );
	}, [ state.items.length, state.isFetching, state.hasAmountsError ] ); // eslint-disable-line react-hooks/exhaustive-deps

	useEffect( () => {
		if ( ! state.isFetching ) {
			setSaveDeferred( false );
		}
	}, [ state.isFetching ] );

	const { i18n } = eddAdminOrderOverview;

	return (
		<OrderOverviewContext.Provider value={ { state, dispatch } }>
			{ state.hasAmountsError && (
				<div className="notice notice-error inline" role="alert" tabIndex={ -1 } ref={ amountsErrorRef }>
					<p>
						{ i18n.amountsNotRecalculated }
					</p>
				</div>
			) }
			<div role="status">
				{ isSaveDeferred && (
					<div className="notice notice-warning inline">
						<p>
							{ i18n.amountsUpdating }
						</p>
					</div>
				) }
			</div>
			<OrderSummary />
			{ ! state.isRefund && <OrderActions /> }
		</OrderOverviewContext.Provider>
	);
};

export default OrderOverviewApp;

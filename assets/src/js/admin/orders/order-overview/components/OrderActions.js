/* global eddAdminOrderOverview */

/**
 * Order overview actions: add item, discount, or adjustment, and refund.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useState, useEffect } from '@wordpress/element';
import { useOrderOverview } from '../state/context.js';
import { edd_attach_tooltips as setupTooltips } from 'admin/components/tooltips';
import AddOrderItemDialog from './AddOrderItemDialog.js';
import AddOrderDiscountDialog from './AddOrderDiscountDialog.js';
import AddOrderAdjustmentDialog from './AddOrderAdjustmentDialog.js';

function getRefundLockIcon( order, refundable, override, windowPassed, i18n ) {
	if ( 'amazon' === order.gateway ) {
		return (
			<span
				className="dashicons dashicons-lock"
				title={ i18n.amazonLockTip }
			></span>
		);
	}
	if ( refundable && override && windowPassed ) {
		return (
			<span
				className="edd-help-tip dashicons dashicons-unlock"
				title={ i18n.refundWindowOverrideTip }
			></span>
		);
	}
	if ( ! refundable && windowPassed ) {
		return (
			<span
				className="edd-help-tip dashicons dashicons-lock"
				title={ i18n.refundWindowPassedTip }
			></span>
		);
	}
	return null;
}

const OrderActions = () => {
	const { state } = useOrderOverview();
	const { i18n, isRefundable, isRefundOverride, isWindowPassed } = eddAdminOrderOverview;

	const [ addItemOpen, setAddItemOpen ]             = useState( false );
	const [ addDiscountOpen, setAddDiscountOpen ]     = useState( false );
	const [ addAdjustmentOpen, setAddAdjustmentOpen ] = useState( false );

	// Attach WordPress tooltips to any help-tip elements after render.
	useEffect( () => {
		const el = document.getElementById( 'edd-order-overview-actions' );
		if ( el && globalThis.jQuery ) {
			setupTooltips( globalThis.jQuery( el ).find( '.edd-help-tip' ) );
		}
	}, [] );

	const { isAdding, hasDiscounts, order, isFetching } = state;
	const isRefunded   = 'refunded' === order.status;
	const netTotal     = +( order.netTotal ?? 0 );
	const refundable   = '1' === String( isRefundable );
	const override     = '1' === String( isRefundOverride );
	const windowPassed = '1' === String( isWindowPassed );

	let mainContent = null;
	if ( isAdding ) {
		mainContent = (
			<>
				<button
					id="add-adjustment"
					className="button button-secondary"
					disabled={ isFetching }
					onClick={ ( e ) => { e.preventDefault(); setAddAdjustmentOpen( true ); } }
				>
					{ i18n.addAdjustment }
				</button>

				{ hasDiscounts && (
					<button
						id="add-discount"
						className="button button-secondary"
						disabled={ isFetching }
						onClick={ ( e ) => { e.preventDefault(); setAddDiscountOpen( true ); } }
					>
						{ i18n.addDiscount }
					</button>
				) }

				<button
					id="add-item"
					className="button button-secondary"
					disabled={ isFetching }
					autoFocus
					onClick={ ( e ) => { e.preventDefault(); setAddItemOpen( true ); } }
				>
					{ i18n.addDownload }
				</button>
			</>
		);
	} else if ( ! isRefunded && netTotal > 0 ) {
		mainContent = (
			<>
				<div className="edd-order-overview-actions__locked">
					{ i18n.cannotModify }
					<span
						className="edd-help-tip dashicons dashicons-editor-help"
						title={ i18n.issueRefundTip }
					></span>
				</div>

				<div className="edd-order-overview-actions__refund">
					{ getRefundLockIcon( order, refundable, override, windowPassed, i18n ) }

					<button
						id="refund"
						className="button button-secondary edd-refund-order"
						disabled={ ! refundable && ! override }
					>
						{ i18n.initializeRefund }
					</button>
				</div>

				{ 'amazon' === order.gateway && (
					<div className="edd-order-overview-actions__notice notice notice-warning">
						<p>
							{ i18n.amazonRefundNotice }
						</p>
					</div>
				) }
			</>
		);
	}

	return (
		<div id="edd-order-overview-actions" className="edd-order-overview-actions inside">
			{ mainContent }

			{ addItemOpen && (
				<AddOrderItemDialog onClose={ () => setAddItemOpen( false ) } />
			) }
			{ addDiscountOpen && (
				<AddOrderDiscountDialog onClose={ () => setAddDiscountOpen( false ) } />
			) }
			{ addAdjustmentOpen && (
				<AddOrderAdjustmentDialog onClose={ () => setAddAdjustmentOpen( false ) } />
			) }
		</div>
	);
};

export default OrderActions;

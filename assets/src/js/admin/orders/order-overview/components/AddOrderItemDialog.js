/* global ajaxurl eddAdminOrderOverview */

/**
 * Dialog for adding a download to an order.
 *
 * @package     EDD\Admin\Orders
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useState, useRef, useEffect } from '@wordpress/element';
import Dialog from '../../../components/dialog/Dialog.js';
import TomSelectField from '../../../components/tom-select/index.js';
import { useOrderOverview } from '../state/context.js';
import { updateAmounts, getItemAmountsBody } from '../utils/update-amounts.js';
import { getTaxConfig, formatTaxLocation } from '../state/selectors.js';

const TITLE_ID = 'edd-add-order-item-title';

const AddOrderItemDialog = ( { onClose } ) => {
	const { state, dispatch } = useOrderOverview();
	const { i18n, initialDownloads = [], currencyPosition } = eddAdminOrderOverview;

	const [ productId, setProductId ] = useState( 0 );
	const [ priceId, setPriceId ] = useState( null );
	const [ productName, setProductName ] = useState( '' );
	const [ quantity, setQuantity ] = useState( 1 );
	const [ isManual, setIsManual ] = useState( false );
	const [ amountManual, setAmountManual ] = useState( '' );
	const [ taxManual, setTaxManual ] = useState( '' );
	const [ subtotalManual, setSubtotalManual ] = useState( '' );
	const [ amounts, setAmounts ] = useState( { amount: 0, tax: 0, subtotal: 0, total: 0 } );
	const [ error, setError ] = useState( null );
	const [ isDuplicate, setIsDuplicate ] = useState( false );
	const [ isPreviewing, setIsPreviewing ] = useState( false );
	const [ isSubmitting, setIsSubmitting ] = useState( false );
	const previewRequestRef = useRef( null );

	const { number } = state.formatters;
	const taxConfig = getTaxConfig( state );
	const currencySymbol = state.order.currencySymbol ?? '';
	const symbolBefore = 'before' === ( currencyPosition ?? 'before' );

	useEffect( () => () => previewRequestRef.current?.abort(), [] );

	const isInOrder = ( pid, prid ) => state.items.some(
		( item ) => item.productId === pid && String( item.priceId ) === String( prid )
	);

	const fetchAmounts = async ( pid, prid ) => {
		previewRequestRef.current?.abort();
		const request = new AbortController();
		previewRequestRef.current = request;

		setIsPreviewing( true );
		setError( null );

		// The preview prices one unit: handleSubmit applies the quantity, and the handler multiplies by any quantity sent.
		const body = getItemAmountsBody( state, {
			productId: pid,
			priceId: prid,
			quantity: 1,
			amount: 0,
			tax: 0,
			subtotal: 0,
		} );

		try {
			const resp = await fetch( ajaxurl, { method: 'POST', body, signal: request.signal } );
			const json = await resp.json();

			if ( !json.success ) {
				throw new Error( json.data?.message ?? 'Unknown error' );
			}

			const { amount, tax, subtotal, total } = json.data;
			setAmounts( { amount, tax, subtotal, total } );
			setAmountManual( number.format( amount ) );
			setTaxManual( number.format( tax ) );
			setSubtotalManual( number.format( subtotal ) );
			setIsDuplicate( isInOrder( pid, prid ) );
		} catch ( err ) {
			if ( request.signal.aborted ) {
				return;
			}
			setError( err.message );
			setProductId( 0 );
			setPriceId( null );
			setProductName( '' );
		} finally {
			if ( previewRequestRef.current === request ) {
				setIsPreviewing( false );
			}
		}
	};

	const handleDownloadChange = ( value ) => {
		if ( !value ) {
			previewRequestRef.current?.abort();
			setProductId( 0 );
			setPriceId( null );
			setProductName( '' );
			return;
		}

		const parts = value.split( '_' );
		const pid = Number.parseInt( parts[ 0 ], 10 );
		const prid = parts[ 1 ] ? Number.parseInt( parts[ 1 ], 10 ) : null;
		const selectEl = document.getElementById( 'edd-add-item-download' );
		const optionEl = selectEl?.options[ selectEl.selectedIndex ];
		const pname = optionEl?.text ?? '';

		setProductId( pid );
		setPriceId( prid );
		setProductName( pname );

		void fetchAmounts( pid, prid );
	};

	const handleSubmit = async ( e ) => {
		e.preventDefault();

		if ( 0 === productId || isDuplicate ) {
			return;
		}

		// A failed removal can restore this download while the dialog is open.
		if ( isInOrder( productId, priceId ) ) {
			setIsDuplicate( true );
			return;
		}

		setError( null );

		let finalAmount, finalTax, finalSubtotal;

		if ( isManual ) {
			finalAmount = number.unformat( amountManual );
			finalTax = number.unformat( taxManual );
			finalSubtotal = number.unformat( subtotalManual );
		} else {
			finalAmount = amounts.amount;
			finalTax = amounts.tax * quantity;
			finalSubtotal = amounts.subtotal * quantity;
		}

		const newItem = {
			id: `new-${ Date.now() }`,
			orderId: `new-${ Date.now() }`,
			productId,
			priceId,
			productName,
			quantity,
			amount: finalAmount,
			tax: finalTax,
			subtotal: finalSubtotal,
			total: finalSubtotal - 0 + finalTax,
			discount: 0,
			adjustments: [],
			status: '',
			statusLabel: '',
			_isAdjustingManually: isManual,
			amountManual,
			taxManual,
			subtotalManual,
		};

		dispatch( { type: 'ADD_ITEM', payload: newItem } );

		const nextState = { ...state, items: [ ...state.items, newItem ] };
		setIsSubmitting( true );
		try {
			await updateAmounts( nextState, dispatch );
			onClose();
		} catch ( err ) {
			dispatch( { type: 'REMOVE_ITEM', payload: newItem.id } );
			setError( err.message );
			setIsSubmitting( false );
		}
	};

	const isBusy = isPreviewing || isSubmitting;
	const disabled = 0 === productId || isDuplicate || isBusy || state.isFetching;

	// A failed add reports its error in this dialog, so it must still be open when the request settles.
	const handleClose = () => {
		if ( ! isSubmitting ) {
			onClose();
		}
	};

	return (
		<Dialog
			open={true}
			onClose={handleClose}
			title={i18n.addDownloadTitle}
			titleId={TITLE_ID}
			className="edd-modal--overflow-visible"
		>
			<div className="edd-order-overview-modal">
				<form className="edd-order-overview-add-item" onSubmit={handleSubmit}>
					{error && (
						<div className="notice notice-error">
							<p>{error}</p>
						</div>
					)}

					<div className="edd-form-group">
						<label htmlFor="edd-add-item-download">
							{i18n.downloadSingular}
						</label>
						<div className="edd-form-group__control">
							<TomSelectField
								id="edd-add-item-download"
								data-placeholder={i18n.searchDownload}
								data-search-type="download"
								data-search-variations="1"
								data-search-variations-only="1"
								onChange={handleDownloadChange}
							>
								<option value=""></option>
								{productId === 0 ? null : (
									<option value={priceId !== null ? `${ productId }_${ priceId }` : String( productId )}>
										{productName}
									</option>
								)}
								{initialDownloads.map( ( dl ) => (
									<option key={dl.id} value={dl.id}>{dl.name}</option>
								) )}
							</TomSelectField>

							{isDuplicate && (
								<span className="edd-order-overview-error">
									{i18n.duplicateDownload}
								</span>
							)}
						</div>
					</div>

					{state.hasQuantity && (
						<div className="edd-form-group">
							<label htmlFor="edd-add-item-quantity">
								{i18n.quantity}
							</label>
							<div className="edd-form-group__control">
								<input
									type="number"
									id="edd-add-item-quantity"
									className="edd-add-order-quantity"
									value={quantity}
									step="1"
									min="1"
									disabled={disabled}
									onChange={( e ) => setQuantity( Number.parseInt( e.target.value, 10 ) )}
								/>
							</div>
						</div>
					)}

					<div className="edd-form-group">
						<div className="edd-form-group__control edd-toggle">
							<input
								type="checkbox"
								id="edd-add-item-auto-calculate"
								checked={!isManual}
								disabled={disabled}
								onChange={( e ) => setIsManual( !e.target.checked )}
							/>
							<label htmlFor="edd-add-item-auto-calculate">
								{i18n.autoCalculate}
								{taxConfig && '' !== taxConfig.country && (
									<>
										<br />
										<small>
											{i18n.taxRateLabel.replace( '%s', formatTaxLocation( taxConfig ) )}
										</small>
									</>
								)}
							</label>
						</div>
					</div>

					{'' === taxConfig?.country && !isManual && (
						<div className="notice notice-warning">
							<p>
								<strong>{i18n.noTaxRate}</strong>
								<br />
								{i18n.taxFromAddress}
							</p>
							<p>
								<button
									className="button button-secondary"
									type="button"
									disabled={isSubmitting}
									onClick={() => {
										handleClose();
										document.querySelector( '[href="#edd_general_address"]' )?.click();
										document.getElementById( 'edd_order_address_country' )?.focus();
									}}
								>
									{i18n.setAddress}
								</button>
							</p>
						</div>
					)}

					{isManual && (
						<>
							<div className="edd-form-group">
								<label htmlFor="edd-add-item-amount">{i18n.unitPrice}</label>
								<div className="edd-form-group__control edd-amount-type-wrapper">
									{symbolBefore && <span className="edd-input__symbol edd-input__symbol--prefix">{currencySymbol}</span>}
									<input type="text" id="edd-add-item-amount" value={amountManual} onChange={( e ) => setAmountManual( e.target.value )} />
									{!symbolBefore && <span className="edd-input__symbol edd-input__symbol--suffix">{currencySymbol}</span>}
								</div>
							</div>

							{taxConfig && (
								<div className="edd-form-group">
									<label htmlFor="edd-add-item-tax">
										{i18n.tax}
										{taxConfig.country && ` (${ formatTaxLocation( taxConfig ) })`}
									</label>
									<div className="edd-form-group__control edd-amount-type-wrapper">
										{symbolBefore && <span className="edd-input__symbol edd-input__symbol--prefix">{currencySymbol}</span>}
										<input type="text" id="edd-add-item-tax" value={taxManual} onChange={( e ) => setTaxManual( e.target.value )} />
										{!symbolBefore && <span className="edd-input__symbol edd-input__symbol--suffix">{currencySymbol}</span>}
									</div>
								</div>
							)}

							<div className="edd-form-group">
								<label htmlFor="edd-add-item-subtotal">{i18n.amount}</label>
								<div className="edd-form-group__control edd-amount-type-wrapper">
									{symbolBefore && <span className="edd-input__symbol edd-input__symbol--prefix">{currencySymbol}</span>}
									<input type="text" id="edd-add-item-subtotal" value={subtotalManual} onChange={( e ) => setSubtotalManual( e.target.value )} />
									{!symbolBefore && <span className="edd-input__symbol edd-input__symbol--suffix">{currencySymbol}</span>}
								</div>
							</div>
						</>
					)}

					<p className="submit">
						<button
							type="submit"
							className={`button button-primary${ isBusy ? ' updating-message' : '' }`}
							disabled={disabled}
						>
							{i18n.addDownload}
						</button>
					</p>
				</form>
			</div>
		</Dialog>
	);
};

export default AddOrderItemDialog;

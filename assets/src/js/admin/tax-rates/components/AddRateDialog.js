/* global eddTaxRates */

/**
 * Dialog for adding a tax rate.
 *
 * @package     EDD\Admin\TaxRates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useState } from '@wordpress/element';
import Dialog from '../../components/dialog/Dialog.js';
import TomSelectField from '../../components/tom-select/index.js';
import RegionField from './RegionField.js';
import { useTaxRates } from '../state/context.js';

const DIALOG_TITLE_ID = 'edd-add-tax-rate-title';

/**
 * Dialog for adding a new tax rate.
 *
 * A '*' country is stored as-is in state but normalized to '' when checking
 * for duplicates, matching how the database stores all-countries rates.
 * A zero rate asks for confirmation but is added whatever the answer.
 *
 * @param {Object}   props
 * @param {Function} props.onClose  Called when the dialog should close.
 * @return {Element}
 */
const AddRateDialog = ( { onClose } ) => {
	const { i18n, countries } = eddTaxRates;
	const { state, dispatch } = useTaxRates();

	const [ country, setCountry ] = useState( '' );
	const [ isGlobal, setIsGlobal ] = useState( true );
	const [ region, setRegion ] = useState( '' );
	const [ amount, setAmount ] = useState( '' );

	const handleCountryChange = ( value ) => {
		setCountry( value );
		setRegion( '' );
		setIsGlobal( true );
	};

	const handleGlobalChange = ( e ) => {
		setIsGlobal( e.target.checked );
		if ( e.target.checked ) {
			setRegion( '' );
		}
	};

	// '*' forces the "Apply to whole country" row hidden (it's implied).
	const showRegionGlobal = country !== '' && country !== '*';
	const showRegionWrapper = showRegionGlobal && ! isGlobal;

	const handleSubmit = ( e ) => {
		e.preventDefault();

		if ( ! country ) {
			// eslint-disable-next-line no-alert
			alert( i18n.emptyCountry );
			return;
		}

		const parsedAmount = Number.parseFloat( amount ) || 0;

		if ( parsedAmount < 0 ) {
			// eslint-disable-next-line no-alert
			alert( i18n.negativeTax );
			return;
		}

		// Normalize only for the duplicate check: the server loads all-countries rates as country '' with global false.
		const checkCountry = country === '*' ? '' : country;
		const checkRegion  = country === '*' ? '' : region;
		// An empty region is a country-wide rate whether or not the box is ticked, as when activating a rate.
		const checkGlobal  = country === '*' ? false : '' === region;

		const hasDuplicate = state.rates.some(
			( r ) =>
				r.status === 'active' &&
				r.country === checkCountry &&
				r.region === checkRegion &&
				r.global === checkGlobal
		);

		if ( hasDuplicate ) {
			const countryStr = '' === checkCountry ? '*' : checkCountry;
			const regionStr  = '' === checkRegion ? '' : `: ${ checkRegion }`;
			// eslint-disable-next-line no-alert
			alert( i18n.duplicateRate.replace( '%s', `"${ countryStr }${ regionStr }"` ) );
			return;
		}

		if ( parsedAmount === 0 ) {
			// eslint-disable-next-line no-alert
			confirm( i18n.emptyTax );
		}

		dispatch( {
			type: 'ADD_RATE',
			payload: {
				country: country,                              // keep '*' as-is
				region:  country === '*' ? '' : region,
				global:  country === '*' ? true : isGlobal,
				amount:  parsedAmount,
				status:  'active',
			},
		} );

		onClose();
	};

	return (
		<Dialog
			open={ true }
			onClose={ onClose }
			title={ i18n.addNewRate }
			titleId={ DIALOG_TITLE_ID }
			width="350px"
			className="edd-modal--overflow-visible"
		>
			<form className="edd-tax-rates-dialog" onSubmit={ handleSubmit }>
				<div className="edd-tax-rates-dialog__content">
					<div className="edd-form-group">
						<label htmlFor="tax_rate_country">{ i18n.country }</label>
						<div className="edd-form-group__control">
							<TomSelectField
								id="tax_rate_country"
								data-placeholder={ i18n.selectCountry }
								onChange={ handleCountryChange }
							>
								{ countries.map( ( { value, text } ) => (
									<option key={ value } value={ value }>{ text }</option>
								) ) }
							</TomSelectField>
						</div>
					</div>

					{ showRegionGlobal && (
						<div id="tax_rate_region_global" className="edd-form-group">
							<div className="edd-form-group__control edd-toggle">
								<input
									type="checkbox"
									checked={isGlobal}
									onChange={handleGlobalChange}
									id="tax_rate_global"
								/>
								<label
									htmlFor="tax_rate_global"
								>
									{ i18n.applyToCountry }
								</label>
							</div>
						</div>
					) }

					{ showRegionWrapper && (
						<div id="tax_rate_region_wrapper" className="edd-form-group">
							<label htmlFor="tax_rate_region">{ i18n.region }</label>
							<div className="edd-form-group__control">
								<RegionField
									country={ country }
									global={ isGlobal }
									onChange={ setRegion }
								/>
							</div>
						</div>
					) }

					<div className="edd-form-group">
						<label htmlFor="tax_rate_amount">{ i18n.rate }</label>
						<div className="edd-form-group__control edd-amount-type-wrapper">
							<input
								type="number"
								step="0.0001"
								min="0.0"
								max="99"
								id="tax_rate_amount"
								value={ amount }
								onChange={ ( e ) => setAmount( e.target.value ) }
							/>
							<span className="edd-input__symbol edd-input__symbol--suffix">
								{ i18n.taxRateSymbol }
							</span>
						</div>
					</div>
				</div>

				<div className="edd-tax-rates-dialog__actions">
					<button type="submit" className="button button-primary">
						{ i18n.addRate }
					</button>
					<button type="button" className="button button-secondary" onClick={ onClose }>
						{ i18n.cancel }
					</button>
				</div>
			</form>
		</Dialog>
	);
};

export default AddRateDialog;

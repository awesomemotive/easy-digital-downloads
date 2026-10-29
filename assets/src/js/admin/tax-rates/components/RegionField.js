/* global eddTaxRates, ajaxurl */

/**
 * Region field for the add tax rate dialog.
 *
 * @package     EDD\Admin\TaxRates
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useState, useEffect } from '@wordpress/element';
import TomSelectField from '../../components/tom-select/index.js';

/**
 * Fetches and renders the region input for a given country.
 *
 * Shows a Tom Select dropdown for countries with defined states/provinces,
 * or a plain text input for countries without state data ('nostates').
 * Renders nothing while loading or when no country is selected.
 *
 * @param {Object}   props
 * @param {string}   props.country    ISO country code ('*' = all countries).
 * @param {boolean}  props.global     Whether "Apply to whole country" is checked.
 * @param {Function} props.onChange   Called with the selected/typed region value.
 * @return {Element|null}
 */
const RegionField = ( { country, global: isGlobal, onChange } ) => {
	const [ states, setStates ] = useState( null );
	const [ loading, setLoading ] = useState( false );

	useEffect( () => {
		if ( ! country || country === '*' ) {
			setStates( null );
			return;
		}

		const controller = new AbortController();

		setLoading( true );
		setStates( null );

		const body = new FormData();
		body.append( 'action', 'edd_get_shop_states' );
		body.append( 'country', country );
		body.append( 'nonce', eddTaxRates.nonce );
		body.append( 'field_name', 'tax_rate_region' );
		body.append( 'chosen', 'false' );

		const fetchStates = async () => {
			try {
				const response = await fetch( ajaxurl, { method: 'POST', body, signal: controller.signal } );
				const html     = await response.text();
				setStates( html.trim() );
				setLoading( false );
			} catch ( err ) {
				if ( err.name !== 'AbortError' ) {
					setLoading( false );
				}
			}
		};

		void fetchStates();

		return () => controller.abort();
	}, [ country ] );

	if ( ! country || country === '*' || loading || states === null || isGlobal ) {
		return null;
	}

	if ( states === 'nostates' ) {
		return (
			<input
				type="text"
				id="tax_rate_region"
				onChange={ ( e ) => onChange( e.target.value ) }
			/>
		);
	}

	const parser = new DOMParser();
	const doc = parser.parseFromString( states, 'text/html' );
	const stateOptions = Array.from( doc.querySelectorAll( 'option' ) ).map( ( opt ) => ( {
		value: opt.value,
		text: opt.textContent.trim(),
	} ) );

	return (
		<TomSelectField
			id="tax_rate_region"
			options={ stateOptions }
			onChange={ onChange }
		/>
	);
};

export default RegionField;

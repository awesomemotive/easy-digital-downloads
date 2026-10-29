/**
 * React wrapper for Tom Select.
 *
 * @package     EDD\Admin\Components
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useRef, useEffect } from '@wordpress/element';
import { initChosen } from 'utils/chosen.js';

/**
 * React wrapper for Tom Select using EDD's standard initChosen() config.
 *
 * Two usage modes:
 * - Static options: pass <option> elements as children (rendered once; Tom Select takes over).
 * - Dynamic options: pass an options array ({ value, text }[]) that syncs via Tom Select's API.
 * - AJAX search: pass data-search-type attribute via props; initChosen() configures load automatically.
 *
 * @param {Object}    props
 * @param {Array}     [props.options]   Dynamic options array { value, text }[]. Synced when changed.
 * @param {string}    [props.value]     Controlled selected value. Synced silently to Tom Select.
 * @param {Function}  [props.onChange]  Called with the new value string when selection changes.
 * @param {string}    [props.className] Additional CSS classes for the select element.
 * @param {*}         [props.children]  Static <option> elements for non-AJAX selects.
 * @return {Element} A <select> element initialized and managed by Tom Select.
 */
const TomSelectField = ( { options, value, onChange, className, children, ...props } ) => {
	const selectRef   = useRef( null );
	const onChangeRef = useRef( onChange );

	// Tom Select is bound once, so route changes through a ref to reach the current handler and its state.
	onChangeRef.current = onChange;

	useEffect( () => {
		const el = selectRef.current;
		if ( ! el ) {
			return;
		}

		initChosen( el );

		const instance = el.tomselect;
		if ( ! instance ) {
			return;
		}

		const handleChange = ( newValue ) => onChangeRef.current?.( newValue );
		instance.on( 'change', handleChange );

		return () => {
			instance.off( 'change', handleChange );
			instance.destroy();
		};
	}, [] );

	useEffect( () => {
		const instance = selectRef.current?.tomselect;
		if ( ! instance || ! options ) {
			return;
		}
		instance.clearOptions();
		instance.addOptions( options );
		instance.sync();
	}, [ options ] );

	useEffect( () => {
		const instance = selectRef.current?.tomselect;
		if ( ! instance || value === undefined ) {
			return;
		}
		instance.setValue( value, true );
	}, [ value ] );

	return (
		<select
			ref={ selectRef }
			className={ [ 'edd-select-chosen', className ].filter( Boolean ).join( ' ' ) }
			{ ...props }
		>
			{ children }
		</select>
	);
};

export default TomSelectField;

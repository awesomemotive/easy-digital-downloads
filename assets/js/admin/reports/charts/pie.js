/* global Chart */

/**
 * Internal dependencies.
 */
import { toolTipBaseConfig, getLabelWithTypeCondition } from './utils';

/**
 * Render a line chart.
 *
 * @param {Object} config Global chart config.
 * @return {Chart}
 */
export const render = ( config ) => {
	const { target } = config;

	// Config tooltips.
	config.options.tooltips = tooltipConfig( config );

	// Render
	return new Chart( document.getElementById( target ), config );
};

/**
 * Get custom tooltip config for pie charts.
 *
 * @param {Object} config Global chart config.
 * @return {Object}
 */
export const tooltipConfig = ( config ) => ( {
	...toolTipBaseConfig,
	enabled: false, // Use custom tooltip for consistent styling

	callbacks: {
		/**
		 * Generate a label.
		 *
		 * @param {Object} t
		 * @param {Object} d
		 */
		label: function( t, d ) {
			const { options: { datasets, otherLabel, otherBreakdown } } = config;
			const dataset = d.datasets[ t.datasetIndex ];
			// as options dataset contains pie chart data we need to find the dataset by label here.
			const datasetConfig = Object.values( datasets ).find( value => value.label && value.label.toLowerCase() === dataset.label.toLowerCase() );

			const total = dataset.data.reduce( function( previousValue, currentValue, currentIndex, array ) {
				return previousValue + currentValue;
			} );

			const currentValue = dataset.data[ t.index ];
			const label = getLabelWithTypeCondition( currentValue, datasetConfig );
			const precentage = Math.floor( ( ( currentValue / total ) * 100 ) + 0.5 );

			// Check if this is the "Other" piece and show breakdown
			const currentLabel = d.labels[ t.index ];
			if ( currentLabel === otherLabel && otherBreakdown && Object.keys( otherBreakdown ).length > 0 ) {
				const breakdownLines = Object.entries( otherBreakdown ).map( ( [ piece, value ] ) => {
					const pieceLabel = getLabelWithTypeCondition( value, datasetConfig );
					const piecePercentage = Math.floor( ( ( value / total ) * 100 ) + 0.5 );
					return `  • ${ piece }: ${ pieceLabel } (${ piecePercentage }%)`;
				} );

				// Return array of lines for proper multi-line display
				return [
					`${ currentLabel }: ${ label } (${ precentage }%)`,
					'',
					'Breakdown:',
					...breakdownLines
				];
			}

			return `${ currentLabel }: ${ label } (${ precentage }%)`;
		},
	},
} );

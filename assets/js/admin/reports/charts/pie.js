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

	callbacks: {
		/**
		 * Generate a label.
		 *
		 * @param {Object} t
		 * @param {Object} d
		 */
		label: function( t, d ) {
			const { options: { datasets } } = config;
			const datasetConfig = datasets[ Object.keys( datasets )[ t.datasetIndex ] ];
			const dataset = d.datasets[ t.datasetIndex ];

			const total = dataset.data.reduce( function( previousValue, currentValue, currentIndex, array ) {
				return previousValue + currentValue;
			} );

			const currentValue = dataset.data[ t.index ];
			const label = getLabelWithTypeCondition( dataset.data[ t.index ], datasetConfig );
			const precentage = Math.floor( ( ( currentValue / total ) * 100 ) + 0.5 );

			return `${ d.labels[ t.index ] }: ${ label } (${ precentage }%)`;
		},
	},
} );

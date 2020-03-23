/* global Chart */

/**
 * Internal dependencies.
 */
import { NumberFormat } from '@easy-digital-downloads/currency';
import { getLabelWithTypeCondition, toolTipBaseConfig } from './utils';

/**
 * Render a line chart.
 *
 * @param {Object} config Global chart config.
 * @return {Chart}
 */
export const render = ( config ) => {
	const { target } = config;
	const number = new NumberFormat();

	const lineConfig = {
		...config,
		options: {
			...config.options,
			tooltips: tooltipConfig( config ),
			scales: {
				...config.options.scales,
				yAxes: [
					{
						ticks: {
							callback: ( value, index, values ) => {
								return number.format( value );
							},
						},
					},
				],
			},
		},
	};

	// Render
	return new Chart( document.getElementById( target ), lineConfig );
};

/**
 * Get custom tooltip config for line charts.
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
			const label = getLabelWithTypeCondition( t.yLabel, datasetConfig );

			return `${ d.datasets[ t.datasetIndex ].label }: ${ label }`;
		},
	},
} );

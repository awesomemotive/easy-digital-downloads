/* global Chart */

/**
 * Internal dependencies.
 */
import { getLabelWithTypeCondition, toolTipBaseConfig, attachAxisTickFormattingCallback } from './utils';

/**
 * Render a bar chart.
 *
 * @param {Object} config Global chart config.
 * @return {Chart}
 */
export const render = ( config ) => {
	const { target } = config;

	// Attach formatting callback to Y axes ticks.
	config.options.scales.yAxes.forEach( axis => {
		if ( axis.ticks.hasOwnProperty( 'formattingType' ) ) {
			axis.ticks = attachAxisTickFormattingCallback( axis.ticks );
		}

		if ( axis.ticks.hasOwnProperty( 'hideNegativeTicks' ) && axis.ticks.hideNegativeTicks ) {
			axis.afterTickToLabelConversion = function(scaleInstance) {
				for (let index = scaleInstance.ticksAsNumbers.length - 1; index >= 0; index--) {
					if (scaleInstance.ticksAsNumbers[index] < 0) {
						scaleInstance.ticksAsNumbers.splice(index, 1);
						scaleInstance.ticks.splice(index, 1);
					}
				}
			}
		}
	});

	// Extract labels from options if they exist (for categorical bar charts)
	const labels = config.options.labels || [];
	const hasLabels = labels.length > 0;

	const barConfig = {
		...config,
		data: {
			// For categorical bar charts, labels need to be at the data level
			labels: hasLabels ? labels : undefined,
			datasets: config.data.datasets || [],
		},
		options: {
			...config.options,
			maintainAspectRatio: false,
			tooltips: tooltipConfig( config ),
			scales: {
				yAxes: [
					...config.options.scales.yAxes,
				],
				xAxes: [
					{
						...config.options.scales.xAxes[0],
						ticks: {
							maxTicksLimit: hasLabels ? labels.length : 12,
							autoSkip: false,
							maxRotation: 45,
						},
					},
				],
			},
			legend: {
				// For categorical bar charts with labels, hide the legend since each bar is its own category
				// For time-based bar charts (multiple datasets), show the legend
				display: ! ( hasLabels && config.data.datasets.length === 1 )
			},
		},
	};

	// Remove labels from options since they're now in data
	delete barConfig.options.labels;

	let chartTarget = document.getElementById( target );
	let chart = new Chart( chartTarget, barConfig );

	// Render.
	return chart;
};

/**
 * Get custom tooltip config for bar charts.
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

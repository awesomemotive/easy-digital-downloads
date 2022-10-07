/* global Chart */

/**
 * Internal dependencies.
 */
import moment, { utc } from 'moment';
import { getLabelWithTypeCondition, toolTipBaseConfig, attachAxisTickFormattingCallback } from './utils';

/**
 * Render a line chart.
 *
 * @param {Object} config Global chart config.
 * @return {Chart}
 */
export const render = ( config ) => {
	const { target } = config;
	const {
		dates: {
			utc_offset: utcOffset,
			hour_by_hour: hourByHour,
			day_by_day: dayByDay,
		},
	} = config;

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

	const lineConfig = {
		...config,
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
							maxTicksLimit: 12,
							autoSkip: true,
							maxRotation: 0,
						},
						time: {
							...config.options.scales.xAxes[0].time,
							parser: function( date ) {
								// Use UTC for larger dataset averages.
								// Specifically this ensures month by month shows the start of the month
								// if the UTC offset is negative.
								if ( ! hourByHour && ! dayByDay ) {
									return moment.utc( date );
								} else {
									return moment( date ).utcOffset( utcOffset );
								}
							},
						},
					},
				],
			},
			legend:{
				// Detect click on a dataset legend item and hide the dataset with it's Y axis.
				onClick: function( event, legendItem ) {
					const dataset = this.chart.config.data.datasets[ legendItem.datasetIndex ];

					// Find Y axis that belongs to the dataset.
					if ( dataset.hasOwnProperty( 'yAxisID' ) ) {
						const axisIndex = this.chart.options.scales.yAxes.findIndex( axis => {
							return axis.id === dataset.yAxisID;
						});

						if ( axisIndex !== -1 ) {
							// Toggle the visibility of Y axis.
							this.chart.options.scales.yAxes[ axisIndex ].display = ! this.chart.options.scales.yAxes[ axisIndex ].display;
						}
					}

					// Toggle the visibility of the dataset.
					dataset.hidden = ! dataset.hidden;
					this.chart.update();
				}
			},
		},
	};

	let chartTarget = document.getElementById( target );
	let chart = new Chart( chartTarget, lineConfig );

	/*
	* If there are multiple Y axes, we have to align their baseline.
	* We have to take yAxes after chart is initialized so that
	* we can get calculated min and max of each axis.
	*/
	let yAxes = []
	for ( const [key, scale] of Object.entries( chart.scales ) ) {
		// Find out if this is Y axis.

		if ( 'time' !== scale.type ) {
			yAxes.push( scale )
		}
	}

	if ( yAxes.length > 1 ) {
		yAxes.forEach(axis => {
			// Max and min is already calculated by chart.js.
			axis.range = (axis.max - axis.min);
			// Express the min / max values as a fraction of the overall range.
			axis.min_ratio = axis.min / axis.range
			axis.max_ratio = axis.max / axis.range
		})

		// Find the largest of min and max ratio.
		let largest_ratio = yAxes.reduce((a, b) => ({
			min_ratio: Math.min(a.min_ratio, b.min_ratio),
			max_ratio: Math.max(a.max_ratio, b.max_ratio)
		}))

		// Scale each axis according to the ratio.
		yAxes.forEach(axis => {
			let min_ticks = largest_ratio.min_ratio * axis.range;
			let max_ticks = largest_ratio.max_ratio * axis.range;

			// Set options to the chart axis.
			let chart_axis = chart.options.scales.yAxes.find(x => x.id === axis.id);
			if (chart_axis) {
				chart_axis.ticks.min = min_ticks;
				chart_axis.ticks.max = max_ticks;
			}
		})

		chart.update();
	}

	// Render.
	return chart;
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

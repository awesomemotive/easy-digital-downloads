/* global Chart */

/**
 * Internal dependencies.
 */
import moment, { utc } from 'moment';
import momentTimezone from 'moment-timezone';
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
							...config.options.scales.xAxes[0].ticks,
							maxTicksLimit:12,
							autoSkip: true,
							callback( value, index, ticks ) {
								return moment.tz( ticks[index].value, config.dates.timezone ).format( config.dates.time_format );
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

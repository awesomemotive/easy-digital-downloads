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

	let chartTarget = document.getElementById( target );
	let chart = new Chart( chartTarget, lineConfig );

	// Make adjustments to the config based on the data sets.
	/**
	 * Setup some sane min and max values for the y-axes.
	 *
	 * If there are two y-axes, then we need to possibly adjust the min on both to to ensure the `0` line is in the same
	 * plane for both.
	 */
	 let yAxesConstraints = [];
	 config.data.datasets.forEach( function( values, index ) {
		 let yValues = values.data.map(item => item.y); // Pull out just the y values.
		 let max = Math.max.apply( null, yValues ),
			 min =  Math.min.apply( null, yValues );

		 yAxesConstraints[ index ] = {
			 min: Math.floor( min + ( min * .10 ) ),
			 max: Math.ceil( max + ( max * .10 ) ),
		 };
	 });

	 // If we have more than one axes here determine if one is lower than 0, so we can adjust the other graphs.
	 if ( yAxesConstraints.length > 1 ) {

		 if ( yAxesConstraints[0]['min'] < 0 && yAxesConstraints[1]['min'] >= 0 ) {
			 yAxesConstraints[1]['min'] = -2;
		 } else if ( yAxesConstraints[1][min] < 0 && yAxesConstraints[0]['min'] >= 0 ) {
			 yAxesConstraints[0]['min'] = -200;
		 }

		 yAxesConstraints.forEach( function( values, index ) {
			 config.options.scales.yAxes[ index ]['ticks']['min'] = values[ 'min' ];
			 config.options.scales.yAxes[ index ]['ticks']['max'] = values[ 'max' ];
		 } );
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

/* global Chart */

/**
 * Internal dependencies.
 */
import { getLabelWithTypeCondition, toolTipBaseConfig } from './utils';

/**
 * Render a line chart.
 *
 * @param {Object} config Global chart config.
 * @return {Chart}
 */
export const render = ( config ) => {
	const {
		dates,
		options,
		data,
		target,
	} = config;

	Chart.defaults.global.pointHitDetectionRadius = 5;

	// Convert dataset x-axis values to moment() objects.
	_.each( data.datasets, ( dataset ) => {
		_.each( dataset.data, ( pair, index ) => {

			console.log( pair.x );

			// Moment.js accepts a date object so we'll turn the timestamp into a date object here.
			let date = new Date( parseInt( pair.x ) );

			// Offset the moment.js so it is set to match the WordPress timezone, which is n dates.utc_offset
			pair.x = moment( date ).utcOffset( parseInt( dates.utc_offset ) ).format( 'LLL' );

		} );
	} );

	// Set min and max moment() values for the x-axis.
	// @todo Not sure this is the correct way to be setting this?
	_.each( options.scales.xAxes, ( xaxis ) => {
		if ( ! dates.day_by_day ) {
			xaxis.time.unit = 'month';
		}

		xaxis.time.min = moment( dates.start.date );
		xaxis.time.max = moment( dates.end.date );
	} );

	// Config tooltips.
	config.options.tooltips = tooltipConfig( config );

	// Render
	return new Chart( document.getElementById( target ), config );
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

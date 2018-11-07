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

	// Convert dataset x-axis values to moment() objects.
	_.each( data.datasets, ( dataset ) => {
		_.each( dataset.data, ( pair, index ) => {
			if ( ! dates.hour_by_hour ) {
				pair.x = moment( pair.x ).utcOffset( 0 ).format( 'LLL' );
			} else {
				pair.x = moment( pair.x ).utcOffset( dates.utc_offset ).format( 'LLL' );
			}
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
			const label = getLabelWithTypeCondition( t.yLabel, config );

			return `${ d.datasets[ t.datasetIndex ].label }: ${ label }`;
		},
	},
} );

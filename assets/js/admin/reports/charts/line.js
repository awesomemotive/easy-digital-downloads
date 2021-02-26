/* global Chart */

/**
 * Internal dependencies.
 */
import { NumberFormat } from '@easydigitaldownloads/currency';
import { getLabelWithTypeCondition, toolTipBaseConfig } from './utils';

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
						...config.options.scales.yAxes[0],
						ticks: {
							callback: ( value, index, values ) => {
								return number.format( value );
							},
						},
					},
				],
				xAxes: [
					{
						...config.options.scales.xAxes[0],
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

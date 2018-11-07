/* global Chart */

/**
 * Internal dependencies.
 */
import { getLabelWithTypeCondition } from './utils';

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

	console.log(config);

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
	enabled: false,
	mode: 'index',
	position: 'nearest',

	/**
	 * Output a a custom tooltip.
	 *
	 * @param {Object} tooltip Tooltip data.
	 */
	custom: function( tooltip ) {
		// Tooltip element.
		let tooltipEl = document.getElementById( 'edd-chartjs-tooltip' );

		if ( ! tooltipEl ) {
			tooltipEl = document.createElement( 'div' );
			tooltipEl.id = 'edd-chartjs-tooltip';
			tooltipEl.innerHTML = '<table></table>';

			this._chart.canvas.parentNode.appendChild( tooltipEl );
		}

		// Hide if no tooltip.
		if ( tooltip.opacity === 0 ) {
			tooltipEl.style.opacity = 0;
			return;
		}

		// Set caret position.
		tooltipEl.classList.remove( 'above', 'below', 'no-transform' );
		if ( tooltip.yAlign ) {
			tooltipEl.classList.add( tooltip.yAlign );
		} else {
			tooltipEl.classList.add( 'no-transform' );
		}

		function getBody( bodyItem ) {
			return bodyItem.lines;
		}

		// Set Text
		if ( tooltip.body ) {
			const titleLines = tooltip.title || [];
			const bodyLines = tooltip.body.map( getBody );

			let innerHtml = '<thead>';

			titleLines.forEach( function( title ) {
				innerHtml += '<tr><th>' + title + '</th></tr>';
			} );

			innerHtml += '</thead><tbody>';

			bodyLines.forEach( function( body, i ) {
				const colors = tooltip.labelColors[ i ];
				let style = 'background:' + colors.borderColor;
				style += '; border-color:' + colors.borderColor;
				style += '; border-width: 2px';
				const span = '<span class="edd-chartjs-tooltip-key" style="' + style + '"></span>';
				innerHtml += '<tr><td>' + span + body + '</td></tr>';
			} );

			innerHtml += '</tbody>';

			const tableRoot = tooltipEl.querySelector( 'table' );
			tableRoot.innerHTML = innerHtml;
		}

		const positionY = this._chart.canvas.offsetTop;
		const positionX = this._chart.canvas.offsetLeft;

		// Display, position, and set styles for font
		tooltipEl.style.opacity = 1;
		tooltipEl.style.left = positionX + tooltip.caretX + 'px';
		tooltipEl.style.top = positionY + tooltip.caretY + 'px';
		tooltipEl.style.fontFamily = tooltip._bodyFontFamily;
		tooltipEl.style.fontSize = tooltip.bodyFontSize + 'px';
		tooltipEl.style.fontStyle = tooltip._bodyFontStyle;
		tooltipEl.style.padding = tooltip.yPadding + 'px ' + tooltip.xPadding + 'px';
	},

	callbacks: {
		/**
		 * Generate a label.
		 *
		 * @param {Object} t
		 * @param {Object} d
		 */
		label: function( t, d ) {
			let label = getLabelWithTypeCondition( t.yLabel, config );

			return `${ d.datasets[ t.datasetIndex ].label }: ${ label }`;
		},
	}
} );

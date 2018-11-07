/* global eddAdminReportsCharts, edd_vars */

Chart.defaults.global.pointHitDetectionRadius = 5;

const { charts } = eddAdminReportsCharts;
const { currency_sign, currency_pos } = edd_vars;

/**
 * Render a chart.
 *
 * @param {Object} config Global chart config.
 * @return {Chart}
 */
const renderChart = ( config ) => {
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
	config.options.tooltips = {
		...config.options.tooltips,
		...getToolTipConfig( config ),
		callbacks: isPie( config ) ? getPieTooltipConfig( config ) : getLineTooltipConfig( config ),
	}

	// Render
	return new Chart( document.getElementById( target ), config );
};

/**
 * Get custom tooltip config for line charts.
 *
 * @param {Object} config Global chart config.
 * @return {Object}
 */
const getLineTooltipConfig = ( config ) => ( {
	/**
	 * Generate a label.
	 *
	 * @param {Object} t
	 * @param {Object} d
	 */
	label: function ( t, d ) {
		let conditional = '';
		let yLabel = t.yLabel;

		const {
			target,
			options: {
				datasets,
			}
		} = config;

		if ( datasets ) {
			_.each( datasets, ( dataset ) => {
				const { type } = dataset;
				
				if ( 'currency' === type ) {
					conditional += `t.datasetIndex === ${ target } || `;
				};
			} );
		}

		conditional.slice( 0, -4 );

		if ( '' !== conditional ) {
			if ( 'before' === currency_pos ) {
				yLabel = currency_sign + t.yLabel.toFixed(2);
			} else {
				yLabel = t.yLabel.toFixed(2) + currency_sign;
			}
		}

		return d.datasets[t.datasetIndex].label + ': ' + yLabel;
	},
} );

/**
 * Get custom tooltip config for pie charts.
 *
 * @param {Object} config Global chart config.
 * @return {Object}
 */
const getPieTooltipConfig = ( config ) => ( {
	/**
	 * Generate a label.
	 *
	 * @param {Object} t
	 * @param {Object} d
	 */
	label: function ( t, d ) {
		// @todo DRY this with Line config.
		let conditional = '';
		let yLabel = t.yLabel;

		const {
			target,
			options: {
				datasets,
			}
		} = config;

		if ( datasets ) {
			_.each( datasets, ( dataset ) => {
				const { type } = dataset;
				
				if ( 'currency' === type ) {
					conditional += `t.datasetIndex === ${ target } || `;
				};
			} );
		}

		conditional.slice( 0, -4 );

		var dataset = d.datasets[ t.datasetIndex ];
		var total = dataset.data.reduce( function( previousValue, currentValue, currentIndex, array ) {
			return previousValue + currentValue;
		} );

		var currentValue = dataset.data[ t.index ];
		var precentage = Math.floor( ( ( currentValue / total ) * 100 ) + 0.5 );

		if ( '' !== conditional ) {
			if ( 'before' === currency_pos ) {
				yLabel = currency_sign + t.yLabel.toFixed(2);
			} else {
				yLabel = t.yLabel.toFixed(2) + currency_sign;
			}
		}

		return d.labels[ t.index ] + ': ' + currentValue + ' (' + precentage + '%)';
	},
} );

/**
 * Get shared tooltip configuration.
 *
 * @param {Object} config Global chart config.
 * @return {Object}
 */
const getToolTipConfig = ( config ) => ( {
	/**
	 * Output a a custom tooltip.
	 *
	 * @param {Object} tooltip Tooltip data.
	 */
	custom: function ( tooltip ) {
		// Tooltip element.
		var tooltipEl = document.getElementById( 'edd-chartjs-tooltip' );

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
			tooltipEl.classList.add(tooltip.yAlign );
		} else {
			tooltipEl.classList.add( 'no-transform' );
		}

		function getBody( bodyItem ) {
			return bodyItem.lines;
		}

		// Set Text
		if ( tooltip.body ) {
			var titleLines = tooltip.title || [];
			var bodyLines = tooltip.body.map( getBody );

			var innerHtml = '<thead>';

			titleLines.forEach( function ( title ) {
				innerHtml += '<tr><th>' + title + '</th></tr>';
			});

			innerHtml += '</thead><tbody>';

			bodyLines.forEach( function ( body, i ) {
				var colors = tooltip.labelColors[ i ];
				var style = 'background:' + colors.borderColor;
				style += '; border-color:' + colors.borderColor;
				style += '; border-width: 2px';
				var span = '<span class="edd-chartjs-tooltip-key" style="' + style + '"></span>';
				innerHtml += '<tr><td>' + span + body + '</td></tr>';
			});

			innerHtml += '</tbody>';

			var tableRoot = tooltipEl.querySelector( 'table' );
			tableRoot.innerHTML = innerHtml;
		}

		var positionY = this._chart.canvas.offsetTop;
		var positionX = this._chart.canvas.offsetLeft;

		// Display, position, and set styles for font
		tooltipEl.style.opacity = 1;
		tooltipEl.style.left = positionX + tooltip.caretX + 'px';
		tooltipEl.style.top = positionY + tooltip.caretY + 'px';
		tooltipEl.style.fontFamily = tooltip._bodyFontFamily;
		tooltipEl.style.fontSize = tooltip.bodyFontSize + 'px';
		tooltipEl.style.fontStyle = tooltip._bodyFontStyle;
		tooltipEl.style.padding = tooltip.yPadding + 'px ' + tooltip.xPadding + 'px';
	},
} );

/**
 * Determine if a pie graph.
 *
 * @todo maybe pass from data?
 *
 * @param {Object} config Chart config.
 * @return {Bool}
 */
const isPie = ( config ) => {
	const { type } = config;

	return type === 'pie' || type  === 'doughnut';
}

/**
 * Render the registered charts.
 */
_.each( charts, ( config ) => renderChart( config ) );

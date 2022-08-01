/**
 * Internal dependencies
 */
import { Currency } from '@easy-digital-downloads/currency';

/**
 * Determine if a pie graph.
 *
 * @todo maybe pass from data?
 *
 * @param {Object} config Global chart config.
 * @return {Bool}
 */
export const isPieChart = ( config ) => {
	const { type } = config;

	return type === 'pie' || type === 'doughnut';
};

/**
 * Determine if a chart's dataset has a special conditional type.
 *
 * Currently just checks for currency.
 *
 * @param {string} label Current label.
 * @param {Object} config Global chart config.
 */
export const getLabelWithTypeCondition = ( label, datasetConfig ) => {
	let newLabel = label;
	const { type } = datasetConfig;

	if ( 'currency' === type ) {
		const currency = new Currency();

		newLabel = currency.format( label, false );
	}

	return newLabel;
};

/**
 * Shared tooltip configuration.
 */
export const toolTipBaseConfig = {
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

			innerHtml += '</thead><tbody>';

			bodyLines.forEach( function( body, i ) {
				const colors = tooltip.labelColors[ i ];
				const { borderColor, backgroundColor } = colors;

				// Super dirty check to use the legend's color.
				let fill = borderColor;

				if ( fill === 'rgb(230, 230, 230)' || fill === '#fff' ) {
					fill = backgroundColor;
				}

				const style = [
					`background: ${ fill }`,
					`border-color: ${ fill }`,
					'border-width: 2px',
				];

				const span = '<span class="edd-chartjs-tooltip-key" style="' + style.join( ';' ) + '"></span>';

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
};

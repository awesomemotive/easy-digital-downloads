/**
 * Internal dependencies
 */
import { Currency, NumberFormat } from '@easy-digital-downloads/currency';

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
	intersect: false,
	mode: 'index',

	/**
	 * Output a a custom tooltip.
	 *
	 * @param {Object} tooltip Tooltip data.
	 */
	custom: function( tooltip ) {
		// Tooltip element.
		let tooltipEl = document.getElementById( this._chart.canvas.parentNode.id + '-tooltip' );

		if ( ! tooltipEl ) {
			tooltipEl = document.createElement( 'div' );
			tooltipEl.id = this._chart.canvas.parentNode.id + '-tooltip';
			tooltipEl.classList.add( 'edd-chartjs-tooltip' );
			tooltipEl.innerHTML = '<table></table>';

			this._chart.canvas.parentNode.appendChild( tooltipEl );
		}

		// Hide if no tooltip.
		if ( tooltip.opacity === 0 ) {
			tooltipEl.style.opacity = 0;
			return;
		}

		function getBody( bodyItem ) {
			return bodyItem.lines;
		}

		// Set Text
		if ( tooltip.body ) {
			const titleLines = tooltip.title || [];
			const bodyLines = tooltip.body.map( getBody );

			let innerHtml = '<thead>' + titleLines + '</thead>';
			innerHtml    += '<tbody>';

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

		// Position the tooltip.
		const chartRect = this._chart.canvas.getBoundingClientRect();
		let elementRect = tooltipEl.getBoundingClientRect();

		const positionX = this._chart.canvas.offsetLeft + tooltip.caretX;
		// If the positionX is greater than 1/2 of the chart width, move it to the left.
		let elementXPosition = positionX;
		if ( positionX >= ( chartRect.width / 2 ) ) {
			elementXPosition = positionX - ( elementRect.width / 2 ) - 20;
		} else {
			elementXPosition = positionX + ( elementRect.width / 2 ) + 20;
		}

		tooltipEl.style.left = elementXPosition + 'px';

		const positionY = this._chart.canvas.offsetTop + Math.round( this._chart.canvas.height / 5 );
		tooltipEl.style.top = positionY + 'px';

		// Set the font styles.
		tooltipEl.style.fontFamily = tooltip._bodyFontFamily;
		tooltipEl.style.fontSize = tooltip.bodyFontSize + 'px';
		tooltipEl.style.fontStyle = tooltip._bodyFontStyle;

		// Display the tooltip.
		tooltipEl.style.opacity = 1;
	},
};

/**
 * Attach formatting callback to axis ticks.
 *
 * @param {Object} ticks Axis ticks configuration.
 */
 export const attachAxisTickFormattingCallback = ( ticks ) => {
	const number = new NumberFormat();

	ticks.callback = function( value, index, values ) {
		switch ( ticks.formattingType ) {
			case 'integer':
				value = parseInt( value );
				break;
			case 'format':
				value = number.format( value );
				break;
			default:
		}

		return value;
	}

	return ticks;
};

/* global edd_vars */

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
export const getLabelWithTypeCondition = ( label, config ) => {
	const { currency_sign, currency_pos } = edd_vars;
	let conditional = '';
	let newLabel = label;

	const {
		target,
		options: {
			datasets,
		},
	} = config;

	if ( datasets ) {
		_.each( datasets, ( dataset ) => {
			const { type } = dataset;

			if ( 'currency' === type ) {
				conditional += `t.datasetIndex === ${ target } || `;
			}
		} );
	}

	conditional.slice( 0, -4 );

	if ( '' !== conditional ) {
		// @todo support better currency locales.
		const amount = label.toFixed( 2 );

		if ( 'before' === currency_pos ) {
			newLabel = currency_sign + amount;
		} else {
			newLabel = amount + currency_sign;
		}
	}

	return newLabel;
}

/* global eddAdminReportsCharts */

/**
 * Internal dependencies.
 */
import { render as lineChartRender } from './line.js';
import { render as pieChartRender } from './pie.js';
import { isPieChart } from './utils.js';

( () => {
	if ( _.isUndefined( window.eddAdminReportsCharts ) ) {
		return;
	}

	// Set ChartJS defaults.
	Chart.defaults.global.pointHitDetectionRadius = 5;

	/**
	 * Render the registered charts.
	 */
	_.each( window.eddAdminReportsCharts.charts, ( config ) => {
		const isPie = isPieChart( config );

		if ( isPieChart( config ) ) {
			pieChartRender( config );
		} else {
			lineChartRender( config );
		}
	} );
} )();

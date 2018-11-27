/* global eddAdminReportsCharts */

/**
 * Internal dependencies.
 */
import { render as lineChartRender } from './line.js';
import { render as pieChartRender } from './pie.js';
import { isPieChart } from './utils.js';

// Set ChartJS defaults.
Chart.defaults.global.pointHitDetectionRadius = 5;

// Get Bootstrapped chart data.
const { charts } = eddAdminReportsCharts;

/**
 * Render the registered charts.
 */
_.each( charts, ( config ) => {
	const isPie = isPieChart( config );
	
	if ( isPieChart( config ) ) {
		pieChartRender( config );
	} else {
		lineChartRender( config );
	}
} );

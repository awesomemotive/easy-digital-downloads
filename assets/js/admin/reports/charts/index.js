/* global eddAdminReportsCharts */

/**
 * Internal dependencies.
 */
import { render as lineChartRender } from './line.js';
import { render as pieChartRender } from './pie.js';
import { isPieChart } from './utils.js';

/**
 * Render a chart based on config.
 *
 * @param {Object} config Chart config.
 */
window.eddRenderReportChart = ( config ) => {
	const isPie = isPieChart( config );

	if ( isPieChart( config ) ) {
		pieChartRender( config );
	} else {
		lineChartRender( config );
	}
};

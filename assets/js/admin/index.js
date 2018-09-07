/**
 * Internal dependencies.
 */

// Pages-specific.
import EDD_Download_Configuration from './downloads';
import EDD_Notes from './notes';
import EDD_Edit_Payment from './payments';
import EDD_Add_Order from './orders';
import EDD_Discount from './discounts';
import EDD_Customer from './customers';
import EDD_Reports from './reports';
import EDD_Settings from './settings';
import EDD_Tools from './tools';
import EDD_Export from './tools/export';
import EDD_Import from './tools/import';
import './dashboard';

// Global components applied directly to DOM.
import './components/date-picker';
import './components/chosen';
import './components/tooltips';
import './components/vertical-sections';
import './components/sortable-list';
import './components/user-search';

// @todo These should be separate entry points and loaded only on pages needed.
// @see https://github.com/easydigitaldownloads/easy-digital-downloads/issues/3535
jQuery( document ).ready( function( $ ) {
	// Download.
	EDD_Download_Configuration.init();

	// Notes.
	EDD_Notes.init();

	// Payments.
	EDD_Edit_Payment.init();

	// Orders.
	EDD_Add_Order.init();

	// Discounts.
	EDD_Discount.init();

	// Customers.
	EDD_Customer.init();

	// Reports.
	EDD_Reports.init();
	
	// Settings.
	EDD_Settings.init();

	// Tools.
	EDD_Tools.init();

	// Export/Import.
	EDD_Export.init();
	EDD_Import.init();
} );

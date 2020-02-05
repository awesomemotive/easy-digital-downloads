/* global $ */

/**
 * Internal dependencies
 */
import { jQueryReady } from 'utils/jquery.js';
import { reindex } from './index.js';
import { updateAmounts } from './../order-amounts';

import { removeRow } from './../utils/list-table.js';

jQueryReady( () => {

	$( document.body ).on( 'click', '.orderitems .remove-item', function( e ) {
		e.preventDefault();

		const button = $( this );
		const row = button.parents( 'tr' );

		// Remove row.
		removeRow( row );

		// @todo attach to edd-admin-remove-order-download trigger in /order-amounts
		updateAmounts();
		reindex();

		return false;
	} );

} );

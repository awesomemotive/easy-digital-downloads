/* global $ */

/**
 * Internal dependencies
 */
import { reindexRows } from './../utils/list-table.js';
import './add-adjustment.js';

/**
 * Reindex order item table rows.
 * 
 * @since 3.0
 */
export const reindex = () => reindexRows( $( '.orderadjustments tbody tr:not(.no-items)' ) );

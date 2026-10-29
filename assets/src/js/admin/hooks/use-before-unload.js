/**
 * Unsaved changes warning hook.
 *
 * @package     EDD\Admin\Hooks
 * @copyright   Copyright (c) 2026, Sandhills Development, LLC
 * @license     https://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       3.7.3
 */

import { useEffect } from '@wordpress/element';

/**
 * Warn the user before leaving the page when there are unsaved changes.
 *
 * Attaches a beforeunload handler while isDirty is true, removes it when false.
 *
 * @param {boolean} isDirty Whether there are unsaved changes.
 * @return {void}
 */
export function useBeforeUnload( isDirty ) {
	useEffect( () => {
		if ( ! isDirty ) {
			return;
		}

		const handleBeforeUnload = ( e ) => {
			e.preventDefault();
			e.returnValue = '';
		};

		globalThis.addEventListener( 'beforeunload', handleBeforeUnload );

		return () => {
			globalThis.removeEventListener( 'beforeunload', handleBeforeUnload );
		};
	}, [ isDirty ] );
}

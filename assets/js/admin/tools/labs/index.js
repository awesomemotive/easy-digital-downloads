/* global ajaxurl, jQuery, wp */

/**
 * Internal dependencies
 */
import { initializeSettingsToggles } from 'admin/components/settings/toggles';

// Requirements are auto-initialized globally when requirements.js loads
// but we still import it to ensure it's bundled with this script
import 'admin/components/conditionals/requirements';

// Ensure jQuery and WordPress heartbeat are available
if ( typeof jQuery === 'undefined' || typeof wp === 'undefined' || ! wp ) {
	throw new Error( 'Labs script requires jQuery and WordPress heartbeat' );
}

const $ = jQuery;

/**
 * Get the IDs of visible profiler boxes.
 *
 * @return {Array<string>} Array of profiler IDs.
 */
function getVisibleProfilerIds() {
	const ids = [];
	$( '.edd-profiler-log' ).each( function () {
		const $box = $( this );
		if ( $box.is( ':visible' ) ) {
			const requires = $box.attr( 'data-requires' );
			if ( requires ) {
				const id = requires.replace( /_profiler$/, '' );
				ids.push( id );
			}
		}
	} );
	return ids;
}

/**
 * Initialize the Labs tools tab.
 */
function initializeLabs() {
	// Initialize the settings toggle functionality.
	initializeSettingsToggles();

	// Handle cookie setting updates from settings toggles
	document.addEventListener( 'eddSettingToggled', function ( event ) {
		const cookieData = event?.detail?.cookie;
		if ( cookieData ) {
			const expirationDate = new Date( cookieData.expiration * 1000 );
			const cookieString = `${ cookieData.name }=${ encodeURIComponent( cookieData.value ) }; expires=${ expirationDate.toUTCString() }; path=/; SameSite=Lax`;
			document.cookie = cookieString;
		}
	} );

	// Setup heartbeat communication for profiler logs
	$( document ).on( 'heartbeat-send', function ( event, data ) {
		const ids = getVisibleProfilerIds();
		if ( ids.length ) {
			data.eddLabsLogs = { ids };
		}
	} );

	$( document ).on( 'heartbeat-tick', function ( event, data ) {
		if ( ! data?.eddLabsLogs ) {
			return;
		}

		const payload = data.eddLabsLogs;
		for ( const id in payload ) {
			const $area = $( '#edd-profiler-log-' + id + ' textarea[name="edd-profiler-log-contents"]' );
			if ( $area.length ) {
				$area.val( payload[ id ].contents || '' );
			}

			const $file = $( '#edd-profiler-log-' + id ).closest( '.inside' ).find( 'code' );
			if ( $file.length && payload[ id ].path ) {
				$file.text( payload[ id ].path );
			}

			// Update the last updated timestamp.
			const $timestamp = $( '.edd-profiler-log__time[data-profiler="' + id + '"]' );
			if ( $timestamp.length && payload[ id ].updated ) {
				$timestamp.text( payload[ id ].updated );
				// Show the timestamp container now that we have a value
				$timestamp.closest( '.edd-profiler-log__timestamp' ).removeClass( 'edd-hidden' );
			}
		}
	} );
}

// Initialize when document is ready
document.addEventListener( 'DOMContentLoaded', function () {
	initializeLabs();
} );

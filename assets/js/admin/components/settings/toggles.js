/* global eddSettings */

/**
 * Handle the AJAX toggle request for a setting.
 *
 * @param {Event} event The change event from the checkbox.
 * @return {void}
 */
function handleToggleChange( event ) {
	const input = event.target;
	if ( ! input || input.nodeName !== 'INPUT' || input.type !== 'checkbox' ) {
		return;
	}

	const setting = input.dataset.setting;
	if ( ! setting ) {
		return;
	}

	// Disable during request
	input.disabled = true;
	const desiredChecked = input.checked;

	const body = new URLSearchParams();
	body.append( 'action', 'edd_toggle_ajax_setting' );
	body.append( 'nonce', eddSettings?.nonce || '' );
	body.append( 'setting', setting );
	body.append( 'value', desiredChecked ? '1' : '0' );

	fetch( eddSettings.ajaxurl, {
		method: 'POST',
		headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
		credentials: 'same-origin',
		body: body,
	} )
		.then( function ( res ) {
			if ( ! res.ok ) {
				throw new Error( 'Network response was not ok' );
			}
			return res.json();
		} )
		.then( function ( json ) {
			if ( ! json?.success ) {
				throw new Error( json?.data?.message || 'Save failed' );
			}

			// Dispatch custom event for other modules to listen to
			document.dispatchEvent(
				new CustomEvent( 'eddSettingToggled', {
					detail: { setting, value: desiredChecked, ...json.data },
				} )
			);
		} )
		.catch( function () {
			// Revert UI on error
			input.checked = ! desiredChecked;
		} )
		.finally( function () {
			input.disabled = false;
		} );
}

/**
 * Initialize the settings toggle listeners.
 * This should be called once the DOM is ready.
 *
 * @return {void}
 */
export function initializeSettingsToggles() {
	document.addEventListener( 'change', handleToggleChange );
}

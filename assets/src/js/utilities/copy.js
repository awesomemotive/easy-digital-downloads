/**
 * Copy to Clipboard Utility
 *
 * A reusable utility for copying text to the clipboard with visual feedback.
 * Can be imported and used by any EDD script.
 *
 * Usage:
 *   import '@easy-digital-downloads/copy';
 *
 * Markup patterns:
 *
 * 1. Copy from an input element:
 *    <input type="text" id="my-input" value="Text to copy">
 *    <button class="edd-button__copy" data-clipboard-target="#my-input">Copy</button>
 *
 * 2. Copy direct text:
 *    <button class="edd-button__copy" data-clipboard-text="Text to copy">Copy</button>
 *
 * Features:
 * - Copies text to clipboard using the modern Clipboard API
 * - Shows "Copied!" feedback for 2 seconds
 * - Adds 'updated-message' class during feedback
 * - Works with both input elements and direct text
 *
 * @since 3.6.5
 */

document.addEventListener( 'click', function ( event ) {
	if ( !event.target.classList.contains( 'edd-button__copy' ) ) {
		return;
	}

	let textToCopy = '';

	// Check if copying from an input element
	const target = event.target.dataset.clipboardTarget;
	if ( target ) {
		const element = document.querySelector( target );
		if ( element ) {
			element.select();
			element.setSelectionRange( 0, 99999 );
			textToCopy = element.value;
		}
	} else {
		// Check if copying direct text
		textToCopy = event.target.dataset.clipboardText;
	}

	if ( !textToCopy ) {
		return;
	}

	// Copy to clipboard
	navigator.clipboard.writeText( textToCopy );

	// Show success feedback
	const originalText = event.target.innerText;

	event.target.classList.add( 'updated-message' );
	event.target.innerText = edd_vars.copy_success || 'Copied!';

	setTimeout( function() {
		event.target.classList.remove( 'updated-message' );
		event.target.innerText = originalText;
	}, 2000 );
} );

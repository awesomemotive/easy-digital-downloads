/* global eddEmailStyles, globalThis, document */

/**
 * Email style formats: insert or wrap content with the selected format HTML.
 *
 * Uses a native dialog element (same pattern as Cart Recovery modal). Format definitions
 * (block/selector, classes, styles) are used to build HTML and insert via TinyMCE.
 *
 * @package EDD\Admin\Emails
 */

/**
 * Build a style string from a format's styles object.
 *
 * @param {Object} styles Key-value CSS properties.
 * @return {string} style="..." string value (escaped).
 */
function buildStyleString ( styles ) {
	if ( !styles || typeof styles !== 'object' ) {
		return '';
	}
	const parts = [];
	Object.keys( styles ).forEach( function ( key ) {
		const val = styles[ key ];
		if ( val != null && val !== '' ) {
			parts.push( key + ':' + val );
		}
	} );
	return parts.join( '; ' );
}

/**
 * Build a class string from a format's classes (string or array).
 *
 * @param {string|string[]} classes Class name(s).
 * @return {string} Space-separated class string.
 */
function buildClassString ( classes ) {
	if ( !classes ) {
		return '';
	}
	return Array.isArray( classes ) ? classes.join( ' ' ) : classes;
}

/**
 * Insert or wrap content with the selected format by building HTML from the format definition.
 *
 * @param {string} formatName The format name (key in eddEmailStyleFormats.formats).
 */
function applyFormat ( formatName ) {
	const config = eddEmailStyles || {};
	const editorId = config.editorId || 'edd-email-content';
	const formats = config.formats || [];
	const editor = globalThis.tinymce?.get( editorId );

	if ( !editor || !formatName ) {
		return;
	}

	const format = formats.find( function ( f ) {
		return f.name === formatName;
	} );

	if ( !format ) {
		return;
	}

	const classStr = buildClassString( format.classes );
	const styleStr = buildStyleString( format.styles );
	const attrClass = classStr ? ' class="' + classStr.replaceAll( /"/g, '&quot;' ) + '"' : '';
	const attrStyle = styleStr ? ' style="' + styleStr.replaceAll( /"/g, '&quot;' ) + '"' : '';

	editor.focus();

	if ( format.block ) {
		const tag = format.block || 'div';
		const content = editor.selection.getContent( { format: 'html' } );
		const inner = content?.trim() ? content : '&nbsp;';
		const html = '<' + tag + attrClass + attrStyle + '>' + inner + '</' + tag + '>';
		editor.execCommand( 'mceInsertContent', false, html );
	} else if ( format.selector === 'a' ) {
		const content = editor.selection.getContent( { format: 'html' } );
		let href = '#';
		let inner = ( config.linkPlaceholder || 'Link' );
		if ( content?.trim() ) {
			const temp = document.createElement( 'div' );
			temp.innerHTML = content;
			const existingLink = temp.querySelector( 'a' );
			if ( existingLink ) {
				href = existingLink.dataset.href || href;
				inner = existingLink.innerHTML;
			} else {
				inner = content;
			}
		}
		const attrHref = ' href="' + ( href || '#' ).replaceAll( /"/g, '&quot;' ) + '"';
		const html = '<a' + attrHref + attrClass + attrStyle + '>' + inner + '</a>';
		editor.execCommand( 'mceInsertContent', false, html );
	}
}

/**
 * Internal dependencies.
 */
import { setupEddModal } from '@easy-digital-downloads/modal';

/**
 * Set up format list button clicks: close dialog and insert the format.
 *
 * @param {Object} modalApi From setupEddModal; used to close the dialog on insert.
 */
function setupFormatButtons ( modalApi ) {
	const buttons = document.querySelectorAll( '.edd-email-styles__button' );
	if ( ! buttons.length ) {
		return;
	}

	buttons.forEach( function ( button ) {
		button.addEventListener( 'click', function () {
			const formatName = button.dataset.formatName;
			if ( modalApi && typeof modalApi.close === 'function' ) {
				modalApi.close();
			}
			if ( formatName ) {
				applyFormat( formatName );
			}
		} );
	} );
}

document.addEventListener( 'DOMContentLoaded', function () {
	const trigger = document.querySelector( '.edd-email-styles-inserter' );
	const modalApi = trigger ? setupEddModal( { trigger } ) : null;
	setupFormatButtons( modalApi );
} );

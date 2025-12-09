/**
 * Cart Preview SVG Icons
 *
 * Single source of truth for all cart preview icons.
 * Icons from https://heroicons.com/
 *
 * @package EDD\CartPreview
 * @since   3.6.2
 */

/**
 * Get icon SVG string by name.
 *
 * @since 3.6.2
 * @param {string} name Icon name
 * @return {string} SVG markup
 */
export const getIcon = ( name ) => {
	return icons[ name ] || '';
};

/**
 * Inject SVG icons into elements with data-edd-icon attributes.
 *
 * Finds all elements with [data-edd-icon] attribute within the specified root
 * and injects the corresponding SVG from the icon library.
 *
 * @since 3.6.2
 * @param {Element|ShadowRoot|Document} root Root element to search within. Defaults to document.
 * @return {number} Number of icons injected
 */
export const injectIcons = ( root = document ) => {
	// Find all elements with data-edd-icon attribute
	const iconElements = root.querySelectorAll( '[data-edd-icon]' );
	let injectedCount = 0;

	for ( const element of iconElements ) {
		const iconName = element.dataset.eddIcon;
		if ( ! iconName ) {
			continue;
		}

		const iconSVG = getIcon( iconName );
		if ( ! iconSVG ) {
			continue;
		}

		element.innerHTML = iconSVG;
		injectedCount++;
	}

	return injectedCount;
};

const icons = {
	// Shopping cart (outline, 24x24)
	// https://heroicons.com/ - shopping-cart
	'cart': `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
		<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 00-16.536-1.84M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
	</svg>`,

	// Trash can (outline, 24x24)
	// https://heroicons.com/ - trash
	'trash': `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
		<path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
	</svg>`,

	// X Mark (outline, 24x24)
	// https://heroicons.com/ - x-mark
	'x-mark': `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
		<path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
	</svg>`,

	// Plus (mini, 20x20)
	// https://heroicons.com/ - plus (mini)
	'plus': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
		<path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
	</svg>`,

	// Minus (mini, 20x20)
	// https://heroicons.com/ - minus (mini)
	'minus': `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
		<path fill-rule="evenodd" d="M4 10a.75.75 0 01.75-.75h10.5a.75.75 0 010 1.5H4.75A.75.75 0 014 10z" clip-rule="evenodd" />
	</svg>`,

	// Chevron down (outline, 24x24)
	'down': `<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
		<path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
	</svg>`,
};


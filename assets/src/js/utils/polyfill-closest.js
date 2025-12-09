/// Polyfill .closest
// @link https://developer.mozilla.org/en-US/docs/Web/API/Element/closest#Polyfill
if ( ! Element.prototype.matches ) {
	Element.prototype.matches =
		Element.prototype.msMatchesSelector ||
		Element.prototype.webkitMatchesSelector;
}

if ( ! Element.prototype.closest ) {
	Element.prototype.closest = function( s ) {
		let el = this;

		do {
			if ( Element.prototype.matches.call( el, s ) ) return el;

			el = el.parentElement || el.parentNode;
		} while ( el !== null && el.nodeType === 1 );

		return null;
	};
}

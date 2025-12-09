export function getGlobal( itemName ) {
	if ( null === itemName ) {
		return window.eddStripe;
	}

	if ( ! itemName in window.eddStripe ) {
		return '';
	}

	return window.eddStripe[itemName];
}

export function setGlobal( itemName, value, returnBack ) {
	window.eddStripe[itemName] = value;

	if ( returnBack ) {
		return window.eddStripe[itemName];
	}
}
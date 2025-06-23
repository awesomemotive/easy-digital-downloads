/* global SquareCheckout, edd_scripts, edd_square_vars, wp */

import { init } from './square.js'; // eslint-enable @wordpress/dependency-group

( () => {
	try {
		wp.domReady( init );
	} catch ( error ) {
		alert( error.message );
	}
} )();

/**
 * External dependencies
 */

// Import Polyfills for MicroModal IE 11 support.
// https://github.com/Ghosh/micromodal#ie-11-and-below
// https://github.com/ghosh/Micromodal/issues/49#issuecomment-424213347
// https://github.com/ghosh/Micromodal/issues/49#issuecomment-517916416
import 'core-js/modules/es.object.assign';
import 'core-js/modules/es.array.from';

import MicroModal from 'micromodal';

const DEFAULT_CONFIG = {
	disableScroll: true,
	awaitOpenAnimation: true,
	awaitCloseAnimation: true,
};

function setup( options ) {
	const config = {
		...DEFAULT_CONFIG,
		...options,
	};

	MicroModal.init( config );
}

function open( modalId, options ) {
	const config = {
		...DEFAULT_CONFIG,
		...options,
	};

	MicroModal.show( modalId, config );
}

function close( modalId ) {
	MicroModal.close( modalId );
}

export default {
	setup,
	open,
	close,
};

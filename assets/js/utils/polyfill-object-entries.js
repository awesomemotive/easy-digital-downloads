/// Polyfill Object.entries
// @link https://developer.mozilla.org/en-US/docs/Web/JavaScript/Reference/Global_Objects/Object/entries#Polyfill
if ( ! Object.entries ) {
	Object.entries = function( obj ) {
		var ownProps = Object.keys( obj ),
			i = ownProps.length,
			resArray = new Array( i ); // preallocate the Array

		while ( i-- ) {
			resArray[ i ] = [ ownProps[ i ], obj[ ownProps[ i ] ] ];
		}

		return resArray;
	};
}

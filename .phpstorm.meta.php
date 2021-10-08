<?php
namespace PHPSTORM_META {
	// Allow PhpStorm IDE to resolve return types when calling EDD( Object_Type::class ) or EDD( `Object_Type` ).
	override(
		\EDD( 0 ),
		map( [
			'' => '@',
			'' => '@Class',
		] )
	);
}

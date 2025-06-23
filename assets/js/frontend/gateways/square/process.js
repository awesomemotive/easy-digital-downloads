export async function tokenizeCard( verificationDetails ) {
	const tokenResult = await window.eddSquare.card.tokenize( verificationDetails );

	if ( tokenResult.status !== 'OK' ) {
		throw new Error( window.eddSquare.i18n.invalid_card );
	}

	return tokenResult.token;
}
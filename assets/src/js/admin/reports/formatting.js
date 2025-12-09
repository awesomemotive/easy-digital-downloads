
export var eddLabelFormatter = function( label, series ) {
	return '<div style="font-size:12px; text-align:center; padding:2px">' + label + '</div>';
};

export var eddLegendFormatterSales = function( label, series ) {
	const slug = label.toLowerCase().replace( /\s/g, '-' ),
		color = '<div class="edd-legend-color" style="background-color: ' + series.color + '"></div>',
		value = '<div class="edd-pie-legend-item">' + label + ': ' + Math.round( series.percent ) + '% (' + eddFormatNumber( series.data[ 0 ][ 1 ] ) + ')</div>',
		item = '<div id="' + series.edd_vars.id + slug + '" class="edd-legend-item-wrapper">' + color + value + '</div>';

	jQuery( '#edd-pie-legend-' + series.edd_vars.id ).append( item );

	return item;
};

export var eddLegendFormatterEarnings = function( label, series ) {
	const slug = label.toLowerCase().replace( /\s/g, '-' ),
		color = '<div class="edd-legend-color" style="background-color: ' + series.color + '"></div>',
		value = '<div class="edd-pie-legend-item">' + label + ': ' + Math.round( series.percent ) + '% (' + eddFormatCurrency( series.data[ 0 ][ 1 ] ) + ')</div>',
		item = '<div id="' + series.edd_vars.id + slug + '" class="edd-legend-item-wrapper">' + color + value + '</div>';

	jQuery( '#edd-pie-legend-' + series.edd_vars.id ).append( item );

	return item;
};

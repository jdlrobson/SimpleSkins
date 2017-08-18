$( function () {
	$( 'nav .toggle' ).next( 'ul' ).hide();
	$( 'nav .toggle' ).on( 'click', function () {
		$( this ).toggleClass( 'open' ).next( 'ul' ).toggle();
	} );
} );

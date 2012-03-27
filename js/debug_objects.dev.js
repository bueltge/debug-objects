function toggle(obj) {
	var el = document.getElementById(obj);
	if ( el.style.display != 'block' ) {
		el.style.display = 'block';
	} else {
		el.style.display = 'none';
	}
}

( function( $ ) {
	
	$(function() {
		$( '#debugobjectstabs' ).tabs( {
			cache: true,
			cookie: { expires: 7 }
		} );
	} );
	
	$( '#wpadminbar' ).click( function() {
		$( 'html, body' ).animate( { scrollTop: 0 }, 100 );
	} );
	$( '#wpadminbar li' ).click( function( e ) { 
		e.stopPropagation(); 
	} );
	
} )( jQuery );

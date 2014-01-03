/**
 * Start the html inspector
 * 
 * @since   01/03/2014
 */

var html_errors = [];

HTMLInspector.inspect( {
	domRoot: "html",
	useRules: null,
	excludeRules: null,
	excludeElements: ["svg", "#debugobjects"],
	excludeSubTrees: ["svg", "iframe", "#debugobjects"],
	onComplete: function(errors) {
		errors.forEach( function(error){
			// report errors to external service...
			console.warn( error.message, error.context );
			var obj = {
				message: error.message,
				context: error.context
			};
			html_errors.push( obj );
			//document.getElementById( '#debugobjects' ).innerHTML = '<ol><li>html data</li></ol>';
			//JSON.stringify(error.message);
		} );
	}
} );

/**
 * Convert special characters to HTML entities
 * 
 * @param {Object} content
 */
function htmlspecialchars( content ) {
	
	content = content.replace(/&/g, "&amp;").replace(/>/g, "&gt;").replace(/</g, "&lt;").replace(/"/g, "&quot;");
	
	return content;
}

/**
 * If element is ready then add html with each error message
 */
( function( $ ) {
	
	$(function() {
		
		var html = '';
		for ( var key in html_errors ) {
			
			if ( html_errors.hasOwnProperty( key ) ) {
				//console.log( html_errors[key]['message'] );
				html += '<li>' + htmlspecialchars( html_errors[key]['message'] );
				
				for ( var context in html_errors[key]['context'] ){
					if ( context === 'outerHTML' ) {
						//console.log( html_errors[key]['context']['outerHTML'] );
						html += '<br><code>' + htmlspecialchars( html_errors[key]['context']['outerHTML'] ) + '</code>';
					}
				}
				
				html += '</li>';
			}
			
		}
		
		$( '#debugobjects #htmlinspector h4' ).after( '<ol>' + html + '</ol>' );
	});

} )( jQuery );

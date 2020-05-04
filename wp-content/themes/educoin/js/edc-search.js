( function ( $, root, undefined ) {
	
	$( function () {
		
		'use strict';
		
        $( document ).ready( function() {

            // funkstsyia otsylki Ajax zaprosa
            function edcMakingSearchRequest( searchTerm ) {

                // getting data to send
                var dataToSend = {
                    search_query: searchTerm,
                    search_type: 'google'
                };
                dataToSend = JSON.stringify( dataToSend );

                $.ajax({
                    url: ajaxUrl,
                    type: 'POST',
                    timeout: 5000,
                    dataType: 'html',
                    data: {
                      action: 'edc_making_search_request',
                      data: dataToSend
                    },
                    success: function( response ) {
                      // doing something
                    }
                });
            }

            
            // list event of sending search form of google search
            $( 'form.gsc-search-box button.gsc-search-button' ).live( 'click', function() {
              var form = $( this ).closest( 'form.gsc-search-box' );
              var searchTerm = $.trim( $( 'input.gsc-input' ).val() );
              
              // make Ajax request
              edcMakingSearchRequest(searchTerm);
            })
            // list event of sending search form of google search
            $( 'form.gsc-search-box input.gsc-input' ).live( 'keyup', function( e ) {
              if( e.which == 13 ) { // 13 = 'enter' key pressed
                var searchTerm = $.trim( this.value );
                
              // make Ajax request
              edcMakingSearchRequest(searchTerm);
              }
            });


        } );
      
    } );

} )( jQuery, this );
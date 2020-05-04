( function ( $, root, undefined ) {
	
	$( function () {
		
		'use strict';
		
        $( document ).ready( function() {

            /* ZAPUSKAEM TAIMER */ 
            var $timer = $( '.timer' );
            var endDate = new Date( $timer.data( 'time-year-to' ), $timer.data( 'time-month-to' ) - 1, $timer.data( 'time-day-to' ), $timer.data( 'time-hour-to' ), $timer.data( 'time-minute-to' ), $timer.data( 'time-second-to' ) );

            $( '.timer' ).countdown( {
                date: endDate,
                render: function( data ) {
                    $( this.el ).html( "<span class='timer__digits'>" + this.leadingZeros( data.days, 2 ) + "</span>д:<span class='timer__digits'>" + this.leadingZeros( data.hours, 2 ) + "</span>ч:<span class='timer__digits'>" + this.leadingZeros( data.min, 2 ) + "</span>м:<span class='timer__digits'>" + this.leadingZeros( data.sec, 2 ) + "</span>с" );
                }
            } );

        } );
      
    } );

} )( jQuery, this );
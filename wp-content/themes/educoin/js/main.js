( function ( $, root, undefined ) {
	
	$( function () {
		
		'use strict';
        
        // funkstsyia otsylki Ajax zaprosa na obnovlenie informatsyi o
        // krainem vremeni poseshchenii saita
        function edcAnalyticsSaveUserLastVisitDate() {
        
            // getting data to send
            var dataToSend = {
                user_last_visit_date: Cookies.get( 'edc_auth_user_last_visit_date' )
            };
            dataToSend = JSON.stringify( dataToSend );
            
            $.ajax({
                url: ajaxUrl,
                type: 'POST',
                timeout: 5000,
                dataType: 'html',
                data: {
                  action: 'edc_analytics_save_user_last_visit_date',
                  data: dataToSend
                },
                success: function( response ) {
                      
                   var data = JSON.parse( response );
                  
                   // is last visit date info updated?
                   if ( data.isUpdated ) {
                      Cookies.set( 'edc_auth_user_last_visit_date', data.time )
                   }
                }
            });
        }
        
        
        
        
        $( document ).ready( function() {

			// get current course ID
            var currentCourseId = getCurrentCourseId();

            var readMoreBasicConfig = {
                speed: 100,
                collapsedHeight: 0,
                moreLink: '<a href="#">Раскрыть</a>', 
                lessLink: '<a href="#">Свернуть</a>',
            }
            
            // ----------- initialize readmore blocks
            var readMoreConfig = readMoreBasicConfig;
            readMoreConfig['afterToggle'] = function(trigger, element, expanded) {

                // is element just closed?
                if( !expanded ) {

                    var introElement;
                    var introElementId;

                    // get first main block related to current readmore-block
                    introElement = $( element ).parent().find('> :first-child');
                    introElementId = $( introElement ).attr( "id" );

                    // check whether element do not have Id attribute
                    if ( isEmpty( introElementId ) ) {
                        // generate random Id for element
                        introElementId = "id" + Math.random().toString( 36 ).substring( 2, 15 );
                        // set Id in element
                        $( introElement ).attr( "id", introElementId );
                    }

                    // scroll to top of the intro block
                    goToElem( introElementId, 0 );
                }
            }
            // create readmore blocks
            $( '.readmore' ).readmore( readMoreConfig );
            
            // -------- initialize readmore block for course extra info section on course page
            var readMoreConfig = readMoreBasicConfig;
            readMoreConfig['afterToggle'] = function(trigger, element, expanded) {
                
                // is element just closed?
                if( !expanded ) {
					setCourseExtraInfoIsClosedStatus( true );
                } else {
					setCourseExtraInfoIsClosedStatus( false );
                }
            };
            
            var courseExtraInfoIsClosedStatus = getCourseExtraInfoIsClosedStatus();
            
            if ( !courseExtraInfoIsClosedStatus || ( courseExtraInfoIsClosedStatus === '' ) ) {
                readMoreConfig['startOpen'] = true;
            } else {
                readMoreConfig['startOpen'] = false;
            }
            // create readmore block for course extra info section on course page
            $( '.readmore_course-extra-info' ).readmore( readMoreConfig );
            
			
            // Initialize anchors scrolling
            initAnchrorsScrolling();
            
            // there is should be some type button which should be pressed twisely at least in order to
            // confirm the current action
            $( '.edc-double-confirm-btn' ).click( function() {
                var checkbox = $( this ).find( 'input[type=checkbox]' );
                if ( $( checkbox ).is( ':checked' ) ) {
                    return true;
                } else {
                    $( checkbox ).attr( 'checked', 'checked' );
                    return false;
                }
            })
            
            // disable ability to click on submit button twisely, we should disable it after 
            // clicking on it
            $( 'form' ).on( 'submit', function() {
                $( '#main-overlay' ).fadeIn( 200 );
            });
            
            // should we make comfortable reading?
            if ( $( '.comfortable-reading-mode' ).length ) {
                makeComfortableReading();
            }
            
            // add tag to video in HotJar in order to have ability of quick filtering by user's Id
            hj( 'tagRecording', ['EdcUserId' + php_currentUser.id] );
            
            // enable bootsrtap's popovers 
            $( '[data-toggle="popover"]' ).popover( { 'html': true } );

            // autohide temp alert messages with Notification type
            setTimeout( 
                function() {
                    $( '.alert.alert-temp.alert-primary' ).fadeOut( 500 )
                },
                3000 )
            // show temp and permanent alert messages with Error and Warning type. We use this
            // in order to make messages more perceptible.
            setTimeout( 
                function() {
                    $( '.alert.alert-warning, .alert.alert-danger' ).fadeIn( 500 )
                },
                500 )
            // hide alert message if users clicked on it
            $( '.alert[role="alert"]' ).on( 'click', function() {
                $( this ).fadeOut( 500 )
            } )

            // define client's timezone offset
            defineClientTimezoneOffset();

            // create tooltips
            $( '[data-toggle="tooltip"]' ).tooltip();
            
            /* otsylka Ajax zaprosa na obnovlenie informatsyi o
               krainem vremeni poseshchenii saita */
            // set time how often we should send last visit date info
            var timeoutMsecSendUserLastVisitDate = 60 * 1000;
            // delaem otsylku periodichecki po taimautu
            var timerUserLastVisitDate = new RecurringTimer(
                function() {

                    edcAnalyticsSaveUserLastVisitDate();
                },
                timeoutMsecSendUserLastVisitDate );
            // Mojet byt' situatsyia kogda okno so stranitsei svernuto, a 
            // takje fokus nahoditsia v okne konsoli brauzera.
            // Poetomu vmesto 'blur' i 'focus' na ob'ekte Window, my
            // ispol'zuem eto sobytie.
            $( window ).on( 'visibilitychange', function() {
                
                // browser tab is active? 
                if ( document.hidden ) {
                    // pause timer
                    timerUserLastVisitDate.pause();
                } else {
                    // resume timer
                    timerUserLastVisitDate.resume();
                    
                    // chelovek ne vsegda budet na stranitse hotia on mojet tem ne menee rabotat'
                    // s domashkoi
                    edcAnalyticsSaveUserLastVisitDate();
                }

            });
            
            // should we expand all reamore_blocks/accordions?
            if ( isAllItemsShoudBeExpaned() ) {
                // expand all of the reamore blocks
                $( '.readmore' ).readmore( 'toggle' );
            }

        } );
      
    } );

} )( jQuery, this );
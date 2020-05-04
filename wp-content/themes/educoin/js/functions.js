/**
  * Smaller represantation of function 'console.log()'
  */ 
function l( val ) {
    console.log( val )
}

/**
  * Smaller represantation of function 'console.dir()'
  */ 
function d( val ) {
    console.dir( val )
}

/**
  * Go to defined url
  */
function goToUrl( url ) {
    location.href = url
}

/**
  * Go to certain element on the page
  */
function goToElem( elemId, speed = 400 ) {

    // define total height of fixed headers
    var topOffset = 0;
    topOffset += ( $('#wpadminbar').length ) ? $('#wpadminbar').outerHeight() : 0;
    topOffset += ( $('nav.navbar').length ) ? $('nav.navbar').outerHeight() : 0;
    topOffset += 10;
    
    // scroll to needed element
    $( 'html, body' ).animate( {
        scrollTop: parseInt( $( '#' + elemId ).offset().top - topOffset )
    }, speed );
}

/**
  * Check whether variable is empty
  */
function isEmpty( e ) {
  switch (e) {
    case "":
    case 0:
    case "0":
    case null:
    case false:
    case undefined:
      return true;
    default:
      return false;
  }
}

function setCurrentCourseId( id ) {

    Cookies.set( 'edc_current_course_id', id );
}

function getCurrentCourseId() {

    return Cookies.get( 'edc_current_course_id' );
}

/**
  * It is function for strings. It replaces every occurence of 'find'
  * and replaces by 'replace'
  */
function replaceAll( originalString, find, replace ) {

    return originalString.replace( new RegExp( find, 'g' ), replace );
}
       
/** !!! It is a part of getting to know client timezone routine,
  * so it is not independent function.
  * Standartnoye polucheniye TimeZoneOffset ne rabotayet, tak kak zona 
  * mozhet byt' u cheloveka ustanovlena ne pravil'no, no yego vremya kotoroye 
  * u nego na chasakh v komp'yutere - vsegda vernoye. Poetomu zadacha takaya 
  * chto nuzhno poluchit' raznitsu mezhdu vremenem kliyenta i servernym vremenem.
  * Dannyy kod ne imeet smysla refaktorit' tak kak po drugomu on rabotat'
  * n budet
  */
function defineClientTimezoneOffset() {
         
    if ( Cookies.get( 'edc_tz_timezone_offset_minutes') === undefined ) {

        // get server date from cookies, this value is set by server
        var serverDate = Cookies.get( 'edc_tz_server_date' );
        
        // make decoding of special chars
        serverDate = replaceAll( serverDate, '__s__', ' ' );
        serverDate = replaceAll( serverDate, '__dts__', ':' );
        
        // create server date object
        serverDate = new Date( serverDate ).getTime();

        var options = {
        
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: 'numeric',
            minute: 'numeric'
        };
        var localDateObj = new Date();
        var localDateStr = localDateObj.toLocaleString( "en", options );
            localDateStr = new Date( localDateStr );
        var localDate = new Date( localDateStr ).getTime();
        
        var dateDifferenceMinutes = ( localDate - serverDate ) / 60 / 1000;
        
        // save timezone to cookies
        var in1Hour = 1 / 24;     // 1 day DIV 24 hours = 1 hour
        Cookies.set( 
            'edc_tz_timezone_offset_minutes', 
            dateDifferenceMinutes, 
            {expires: in1Hour} 
        );
    }
}

/** Tt helps to make setInterval: pause/resume
  * Example:
  * 
  * var timer = new RecurringTimer( function() {
  *     alert( "Done!" );
  * }, 1000 );
  *
  * timer.pause();   
  * timer.resume();  
  */
function RecurringTimer( callback, delay ) {

    var timerId, start, remaining = delay;

    this.pause = function() {
    
        window.clearTimeout( timerId );
        remaining -= new Date() - start;
    };

    var resume = function() {
    
        start = new Date();
        timerId = window.setTimeout( function() {
        
            remaining = delay;
            resume();
            callback();
        }, remaining);
    };

    this.resume = resume;

    this.resume();
}   
            
/** It helps to make setTimeout: pause/resume
  * Example:
  *
  * var timer = new Timer( function() {
  *     alert( "Done!" );
  * }, 1000 );
  *
  * timer.pause();   
  * timer.resume();  
  */
function Timer( callback, delay ) {

    var timerId, start, remaining = delay;

    this.pause = function() {
    
        window.clearTimeout( timerId );
        remaining -= new Date() - start;
    };

    this.resume = function() {
    
        start = new Date();
        window.clearTimeout(timerId);
        timerId = window.setTimeout( callback, remaining );
    };

    this.resume();
}

/** 
  * Initialize anchors scrolling
  */
function initAnchrorsScrolling() {

    // set duration of anchor highlighting
    var anchorHighlightingDuration = 5000;

    // make scrolling after timeout because it can scroll when the page is not rendered yet
    setTimeout( 
        function() {
            // Fix browser bug when trying to show anchored block on the page, because
            // in case we have some fixed headers on the top, then it ignores top offset.
            // Get hash value from URL, to define whether we have anchor.
            var pageAnchor = window.location.hash.substr( 1 );
            if ( pageAnchor != "" ) {
                // go to anchored block
                goToElem( pageAnchor );

                // make anchor highlighting
                $( '#' + pageAnchor ).addClass( 'active' );
                setTimeout( function(){$( '#' + pageAnchor ).removeClass( 'active' )}, anchorHighlightingDuration );
            }
        },
        1000 
    )

    // when user clicks on anchor then scroll top to it
    $( '.main-content .anchor' ).on( 'click', function() {
        // get element ID
        var elemId = $( this ).attr( 'id' );
        // scroll to element
        goToElem( elemId );
        
        return false;
    } )
    
    // when user clicks on link with acnhor then scroll top to it
    // when click on '<a href="#637">ссылка</a>' then scroll to its anchor
    $( 'a[href^=#]:not([aria-expanded])' ).on( 'click', function() {
        // get element ID
        var elemId = $( this ).attr( 'href' ).slice( 1 );
        // scroll to element
        goToElem( elemId );
        // make anchor highlighting
        $( '#' + elemId ).addClass( 'active' );
        setTimeout( function(){$( '#' + elemId ).removeClass( 'active' )}, anchorHighlightingDuration );

        return false;
    } )
}

/** 
  * Make comfortable reading
  */
function makeComfortableReading() {

    // mark each even Paragraph in order to make reading some large part of text more
    // comfortable
    var paragraphs = [];
    $( '.comfortable-reading-mode .main-content p' ).each(function() {

        if ( $( this ).html() == '&nbsp;' ) {
            return;
        } else if ( $( this ).html() == '' ) {
            return;
        }
        // add paragraph to array
        paragraphs.push( this );
    })
    for ( var i = 0; i < paragraphs.length; i++ ) {
        // is even index?
        if ( i % 2 == 0 ) {
            $( paragraphs[i] ).addClass( 'even-node-comfortable-reading' );
        }
    }
}

/**
  * Get parameter from URL
  */
function getUrlParam( name ) {
    return ( location.search.split( name + '=' )[1] || '' ).split( '&' )[0];
}

/**
  * Is all items should be expanded on the page?
  */
function isAllItemsShoudBeExpaned() {
    return !isEmpty( getUrlParam( 'is_all_expand' ) );
}

/**
  * It is used for setting status of extra info block on course page: is it should be closed or opened
  */
function setCourseExtraInfoIsClosedStatus( status ) {

	var statusStr = JSON.stringify( status );

	// set status to cookies
	Cookies.set( 'edc_auth_course_extra_info_is_closed_status', statusStr );
}

/**
  * It is used for getting status of extra info block on course page: is it should be closed or opened
  */
function getCourseExtraInfoIsClosedStatus() {
	
	var courseExtraInfoIsClosedStatusStr = Cookies.get( 'edc_auth_course_extra_info_is_closed_status' );
	
	if ( isEmpty( courseExtraInfoIsClosedStatusStr ) ) return '';
	
	var courseExtraInfoIsClosedStatus = JSON.parse( courseExtraInfoIsClosedStatusStr );
	
	return courseExtraInfoIsClosedStatus;
}

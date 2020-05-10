f<?php

    global $tmpl_vars;
            
    // get timezones
    $server_timezone = date_default_timezone_get();
    $client_timezone = edc_get_client_timezone();
    
    // set timezone to client's timezone
    date_default_timezone_set( $client_timezone );

    // create date array for timer of webinar providing 
    $providingDateArray['d'] = date( 'd', $tmpl_vars['date_providing'] );
    $providingDateArray['m'] = date( 'm', $tmpl_vars['date_providing'] );
    $providingDateArray['Y'] = date( 'Y', $tmpl_vars['date_providing'] );
    $providingDateArray['H'] = date( 'H', $tmpl_vars['date_providing'] );
    $providingDateArray['i'] = date( 'i', $tmpl_vars['date_providing'] );
    $providingDateArray['s'] = date( 's', $tmpl_vars['date_providing'] );
    
    // get html code of timer
    $timer_html = "<span class='timer' data-time-day-to='{$providingDateArray['d']}' data-time-month-to='{$providingDateArray['m']}' data-time-year-to='{$providingDateArray['Y']}' data-time-hour-to='{$providingDateArray['H']}' data-time-minute-to='{$providingDateArray['i']}' data-time-second-to='{$providingDateArray['s']}'><span class='timer__digits'>00</span>д:<span class='timer__digits'>01</span>ч:<span class='timer__digits'>01</span>м:<span class='timer__digits'>01</span>с</span>";
    
    // return back server timezone
    date_default_timezone_set( $server_timezone );
?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=$tmpl_vars['title']?></h2>
            <br>
            
            <h3 class="subtitle"><?=__( 'Описание', TEXTDOMAIN )?></h3>
            <hr class="subtitle-devider">
           
            <p class="text-center">До открытия урока осталось: <?=$timer_html?></p> 
                
        </div>
    </div>
        
</div>

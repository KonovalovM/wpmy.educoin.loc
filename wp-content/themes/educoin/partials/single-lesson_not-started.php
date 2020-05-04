<?php

    global $tmpl_vars;
    
    // generate webinar URL for analytics system
    $params = [
        'task' => 'user_attended_webinar',
        'lesson_id' => $tmpl_vars['lesson_id']
    ];
    $analytics_webinar_url = edc_generate_url_analytics( $tmpl_vars['webinar_url'], $params );
    
    // set when show webinar url before lesson starts
    $show_webinar_url_before_mins = 15;
    
    // define when we should show webinar URL
    $date_show_webinar_url = $tmpl_vars['date_providing'] - ( $show_webinar_url_before_mins * 60 );
    // get current time
    $cur_date = time();
    
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
           
            <?php if( $date_show_webinar_url > $cur_date ) { ?>
               
                <p class="text-center">Здесь, за <?=$show_webinar_url_before_mins?> мин до урока будет ссылка на вебинар.</p>
                <p class="text-center">А на следующий день, тут будет его видеозапись с описанием и домашним заданием.</p>
                <p class="text-center">До начала осталось: <?=$timer_html?></p> 
                
            <?php } else { ?>
               
                <p class="text-center">До начала осталось: <?=$timer_html?></p> 
                <p class="text-center">Чтобы перейти к вебинару, кликни <a href="<?=$analytics_webinar_url?>" target="_blank">здесь</a>.</p>
                
            <?php } ?>
            
            
        </div>
    </div>
        
</div>
<?php

    global $tmpl_vars;
    
    // get tomorrow date
    $date_providing = strtotime( date( 'd.m.Y 00:00:00',  $tmpl_vars['date_providing'] ) );
    $one_day = 24 * 60 * 60;
    $tomorrow_date = edc_date_show_in_client_tz( $date_providing + $one_day, edc_get_short_date_format() );
?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=$tmpl_vars['title']?></h2>
            <br>
            
            <h3 class="subtitle"><?=__( 'Описание', TEXTDOMAIN )?></h3>
            <hr class="subtitle-devider">
           
            <p class="text-center">Видеозапись урока, его описание и домашние задания будут тут <?=$tomorrow_date?> утром.</p>
               
        </div>
    </div>
        
</div>
<?php /* Template Name: Admin section -> Events */ ?>

<?php

    // getting calendar
    // can user view calendar for organizators?
    if ( current_user_can( 'edc_view_calendar_organizators' ) ) {
        // get calendar from general settings page
        $calendar = get_field( 'edc_calendar_organizators', 395 );
    } 
    // can user view calendar for team?
    else if ( current_user_can( 'edc_view_calendar_team' ) ) {
        // get calendar from general settings page
        $calendar = get_field( 'edc_calendar_team', 395 );
    } else {
        $calendar = '';
    }
    
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=get_the_title( 700 )?></h2>
            <br>
               
            <div class="calendar-cont"><?=$calendar?></div>
                
        </div>
    </div>
        
</div>

<?php get_footer() ?>
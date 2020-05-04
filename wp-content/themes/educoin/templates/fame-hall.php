<?php /* Template Name: Hall of fame */ ?>

<?php

    // get page's ID
    $page_id = get_the_id();
    
    // get ratings data for current month
    $start_date_month = strtotime( date( '15.10.2019 00:00:00' ) );
    $end_date_month = strtotime( date( '31.10.2019 23:59:59' ) );
    $ratings_month = edc_get_supports_rating_table_data( $start_date_month, $end_date_month );    
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=get_the_title( $page_id )?></h2>
            <br>

            <p class="text-center"><?=edc__( 'Скоро будет...' )?></p>
        </div>
    </div>
        
</div>

<?php get_footer() ?>
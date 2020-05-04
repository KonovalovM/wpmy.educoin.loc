<?php

    $categ = get_term( edc_get_current_course_id() );

    $items_args = [
        'post_type' => 'additional_article',
        $categ->taxonomy => $categ->slug,
    ];
    $items_query = new WP_Query( $items_args );
    $items = $items_query->posts;

?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>


            <h2><?=__( 'Вспомогательные статьи', TEXTDOMAIN )?></h2>

            <?php 
                foreach ( $items as $item ) { 

                    // read whether article is closed for viewing
                    $is_demo_post_closed = get_field( 'edc_is_demo_post_closed', $item->ID );
            ?>
                   
                    <?php // is this article is demo-article and is closed for viewing? ?>
                    <?php if ( edc_is_demo_lesson( $item->ID ) && get_field( 'edc_is_demo_post_closed', $item->ID ) ) { ?>
                            
                        <a href="#" data-toggle="modal" data-target="#demo_modal_warning_popup"><?=$item->post_title; ?></a>
                        <br>
                    <?php } else { ?>
                       
                        <a href="<?=get_permalink( $item->ID )?>"><?=$item->post_title; ?></a>
                        <br>
                    <?php }?>
                   
            <?php 
                } 
            ?>

        </div>
    </div>
</div>

<?php get_footer() ?>

<!-- Insert demo modal popup for demo lesson -->
<?php if ( edc_is_demo_lesson( get_the_id() ) ) { echo edc_get_modal_popup_demo_warning(); }?>
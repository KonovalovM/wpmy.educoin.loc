<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title">Занятие: "<?php echo get_the_title(); ?>"</h2>
            <br>

            <?php echo edc_get_post_content( get_the_id() ); ?>
            
        </div>
    </div>
        
</div>


<?php get_footer() ?>
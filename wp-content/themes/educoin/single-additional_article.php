<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=get_the_title()?></h2>
            <br>

            <?=edc_get_post_content( get_the_id() )?>
            
        </div>
    </div>
        
</div>

<?php get_footer() ?>

<!-- Insert demo modal popup for demo lesson -->
<?php if ( edc_is_demo_lesson( get_the_id() ) ) { echo edc_get_modal_popup_demo_warning(); }?>
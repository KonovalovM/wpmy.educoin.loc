<?php /* Template Name: Contacts */ ?>


<?php 

    // get page's ID
    $page_id = get_the_id();
    // get lesson content
    $page_content = edc_get_post_content( $page_id );
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=get_the_title( $page_id )?></h2>
            <br>

            <?=$page_content?>
            
        </div>
    </div>
        
</div>


<?php get_footer() ?>
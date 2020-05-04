<?php
    // get the page ID
    $page_id = get_the_id();
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=get_the_title()?></h2>

            <br>
            <br>

            <?=edc_get_post_content( $page_id )?>
            
        </div>
    </div>
        
</div>

<?php get_footer() ?>
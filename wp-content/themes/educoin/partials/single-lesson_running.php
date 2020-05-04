<?php

    global $tmpl_vars;

    // generate webinar URL for analytics system
    $params = [
        'task' => 'user_attended_webinar',
        'lesson_id' => $tmpl_vars['lesson_id']
    ];
    $analytics_webinar_url = edc_generate_url_analytics( $tmpl_vars['webinar_url'], $params );
?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=$tmpl_vars['title']?></h2>
            <br>
            
            <h3 class="subtitle"><?=__( 'Описание', TEXTDOMAIN )?></h3>
            <hr class="subtitle-devider">
           
            <p class="text-center">Вебинар уже идет. Поторопись!</p>
            <p class="text-center">Чтобы перейти к просмотру, кликни <a href="<?=$analytics_webinar_url?>" target="_blank">здесь</a>.</p>
               
        </div>
    </div>
        
</div>
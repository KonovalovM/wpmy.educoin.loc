<?php

    global $tmpl_vars;
?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <?php /* Are we in preview mode? */ ?>
            <?php if ( $tmpl_vars['is_preview_mode'] ) { ?>
                <h2 class="page-title"><?=edc__( 'РЕЖИМ ПРЕДПРОСМОТРА' )?></h2>
            <?php } ?>
           
            <h2 class="page-title"><?=$tmpl_vars['title']?></h2>
            
            <?php if ( !empty( $tmpl_vars['lesson_description'] ) ) { ?>
                <p class="lesson-description"><?=$tmpl_vars['lesson_description']?></p>
            <?php } ?>
            <br>
            
            <div class="videos">
                <?php foreach ( $tmpl_vars['videos'] as $video ) { ?>
                    <div class="video-player-cont"><?=$video?></div>
                <?php } ?>
            </div>
            
            <!-- show minilessons -->
            <?php if ( count( $tmpl_vars['minilessons'] ) ) { ?>
            
                <br>
                <br>
            
                <?php 
                    $minilessons_accordion_items = [];
                    foreach ( $tmpl_vars['minilessons'] as $key => $minilesson ) { 

                        // get minilesson's content
                        $minilesson_content = html_entity_decode( $minilesson['content'] );
                        $minilesson_content = apply_filters('the_content', $minilesson_content );

                        $minilesson_accordion_item = [];
                        // set title for course accordion
                        $minilesson_accordion_item['title'] = edc__( 'Миниурок' ) . ' №' . ($key+1) . ' "' . $minilesson['title'] . '"';
                        // set content for course accordion
                        $minilesson_accordion_item['content'] = '<div class="video-player-cont">' . $minilesson['video'] . '</div><br><br><br>' . $minilesson_content;
                        // should the item be opened?
                        $minilesson_accordion_item['is_expanded'] = edc_is_all_items_shoud_be_expaned();

                        // add accordion item data to main accordion data variable
                        $minilessons_accordion_items[] = $minilesson_accordion_item;
                    } 
                ?>

                <!-- show accordion -->
                <?=edc_html_generate_accordion( $minilessons_accordion_items )?>
                    
                <div class="form-row justify-content-center">
                    <div class="form-group col-xs-3 text-center">
                       
                        <button type="button" class="btn btn-success mb-4 mt-4" data-toggle="modal" data-target="#demo_modal_warning_popup"><?=edc__( 'Задать вопрос' )?></button>
                        <button type="button" class="btn btn-primary mb-4 mt-4" data-toggle="modal" data-target="#demo_modal_warning_popup"><?=edc__( 'Запросить звонок от саппорта' )?></button>
                        <button type="button" class="btn btn-primary mb-4 mt-4" data-toggle="modal" data-target="#demo_modal_warning_popup"><?=edc__( 'Добавиться в чат к ученикам' )?></button>

                    </div>
                </div>
                
                
            <?php } ?>
              
            <br>
            <br>
            <?=$tmpl_vars['lesson_content']?>
            
            <!-- Content for supports -->
            <?php if ( ( current_user_can( 'edc_view_lesson_supports_materials_area' ) ) && 
                       !empty( $tmpl_vars['lesson_content_for_supports'] ) ) { ?>
            
                <br>
                <br>          
                <h3 class="subtitle"><?=edc__( 'Материалы для саппортов' )?></h3>
                <hr class="subtitle-devider">
                <?=html_entity_decode( $tmpl_vars['lesson_content_for_supports'] )?> 
            
            <?php } ?>
  
        </div>
    </div>
        
</div>


<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <br>
            <br>
            <h3 class="subtitle"><?=edc__( 'Проверка' )?></h3>
            <hr class="subtitle-devider">

            <form class="" action="<?=get_permalink()?>" method="post">

                <div class="form-row">
                    <div class="form-group col-md-12">
                       
                        <?php 
                        
                            // define whether text editor should be disabled
                            if ( $tmpl_vars['lesson_progress']['is_need_to_check'] || 
                                 !empty( $tmpl_vars['lesson_progress']['is_accepted'] ) ||
                                 ( $tmpl_vars['passing_status'] == 'closed' ) ) {
                                
                                $disabled = 'disabled';
                            
                            } else {
                            
                                $disabled = '';
                            }
                        ?>
                       
                        <label for="homework"><?=edc__( 'Пропиши здесь результаты выполненных задач:' )?></label>
                        <div class="homework-cont">
                            <textarea id="homework" class="advanced form-control" id="homework" rows="5" name="homework" <?=$disabled?>><?=html_entity_decode( $tmpl_vars['lesson_progress']['homework'] )?></textarea>                            
                        </div>
                    </div>
                </div>

                <div class="text-center">
                   
                    <?php 
                    
                        // is lesson closed?
                        if ( $tmpl_vars['passing_status'] == 'closed'  ) {
                    ?>
                            <button type="submit" name="btn_send_for_check" class="btn btn-secondary mb-4 mt-4 mx-auto" disabled><?=edc__( 'Урок закрыт' )?></button>
                    <?php 
                        } 
                        // is lesson not closed?
                        else {
                    ?>
                            <button type="button" class="btn btn-primary mb-4 mt-4" data-toggle="modal" data-target="#demo_modal_warning_popup"><?=edc__( 'Выслать на проверку' )?></button>
                    <?php 
                        }                    
                    ?>

                </div>

            </form>
        </div>  
    </div>
</div>

<?php

    // fill template variables with appropriate data
    $tmpl_vars['user'] = $user;
    $tmpl_vars['is_show_intro_info'] = false;
    
    // define should we show opened accordion with homeworks history
    $collapse_class = ( $tmpl_vars['is_open_homeworks_history'] ) ? 'show' : '';
?>
<!-- homeworks history anchor -->
<a id="hh"></a>
<div id="accordion" role="tablist">

    <div class="card">
        <div class="card-header" role="tab" id="heading1">
            <h5 class="mb-0">
                <a data-toggle="collapse" href="#collapse1" aria-expanded="true" aria-controls="collapse1"><?=edc__( 'История проверок домашних заданий' )?></a>
            </h5>
        </div>

        <div id="collapse1" class="collapse <?=$collapse_class?>" role="tabpanel" aria-labelledby="heading1" data-parent="#accordion">
            <div class="card-body">

            <!-- show template with homeworks history -->
            <?php get_template_part( 'partials/lesson_homeworks-history' ) ?>
            
            </div>
        </div>
    </div>

</div>

<!-- Insert demo modal popup for demo lesson -->
<?=edc_get_modal_popup_demo_warning()?>
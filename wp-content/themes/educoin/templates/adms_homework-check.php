<?php /* Template Name: Admin section -> Homework check */ ?>

<?php

    // get allowed courses to which current user is assigned
    $allowed_courses = edc_get_user_course_lists( 0, true, 0, true );

    $query_params = [];
    
    // do we have allowed courser?
    if ( !empty( $allowed_courses ) ) {
        $query_params['course_id'] = array_column( $allowed_courses, 'term_id' );
    } else {
        $query_params['course_id'] = [ 0 ];
    }

    // get homeworks list to check
    $homeworks_list = edc_get_homeworks_for_check( $query_params );
    $current_user_id = get_current_user_id();
    
    // get current checking homework
    $current_checking_homework = edc_get_user_current_checking_homework(); 
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=edc__( 'Проверка домашних заданий' )?></h2>
            <br>
            
            <?php 
              // do current user already checking some homework?
              if ( !empty( $current_checking_homework ) ) { 
              
                  $homework = $current_checking_homework;

                  $unique_id = edc_generate_unique_id( 8 );
                
                  // get category ID
                  $post_terms = wp_get_post_terms( $homework['lesson_id'], 'course' );
                  $category_id = $post_terms[0]->term_id;

                  // get support of the user
                  $support = edc_get_user_current_support( $category_id, $homework['user_id'] );

                  // get lesson's category name
                  $category_name = get_term( $category_id )->name;

                  // get date of request for checking homework
                  $date_of_request = edc_date_show_in_client_tz( strtotime( $homework['sent_for_checking_date'] ) );

                  // get user name
                  $user_name = get_userdata($homework['user_id'])->display_name;

                  // get checker name
                  $checker_name = get_userdata($homework['checker_id'])->display_name;

                  // get lesson's number
                  $lesson_num = get_field( 'edc_lesson_order_num', $homework['lesson_id'] );

                  // get quantity of checking requests
                  $qnty_check_request = edc_get_user_lesson_homeworks_attemps_quantity( $homework['lesson_id'], $homework['user_id'] );

                  // get the status of homework
                  $homework_status = edc_get_homework_status( $homework['homework_id'] );

                  // check whether user have low priority
                  $is_user_low_priority = edc_get_user_courses( $homework['user_id'], $category_id )['is_low_priority'];

                  // forming accordion title
                  $accordion_title = "{$date_of_request}";
                  if ( $is_user_low_priority ) {
                      $accordion_title = edc__( 'Низкий приоритет' ) . " - " . $accordion_title;
                  }
                  // do user have support?
                  if ( !empty( $support ) ) {
                      $accordion_title = edc__( 'Куратор' ) . ' ' . $support['support_name'] . " - " . $accordion_title . " - " . $user_name;
                  } else {
                      $accordion_title = edc__( 'Без куратора' ) . " - " . $accordion_title;
                  }
            ?>
            
                  <p><?=edc__( 'Текущая проверяемая работа' )?></p>
                  <div id="accordion_<?=$unique_id?>" role="tablist">

                      <div class="card border-primary">
                        <div class="card-header" role="tab" id="heading_<?=$unique_id?>_1">
                          <h5 class="mb-0">
                            <a data-toggle="collapse" href="#collapse_<?=$unique_id?>_1" aria-expanded="true" aria-controls="collapse_<?=$unique_id?>_1">
                              <?=$accordion_title?>
                            </a>
                          </h5>
                        </div>

                        <div id="collapse_<?=$unique_id?>_1" class="collapse show" role="tabpanel" aria-labelledby="heading_<?=$unique_id?>_1" data-parent="#accordion_<?=$unique_id?>">
                          <div class="card-body">

                            <?php if ( !empty( $support ) ) { ?>
                                <?=edc__( 'Куратор' )?>: <?=$support['support_name']?>
                                <br>
                            <?php } ?>
                            
                            <?=edc__( 'Пользователь' )?>: <a href="<?=get_permalink( 54 ) . '?id=' . $homework['user_id']?>" target="_blank"><?=$homework['user_login']?> (<?=$user_name?>)</a>
                            <br><br>                            
                            
                            <!-- show course name -->
                            <?=edc__( 'Курс' )?> "<?=$category_name?>"<br>
                            <?=edc__( 'Урок' )?> №<?=$lesson_num?>: <a href="<?=get_permalink( $homework['lesson_id'] )?>" target="_blank">"<?=get_the_title( $homework['lesson_id'] )?>"</a><br>
                            <?=edc__( 'Попытка' )?>: №<?=$qnty_check_request?><br>
                            <?=edc__( 'История домашних заданий' )?>: <a href="<?=get_permalink( 54 ) . '?id=' . $homework['user_id']?>&task=homeworks_history&lesson_id=<?=$homework['lesson_id']?>" target="_blank"><?=edc__( 'ссылка' )?></a>
                            <br>
                            <br>
                            <?=edc__( 'Текст' )?>:<br>
                            <?=html_entity_decode( $homework['homework'] )?>
                            <br>
                            <br>

                            <form class="" action="<?=get_permalink()?>" method="post">
                                <input type="hidden" name="task" value="homework">
                                <input type="hidden" name="action" value="checking">
                                <input type="hidden" name="lesson_id" value="<?=$homework['lesson_id']?>">
                                <input type="hidden" name="user_id" value="<?=$homework['user_id']?>">

                                <p><?=edc__( 'Детальная обратная связь по работе ученика (видна ученику в уроке).' )?> <a data-toggle="tooltip" title="<?=edc__( 'Конструктивно хвалим когда принимаем работу и подбадриваем когда отклоняем работу.' )?>" href="" onclick="return false;"><?=edc__( 'Детали' )?></a>:</p>
                                <textarea class="form-control advanced" rows="5" name="checker_comments"></textarea>
                                <hr>
                                
                                <br>
                                <p><?=edc__( 'Краткая (до 1000 символов) обратная связь (для мессенжеров).' )?> <a data-toggle="tooltip" title="<?=edc__( 'Конструктивно хвалим когда принимаем работу и подбадриваем когда отклоняем работу.' )?>" href="" onclick="return false;"><?=edc__( 'Детали' )?></a>:</p>
                                <p>
                                  <span data-toggle="tooltip" title="<?=edc__( 'Палец вверх' )?>">&nbsp;👍</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Подмигивание' )?>">&nbsp;😉</span>
                                  <span data-toggle="tooltip" title="<?=edc__( 'Улыбка' )?>">&nbsp;😊</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Смех до слез' )?>">&nbsp;😂</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Бум' )?>">&nbsp;💥</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Круть' )?>">&nbsp;🤘</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Огонь' )?>">&nbsp;🔥</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Хлопушка с конфети' )?>">&nbsp;🎉</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Шар из конфети' )?>">&nbsp;🎊</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Танцор' )?>">&nbsp;🕺</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Хлопки в ладоши' )?>">&nbsp;👏</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Бицепс' )?>">&nbsp;💪</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Секундомер' )?>">&nbsp;⏱</span> 
                                  <span data-toggle="tooltip" title="<?=edc__( 'Звезда' )?>">&nbsp;⭐&nbsp;</span> 
                                </p>
                                <textarea class="form-control" minlength="10" maxlength="1000" required rows="5" name="checker_comments_short"></textarea>
                                <br>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="is_send_to_group_chat_checker_comments_short"  value="1" checked>
                                    <label class="form-check-label"><?=edc__( 'Дублировать в чат группы с учениками? Высылается лишь в случае когда мы засчитываем работу.' )?></label>
                                </div>
                                <hr>
                                
                                <br>
                                <p><?=edc__( 'Обратная связь по ученику и его работе (отображается преподавательской команде).' )?> <a data-toggle="tooltip" title="<?=edc__( 'Тут мы пишем свою обратную связь только по данному конкретному ученику и только за период данной конкретной сделанной работы. Тут мы можем озвучить например были ли с ним какие-то трудности, может он лентяй, может он постоянно ноет и не нацелен на то чтобы стать программистом, а постоянно выпрашивает готовые прямые ответы, может с ним еще какие-то проблемы были при выполнение данной домашней работы. Но может быть и такое что это ученик с которым было приятно работать, который старался, который не искал отговорки, а выкладывался на максимум при выполнении данной домашней работы. В общем все это детально описываем если есть что сказать.' )?>" href="" onclick="return false;"><?=edc__( 'Детали' )?></a>:</p>
                                <textarea class="form-control" minlength="10" required rows="5" name="checker_comments_for_teachers"></textarea>
                                <br>

                                <button type="submit" name="btn_accept" class="btn btn-success btn-sm edc-double-confirm-btn"><input type="checkbox"/><?=edc__( 'Принять' )?></button>
                                <button type="submit" name="btn_decline" class="btn btn-danger btn-sm"><?=edc__( 'Отклонить' )?></button>
                            </form>


                          </div>
                        </div>
                      </div>

                    </div>
                    <br>
                    <br>

            <?php 
              } 
              // there is no any already checking homework
              else { 
            ?>
                
                  <p><?=edc__( 'Курсы по которым ты можешь проверять домашние задания' )?>:</p>
                  <?php 
                      // do we have allowed courses for current user?
                      if ( !empty( $allowed_courses ) ) {
                  ?>
                          <div class="readmore">
                            <ul>
                  <?php
                          foreach ( $allowed_courses as $allowed_course ) {
                  ?>
                              <li><?=$allowed_course->name?></li>
                  <?php
                          }
                  ?>
                            </ul>
                          </div>
                          <br>
                  <?php 
                      } else {
                  ?>
                          <ul>
                              <li><?=edc__( 'Список пуст' )?></li>
                          </ul>
                  <?php
                      }
                  ?>
                
                
                  <p><?=edc__( 'Приступить к проверке следующей работы' )?>:</p>
                  <form action="<?=get_permalink()?>" method="post">
                      <input type="hidden" name="task" value="homework">
                      <input type="hidden" name="action" value="take_next_homework">
                  
                      <?php if ( !empty( edc_get_next_homework_for_checking() ) ) { ?>
                          <button type="submit" name="btn_start_checking" class="btn btn-success btn-sm"><?=edc__( 'Приступить' )?></button>
                      <?php } else { ?>
                          <button type="button" name="btn_start_checking" class="btn btn-secondary btn-sm" data-toggle="tooltip" title="<?=edc__( 'Отсутсвуют работы на проверку' )?>"><?=edc__( 'Приступить' )?></button>
                      <?php } ?>
                  </form>
                  <br><br><br>
            <?php 
              }
            ?>
            
            
            
            <!-- show list -->
            <?php if ( !empty( $homeworks_list ) ) { ?>
               
                <?php 
                    // groupping homeworks by their category
                    $homeworks_by_groups = [];
                    foreach ( $homeworks_list as $key => $homework ) { 

                        // do not show here homework that is already checking because we
                        // showing it upper
                        // does current user alredy check this homework?
                        if ( !empty( $current_checking_homework ) && 
                             $current_checking_homework['homework_id'] == $homework['homework_id'] ) {
                            continue;
                        }

                        // get category ID
                        $post_terms = wp_get_post_terms( $homework['lesson_id'], 'course' );
                        $category_id = $post_terms[0]->term_id;

                        $homeworks_by_groups[$category_id][] = $homework;
                    }

                    foreach ( $homeworks_by_groups as $category_id => $homeworks ) {
                    
                        // get lesson's category name
                        $category_name = get_term( $category_id )->name;
                ?>
                        
                        <!-- show course name -->
                        <p><?=edc__( 'Курс' )?> "<?=$category_name?>"</p>
                        <div id="accordion_<?=$category_id?>" role="tablist">

                            <?php
                                foreach ( $homeworks as $key => $homework ) { 

                                    // get date of request for checking homework
                                    $date_of_request = edc_date_show_in_client_tz( strtotime( $homework['sent_for_checking_date'] ) );

                                    // get support of the user
                                    $support = edc_get_user_current_support( $category_id, $homework['user_id'] );

                                    // get user name
                                    $user_name = get_userdata($homework['user_id'])->display_name;
                                    
                                    // get checker name
                                    $checker_name = get_userdata($homework['checker_id'])->display_name;
                                    
                                    // get lesson's number
                                    $lesson_num = get_field( 'edc_lesson_order_num', $homework['lesson_id'] );
                                    
                                    // get quantity of checking requests
                                    $qnty_check_request = edc_get_user_lesson_homeworks_attemps_quantity( $homework['lesson_id'], $homework['user_id'] );
                                    
                                    // get the status of homework
                                    $homework_status = edc_get_homework_status( $homework['homework_id'] );
                                    
                                    // define whether user can take this homework for checking
                                    $is_user_can_take_homework_for_checking = edc_is_support_can_take_homework_for_checking( $homework['user_id'], $homework['lesson_id'] );
                                    
                                    // define card color
                                    $card_class = 'border-success';
                                    if ( $homework_status == 'checking' ) $card_class = 'border-primary';
                                    if ( !$is_user_can_take_homework_for_checking['result'] ) $card_class = 'border-secondary';
                                    
                                    // check whether user have low priority
                                    $is_user_low_priority = edc_get_user_courses( $homework['user_id'], $category_id )['is_low_priority'];
                                    
                                    // forming accordion title
                                    $accordion_title = "{$date_of_request}";
                                    if ( $is_user_low_priority ) {
                                        $accordion_title = edc__( 'Низкий приоритет' ) . " - " . $accordion_title;
                                    }
                                    // do user have support?
                                    if ( !empty( $support ) ) {
                                        $accordion_title = edc__( 'Куратор' ) . ' ' . $support['support_name'] . " - " . $accordion_title . " - " . $user_name;
                                    } else {
                                        $accordion_title = edc__( 'Без куратора' ) . " - " . $accordion_title;
                                    }
                            ?>

                                    <div class="card <?=$card_class?>">
                                      <div class="card-header" role="tab" id="heading_<?=$category_id?>_<?=$key?>">
                                        <h5 class="mb-0">
                                          <a data-toggle="collapse" href="#collapse_<?=$category_id?>_<?=$key?>" aria-expanded="true" aria-controls="collapse_<?=$category_id?>_<?=$key?>">
                                            <?=$accordion_title?>
                                          </a>
                                        </h5>
                                      </div>

                                      <div id="collapse_<?=$category_id?>_<?=$key?>" class="collapse" role="tabpanel" aria-labelledby="heading_<?=$category_id?>_<?=$key?>" data-parent="#accordion_<?=$category_id?>">
                                        <div class="card-body">
                                          
                                          <?php // is homework is not taken for checking? ?>
                                          <?php if ( $homework_status != 'checking' ) { ?>
                                                            
                                              <?php if ( !$is_user_can_take_homework_for_checking['result'] ) { ?>       
                                                      <?php if ( !empty( $support ) ) { ?>
                                                         
                                                          <?=edc__( 'Куратор' )?>: <?=$support['support_name']?>
                                                          <br>

                                                          <?=edc__( 'Пользователь' )?>: <a href="<?=get_permalink( 54 ) . '?id=' . $homework['user_id']?>" target="_blank">
                                                          <?=$homework['user_login']?> (<?=$user_name?>)</a><br><br>

                                                          <?=edc__( 'Урок' )?> №<?=$lesson_num?>: <a href="<?=get_permalink( $homework['lesson_id'] )?>" target="_blank">"<?=get_the_title( $homework['lesson_id'] )?>"</a><br>

                                                          <?=edc__( 'Попытка' )?>: №<?=$qnty_check_request?><br>
                                                          <?=edc__( 'История домашних заданий' )?>: <a href="<?=get_permalink( 54 ) . '?id=' . $homework['user_id']?>&task=homeworks_history&lesson_id=<?=$homework['lesson_id']?>" target="_blank"><?=edc__( 'ссылка' )?></a>
                                                          <br>
                                                          <br>
                                                          
                                                      <?php } ?>

                                                      <button type="submit" name="btn_start_checking" class="btn btn-secondary btn-sm" data-toggle="tooltip" title="<?=$is_user_can_take_homework_for_checking['message']?>"><?=edc__( 'Приступить к проверке' )?></button>
                                              
                                              <?php } else { ?>                         

                                                  <form class="" action="<?=get_permalink()?>" method="post">
                                                      <input type="hidden" name="task" value="homework">
                                                      <input type="hidden" name="action" value="checking">
                                                      <input type="hidden" name="lesson_id" value="<?=$homework['lesson_id']?>">
                                                      <input type="hidden" name="user_id" value="<?=$homework['user_id']?>">
                                                      
                                                      <?php if ( !empty( $support ) ) { ?>
                                                         
                                                          <?=edc__( 'Куратор' )?>: <?=$support['support_name']?>
                                                          <br>

                                                          <?=edc__( 'Пользователь' )?>: <a href="<?=get_permalink( 54 ) . '?id=' . $homework['user_id']?>" target="_blank">
                                                          <?=$homework['user_login']?> (<?=$user_name?>)</a><br><br>

                                                          <?=edc__( 'Урок' )?> №<?=$lesson_num?>: <a href="<?=get_permalink( $homework['lesson_id'] )?>" target="_blank">"<?=get_the_title( $homework['lesson_id'] )?>"</a><br>

                                                          <?=edc__( 'Попытка' )?>: №<?=$qnty_check_request?><br>
                                                          <?=edc__( 'История домашних заданий' )?>: <a href="<?=get_permalink( 54 ) . '?id=' . $homework['user_id']?>&task=homeworks_history&lesson_id=<?=$homework['lesson_id']?>" target="_blank"><?=edc__( 'ссылка' )?></a>
                                                          <br>
                                                          <br>
                                                          
                                                      <?php } ?>

                                                      <button type="submit" name="btn_start_checking" class="btn btn-success btn-sm"><?=edc__( 'Приступить к проверке' )?></button>
                                                  </form>   
                                                  
                                              <?php } ?>      
                                              
                                          <?php } else { ?>
                                          

                                              <?php if ( !empty( $support ) ) { ?>
                                                  <?=edc__( 'Куратор' )?>: <?=$support['support_name']?>
                                                  <br>
                                              <?php } ?>

                                              <?=edc__( 'Пользователь' )?>: <a href="<?=get_permalink( 54 ) . '?id=' . $homework['user_id']?>" target="_blank">
                                              <?=$homework['user_login']?> (<?=$user_name?>)</a><br><br>
                                              
                                              <?=edc__( 'Урок' )?> №<?=$lesson_num?>: <a href="<?=get_permalink( $homework['lesson_id'] )?>" target="_blank">"<?=get_the_title( $homework['lesson_id'] )?>"</a><br>
                                             
                                              <?=edc__( 'Попытка' )?>: №<?=$qnty_check_request?><br>
                                              <?=edc__( 'История домашних заданий' )?>: <a href="<?=get_permalink( 54 ) . '?id=' . $homework['user_id']?>&task=homeworks_history&lesson_id=<?=$homework['lesson_id']?>" target="_blank"><?=edc__( 'ссылка' )?></a>
                                              <br>
                                              <br>
                                              <?=edc__( 'Текст' )?>:<br>
                                              <?=html_entity_decode( $homework['homework'] )?>
                                              <br>
                                              <br>
                                          
                                              <?php // is current user is not current homework's checker? ?>
                                              <?php if ( $homework['checker_id'] != $current_user_id )  {?>
                                                  <a class="btn btn-secondary btn-sm" href="#" role="button" disabled><?=edc__( 'Проверяет' )?> <?=$checker_name?></a>
                                              <?php } ?>
                                          
                                          <?php } ?>

                                        </div>
                                      </div>
                                    </div>

                            <?php 
                                } 
                            ?>

                        </div>
                        <br>
                        <br>
                
                <?php
                    }
                ?>

                
            <?php } else { ?>
               
                <p class="text-center"><?=edc__( 'Список для проверки пуст.' )?></p>
                
            <?php } ?>
       
        </div>
    </div>
        
</div>


<?php get_footer() ?>
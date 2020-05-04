<?php

    global $tmpl_vars;
?>

<h4 class="page-title"><?=edc__( 'Действия' )?></h4>
<br>
<p><?=edc__( 'Курсы' )?></p>

<!-- Add user to course form -->
<form class="form-inline" action="<?=get_permalink(); ?>" method="post">

  <input type="hidden" name="task" value="user">
  <input type="hidden" name="action" value="add_course">
  <input type="hidden" name="user_id" value="<?=$tmpl_vars['user_id']?>">

  <div class="form-group mb-2 mr-sm-2">
    <select class="form-control form-control-sm mb-2" name="course_id">
      <option value="0"><?=edc__( 'Выберите курс' )?></option>
      <?php 
          // get courses
          $courses = edc_get_courses( false );

          // get user's courses
          $user_courses = edc_get_user_courses( $tmpl_vars['user_id'] );
          $user_courses_ids = array_column( $user_courses, 'course_id' );

          foreach ( $courses as $course ) { 

              // should we disable the selection of it in the list?
              if ( in_array( $course->term_id, $user_courses_ids ) ) {
                  $disabled = 'disabled';
              } else {
                  $disabled = '';
              };

      ?>
              <option <?=$disabled?> value="<?=$course->term_id?>"><?=$course->name?></option>
      <?php 
          } 
      ?>
    </select>
  </div>
  <div class="form-group mb-2">
    <button type="submit" class="btn btn-primary btn-sm mb-2"><?=edc__( 'Добавить' )?></button>
  </div>

</form>

  <?php 
      $accordion_items = [];
      foreach ( $user_courses as $key => $user_course ) { 

          // get user course disabling status
          $course_disabling_status = edc_get_user_course_disabling_status( $tmpl_vars['user_id'], $user_course['course_id'] );

          // is course ended?
          if ( edc_is_course_ended( $user_course['course_id'], $tmpl_vars['user_id'] ) ) {
              $is_course_inactive = true;
              $status_text = edc__( 'Неактивен' );

              // getting course end date
              // get user's course date end
              $course_date_end = $user_course['course_date_end'];
              // in case user's course date end is not set then we should get
              // general course date end
              if ( empty( $course_date_end ) ) {
                  $course_date_end = get_field( 'edc_course_date_end', 'course_' . $user_course['course_id']  );
              }

              $course_date_end_in_client_tz = edc_date_show_in_client_tz( strtotime( $course_date_end ) );

              $course_inactiveness_description = sprintf( 
                  edc__( 'Курс закончился %s' ), 
                  $course_date_end_in_client_tz );
          }
          // is course disabled for user?
          else if ( $course_disabling_status['is_disabled'] ) {
              $is_course_inactive = true;
              $status_text = edc__( 'Неактивен' );
              $course_inactiveness_description = $course_disabling_status['message'];
          }
          // course is active
          else {
              $is_course_inactive = false;
              $status_text = edc__( 'Активен' );
          }

          $accordion_item = [];
          // set title for course accordion
          $accordion_item['title'] = $status_text . ' - ' . edc__( 'Курс' ) . ' &quot;' . $user_course['course_title'] . '&quot;';
          // should the item be opened?
          $accordion_item['is_expanded'] = false;

          // get current support
          $cur_support = edc_get_user_current_support( $user_course['course_id'], $tmpl_vars['user_id'] );

          // get start course date
          $course_date_start = ( !empty( $user_course['course_date_start'] ) )? date( 'd.m.Y', strtotime( $user_course['course_date_start'] ) ) : '';

          // get disable access date
          $course_date_end = ( !empty( $user_course['course_date_end'] ) )? date( 'd.m.Y', strtotime( $user_course['course_date_end'] ) ) : '';

          // get user course certificate
          $query_arr = [
              'course_id' => $user_course['course_id'],
              'user_id' => $tmpl_vars['user_id'],
          ];
          $user_course_cert = edc_get_user_course_certificates( $query_arr )[0];

          // getting accordion item content
          ob_start();     
  ?>

            <!-- Update course data -->
            <form action="<?=get_permalink()?>" method="post">
              <input type="hidden" name="task" value="user">
              <input type="hidden" name="action" value="update_course">
              <input type="hidden" name="user_id" value="<?=$tmpl_vars['user_id']?>">
              <input type="hidden" name="course_id" value="<?=$user_course['course_id']?>">

              <table class="table table-responsive-lg table-hover">
                  <thead>

                      <tr>
                          <th scope="col" class="w-p-20">Поле</th>
                          <th scope="col" class=""><?=edc__( 'Детали' )?></th>
                      </tr>

                  </thead>
                  <tbody>

                      <?php if ( $is_course_inactive ) { ?>
                          <th scope="row" class="w-p-20"><?=edc__( 'Причина неактивности курса' )?></th>
                          <td class="">
                              <?=$course_inactiveness_description?>
                          </td>
                      <?php } ?>

                      <tr>
                          <th scope="row" class="w-p-20"><?=edc__( 'Роль' )?></th>
                          <td class="">

                            <!-- Get list of roles -->
                            <select name="role" class="form-control form-control-sm">
                                <?php 
                                    // forming list of roles
                                    $course_roles = edc_get_list_of_course_roles();  
                                    
                                    // forming 'Select' HTML element
                                    foreach ( $course_roles as $course_role ) { 
                                        $selected = ( $user_course['role'] == $course_role ) ? 'selected' : ''; 
                                ?>
                                        <option value="<?=$course_role?>" <?=$selected?>><?=edc_get_translate_user_role( $course_role )?></option>
                                <?php 
                                    }
                                ?>
                            </select>
                          </td>
                      </tr>
                      <tr>
                          <th scope="row" class="w-p-20"><?=edc__( 'Куратор' )?></th>
                          <td class="">

                            <!-- Get list of the supports -->
                            <select name="support_id" class="form-control form-control-sm">
                                <option value=""><?=edc__( 'Выбери куратора' )?></option>
                                <?php 
                                    // get list of the supports
                                    $supports = edc_get_users(
                                      [
                                        'role' => edc_get_checkers_homeworks_roles(),
                                        'course_id' => [ $user_course['course_id'] ],
                                      ]
                                    );  
                                                                        
                                    foreach ( $supports as $support ) { 
                                        $selected = ( $support['ID'] == $cur_support['support_id'] ) ? 'selected' : ''; 
                                ?>
                                        <option value="<?=$support['ID']?>" <?=$selected?>><?="{$support['ID']} - {$support['display_name']}"?></option>
                                <?php 
                                    }
                                ?>
                            </select>
                          </td>
                      </tr>
                      <tr>
                          <th scope="row" class="w-p-20"><?=edc__( 'Статус по курсу' )?></th>
                          <td class="">

                            <!-- Get list of course statuses -->
                            <select name="course_to_user_status_id" class="form-control form-control-sm mb-2">
                                <?php 
                                    // get list of the course statuses
                                    $course_statuses = edc_get_course_statuses();
                                    // get user's course info
                                    $user_course = edc_get_user_courses( $tmpl_vars['user_id'], $user_course['course_id'] );  

                                    foreach ( $course_statuses as $course_status ) { 
                                        $selected = ( $user_course['course_to_user_status_id'] == $course_status['id'] ) ? 'selected' : '';     
                                ?>
                                        <option value="<?=$course_status['id']?>" <?=$selected?>><?=$course_status['title']?></option>
                                <?php 
                                    }
                                ?>
                            </select>

                            <input type="text" class="form-control form-control-sm" value="<?=$user_course['disabling_reason']?>" placeholder="<?=edc__( 'Причина деактивации...' )?>" name="disabling_reason" />
                          </td>
                      </tr>
                      <tr>
                          <th scope="row" class="w-p-20"><?=edc__( 'Дата начала (открытия доступа) курса' )?></th>
                          <td class="">
                            <input type="text" class="form-control form-control-sm" value="<?=$course_date_start?>" placeholder="<?=edc__( 'Дата начала курса...' )?>" name="course_date_start" />
                          </td>
                      </tr>
                      <tr>
                          <th scope="row" class="w-p-20"><?=edc__( 'Дата окончания курса' )?></th>
                          <td class="">
                            <input type="text" class="form-control form-control-sm" value="<?=$course_date_end?>" placeholder="<?=edc__( 'Дата окончания курса...' )?>" name="course_date_end" />
                          </td>
                      </tr>
                      <tr>
                          <th scope="row" class="w-p-20"><?=edc__( 'Отключены проверки ДЗ?' )?></th>
                          <td class="">

                            <?php
                              $checked = ( $user_course['is_homeworks_disabled'] ) ? 'checked' : '';
                            ?>
                            <div class="form-check">
                              <input <?=$checked?> type="checkbox" class="form-check-input" name="is_homeworks_disabled" value="1">
                            </div>

                          </td>
                      </tr>
                      <tr>
                          <th scope="row" class="w-p-20"><?=edc__( 'Демо режим?' )?></th>
                          <td class="">

                            <?php
                              $checked = ( $user_course['is_demo_mode'] ) ? 'checked' : '';
                            ?>
                            <div class="form-check">
                              <input <?=$checked?> type="checkbox" class="form-check-input" name="is_demo_mode" value="1">
                            </div>

                          </td>
                      </tr>
                      <tr>
                          <th scope="row" class="w-p-20"><?=edc__( 'Низкий приоритет?' )?> <a href="#" title="<?=edc__( 'Как правило это бесплатники. Их работы будут проверяться в последнюю очередь и т.д.' )?>" data-toggle="tooltip">(?)</a></th>
                          <td class="">

                            <?php
                              $checked = ( $user_course['is_low_priority'] ) ? 'checked' : '';
                            ?>
                            <div class="form-check">
                              <input <?=$checked?> type="checkbox" class="form-check-input" name="is_low_priority" value="1">
                            </div>

                          </td>
                      </tr>
                      <tr>
                          <th scope="row" class="w-p-20"><?=edc__( 'Комментарии идущие в сертификат ученика' )?></th>
                          <td class="">
                                <textarea class="form-control advanced" rows="5" name="cert_comments"><?=html_entity_decode( $user_course_cert['comments'] )?></textarea>
                          </td>
                      </tr>
                  </tbody>   
              </table>

              <br>
              <button type="submit" class="btn btn-primary btn-sm"><?=edc__( 'Сохранить' )?></button>
            </form>

  <?php
          // get accordion item content
          $accordion_item['content'] = ob_get_contents();
          ob_end_clean();

          // add accordion item data to main accordion data variable
          $accordion_items[] = $accordion_item;
      } 
  ?>

  <!-- show accordion -->
  <?=edc_html_generate_accordion( $accordion_items )?>
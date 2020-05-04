<?php /* Template Name: Admin section -> Students */ ?>

<?php

    // get task
    $task = ( isset( $_REQUEST['task'] ) ) ? sanitize_key( $_REQUEST['task'] ) : '';

    // get course
    $course_id = ( isset( $_GET['course_id'] ) ) ? intval( $_GET['course_id'] ) : 0;
    // get support
    $support_id = ( isset( $_GET['support_id'] ) ) ? intval( $_GET['support_id'] ) : 0;
    // get students status
    $students_status = ( isset( $_GET['students_status'] ) ) ? intval( $_GET['students_status'] ) : 0;
    
    // forming filters for students request
    $filters = [];
    if ( !empty( $course_id ) ) {
        $filters['course_id'] = [ $course_id ];
    }
    if ( !empty( $support_id ) ) {
        $filters['support_id'] = $support_id;
    }
    if ( !empty( $students_status ) ) {
        $filters['students_status'] = $students_status;
    }
    
    // define whether we should show students from all of the courses
    if ( current_user_can( 'edc_view_all_students_stats_on_students_page' ) ) {
        // get all courses
        $courses = edc_get_courses( false );
    } else {
        // get courses to which user is assigned
        $courses = edc_get_user_course_lists( 0, true );
        
        // making ability to show students from all available courses
        if ( empty( $course_id ) ) {
            $filters['course_id'] = array_column( $courses, 'term_id' );
        }
    }

    // get students
    $students = edc_filter_students( $filters );

    if ( $course_id ) {
        // get lessons amount
        $qnty_lessons = count( edc_get_course_lessons_to_take( $course_id ) );
        $qnty_webinars = edc_get_course_webinars_quantity( $course_id );
        
        // get course category information
        $course_categ_info = get_term( $course_id );
        $course_docs_for_supports = get_field( 'edc_course_docs_for_supports', $course_categ_info->taxonomy . '_' . $course_id  );
        
        // transform course category information to array
        $course_docs_for_supports_arr = [];
        if ( !empty( $course_docs_for_supports ) ) {
            $course_docs_for_supports_arr = explode( '<br />', $course_docs_for_supports );
            foreach ( $course_docs_for_supports_arr as $key => $doc ) {
                $course_docs_for_supports_arr[$key] = trim( $doc );
            }
        }
        
    } 
    
    // fill template variables with appropriate data
    global $tmpl_vars;
    
    // define which of the templates we should show
    switch ( true ) {
    
        // should we show visiting pages history?
        case ( $task === 'visiting_pages_history' ):

            // define for which courses we should get homeworks history
            if ( !empty( $course_id ) ) {    
            
                $allowed_courses_ids = array_column( $courses, 'term_id' );
                
                if ( in_array( $course_id, $allowed_courses_ids ) ) {
                    $tmpl_vars['course_id'] = [ $course_id ];
                } else {
                    $tmpl_vars['course_id'] = $allowed_courses_ids;
                }
             
            } else {
                $allowed_courses_ids = array_column( $courses, 'term_id' );
                $tmpl_vars['course_id'] = $allowed_courses_ids;
            }

            // get list of users for which we need to get their visiting pages history
            $query_params = [
                'course_id' => $tmpl_vars['course_id'],
            ];
            $users = edc_get_users( $query_params );
            $tmpl_vars['user_id'] = array_column( $users, 'ID' );

            // set template name
            $tmpl = 'partials/adms-students_visiting-pages-history';
            break;
    
        // should we show homeworks history?
        case ( current_user_can( 'edc_view_stack_recent_checked_homeworks' ) && 
               ( $task === 'homeworks_history' ) ):          
              
            $tmpl_vars['is_show_checker_comments_for_teachers'] = true;            
            $tmpl_vars['is_show_checker_comments_short'] = true;     
            
            // define for which courses we should get homeworks history
            if ( !empty( $course_id ) ) {    
            
                $allowed_courses_ids = array_column( $courses, 'term_id' );
                
                if ( in_array( $course_id, $allowed_courses_ids ) ) {
                    $tmpl_vars['course_id'] = [ $course_id ];
                } else {
                    $tmpl_vars['course_id'] = $allowed_courses_ids;
                }
             
            } else {
                $allowed_courses_ids = array_column( $courses, 'term_id' );
                $tmpl_vars['course_id'] = $allowed_courses_ids;
            }
            
            // set template name
            $tmpl = 'partials/adms-students_homeworks-history';
            break;
    }
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=__( 'Ученики', TEXTDOMAIN )?></h2>
            <br>

            <form class="form-inline filters" action="<?php echo get_permalink(); ?>" method="get">

                    <div class="input-group mb-2 mr-sm-2">
                        <select class="form-control form-control-sm" name="course_id">
                            <option value="0"><?=__( 'Выберите курс', TEXTDOMAIN )?></option>
                            <?php 
                                foreach ( $courses as $course ) { 
                                    $selected = ( $course->term_id === $course_id ) ? 'selected' : '';
                            ?>
                                    <option value="<?=$course->term_id?>" <?=$selected?>><?=$course->name?></option>
                            <?php 
                                } 
                            ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary btn-sm mb-2 mr-sm-2"><?=__( 'Показать', TEXTDOMAIN )?></button>
                    
                    <br>
                
                    <div class="input-group mb-2 mr-sm-2">
                        <select class="form-control form-control-sm" name="support_id">
                            <option value="0"><?=edc__( 'Выбери куратора' )?></option>
                            <?php 
                                // get list of the supports
                                $supports = get_users( [ 'role__in' => edc_get_checkers_homeworks_roles() ] );  
                                foreach ( $supports as $support ) { 
                                    $selected = ( $support->ID == $support_id ) ? 'selected' : ''; 
                            ?>
                                    <option value="<?=$support->ID?>" <?=$selected?>><?="{$support->ID} - {$support->display_name}"?></option>
                            <?php 
                                }
                            ?>
                        </select>
                    </div>
                        
                    <div class="input-group mb-2 mr-sm-2">
                        <select class="form-control form-control-sm" name="students_status">
                            <option value="0" <?=($students_status==0)?'selected':'';?>><?=edc__( 'Выбери статус учеников' )?></option>
                            <option value="1" <?=($students_status==1)?'selected':'';?>><?=edc__( 'Действующие на данный момент' )?></option>
                        </select>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-sm mb-2 mr-sm-2"><?=edc__( 'Показать' )?></button>
                
            </form>
            
            <!-- show additional documents for supports -->
            <?php if ( $course_id && count( $course_docs_for_supports_arr ) ) { ?>
               
                <hr>
                <p><?=edc__( 'Вспомогательные документы' )?>:</p>
                <ul>
                  
                    <?php 
                        foreach ( $course_docs_for_supports_arr as $doc ) { 
                            
                            $doc_arr = explode( ';', $doc );
                      ?>
                   
                            <li>
                                <a href="<?=$doc_arr[1]?>" target="_blank"><?=$doc_arr[0]?></a>
                            </li>

                    <?php 
                        } 
                    ?>
                    
                </ul>
                <p>Дополнительные функции:</p>
                <button type="submit" class="btn btn-secondary btn-sm"><?=edc__( 'Рассылка' )?></button>
                <button type="submit" class="btn btn-secondary btn-sm"><?=edc__( 'Отфильтровать' )?></button>
                <br>
                <br>
                
            <?php } ?>
            
            
            <!-- show neccessary template if needed -->
            <?php if ( !empty( $students ) && !empty( $task ) ) { ?>
            
                <!-- show necessary template -->
                <?php get_template_part( $tmpl )?>
            
            <!-- show table with students -->
            <?php } else if ( !empty( $students ) ) { ?>
              
              <div class="table-responsive">
                <table class="table table-responsive-lg table-hover">
                  <thead>
                    <tr>
                      <th scope="col">#</th>
                      <th scope="col"><?=__( 'Логин', TEXTDOMAIN )?></th>
                      <th scope="col"><?=__( 'Имя', TEXTDOMAIN )?></th>
                      <th scope="col"><?=__( 'Дата крайнего визита', TEXTDOMAIN )?></th>

                      <?php if ( $course_id ) { ?>
                          <th scope="col"><?=__( 'Пройдено уроков', TEXTDOMAIN )?></th>
                      <?php } ?>
                      
                      <?php if ( $course_id ) { ?>
                          <th scope="col"><?=__( 'Куратор', TEXTDOMAIN )?></th>
                      <?php } ?>
                      
                      <?php if ( $course_id ) { ?>
                          <th scope="col"><?=__( 'Роль', TEXTDOMAIN )?></th>
                      <?php } else { ?>
                          <th scope="col"><?=__( 'Основная роль', TEXTDOMAIN )?></th>
                      <?php } ?>
                      
                      <?php if ( $course_id ) { ?>
                          <th scope="col"><?=__( 'Низкий приоритет?', TEXTDOMAIN )?></th>
                      <?php } ?>
                      
                      <th scope="col"><?=__( 'Email', TEXTDOMAIN )?></th>
                      <th scope="col"><?=__( 'Заблокирован?', TEXTDOMAIN )?></th>

                      <?php if ( $course_id ) { ?>
                          <th scope="col"><?=__( 'Деактивирован на курсе?', TEXTDOMAIN )?></th>
                      <?php } ?>

                    </tr>
                  </thead>
                  <tbody>
                   
                      <?php 
                          $i = 1;
                          foreach ( $students as $student ) { 
                          
                              $last_visit_date = $student->extended_info['last_visit_date'];
                              $last_visit_date = ( !empty( $last_visit_date ) ) ? edc_date_show_in_client_tz( strtotime( $last_visit_date ) ) : '';
                              
                              // define whether user is blocked
                              $is_user_blocked = edc_is_user_blocked( $student->ID );
                              $is_user_blocked_text = ( $is_user_blocked ) ? edc__( 'Да' ) : edc__( 'Нет' );
                              
                              if ( $course_id ) {
                                  // define whether user is deactivated on course
                                  $user_course_disabling_status = edc_get_user_course_disabling_status( 
                                      $student->ID,
                                      $course_id );
                                  $is_user_disabled_at_course_text = ( $user_course_disabling_status['is_disabled'] ) ? edc__( 'Да' ) : edc__( 'Нет' );
                              }
                              
                              if ( $course_id ) {
                                  // define whether user has low priority
                                  $is_user_low_priority = edc_get_user_courses( $student->ID, $course_id )['is_low_priority'];
                                  $is_user_low_priority_text = ( $is_user_low_priority ) ? edc__( 'Да' ) : edc__( 'Нет' );
                              }
                              
                              // should we mark row in table as a disabled?
                              $row_class = ( $is_user_blocked || $user_course_disabling_status['is_disabled'] || $is_user_low_priority ) ? 'blocked-user' : '';
                              
                              // getting name of the support
                              $support = edc_get_user_current_support( $course_id, $student->ID );
                              
                              // get user's role 
                              if ( $course_id ) {
                                  $user_role = edc_get_user_courses( $student->ID, $course_id )['role'];
                                  $user_role = edc_get_translate_user_role( $user_role );
                              } else {
                                  $user_role = edc_get_user_roles( $student->ID )[0]['title'];
                              }
                      ?>
                              <tr class="<?=$row_class?>">
                                  <th scope="row"><?=$i?></th>
                                  <td>
                                      <a href="<?=get_permalink( 54 ) . '?id=' . $student->ID?>"><?=$student->user_login?></a>
                                  </td>
                                  <td><?=$student->display_name?></td>
                                  <td><?=$last_visit_date?></td>

                                  <?php if ( $course_id ) { ?>
                                      <td><?=edc_get_user_passed_lessons_qnty( $student->ID, $course_id )?>/<?=$qnty_lessons?></td>
                                  <?php } ?>
                                  
                                  <?php if ( $course_id ) { ?>
                                      <td><?=$support['support_name']?></td>
                                  <?php } ?>
                                  
                                  <td><?=$user_role?></td>
                                  
                                  <?php if ( $course_id ) { ?>
                                      <td><?=$is_user_low_priority_text?></td>
                                  <?php } ?>
                                      
                                  <td><?=$student->user_email?></td>
                                  <td><?=$is_user_blocked_text?></td>
                                  
                                  <?php if ( $course_id ) { ?>
                                      <td><?=$is_user_disabled_at_course_text?></td>
                                  <?php } ?>
                                  
                              </tr>
                      <?php 
                              $i++;
                          } 
                      ?>
                    
                  </tbody>
                </table>
              </div>
                
            <?php } else { ?>
                     
                      <br>
                      <p class="text-center"><?=edc__( 'Ничего не найдено.' )?></p>

            <?php } ?>

           
            <?php if ( !empty( $students ) ) { ?>

              <h4 class="page-title"><?=__( 'Аналитика', TEXTDOMAIN )?></h4>
              <br>

              <ul>
                  <li>
                      <a href="<?=get_permalink() . '?task=visiting_pages_history' . '&' . $_SERVER['QUERY_STRING']?>"><?=edc__( 'Посещение страниц' )?></a>
                  </li>

                  <?php /* do current user has rights to see recent checked homeworks? */ ?>
                  <?php if ( current_user_can( 'edc_view_stack_recent_checked_homeworks' ) ) { ?>
                      <li>
                          <a href="<?=get_permalink() . '?task=homeworks_history' . '&' . $_SERVER['QUERY_STRING']?>"><?=edc__( 'Проверки домашних заданий' )?></a>
                      </li>
                  <?php } ?>

                  <li>
                      <a class="text-secondary" href="#"><?=edc__( 'Поисковые запросы' )?></a>
                  </li>
              </ul>

              <br>
            <?php } ?>
            
        </div>
    </div>
        
</div>


<?php get_footer() ?>
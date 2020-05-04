<?php

    global $tmpl_vars;
    
    // get coourses lists of the user
    $course_lists = edc_get_user_course_lists( $tmpl_vars['user']->ID, true, $tmpl_vars['course_id'] );

    $course_accordion_items = [];
    foreach ( $course_lists as $key => $course ) {
    
        // get passing information
        $user_passed_lessons_qnty = edc_get_user_passed_lessons_qnty( $tmpl_vars['user']->ID, $course->term_id );
        $qnty_lessons = count( edc_get_course_lessons_to_take( $course->term_id ) );
    
        $course_accordion_item = [];
        // set title for course accordion
        $course_accordion_item['title'] = sprintf(
            edc__( 'Курс "%s". Пройдено уроков: %s/%s.' ),
            $course->name,
            $user_passed_lessons_qnty,
            $qnty_lessons
        );  
        
        // get lessons lists for the course
        $lessons = edc_get_course_lessons( $course->term_id );
        
        $lesson_accordion_items = [];
        foreach ( $lessons as $key => $lesson ) {
        
            $lesson_accordion_item = [];
            
            // get homeworks history for the lesson
            $homeworks = edc_get_user_lesson_homeworks_history( $lesson->ID, $tmpl_vars['user']->ID );
            
            // get lesson providing status
            $lesson_providing_status = get_lesson_providing_status( $lesson->ID );
            
            // get lesson open status text
            $lesson_open_status_text = ( $lesson_providing_status == 'not_started' ) ? 
                edc__( 'Закрыт' ) :
                edc__( 'Открыт' );
            
            // get lesson status text
            if ( count( $homeworks ) ) {
            
                // get the status of recent homework
                $lesson_status = edc_get_homework_status( $homeworks[0]['homework_id'] );
                // getting description of the status
                $tmp_arr = [ 
                    'making' => __( 'Выполняется', TEXTDOMAIN ),
                    'waiting_for_check' => __( 'Ожидает проверки', TEXTDOMAIN ),
                    'checking' => __( 'Проверяется', TEXTDOMAIN ),
                    'declined' => __( 'Отклонено', TEXTDOMAIN ),
                    'accepted' => __( 'Принято', TEXTDOMAIN ),
                ];
                $lesson_status_text = ' - ' . $tmp_arr[$lesson_status];
            } else {
                $lesson_status_text = '';
            }
            
            // set title for lesson accordion
            $lesson_accordion_item['title'] = $lesson_open_status_text . ': ' . edc__( 'Урок' ) . ' №' . ($key+1) . ' "' . $lesson->post_title . '"' . $lesson_status_text;

            $lesson_accordion_item['content'] = '';
            foreach ( $homeworks as $key => $homework ) {
            
                // get the status of homework
                $status = edc_get_homework_status( $homework['homework_id'] );
                // getting description of the status
                $tmp_arr = [ 
                    'making' => __( 'Выполняется', TEXTDOMAIN ),
                    'waiting_for_check' => __( 'Ожидает проверки', TEXTDOMAIN ),
                    'checking' => __( 'Проверяется', TEXTDOMAIN ),
                    'declined' => __( 'Отклонено', TEXTDOMAIN ),
                    'accepted' => __( 'Принято', TEXTDOMAIN ),
                ];
                $status_text = $tmp_arr[$status];
                
                // get checker name
                $checker_name = get_userdata( $homework['checker_id'] )->display_name;
            
                // get checking attemp number
                $checking_attemp_num = count( $homeworks ) - $key;
                
                // get date of request for checking homework
                $sent_for_checking_date = edc_date_show_in_client_tz( strtotime( $homework['sent_for_checking_date'] ) );
                $start_date_checking = edc_date_show_in_client_tz( strtotime( $homework['start_date_checking'] ) );
                $end_date_checking = edc_date_show_in_client_tz( strtotime( $homework['end_date_checking'] ) );
            
                // getting homework item content
                ob_start();          
?>
            
                <?php if ( ( $status == 'accepted' ) || ( $status == 'declined' ) ) { ?>

                    <table class="table table-responsive-lg table-hover">
                        <thead>
                            <tr>
                                <th scope="col" class="w-p-20">Поле</th>
                                <th scope="col" class=""><?=__( 'Детали', TEXTDOMAIN )?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Проверял', TEXTDOMAIN )?></td>
                                <td class=""><?=$checker_name?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Попытка', TEXTDOMAIN )?></td>
                                <td class="">№<?=$checking_attemp_num?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Дата подачи', TEXTDOMAIN )?></td>
                                <td class=""><?=$sent_for_checking_date?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Дата начала проверки', TEXTDOMAIN )?></td>
                                <td class=""><?=$start_date_checking?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Дата конца проверки', TEXTDOMAIN )?></td>
                                <td class=""><?=$end_date_checking?></td>
                            </tr>
                            
                            <?php if ( $tmpl_vars['is_show_checker_comments_for_teachers'] ) { ?>
                              <tr>
                                  <td scope="row" class="w-p-20"><?=__( 'Комментарии проверяющего (для преподавательской команды)', TEXTDOMAIN )?></td>
                                  <td class=""><?=nl2br( $homework['checker_comments_for_teachers'] )?></td>
                              </tr>
                            <?php } ?>
                            
                            <?php if ( $tmpl_vars['is_show_checker_comments_short'] ) { ?>
                              <tr>
                                  <td scope="row" class="w-p-20"><?=__( 'Комментарии проверяющего', TEXTDOMAIN )?></td>
                                  <td class=""><?=nl2br( $homework['checker_comments_short'] )?></td>
                              </tr>
                            <?php } ?>
                            
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Детальные комментарии проверяющего', TEXTDOMAIN )?></td>
                                <td class=""><?=edc_legacy_get_showing_checker_comments( $homework['checker_comments'] )?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Проверяемое домашнее задание', TEXTDOMAIN )?></td>
                                <td class=""><?=html_entity_decode( $homework['homework'] )?></td>
                            </tr>
                        </tbody>   
                    </table>

                <?php } else if ( $status == 'making' ) { ?>

                    <table class="table table-responsive-lg table-hover">
                        <thead>
                            <tr>
                                <th scope="col" class="w-p-20">Поле</th>
                                <th scope="col" class=""><?=__( 'Детали', TEXTDOMAIN )?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Проверяемое домашнее задание', TEXTDOMAIN )?></td>
                                <td class=""><?=html_entity_decode( $item['homework'] )?></td>
                            </tr>
                        </tbody>   
                    </table>

                <?php } else if ( $status == 'waiting_for_check' ) { ?>

                    <table class="table table-responsive-lg table-hover">
                        <thead>
                            <tr>
                                <th scope="col" class="w-p-20">Поле</th>
                                <th scope="col" class=""><?=__( 'Детали', TEXTDOMAIN )?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Попытка', TEXTDOMAIN )?></td>
                                <td class="">№<?=$checking_attemp_num?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Дата подачи', TEXTDOMAIN )?></td>
                                <td class=""><?=$sent_for_checking_date?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Проверяемое домашнее задание', TEXTDOMAIN )?></td>
                                <td class=""><?=html_entity_decode( $item['homework'] )?></td>
                            </tr>
                        </tbody>   
                    </table>

                <?php } else if ( $status == 'checking' ) { ?>

                    <table class="table table-responsive-lg table-hover">
                        <thead>
                            <tr>
                                <th scope="col" class="w-p-20">Поле</th>
                                <th scope="col" class=""><?=__( 'Детали', TEXTDOMAIN )?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Проверяет', TEXTDOMAIN )?></td>
                                <td class=""><?=$checker_name?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Попытка', TEXTDOMAIN )?></td>
                                <td class="">№<?=$checking_attemp_num?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Дата подачи', TEXTDOMAIN )?></td>
                                <td class=""><?=$sent_for_checking_date?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Дата начала проверки', TEXTDOMAIN )?></td>
                                <td class=""><?=$start_date_checking?></td>
                            </tr>
                            <tr>
                                <td scope="row" class="w-p-20"><?=__( 'Проверяемое домашнее задание', TEXTDOMAIN )?></td>
                                <td class=""><?=html_entity_decode( $item['homework'] )?></td>
                            </tr>
                        </tbody>   
                    </table>

                <?php } ?>
            
<?php
            
            
                // get homework item content
                $homework_item_content = ob_get_contents();
                ob_end_clean();
            
                $homework_item = '<div class="card bg-light"><h5 class="card-header">' . edc__( 'Статус' ) . ': ' .  $status_text . '</h5><div class="card-body">' . $homework_item_content . '</div></div><br>';
   
                // add content for lesson accordion
                $lesson_accordion_item['content'] .= $homework_item;
            
            }
            
            // in case content is empty then we should not open lesson accordion
            if ( empty( $lesson_accordion_item['content'] ) ) {
                $lesson_accordion_item['is_disabled_opening'] = true;
            }
            
            // add accordion item data to accordion
            $lesson_accordion_items[] = $lesson_accordion_item;
        }
        
        // set content for course accordion
        $course_accordion_item['content'] = edc_html_generate_accordion( $lesson_accordion_items );
        // add accordion item data to accordion
        $course_accordion_items[] = $course_accordion_item;
    }
?>

<h4 class="page-title"><?=__( 'Прохождение уроков', TEXTDOMAIN )?></h4>
<br>

<?=edc_html_generate_accordion( $course_accordion_items )?>
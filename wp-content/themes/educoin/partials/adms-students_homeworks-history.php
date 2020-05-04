<?php

    global $tmpl_vars;
    
    // forming filters for students request
    $filters = [];
    if ( !empty( $tmpl_vars['course_id'] ) ) {
        $filters['course_id'] = $tmpl_vars['course_id'];
    }
    
    // get homeworks history
    $items = edc_get_homeworks_history( $filters );
?>

<?php 

    // getting data for accordion
    $accordion_items = [];

    foreach ( $items as $key => $item ) { 
    
        // reset accordion item data
        $accordion_item = [];

        // get lesson's info
        $lesson_num = get_field( 'edc_lesson_order_num', $item['lesson_id'] );
        $lesson_url = get_permalink( $item['lesson_id'] );
        $lesson_name = get_the_title( $item['lesson_id'] );
        // get lessons category name
        $post_terms = wp_get_post_terms( $item['lesson_id'], 'course' );
        $lesson_category_name = $post_terms[0]->name;
        // get quantity of checking requests
        $qnty_check_request = edc_get_user_lesson_homeworks_attemps_quantity( $item['lesson_id'], $item['user_id'] );

        // get checker name
        $checker_name = get_userdata( $item['checker_id'] )->display_name;

        // get the status of homework
        $status = edc_get_homework_status( $item['homework_id'] );
        // getting description of the status
        $tmp_arr = [ 
            'making' => __( 'Выполняется', TEXTDOMAIN ),
            'waiting_for_check' => __( 'Ожидает проверки', TEXTDOMAIN ),
            'checking' => __( 'Проверяется', TEXTDOMAIN ),
            'declined' => __( 'Отклонено', TEXTDOMAIN ),
            'accepted' => __( 'Принято', TEXTDOMAIN ),
        ];
        $status_text = $tmp_arr[$status];

        // get date of request for checking homework
        $sent_for_checking_date = edc_date_show_in_client_tz( strtotime( $item['sent_for_checking_date'] ) );
        $start_date_checking = edc_date_show_in_client_tz( strtotime( $item['start_date_checking'] ) );
        $end_date_checking = edc_date_show_in_client_tz( strtotime( $item['end_date_checking'] ) );
        
        // set accordion item title
        $counter = $key + 1;
        $accordion_item['title'] = "{$counter}) {$checker_name} : {$sent_for_checking_date}-{$end_date_checking} - {$status_text}";
        
        // getting and setting accordion item content
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
                        <td scope="row" class="w-p-20"><?=__( 'Ученик', TEXTDOMAIN )?></td>
                        <td class=""><?=$item['user_display_name']?></td>
                    </tr>
                    <tr>
                        <td scope="row" class="w-p-20"><?=__( 'Курс', TEXTDOMAIN )?></td>
                        <td class=""><?=$lesson_category_name?></td>
                    </tr>
                    <tr>
                        <td scope="row" class="w-p-20"><?=__( 'Детали урока', TEXTDOMAIN )?></td>
                        <td class=""><?=__( 'Урок', TEXTDOMAIN )?> №<?=$lesson_num?>: <a href="<?=$lesson_url?>" target="_blank">"<?=$lesson_name?>"</a></td>
                    </tr>
                    <tr>
                        <td scope="row" class="w-p-20"><?=__( 'Статус', TEXTDOMAIN )?></td>
                        <td class=""><?=$status_text?></td>
                    </tr>
                    <tr>
                        <td scope="row" class="w-p-20"><?=__( 'Проверял', TEXTDOMAIN )?></td>
                        <td class=""><?=$checker_name?></td>
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
                    <tr>
                        <td scope="row" class="w-p-20"><?=__( 'Комментарии проверяющего (для преподавательской команды)', TEXTDOMAIN )?></td>
                        <td class=""><?=nl2br( $item['checker_comments_for_teachers'] )?></td>
                    </tr>

                    <tr>
                        <td scope="row" class="w-p-20"><?=__( 'Комментарии проверяющего', TEXTDOMAIN )?></td>
                        <td class=""><?=nl2br( $item['checker_comments_short'] )?></td>
                    </tr>
                    <tr>
                        <td scope="row" class="w-p-20"><?=__( 'Детальные комментарии проверяющего', TEXTDOMAIN )?></td>
                        <td class=""><?=edc_legacy_get_showing_checker_comments( $item['checker_comments'] )?></td>
                    </tr>
                    <tr>
                        <td scope="row" class="w-p-20"><?=__( 'Проверяемое домашнее задание', TEXTDOMAIN )?></td>
                        <td class=""><?=html_entity_decode( $item['homework'] )?></td>
                    </tr>
                </tbody>   
            </table>

        <?php } ?>

<?php 
        // set accordion item content
        $accordion_item['content'] = ob_get_contents();
        ob_end_clean();
        
        // add accordion item to array
        $accordion_items[] = $accordion_item;
    } 
?>



<h4 class="page-title"><?=__( 'Проверки домашних заданий', TEXTDOMAIN )?></h4>
<br>

<div class="row">
    <div class="col-md-12">

        <?=edc_html_generate_accordion( $accordion_items )?>

    </div>
</div>

























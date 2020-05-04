<?php

    global $tmpl_vars;
    
    // get homeworks history
    $items = edc_get_user_lesson_homeworks_history( $tmpl_vars['lesson_id'], $tmpl_vars['user']->ID );
    
    // get category information
    $post_terms = wp_get_post_terms( $tmpl_vars['lesson_id'], 'course' );
    $category = $post_terms[0];
    
    // get lesson data
    $lesson_num = get_field( 'edc_lesson_order_num', $tmpl_vars['lesson_id'] );
    $lesson_title = get_the_title( $tmpl_vars['lesson_id'] );
?>

<!-- do we need to show intro information? -->
<?php if ( $tmpl_vars['is_show_intro_info'] ) { ?>
    <br>
    <br>
    <br>
    <br>
    <br>
    <p>
        <?=__( 'Просмотр домашних заданий', TEXTDOMAIN )?>
        <br>
        <?=__( 'Курс', TEXTDOMAIN )?> "<?=$category->name?>"
        <br>
        <?=__( 'Урок', TEXTDOMAIN )?> №<?=$lesson_num?>
        <br>
        <br>
    </p>
<?php } ?>

<div class="row">
    <div class="col-md-12">

        <?php 
            foreach ( $items as $key => $item ) { 
            
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
                
                // get checking attemp number
                $checking_attemp_num = count( $items ) - $key;
                
                // get date of request for checking homework
                $sent_for_checking_date = edc_date_show_in_client_tz( strtotime( $item['sent_for_checking_date'] ) );
                $start_date_checking = edc_date_show_in_client_tz( strtotime( $item['start_date_checking'] ) );
                $end_date_checking = edc_date_show_in_client_tz( strtotime( $item['end_date_checking'] ) );
        ?>

                <div class="card bg-light">
                    <h5 class="card-header"><?=__( 'Статус', TEXTDOMAIN )?>: <?=$status_text?></h5>
                    <div class="card-body">
                       
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

                    </div>
                </div>
                <br>
       
        <?php 
            } 
        ?>

    </div>
</div>

























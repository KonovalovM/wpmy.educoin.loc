<?php /* Template Name: Admin section -> Students -> Profile */ ?>

<?php

    // get user ID
    $user_id = intval( $_GET['id'] );
    
    // get user information
    $user = get_userdata( $user_id );
    
    // check whether we do not have such user in database
    if ( empty( $user ) ) {
        // redirection to default page
        wp_redirect( edc_get_default_internal_page_url() );
        exit;
    }
    
    // get extended data for user
    $user->extended_info = edc_get_user_extended( $user->ID );
    // unescape all values
    $user->extended_info = stripslashes_deep( $user->extended_info );
    
    // get user roles
    $user_roles = edc_get_user_roles( $user->ID );
    $user_roles_str = array_column( $user_roles, 'title' );
    $user_roles_str = implode( ', ', $user_roles_str );

    // define whether user is blocked
    $is_user_blocked = edc_is_user_blocked( $user->ID );
    if ( $is_user_blocked ) $blocking_data = edc_get_user_blocking_data( $user->ID );
    else $blocking_data = [];
    
    // fill template variables with appropriate data
    global $tmpl_vars;
    $tmpl_vars['user'] = $user;
    $tmpl_vars['user_id'] = $user_id;
    
    // define which of the templates we should show
    switch ( true ) {
    
        // should we show visiting pages history?
        case ( isset( $_GET['task'] ) && $_GET['task'] === 'visiting_pages_history' ):
              
            $tmpl = 'partials/adms-students-profile_visiting-pages-history';
            break;
    
        // should we show homeworks history for certain lesson?
        case ( isset( $_GET['task'] ) && $_GET['task'] === 'homeworks_history' && isset( $_GET['lesson_id'] ) ):
              
            $tmpl_vars['lesson_id'] = intval( $_GET['lesson_id'] );
            $tmpl_vars['is_show_intro_info'] = true;            
              
            $tmpl_vars['is_show_checker_comments_for_teachers'] = true;            
            $tmpl_vars['is_show_checker_comments_short'] = true;            
            $tmpl = 'partials/adms-students-profile_homeworks-history_single';
            break;
            
        // should we show homeworks history for all lessons?
        case ( isset( $_GET['task'] ) && $_GET['task'] === 'homeworks_history' ):
              
            $tmpl_vars['is_show_checker_comments_for_teachers'] = true;                  
            $tmpl_vars['is_show_checker_comments_short'] = true;                  
            $tmpl = 'partials/adms-students-profile_homeworks-history_all';
            break;
            
        // should we show searching queries history?
        case ( isset( $_GET['task'] ) && $_GET['task'] === 'searchings' ):
              
            $tmpl = 'partials/adms-students-profile_searchings';
            break;
            
        // should we show notifications history?
        case ( isset( $_GET['task'] ) && $_GET['task'] === 'notifications' ):
              
            $tmpl = 'partials/adms-students-profile_notifications';
            break;
    }
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <?php // is user blocked? ?>
            <?php if ( !$is_user_blocked ) { ?>
                <h2 class="page-title"><?=get_the_title( 54 )?></h2>
            <?php } else { ?>
                <h2 class="page-title"><?=get_the_title( 54 )?> (<?=edc__( 'заблокирован' )?>)</h2>
            <?php } ?>

            <h4 class="page-title"><?=edc__( 'Основное' )?></h4>
            <br>

            <table class="table table-responsive-lg table-hover">
                <thead>
                    <tr>
                        <th scope="col" class="w-p-20">Поле</th>
                        <th scope="col" class=""><?=edc__( 'Детали' )?></th>
                    </tr>
                </thead>
                <tbody>

                    <tr>
                        <th scope="row" class="w-p-20"><?=edc__( 'Логин' )?></th>
                        <td class=""><?=$user->user_login?></td>
                    </tr>
                    <tr>
                        <th scope="row" class="w-p-20"><?=edc__( 'Имя' )?></th>
                        <td class=""><?=$user->display_name?></td>
                    </tr>
                    <tr>
                        <th scope="row" class="w-p-20"><?=edc__( 'Основная роль' )?></th>
                        <td class=""><?=$user_roles_str?></td>
                    </tr>
                    <tr>
                        <th scope="row" class="w-p-20"><?=edc__( 'Дата крайнего визита' )?></th>
                        <td class=""><?=edc_date_show_in_client_tz( strtotime( $user->extended_info['last_visit_date'] ) )?></td>
                    </tr>
                    
                    <?php // is user blocked? ?>
                    <?php if ( $is_user_blocked ) { ?>
                        <tr>
                            <th scope="row" class="w-p-20"><?=edc__( 'Статус' )?></th>
                            <td class=""><?=edc__( 'Заблокирован' )?> <?=edc_date_show_in_client_tz( strtotime( $blocking_data[0] ) )?> по причине "<?=$blocking_data[3]?>" </td>
                        </tr>
                    <?php } ?>
                    
                </tbody>   
            </table>
            <br>

            <div id="accordion" role="tablist">

                <div class="card">
                    <div class="card-header" role="tab" id="heading1">
                        <h5 class="mb-0">
                            <a data-toggle="collapse" href="#collapse1" aria-expanded="true" aria-controls="collapse1"><?=edc__( 'Остальное' )?></a>
                        </h5>
                    </div>

                    <div id="collapse1" class="collapse" role="tabpanel" aria-labelledby="heading1" data-parent="#accordion">
                        <div class="card-body">

                            <table class="table table-responsive-lg table-hover">
                                <thead>

                                    <tr>
                                        <th scope="col" class="w-p-20">Поле</th>
                                        <th scope="col" class=""><?=edc__( 'Детали' )?></th>
                                    </tr>

                                </thead>
                                <tbody>
                                    <tr>
                                        <th scope="row" class="w-p-20"><?=edc__( 'ID' )?></th>
                                        <td class=""><?=$user->ID?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="w-p-20"><?=edc__( 'О себе' )?></th>
                                        <td class=""><?=nl2br( $user->extended_info['about_me'] )?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="w-p-20"><?=edc__( 'Цели на курс' )?></th>
                                        <td class=""><?=nl2br( $user->extended_info['course_goals'] )?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="w-p-20"><?=edc__( 'Дата регистрации' )?></th>
                                        <td class=""><?=edc_date_show_in_client_tz( strtotime( $user->user_registered ) )?></td>
                                    </tr>
                                </tbody>   
                            </table>

                        </div>
                    </div>
                </div>


                <div class="card">
                    <div class="card-header" role="tab" id="heading2">
                        <h5 class="mb-0">
                            <a data-toggle="collapse" href="#collapse2" aria-expanded="true" aria-controls="collapse2"><?=edc__( 'Контакты' )?></a>
                        </h5>
                    </div>

                    <div id="collapse2" class="collapse" role="tabpanel" aria-labelledby="heading2" data-parent="#accordion">
                        <div class="card-body">
                            <table class="table table-responsive-lg table-hover">
                                <thead>

                                    <tr>
                                        <th scope="col" class="w-p-20">Поле</th>
                                        <th scope="col" class=""><?=edc__( 'Детали' )?></th>
                                    </tr>

                                </thead>
                                <tbody>

                                    <tr>
                                        <th scope="row" class="w-p-20"><?=edc__( 'Email' )?></th>
                                        <td class=""><?=$user->user_email?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="w-p-20"><?=edc__( 'Телефон' )?></th>
                                        <td class=""><?=$user->extended_info['user_email']?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="w-p-20"><?=edc__( 'Skype' )?></th>
                                        <td class=""><?=$user->extended_info['skype']?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="w-p-20"><?=edc__( 'Telegram' )?></th>
                                        <td class=""><?=$user->extended_info['telegram_login']?></td>
                                    </tr>
                                    <tr>
                                        <th scope="row" class="w-p-20"><?=edc__( 'Социальная сеть' )?></th>
                                        <td class="">
                                            <a href="<?=$user->extended_info['social_network']?>" target="_blank"><?=$user->extended_info['social_network']?></a>
                                        </td>
                                    </tr>

                                </tbody>   
                            </table>

                        </div>
                    </div>
                </div>

            </div>

            <?php 
                // do you user have needed rights for editing user's course data?
                if ( ( ( $user_id != get_current_user_id() ) && current_user_can( 'edc_edit_user_course_data' ) ) ||
                     ( ( $user_id == get_current_user_id() ) && current_user_can( 'edc_edit_user_course_data_in_his_profile' ) ) ) 
                {
                    // show necessary template
                    get_template_part( 'partials/adms-students-profile_courses-data_edit' );
                } else {
                    // show necessary template
                    get_template_part( 'partials/adms-students-profile_courses-data_view' );
                }
            ?>
              
            <h4 class="page-title"><?=edc__( 'Аналитика' )?></h4>
            <br>

            <ul>
                <li>
                    <a href="<?=get_permalink() . '?id=' . $user_id . '&task=visiting_pages_history'?>"><?=edc__( 'Посещение страниц' )?></a>
                </li>
                <li>
                    <a href="<?=get_permalink() . '?id=' . $user_id . '&task=searchings'?>"><?=edc__( 'Поисковые запросы' )?></a>
                </li>
                <li>
                    <a href="<?=get_permalink() . '?id=' . $user_id . '&task=homeworks_history'?>"><?=edc__( 'Прохождение уроков' )?></a>
                </li>
                <li>
                    <a href="<?=get_permalink() . '?id=' . $user_id . '&task=notifications'?>"><?=edc__( 'Высланные ему уведомления и их прочитка' )?></a>
                </li>
            </ul>

            <br>
            
            <!-- show necessary template -->
            <?php get_template_part( $tmpl ) ?>
       
        </div>
    </div>
        
</div>

<?php get_footer() ?>
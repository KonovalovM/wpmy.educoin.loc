<?php /* Template Name: User course certificate view */ ?>

<?php

  $cert_uid = sanitize_text_field( $_GET['uid'] );

  // check whether we have certificate ID
  if ( !empty( $cert_uid ) ) {
      // get certificate
      $query_arr = [
          'uid' => $cert_uid,
      ];
      $certificate = edc_get_user_course_certificates( $query_arr )[0];
  }
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=get_the_title()?></h2>
            
            <!-- is certificate correct? -->
            <?php 
                if ( empty( $certificate ) ) { 
            ?>
            
                <br>
                <p class="text-center"><?=edc__( 'Такого сертификата не существует' )?></p>
            
            <?php 
                } else {

                  // can we show certificate in general?
                  $is_can_show_course_certificate_in_general = edc_is_can_show_course_certificate_to_user_in_general( $certificate['course_id'], $certificate['user_id'] );
                  // define whether user can view certificate
                  if ( $is_can_show_course_certificate_in_general ) {

                      $data_is_user_can_view_course_certificate = edc_get_data_is_user_can_view_course_certificate( $certificate['course_id'], $certificate['user_id'] );
                  }
                  
                  // can we observe certificate of the user?
                  if ( $data_is_user_can_view_course_certificate['result'] ) {

                      // get user info
                      $user = get_userdata( $certificate['user_id'] );
                      // get course data
                      $course = get_term( $certificate['course_id'] );
                      // get quantity lessons from course
                      $qnty_lessons = count( edc_get_course_lessons_to_take( $certificate['course_id'] ) );  

                      // get date of start course for user
                      $user_start_course_date = edc_get_user_start_course_date( $certificate['course_id'], $certificate['user_id'] );
                      // get date of end course for user
                      $user_end_course_date = edc_get_user_end_course_date( $certificate['course_id'], $certificate['user_id'] );

                      // generate QR-code
                      require_once( get_template_directory() . '/inc/vendors/phpqrcode/qrlib.php' );
                      // define temporary directories
                      $temp_dir = get_template_directory() . '/temp/certificates';
                      $temp_dir_url = get_template_directory_uri() . '/temp/certificates';
                      // forming QR-code content
                      $qr_code_content = get_permalink() . '?uid=' . $certificate['uid'];
                      // generating QR-code
                      QRcode::png( $qr_code_content, "{$temp_dir}{$certificate['uid']}.png", QR_ECLEVEL_L, 3 );

                      // fill template variables with appropriate data
                      global $tmpl_vars;
                      $tmpl_vars['user'] = $user;
                      $tmpl_vars['user_id'] = $certificate['user_id'];  
                      $tmpl_vars['course_id'] = $certificate['course_id'];  

                      $tmpl = 'partials/adms-students-profile_homeworks-history_all';
            ?>

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
                                <th scope="row" class="w-p-20"><?=edc__( 'QR-код' )?></th>
                                <td class=""><img src="<?=$temp_dir_url?><?=$certificate['uid']?>.png" /></td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-p-20"><?=edc__( 'ФИО' )?></th>
                                <td class=""><?=$user->display_name?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-p-20"><?=edc__( 'Почта' )?></th>
                                <td class=""><?=$user->user_email?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-p-20"><?=edc__( 'Название курса' )?></th>
                                <td class=""><?=$course->name?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-p-20"><?=edc__( 'Пройдено уроков' )?></th>
                                <td class=""><?=edc_get_user_passed_lessons_qnty( $certificate['user_id'], $certificate['course_id'] )?>/<?=$qnty_lessons?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-p-20"><?=edc__( 'Дата начала обучения' )?></th>
                                <td class=""><?=edc_date_show_in_client_tz( strtotime( $user_start_course_date ) )?></td>
                            </tr>
                            <tr>
                                <th scope="row" class="w-p-20"><?=edc__( 'Дата завершения обучения' )?></th>
                                <td class=""><?=edc_date_show_in_client_tz( strtotime( $user_end_course_date ) )?></td>
                            </tr>

                            <?php if ( !empty( $certificate['comments'] ) ) { ?>
                                <tr>
                                    <th scope="row" class="w-p-20"><?=edc__( 'Комментарии' )?></th>
                                    <td class=""><?=html_entity_decode( $certificate['comments'] )?></td>
                                </tr>
                            <?php } ?>
                            <tr>
                                <th scope="row" class="w-p-20"><?=edc__( 'Скачать сертификат' )?></th>
                                <td class=""><a href="<?php echo esc_url( add_query_arg( 'pdf', $post->ID ) );?>" target="_blank" class="btn btn-success btn-sm"><?=edc__( 'СЕРТИФИКАТ' )?></a></td>
                            </tr>

                        </tbody>   
                    </table>

                    <!-- show necessary template -->
                    <?php get_template_part( $tmpl )?>
            <?php 
                    } else {
            ?>
                        <br>
                        <p class="text-center"><?=edc__( 'Сертификат недоступен к просмотру' )?></p>  
            <?php
                    }
                } 
            ?>
            

        </div>
    </div>
        
</div>

<?php get_footer() ?>

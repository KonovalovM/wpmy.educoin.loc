<?php
/**
* certificat-view.php
* This template is used to display the content in the PDF
*/
?>

<html>
<head>
      	?>
    <style type="text/css">
        body{
  background-image: url("http://wpmy.educoin.loc/wp-content/themes/educoin/images/certificate.png")no-repeat center top fixed;
     background-size: cover;
}

    </style>

</head>

<body>

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
<!--<img src="<?php echo get_template_directory_uri(); ?>/images/certificat.png" alt="">-->

    <div class="container-fluid">

        <div class="row">
            <div class="col-md-12">

                <h2 class="page-title">СЕРТИФИКАТ ОТ EDUCOIN.BIZ</h2>

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

                <h4 class="page-title"><?=edc__( 'ЭТО УДОСТОВЕРЯЕТ ЧТО СТУДЕНТОМ' )?></h4>
                <br>
                <table class="table table-responsive-lg table-hover">

                    <tbody>

                        <tr>
                            <td class=""><?=$user->display_name?></td>
                        </tr>

                        <tr>
                            <td class=""> Был пройден курс <?=$course->name?></td>
                        </tr>

                        <tr>
                            <th scope="row" class="w-p-20"><?=edc__( 'Дата начала обучения' )?></th>
                            <td class=""><?=edc_date_show_in_client_tz( strtotime( $user_start_course_date ) )?></td>
                        </tr>
                        <tr>
                            <th scope="row" class="w-p-20"><?=edc__( 'Дата завершения обучения' )?></th>
                            <td class=""><?=edc_date_show_in_client_tz( strtotime( $user_end_course_date ) )?></td>
                        </tr>

                        <tr>
                            <th scope="row" class="w-p-20"><?=edc__( 'Educoin.biz' )?></th>

                        </tr>

                            <td class=""><img src="<?=$temp_dir_url?><?=$certificate['uid']?>.png" /></td>

                    </tbody>
                </table>

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
</body>

</html>

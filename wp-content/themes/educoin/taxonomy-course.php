<?php

    global $posts_ids;
    
    $categ = get_term( edc_get_current_course_id() );
    $cur_course_id = edc_get_current_course_id();
    $cur_course = get_term( edc_get_current_course_id() );
    
    // get additional articles
    $additional_articles_args = [
        'post_type' => 'additional_article',
        $categ->taxonomy => $categ->slug,
    ];
    $additional_articles_query = new WP_Query( $additional_articles_args );
    $additional_articles = $additional_articles_query->posts;
    
    // define whether course homeworks disabled
    $is_course_homeworks_disabled = edc_is_course_homeworks_disabled( $cur_course_id );
    // define whether user's course homeworks disabled
    $is_user_course_homeworks_disabled = edc_is_user_course_homeworks_disabled( $cur_course_id );
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>
          
            <div class="row">
                <div class="col-md-12">

                    <h2 class="page-title"><?php _e( 'Курс', TEXTDOMAIN ); ?>: "<?php echo $cur_course->name; ?>" </h2>
                    
                    
                    
                    
                    

                    <!-- Forming section with additional information for user in terms of current course -->
                    <?php
                        // get current support
                        $cur_support = edc_get_user_current_support( $cur_course_id );
                        
                        // get quantity lessons for current user to take
                        $qnty_lessons = count( edc_get_course_lessons_to_take( $cur_course_id ) );

                        // get current course ID
                        $cur_course_id = edc_get_current_course_id();
                        
                        // get current user ID
                        $cur_user_id = get_current_user_id();

                        // getting course end date
                        // get user's course data
                        $user_course_data = edc_get_user_courses( $cur_user_id, $cur_course_id );
                        // get user's course date end
                        $course_date_end = $user_course_data['course_date_end'];
                        // in case user's course date end is not set then we should get
                        // general course date end
                        if ( empty( $course_date_end ) ) {
                            $course_date_end = get_field( 'edc_course_date_end', 'course_' . $cur_course_id  );
                        }
                        // formatting course date end
                        $course_date_end = ( empty( $course_date_end ) ) ? '' : date( 'd.m.Y', strtotime( $course_date_end ) );
                        
                        // can we show certificate in general?
                        $is_can_show_course_certificate_in_general = edc_is_can_show_course_certificate_to_user_in_general( $cur_course_id, $cur_user_id );
                        // define whether user can view certificate
                        if ( $is_can_show_course_certificate_in_general ) {
                            
                            $data_is_user_can_view_course_certificate = edc_get_data_is_user_can_view_course_certificate( $cur_course_id, $cur_user_id );

                            // is user can view course certificate?
                            if ( $data_is_user_can_view_course_certificate['result'] ) {
                            
                                // get certificate ID
                                $query_arr = [
                                    'user_id' => $cur_user_id,
                                    'course_id' => $cur_course_id,
                                ];
                                $cert_uid = edc_get_user_course_certificates( $query_arr )[0]['uid'];
                            
                                // do we have certificate for for current user for current course?
                                if ( empty( $cert_uid ) ) {
                                    $cert_uid = edc_create_user_course_certificate( $cur_course_id, $cur_user_id )[0]['uid'];  
                                }

                                // get url for viewing certificate
                                $url_view_certificate = get_permalink( $posts_ids['user_course_certificate_view'] ) . '?uid=' . $cert_uid;
                            }
                        }
                           
                    ?>

                    <div class="card mt-4">
                        <div class="card-body">
                            <h4 class="card-title text-center"><?php _e( 'Дополнительная информация о курсе', TEXTDOMAIN ); ?></h4>
                            <p class="card-text">

							  <div class="readmore_course-extra-info">
								  <table class="table table-responsive-lg table-hover text-secondary">
									<thead>
									  <tr>
										<th scope="col" class="w-p-20">Поле</th>
										<th scope="col" class=""><?=edc__( 'Детали' )?></th>
									  </tr>
									</thead>

									<tbody>
									  <?php if ( !empty( $course_date_end ) ) { ?>
										<tr>
										  <th scope="row" class="w-p-20"><?=edc__( 'Дата окончания курса' )?></th>
										  <td class=""><?=$course_date_end?></td>
										</tr>
									  <?php } ?>

									  <tr>
										<th scope="row" class="w-p-20"><?=edc__( 'Пройдено уроков' )?></th>
										<td class=""><?=edc_get_user_passed_lessons_qnty( get_current_user_id(), $cur_course_id )?>/<?=$qnty_lessons?></td>
									  </tr>

									  <?php
										  // should we show information about current support for current user?
										  if ( !$is_course_homeworks_disabled && !$is_user_course_homeworks_disabled ) {
										  
											  // get support name text
											  $support_text = ( !empty( $cur_support ) ) ? $cur_support['support_name'] : edc_get_empty_value_table_cell();   
									  ?>
										<tr>
										  <th scope="row" class="w-p-20"><?=edc__( 'Куратор' )?></th>
										  <td class=""><?=$support_text?></td>
										</tr>
									  <?php 
										  } 
									  ?>

									  <?php if ( !empty( $is_can_show_course_certificate_in_general ) ) { ?>
										<tr>
										  <th scope="row" class="w-p-20"><?=edc__( 'Сертификат о пройденном курсе' )?></th>
										  <td class="">

											  <?php if ( !$data_is_user_can_view_course_certificate['result'] ) { ?>    
													  <button class="btn btn-secondary btn-sm" data-toggle="tooltip" title="<?=$data_is_user_can_view_course_certificate['message']?>"><?=edc__( 'Посмотреть' )?></button>

											  <?php } else { ?>      
													  <a href="<?=$url_view_certificate?>" target="_blank" class="btn btn-success btn-sm"><?=edc__( 'Посмотреть' )?></a>
													  <a href="<?php echo esc_url( add_query_arg( 'pdf', $post->ID ) );?>" target="_blank" class="btn btn-success btn-sm"><?=edc__( 'СЕРТИФИКАТ' )?></a>
											  <?php } ?>   


										  </td>
										</tr>
									  <?php }

                                        ?>

									</tbody>   
								  </table>
								</div>

                            </p>
                        </div>
                    </div>
                    
                    <div class="card-deck">
                        
                        <div class="card text-center mt-4 mb-6">
                            <div class="card-body">
                                <h4 class="card-title"><?php _e( 'Уроки', TEXTDOMAIN ); ?></h4>
                                <p class="card-text">Тут ты найдешь непосредственно сами уроки по программированию.</p>
                                <a href="<?php echo get_post_type_archive_link( 'lesson' ); ?>" class="btn btn-primary"><?php _e( 'Перейти', TEXTDOMAIN ); ?></a>
                            </div>
                        </div>
                      
                        <?php // should we show section with additional articles? ?>
                        <?php if ( count( $additional_articles ) ) { ?>
                            <div class="card text-center mt-4 mb-6">
                                <div class="card-body">
                                    <h4 class="card-title"><?php _e( 'Вспомогательные статьи', TEXTDOMAIN ); ?></h4>
                                    <p class="card-text">Здесь ты увидишь вспомогательные к урокам статьи.</p>
                                    <a href="<?php echo get_post_type_archive_link( 'additional_article' ); ?>" class="btn btn-primary"><?php _e( 'Перейти', TEXTDOMAIN ); ?></a>
                                </div>
                            </div>
                        <?php }?>
                        
                        <?php // disable it for a while ?>
                        <!--
                        <?php if ( false ) { ?>
                            <div class="card text-center mt-4 mb-6">
                                <div class="card-body">
                                    <h4 class="card-title"><?php _e( 'Личностный рост', TEXTDOMAIN ); ?></h4>
                                    <p class="card-text">Здесь представлены уроки и задания по личностному росту.</p>
                                    <a href="<?php echo get_post_type_archive_link( 'personal_development' ); ?>" class="btn btn-primary"><?php _e( 'Перейти', TEXTDOMAIN ); ?></a>
                                </div>
                            </div>
                        <?php } ?>
                        -->
                        
                    </div>
                </div>
            </div>
          
        </div>
    </div>        
</div>

<?php get_footer() ?>

<?php

    // get lesson lists
    $lessons = edc_get_course_lessons( edc_get_current_course_id() );

    // check whether user is allowed to take the first lesson
    if ( empty( $lessons[0]->progress ) ) {
        global $current_user;
        // allow to user to take first lesson
        edc_allow_user_to_take_lesson( $current_user->ID, $lessons[0]->ID );
        // update actual status for the first lesson
        $lessons[0]->progress = edc_get_user_lesson_progress( $lessons[0]->ID );
    }
    
    $is_course_ended = edc_is_course_ended( edc_get_current_course_id() );
    
    // define whether course homeworks disabled
    $is_course_homeworks_disabled = edc_is_course_homeworks_disabled( edc_get_current_course_id() );
    // define whether user's course homeworks disabled
    $is_user_course_homeworks_disabled = edc_is_user_course_homeworks_disabled( edc_get_current_course_id() );
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?php _e( 'Уроки', TEXTDOMAIN ); ?></h2>
   
            <table class="table table-striped table-responsive-lg mt-4 mb-4">
                <tbody>
                   
                    <?php
                    
                        // define status of the lesson
                        foreach ( $lessons as $lesson ) {
                        
                            // get lesson passing status
                            $lesson_passing_status = get_lesson_passing_status( $lesson->ID );
                            
                            // get start date
                            $start_date = get_field( 'edc_lesson_date_start', $lesson->ID );
                            // is date was set?
                            if ( !empty( $start_date ) ) {
                                $start_date = edc_date_show_in_client_tz( strtotime( $start_date ) ); 
                            } else {
                                $start_date = '';
                            }
                            
                            // get lesson providing status
                            $lesson_providing_status = get_lesson_providing_status( $lesson->ID );
                            // get the lesson webinar link
                            $webinar_url = get_field( 'edc_lesson_webinar_url', $lesson->ID );
                            // define whether lesson is or will be in online
                            if ( !empty( $webinar_url ) ) {
                                $is_online_event = true;
                            } else {
                                $is_online_event = false;
                            }
                    ?>


                            <?php if ( $lesson_passing_status == 'completed' ) { ?>
                               
                                <tr>
                                    <td class="w-p-1"><?php echo $lesson->order; ?></td>
                                    
                                    <!-- Is homeworks On in course? -->
                                    <?php if ( !$is_course_homeworks_disabled && 
                                               !$is_user_course_homeworks_disabled ) { ?>
                                        <td class="text-center w-p-1">
                                            <span class="status-cont">      

                                                <?php if ( $is_online_event ) {?>
                                                    <span class="is-online-event" title="<?=__( 'Живая онлайн-встреча', TEXTDOMAIN )?>" data-toggle="tooltip">Online</span>
                                                <?php } ?>  

                                                <span class="circle-blue" title="<?=__( 'Пройден', TEXTDOMAIN )?>" data-toggle="tooltip"></span>
                                            </span>
                                        </td>
                                    <?php } ?>
                                    
                                    <td class="text-center w-p-1">
                                        <span><?=$start_date?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo get_permalink( $lesson->ID )?>"><?php echo $lesson->post_title; ?></a>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?php echo get_permalink( $lesson->ID )?>" class="btn btn-primary"><?=__( 'Смотреть', TEXTDOMAIN ); ?></a>
                                    </td>
                                </tr>
                            
                            <?php } else if ( $lesson_passing_status == 'in_progress' ) { ?>

                                <tr>
                                    <td class="w-p-1"><?php echo $lesson->order; ?></td>
                                    
                                    <!-- Is homeworks On in course? -->
                                    <?php if ( !$is_course_homeworks_disabled &&
                                               !$is_user_course_homeworks_disabled ) { ?>
                                        <td class="text-center w-p-1">
                                            <span class="status-cont">

                                                <?php if ( $is_online_event ) {?>
                                                    <span class="is-online-event" title="<?=__( 'Живая онлайн-встреча', TEXTDOMAIN )?>" data-toggle="tooltip">Online</span>
                                                <?php } ?>

                                                <span class="circle-green" title="<?=__( 'Открыт', TEXTDOMAIN )?>" data-toggle="tooltip"></span>
                                            </span>
                                        </td>
                                    <?php } ?>
                                    
                                    <td class="text-center w-p-1">
                                        <span><?=$start_date?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo get_permalink( $lesson->ID )?>"><?php echo $lesson->post_title; ?></a>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?php echo get_permalink( $lesson->ID )?>" class="btn btn-primary"><?=__( 'Смотреть', TEXTDOMAIN )?></a>
                                    </td>
                                </tr>
                                
                            <?php } else if ( ( $lesson_passing_status == 'closed' ) && 
                                              ( current_user_can( 'edc_view_lesson_even_if_not_passed' ) || $is_course_ended || $is_course_homeworks_disabled || $is_user_course_homeworks_disabled ) ) { ?>
                                <?php /* We need to give access to admins and supports to lesson even if not passed the lessons before it */?>

                                <tr>
                                    <td class="w-p-1"><?php echo $lesson->order; ?></td>
                                    
                                    <!-- Is homeworks On in course? -->
                                    <?php if ( !$is_course_homeworks_disabled &&
                                               !$is_user_course_homeworks_disabled ) { ?>
                                        <td class="text-center w-p-1">
                                            <span class="status-cont">
                                                <?php if ( $is_online_event ) {?>
                                                    <span class="is-online-event" title="<?=__( 'Живая онлайн-встреча', TEXTDOMAIN )?>" data-toggle="tooltip">Online</span>
                                                <?php } ?>

                                                <span class="circle-gray" title="<?=__( 'Закрыт. Открой, выполнив предыдущие уроки.', TEXTDOMAIN )?>" data-toggle="tooltip"></span>
                                            </span>
                                        </td>
                                    <?php } ?>
                                    
                                    <td class="text-center w-p-1">
                                        <span><?=$start_date?></span>
                                    </td>
                                    <td>
                                        <a href="<?php echo get_permalink( $lesson->ID )?>"><?php echo $lesson->post_title; ?></a>
                                    </td>
                                    <td class="text-right">
                                        <a href="<?php echo get_permalink( $lesson->ID )?>" class="btn btn-primary"><?=__( 'Смотреть', TEXTDOMAIN )?></a>
                                    </td>
                                </tr>
                            
                            <?php } else if ( $lesson_passing_status == 'closed' ) { ?>

                                <tr>
                                    <td class="w-p-1"><?php echo $lesson->order; ?></td>
                                    
                                    <!-- Is homeworks On in course? -->
                                    <?php if ( !$is_course_homeworks_disabled && 
                                               !$is_user_course_homeworks_disabled ) { ?>
                                        <td class="text-center w-p-1">
                                            <span class="status-cont">

                                                <?php if ( $is_online_event ) {?>
                                                    <span class="is-online-event" title="<?=__( 'Живая онлайн-встреча', TEXTDOMAIN )?>" data-toggle="tooltip">Online</span>
                                                <?php } ?>

                                                <span class="circle-gray" title="<?=__( 'Закрыт. Открой, выполнив предыдущие уроки.', TEXTDOMAIN )?>" data-toggle="tooltip"></span>
                                            </span>
                                        </td>
                                    <?php } ?>
                                    
                                    <td class="text-center w-p-1">
                                        <span><?=$start_date?></span>
                                    </td>
                                    <td><?php echo $lesson->post_title; ?></td>
                                    <td class="text-right">
                                        <a href="#" class="btn btn-secondary disabled"><?=__( 'Смотреть', TEXTDOMAIN )?></a>
                                    </td>
                                </tr>
                            
                            <?php } ?>
                                
                    <?php
                            $i++;
                        }
                    ?>
            
                </tbody>
            </table>
            
        </div>
    </div>
</div>


<?php get_footer() ?>
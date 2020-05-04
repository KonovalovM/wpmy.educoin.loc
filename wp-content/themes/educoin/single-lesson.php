<?php

    // get lesson ID
    $lesson_id = get_the_id();
    
    // define whether user wants to see lesson in preview mode from admin section of the website
    // while he is editing the lesson
    $is_preview_mode = ( sanitize_text_field( $_GET['preview'] ) == 'true' ) ? true : false;
    $is_preview_mode_and_allowed = current_user_can( 'edc_preview_posts' ) && $is_preview_mode;

    // define whether course is ended
    $is_course_ended = edc_is_course_ended( edc_get_current_course_id() );
    // define whether course homeworks disabled
    $is_course_homeworks_disabled = edc_is_course_homeworks_disabled( edc_get_current_course_id() );
    // define whether user's course homeworks disabled
    $is_user_course_homeworks_disabled = edc_is_user_course_homeworks_disabled( edc_get_current_course_id() );

    // get lesson providing status
    $lesson_providing_status = get_lesson_providing_status( $lesson_id );
    // get lesson passing status
    $lesson_passing_status = get_lesson_passing_status( $lesson_id );
    // get lesson content
    $lesson_content = edc_get_post_content( $lesson_id );
    // get the lesson number
    $lesson_num = get_field( 'edc_lesson_order_num', $lesson_id );
    // get the lesson description
    $lesson_description = get_field( 'edc_lesson_description', $lesson_id );
    // get the content for supports
    $lesson_content_for_supports = get_field( 'edc_content_for_supports', $lesson_id );
    // get the lesson webinar link
    $webinar_url = get_field( 'edc_lesson_webinar_url', $lesson_id );
    // get lesson date providing
    $date_providing = strtotime( get_field( 'edc_lesson_date_start', $lesson_id ) );
    
    // get page title
    $title = __( 'Урок №', TEXTDOMAIN ) . $lesson_num . ': "' . get_the_title() . '"';

    global $tmpl_vars;
    $tmpl_vars['lesson_id'] = $lesson_id;
    $tmpl_vars['title'] = $title;
    $tmpl_vars['webinar_url'] = $webinar_url;
    $tmpl_vars['date_providing'] = $date_providing;
    $tmpl_vars['passing_status'] = $lesson_passing_status;
    $tmpl_vars['is_course_ended'] = $is_course_ended;
    $tmpl_vars['is_course_homeworks_disabled'] = $is_course_homeworks_disabled;
    $tmpl_vars['is_user_course_homeworks_disabled'] = $is_user_course_homeworks_disabled;

    switch ( true ) {
    
        // lesson records not started
        case ( $lesson_providing_status === 'not_started' ) && 
             ( empty( $webinar_url ) ) && 
             !$is_preview_mode_and_allowed:
            
            $tmpl = 'partials/single-lesson_records-not-started';
            break;
            
        // lesson not started
        case ( $lesson_providing_status === 'not_started' ) && 
             ( !empty( $webinar_url ) ) &&
             !$is_preview_mode_and_allowed:
            
            $tmpl = 'partials/single-lesson_not-started';
            break;
    
        // lesson running
        case ( $lesson_providing_status === 'running' ) && 
             ( !empty( $webinar_url ) ) &&
             !$is_preview_mode_and_allowed:
        
            $tmpl = 'partials/single-lesson_running';
            break;
    
        // lesson ended and content is absent
        case ( $lesson_providing_status === 'ended' ) && 
             ( empty( $lesson_content ) ) && 
             ( !empty( $webinar_url ) ) &&
             !$is_preview_mode_and_allowed:
        
            $tmpl = 'partials/single-lesson_ended_no-descr';
            break;
    
        // lesson ended and content of it is already done or lesson records started or
        // lesson does not have date
        case ( ( $lesson_providing_status === 'ended' ) && ( !empty( $webinar_url ) ) ) || 
             ( ( ( $lesson_providing_status === 'running' ) || ( $lesson_providing_status === 'ended' ) ) && ( empty( $webinar_url ) ) ) || 
             ( empty( $date_providing ) ) ||
             $is_preview_mode_and_allowed:
        
            $tmpl_vars['is_preview_mode'] = $is_preview_mode;
            
            // get a videos
            $videos = explode( ',', get_field( 'edc_lesson_video', $lesson_id ) );
            foreach ( $videos as $key => $video ) {
                $videos[$key] = ( strpos( $video, 'youtube.com' ) !== false || strpos( $video, 'youtu.be' ) !== false ) ?
                    do_shortcode( '[embedyt]' . $video . '[/embedyt]' ) :
                    do_shortcode( '[KGVID]' . $video . '[/KGVID]' );
            }

            // get user lesson homework progress
            $lesson_progress = edc_get_user_lesson_progress( $lesson_id );
            
            $tmpl_vars['lesson_description'] = $lesson_description;
            $tmpl_vars['lesson_content'] = $lesson_content;
            $tmpl_vars['lesson_content_for_supports'] = $lesson_content_for_supports;
            $tmpl_vars['videos'] = $videos;
            $tmpl_vars['lesson_progress'] = $lesson_progress;
            $tmpl_vars['is_open_homeworks_history'] = intval( $_GET['is_open_hh'] );
            
            // define whether we have minilessons data
            $tmpl_vars['minilessons'] = [];
            if ( !empty( get_field( 'edc_minilesson_1_title', $lesson_id ) ) ) {
            
                // set how many minilessons could be
                $potential_minilessons_quantity = 9;
            
                // get minilessons data in array
                for ( $i = 1; $i < ( $potential_minilessons_quantity + 1 ); $i++ ) {
                
                    // read next minilesson data
                    $title = get_field( "edc_minilesson_{$i}_title", $lesson_id );
                    $video = get_field( "edc_minilesson_{$i}_video", $lesson_id );
                    $content = get_field( "edc_minilesson_{$i}_content", $lesson_id );
                    
                    // is minilesson exist?
                    if ( !empty( $title ) ) {
                    
                        // process video data
                        $video = ( strpos( $video, 'youtube.com' ) !== false || strpos( $video, 'youtu.be' ) !== false ) ?
                            do_shortcode( '[embedyt]' . $video . '[/embedyt]' ) :
                            do_shortcode( '[KGVID]' . $video . '[/KGVID]' );
                    
                        $tmpl_vars['minilessons'][] = [
                            'title' => $title,
                            'video' => $video,
                            'content' => $content
                        ];
                    } 
                    // such minilesson does not exist then we exit from the loop
                    else {
                        break;
                    }
                }
            }
            
            // is lesson is not demo lesson?
            if ( !edc_is_demo_lesson( $lesson_id ) ) {
            
                $tmpl = 'partials/single-lesson_ended';
            } 
            // is lesson is demo lesson?
            else {
            
                $tmpl = 'partials/single-lesson_ended-demo';
            }
            
            
            break;
    }
?>

<?php get_header() ?>

<?php get_template_part( $tmpl ) ?>

<?php get_footer() ?>
<?php /* Template Name: Courses */ ?>


<?php 

    // get course lists
    $courses = edc_get_user_course_lists( 0, false );
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>
          
            <div class="row">
                <div class="col-md-12">

                    <h2 class="page-title"><?=__( 'Курсы', TEXTDOMAIN )?></h2>

                    <?php 
                    
                        if ( !empty( $courses ) ) { 
                            $qnty_courses = count( $courses );
                            $qnty_items_in_row = 3;
                            $i = 1;
                            $j = 1;
                            $subblocks = '';
                            $wrapper = '<div class="card-deck">[content]</div>';
                            foreach ( $courses as $index => $course ) {

                                // get user course disabling status
                                $course_disabling_status = edc_get_user_course_disabling_status( 0, $course->term_id );

                                // is user disabled on the course?
                                if ( $course_disabling_status['is_disabled'] ) {
                                
                                    // forming button hint with the reason of the disconneting
                                    $button_hint = sprintf( edc__( 'Доступ закрыт по причине: &quot;%s&quot;' ), $course_disabling_status['message'] );
                                    
                                    $subblocks .= '<div class="card text-center mt-3 mb-3">';
                                    $subblocks .= '<div class="card-body">';
                                    $subblocks .= '<h4 class="card-title">' . $course->name . '</h4>';
                                    $subblocks .= '<p class="card-text">' . $course->description . '</p>';
                                    $subblocks .= '<button class="btn btn-secondary" title="" data-toggle="tooltip" data-original-title="' . $button_hint . '">' . edc__( 'Перейти' ) . '</button>';
                                    $subblocks .= '</div></div>';
                                }
                                // user is not disabled
                                else {
                                
                                    $subblocks .= '<div class="card text-center mt-3 mb-3">';
                                    $subblocks .= '<div class="card-body">';
                                    $subblocks .= '<h4 class="card-title">' . $course->name . '</h4>';
                                    $subblocks .= '<p class="card-text">' . $course->description . '</p>';
                                    $subblocks .= '<a href="' . get_term_link( $course->term_id ) . '" class="btn btn-primary" onclick="setCurrentCourseId(' . $course->term_id . ')">' . edc__( 'Перейти' ) . '</a>';
                                    $subblocks .= '</div></div>';
                                }

                                
                                if ( ( $i === $qnty_items_in_row ) || ( $j === $qnty_courses ) ) {

                                    // print the nodes
                                    echo str_replace( '[content]', $subblocks, $wrapper );

                                    $i = 0;
                                    $subblocks = '';
                                }
                                $i++;
                                $j++;
                            }
                        } else {
                    ?>
                            <br>
                            <p class="text-center"><?=__( 'Вы не записаны пока ни на один курс. Скоро мы вас добавим.', TEXTDOMAIN )?></p>
                            <p class="text-center"><?=__( 'Можете пока прочитать страницу', TEXTDOMAIN )?> "<a href="<?=get_permalink( 61 )?>"><?=get_the_title( 61 )?></a>".</p>
                    <?php  
                        }
                    ?>

                </div>
            </div>
          
        </div>
    </div>        
</div>

<?php get_footer() ?>
<?php

    $categ = get_term( edc_get_current_course_id() );

    $personal_developments_args = [
        'post_type' => 'personal_development',
        $categ->taxonomy => $categ->slug,
    ];
    $personal_developments_query = new WP_Query( $personal_developments_args );

    // sorting items
    $personal_developments = $personal_developments_query->posts;

    foreach ( $personal_developments as $personal_development ) {
        $order = get_field( 'edc_personal_development_order_num', $personal_development->ID );
        $personal_development->order = $order ? $order : 9999;
    }
    usort( $personal_developments, 'edc_order_title_arr_sort' );
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?php _e( 'Занятия', TEXTDOMAIN ); ?></h2>
   
            <table class="table table-striped mt-4 mb-4">
                <tbody>
                   
                    <?php
                        foreach ( $personal_developments as $personal_development ) {
                    ?>
                            <tr>
                                  <td><?php echo $personal_development->order; ?></td>
                                  <td>
                                      <a href="<?php echo get_permalink( $personal_development->ID )?>"><?php echo $personal_development->post_title; ?></a>
                                  </td>
                                  <td class="text-right">
                                      <a href="<?php echo get_permalink( $personal_development->ID )?>" class="btn btn-primary"><?php _e( 'Смотреть', TEXTDOMAIN ); ?></a>
                                  </td>
                              </tr>
                            
                    <?php
                        }
                    ?>
            
                </tbody>
            </table>
           
        </div>
    </div>
</div>


<?php get_footer() ?>
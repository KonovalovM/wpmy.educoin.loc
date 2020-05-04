<?php get_header() ?>

<?php

    global $g_minimum_search_string_length;

    // get search string
    $search_str = ( isset( $_GET['s'] ) ) ? sanitize_text_field( $_GET['s'] ) : '';
    
    
    if ( mb_strlen( $search_str ) >= $g_minimum_search_string_length ) {
    
        // search only in posts in such course materials as below
        $courses = edc_get_user_course_lists();
        $courses_ids = array_column( $courses, 'term_id' );

        // form search query
        // https://gist.github.com/luetkemj/2023628 - details for search query arguments here
        $search_query = [
            's' => $search_str,
            'sentence' => true,
            'posts_per_page' => -1,
            'orderby' => 'post_modified_gmt',
            'order'   => 'DESC',
            'post_type' => ['lesson', 'additional_article'],
            'tax_query' => [
              'relation' => 'AND', 
              [
                'taxonomy' => 'course',
                'terms' => $courses_ids,
                'operator' => 'IN'
              ],
            ],
        ];

        // get search results
        $result = new WP_Query( $search_query );  
        $found_items = $result->posts;  
        $quantity_found_items = count( $found_items );
    }
?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>
          
            <div class="row">
                <div class="col-md-12">

                    <h2 class="page-title"><?=edc__( 'Поиск' )?></h2>
                    
                    
                    <?php if ( mb_strlen( $search_str ) < $g_minimum_search_string_length ) { ?>
                           
                            <hr>
                            <article>
                                <p><?=edc__( "Поисковая строка должна быть длиннее чем {$g_minimum_search_string_length} символа" )?></p>
                            </article>
                            
                    <?php } else if ( $quantity_found_items ) { ?>
                        <?=edc__( 'Найдено в целом' )?>: <?=$quantity_found_items?> <?=edc__( 'шт' )?><br>
                        <?=edc__( 'Как правильно пользоваться поиском' )?>: <a href="<?=get_permalink( 638 )?>" class="btn btn-primary btn-sm">читать</a>
                        <?php 
                        
                            $post_type_translation_map = [];
                            $post_type_name_map['lesson'] = 'Урок';
                            $post_type_name_map['page'] = 'Страница';
                            $post_type_name_map['additional_article'] = 'Вспомогательная статья';
                        
                            foreach ( $found_items as $key => $item ) {

                                // is current post belongs to 'lesson' post type?
                                if ( $item->post_type === 'lesson' ) {
                                    $lesson_num = get_field( 'edc_lesson_order_num', $item->ID );
                                    $page = $post_type_name_map[$item->post_type] . ' №' . $lesson_num;
                                } 
                                // do we have translation for this type of post?
                                else if ( !empty( $post_type_name_map[$item->post_type] ) ) {
                                    $page = $post_type_name_map[$item->post_type];
                                } 
                                // we do not have translation for this type of post
                                else {
                                    $page = $item->post_type;
                                }

                                // get item data
                                $item_url = get_permalink( $item->ID ); 
                                $item_url = edc_add_param_to_url( $item_url, 'is_all_expand=1' ); 
                                
                                $item_url_display = get_permalink( $item->ID ); 
                                $item_title = $page . ': ' . get_the_title( $item->ID ); 
                        ?>

                                <hr>
                                <article>

                                    <div class="post_title">

                                        <?=($key+1)?>) <a href="<?=$item_url?>" target="_blank"><?=$item_title?></a>
                                    </div>

                                    <div class="post_url">
                                        <?=$item_url_display?>
                                    </div>

                                </article>
                        <?php 
                            }
                            
                            // generating accordion
                            $accordion_items = [
                                [
                                    'title' => edc__( 'Поиск в Google' ),
                                    'content' => '<p>' . edc__( 'Если не нашел на Платформе - поищи в гугл-поисковике.' ) . '</p><div class="gcse-searchresults-only"></div>'
                                ]
                            ];
                        ?>
                        <br>
                        <br>
                        <br>
                        <?=edc_html_generate_accordion( $accordion_items )?>
                                
                    <?php } else if ( $quantity_found_items == 0 ) { ?>
                           
                            <?=edc__( 'Как правильно пользоваться поиском' )?>: <a href="<?=get_permalink( 638 )?>" class="btn btn-primary btn-sm">читать</a>
                            <hr>
                            <article>
                                <p><?=edc__( 'На Платформе по запросу <i>&quot;' . $search_str . '&quot;</i> ничего не найдено. Попробуй другие вариации искомого.' )?></p>
                            </article>
                            
                            <?php 
                            
                                // generating accordion
                                $accordion_items = [
                                    [
                                        'title' => edc__( 'Поиск в Google' ),
                                        'content' => '<p>' . edc__( 'Если не нашел на Платформе - поищи в гугл-поисковике.' ) . '</p><div class="gcse-searchresults-only"></div>'
                                    ]
                                ];
                            ?>
                        <br>
                        <br>
                        <br>
                        <?=edc_html_generate_accordion( $accordion_items )?>

                    <?php } ?>
                    
                </div>
            </div>
            
        </div>
    </div>
</div>

<!-- Include google custom search main file -->
<script async src="https://cse.google.com/cse.js?cx=015824215870596492260:uilsytomej6"></script>

<?php get_footer() ?>
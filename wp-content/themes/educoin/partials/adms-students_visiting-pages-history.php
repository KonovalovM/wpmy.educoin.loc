<?php

    global $tmpl_vars;
    
    // forming filters for request
    $filters = [];
    if ( !empty( $tmpl_vars['user_id'] ) ) {
        $filters['user_id'] = $tmpl_vars['user_id'];
    }
    
    // get visiting pages history
    $items = edc_analytics_get_visiting_pages_history( $filters ); 
?>

<h4 class="page-title"><?=__( 'Посещение страниц', TEXTDOMAIN )?></h4>
<br>
                
<table class="table table-responsive-lg table-hover">
    <thead>

        <tr>

            <th scope="col" class="w-p-1"><?=__( '#', TEXTDOMAIN )?></th>
            <th scope="col"><?=__( 'Пользователь', TEXTDOMAIN )?></th>
            <th scope="col"><?=__( 'Страница', TEXTDOMAIN )?></th>
            <th scope="col"><?=__( 'Время', TEXTDOMAIN )?></th>

        </tr>

    </thead>
    <tbody>

        <?php 
            $post_type_translation_map = [];
            $post_type_name_map['lesson'] = 'Урок';
            $post_type_name_map['page'] = 'Страница';
            $post_type_name_map['additional_article'] = 'Вспомогательная статья';

            foreach ( $items as $key => $item ) {             

                // is current post belongs to 'lesson' post type?
                if ( $item['post_type'] === 'lesson' ) {
                
                    $lesson_num = get_field( 'edc_lesson_order_num', $item['wp_object_id'] );
                    $page = $post_type_name_map[$item['post_type']] . ' №' . $lesson_num;
                } 
                // do we have translation for this type of post?
                else if ( !empty( $post_type_name_map[$item['post_type']] ) ) {

                    $page = $post_type_name_map[$item['post_type']];
                } 
                // we do not have translation for this type of post
                else {

                    $page = $item['post_type'];
                }
                
                // get URL
                $url = get_permalink( $item['wp_object_id'] );
                $url = edc_add_param_to_url( $url, $item['query_string'] );
        ?>

                <tr>
                    <th scope="1" class="w-p-1"><?=$key+1?></th>
                    <td class="">
                        <a href="<?=get_permalink( 54 ) . '?id=' . $item['user_id']?>"><?=$item['display_name']?></a>
                    <td class="">
                        <?=$page?>: "<a href="<?=$url?>" target="_blank"><?=$item['post_title']?></a>"
                    </td>
                    <td class=""><?=edc_date_show_in_client_tz( strtotime( $item['date'] ) )?></td>
                </tr>

        <?php 
            } 
        ?>

    </tbody>   
</table>
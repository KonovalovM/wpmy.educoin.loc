<?php

    global $tmpl_vars;
    
    $items = edc_get_search_queries( $tmpl_vars['user']->ID );
?>

<h4 class="page-title"><?=edc__( 'Поисковые запросы' )?></h4>
<br>
    
<?php if ( count( $items ) ) { ?>
    
    <table class="table table-responsive-lg table-hover">
        <thead>

            <tr>

                <th scope="col" class="w-p-1"><?=__( '#' )?></th>
                <th scope="col"><?=__( 'Запрос' )?></th>
                <th scope="col"><?=__( 'Тип' )?></th>
                <th scope="col"><?=__( 'Время' )?></th>

            </tr>

        </thead>
        <tbody>

            <?php 
                $post_type_translation_map = [];
                $post_type_name_map['lesson'] = 'Урок';
                $post_type_name_map['page'] = 'Страница';
                $post_type_name_map['additional_article'] = 'Вспомогательная статья';

                foreach ( $items as $key => $item ) { 

                    // getting description of the status
                    $tmp_arr = [ 
                        'general' => edc__( 'Поиск на Платформе' ),
                        'google' => edc__( 'Поиск в Google' ),
                    ];
                    $type = $tmp_arr[$item['type']]
            ?>

                    <tr>
                        <th scope="1" class="w-p-1"><?=$key+1?></th>
                        <td class="">
                            <?=$item['query']?>
                        </td>
                        <td class="">
                            <?=$type?>
                        </td>
                        <td class=""><?=edc_date_show_in_client_tz( strtotime( $item['date'] ) )?></td>
                    </tr>

            <?php 
                } 
            ?>

        </tbody>   
    </table>
<?php } else {?>
    <p><?=edc__( 'У пользователя пока не было поисковых запросов' )?></p>
<?php }?>
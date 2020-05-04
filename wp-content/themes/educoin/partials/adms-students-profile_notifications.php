<?php

    global $tmpl_vars;
    
    $items = edc_get_user_notifications( $tmpl_vars['user']->ID );
?>

<h4 class="page-title"><?=edc__( 'Уведомления' )?></h4>
<br>
    
<?php if ( count( $items ) ) { ?>
    
    <table class="table table-responsive-lg table-hover">
        <thead>

            <tr>

                <th scope="col" class="w-p-1"><?=__( '#' )?></th>
                <th scope="col"><?=__( 'Уведомление' )?></th>
                <th scope="col"><?=__( 'Приоритет' )?></th>
                <th scope="col"><?=__( 'Дата отсылки' )?></th>
                <th scope="col"><?=__( 'Дата прочтения' )?></th>

            </tr>

        </thead>
        <tbody>

            <?php 
                foreach ( $items as $key => $item ) { 

                    // get information about reading status
                    $read_date = ( !empty( $notification['read_date'] ) ) ? 
                        edc_date_show_in_client_tz( strtotime( $notification['read_date'] ) ) : 
                        edc_get_empty_value_table_cell();

                    // getting priority
                    if ( ( $item['priority'] == 0 ) ) {
                        $priority_text = edc__( 'Обычный' );
                    } else if ( ( $item['priority'] == 1 ) ) {
                        $priority_text = edc__( 'Важный' );
                    }
            ?>
                    <tr>
                        <th scope="1" class="w-p-1"><?=$key+1?></th>
                        <td class="">
                            <a href="#" data-toggle="popover" data-trigger="hover" title="<?=edc__( 'Описание' )?>" data-content='<?=$item['message']?>' data-placement="right"><?=$item['subject']?></a>
                        </td>
                        <td class="">
                            <?=$priority_text?>
                        </td>
                        <td class=""><?=edc_date_show_in_client_tz( strtotime( $item['sent_date'] ) )?></td>
                        <td class=""><?=$read_date?></td>
                    </tr>

            <?php 
                } 
            ?>

        </tbody>   
    </table>
<?php } else {?>
    <p><?=edc__( 'У пользователя пока не было уведомлений' )?></p>
<?php }?>
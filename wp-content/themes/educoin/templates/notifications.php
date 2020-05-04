<?php /* Template Name: Notifications */ ?>

<?php
    
    
    
	
	
	
// HARDCODE FOR SENDING MASS NOTIFICATIONS
// in order to add notifications go by link '/uvedomleniya/?send_mass_notifications='
/*
if ( isset($_GET['send_mass_notifications'] )) {

	$user_list_id = [ 'course_ids' => [ 23 ] ];
	$subject = '[Сегодня] Онлайн-встреча "Разбор частых проблем и ошибок учеников"';
	$message = '<p>Напоминаем что встреча будет сегодня в 20:00 (Киев) / 21:00 (МСК). Мероприятие будет в Zoom.us. Как им пользоваться описано по ссылке https://lessons.educoin.biz/poleznye-stati/podklyuchenie-k-zoom-us/ . Вот ссылка на встречу https://zoom.us/j/513447341</p>';
	$priority = 0;
	
	$sender_id = get_current_user_id();
	edc_add_mass_user_notification( $user_list_id, $sender_id, $subject, $message, $priority );

	dd('Sent');
}
*/
    
    
    
    
    
    
    
    
    
    
    
    
    // get user's notifications
    $user_notifications = edc_get_user_notifications();
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=get_the_title()?></h2>
            <br>
            
            <?php 
                // does user have any notifications?
                if ( count( $user_notifications ) > 0 ) {

                    foreach ( $user_notifications as $key => $notification ) {
                    
                        // get date of sending
                        $sent_date = $notification['sent_date'];
                        $sent_date = edc_date_show_in_client_tz( strtotime( $sent_date ) );
                        
                        // is notification read?
                        $is_notification_read = ( empty( $notification['read_date'] ) ) ? false : true;
                        
                        // get date badge class
                        if ( ( $notification['priority'] == 1 ) && !$is_notification_read ) {
                            $date_badge_class = 'badge-warning';
                        } else if ( ( $notification['priority'] == 0 ) && !$is_notification_read ) {
                            $date_badge_class = 'badge-primary';
                        } else if ( $is_notification_read ) {
                            $date_badge_class = 'badge-secondary';
                        }
            ?>

                        <hr>
                        <article>

                            <p>
                                <span class="badge <?=$date_badge_class?>"><?=$sent_date?></span>
                                <?=$notification['subject']?>
                            </p>
                            
                            <div class="readmore">
                                <p><?=html_entity_decode( $notification['message'] )?></p>
                                
                                <?php if ( !$is_notification_read ) { ?>
                                    <p>
                                        <a href="<?=get_permalink()?>?task=notification&action=read&id=<?=$notification['id']?>" class="btn btn-success btn-sm"><?=edc__( 'Прочтено' )?></a>
                                    </p>
                                <?php } ?>
                            </div>

                        </article>
            <?php 
                    }
                } else {
            ?>
                        <hr>
                        <article>

                            <p>
                                <?=edc__( 'Список уведомлений пока пуст' )?>
                            </p>

                        </article> 
            <?php 
                }
            ?>
            

        </div>
    </div>
        
</div>

<?php get_footer() ?>
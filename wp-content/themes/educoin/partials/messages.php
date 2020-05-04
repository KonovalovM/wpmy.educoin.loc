<?php

    // get message
    $msg = edc_get_status_message();
    if ( !empty( $msg ) ) {
    
        $msg_type = '';
        switch ( $msg['msg_type'] ) {
            case 'warning': $msg_type = 'warning'; break;
            case 'error': $msg_type = 'danger'; break;
            case 'notice': $msg_type = 'primary'; break;
        }
    }

    // get permanent notifications
    $permanent_msg = edc_get_permanent_notifications();
    if ( !empty( $permanent_msg ) ) {
    
        // get just first message
        $permanent_msg = $permanent_msg[0];
    
        $permanent_msg_type = '';
        switch ( $permanent_msg['type'] ) {
            case 'warning': $permanent_msg_type = 'warning'; break;
            case 'error': $permanent_msg_type = 'danger'; break;
            case 'notice': $permanent_msg_type = 'primary'; break;
        }
    }
?>


<?php if ( !empty( $msg ) ) { ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="mt-3 alert alert-<?=$msg_type?> alert-temp" role="alert"><?=$msg['msg_text']?></div>
            </div>
        </div>                    
    </div>
<?php } ?>

<?php if ( !empty( $permanent_msg ) ) { ?>
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="mt-3 alert alert-<?=$permanent_msg_type?>" role="alert"><?=$permanent_msg['text']?></div>
            </div>
        </div>                    
    </div>
<?php } ?>
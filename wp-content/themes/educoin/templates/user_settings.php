<?php /* Template Name: User -> Settings */ ?>


<?php 

    global $current_user;
    $current_user_id = get_current_user_id();
    // get extended user information
    $user_extended = edc_get_user_extended( $current_user_id );
?>

<?php get_header() ?>

<div class="container-fluid">
  
    <div class="row">
        <div class="col-md-12">

            <?php get_sidebar( 'left' ); ?>

            <h2 class="page-title"><?=__( 'Настройки', TEXTDOMAIN )?></h2>
            <br>

            <form class="" action="<?=get_permalink()?>" method="post">

              <input type="hidden" name="task" value="user_settings">
              <input type="hidden" name="action" value="save">
             
             
              <div class="form-row">
                  <div class="form-group col-md-6">
                      <label for="login"><?=__( 'Логин', TEXTDOMAIN )?></label>
                      <input type="text" class="form-control" id="login" disabled value="<?=$current_user->get( 'user_login' )?>">
                  </div>
                  <div class="form-group col-md-6">
                      <label for="user_email"><?=__( 'Email', TEXTDOMAIN )?></label>
                      <input type="email" class="form-control" id="user_email" disabled placeholder="name@example.com" value="<?=$current_user->get( 'user_email' )?>">
                  </div>
              </div>
              
              <div class="form-group">
                <label for="display_name"><?=__( 'Имя', TEXTDOMAIN )?></label>
                <input type="text" class="form-control" id="display_name" disabled value="<?=$current_user->get( 'display_name' )?>">
              </div>
              
              <div class="form-row">
                  <div class="form-group col-md-3">
                      <label for="phone"><?=__( 'Телефон', TEXTDOMAIN )?></label>
                      <input type="text" class="form-control" id="phone" placeholder="" name="phone" value="<?=$user_extended['phone']?>">
                  </div>
                  <div class="form-group col-md-3">
                      <label for="skype"><?=__( 'Skype', TEXTDOMAIN )?></label>
                      <input type="text" class="form-control" id="skype" placeholder="" name="skype" value="<?=$user_extended['skype']?>">
                  </div>
                  <div class="form-group col-md-3">
                      <label for="telegram_login"><?=__( 'Логин в Telegram', TEXTDOMAIN )?></label>
                      <input type="text" class="form-control" id="telegram_login" placeholder="" name="telegram_login" value="<?=$user_extended['telegram_login']?>">
                  </div>
                  <div class="form-group col-md-3">
                      <label for="social_network"><?=__( 'Социальная сеть', TEXTDOMAIN )?></label>
                      <input type="text" class="form-control" id="social_network" placeholder="" name="social_network" value="<?=$user_extended['social_network']?>">
                  </div>
              </div>
              
              <div class="form-group">
                <label for="about_me"><?=__( 'Обо мне', TEXTDOMAIN )?></label>
                <textarea class="form-control" id="about_me" rows="3" name="about_me" placeholder="<?=__( 'Хобби? Кумиры и почему именно они? и др.', TEXTDOMAIN )?>"><?=$user_extended['about_me']?></textarea>
              </div>
              <div class="form-group">
                <label for="course_goals"><?=__( 'Цели на курс', TEXTDOMAIN )?></label>
                <textarea class="form-control" id="course_goals" rows="3" name="course_goals"><?=$user_extended['course_goals']?></textarea>
              </div>
              <div class="form-row">
                <div class="form-group col-md-6">
                  <label for="user_pass"><?=__( 'Новый пароль', TEXTDOMAIN )?></label>
                  <input type="password" class="form-control" id="user_pass" placeholder="" name="user_pass">
                </div>
                <div class="form-group col-md-6">
                  <label for="user_pass_confirmation"><?=__( 'Подтверждение пароля', TEXTDOMAIN )?></label>
                  <input type="password" class="form-control" id="user_pass_confirmation" placeholder="" name="user_pass_confirmation">
                </div>
              </div>
              
              <h2 class="page-title"><?=__( 'Уведомления', TEXTDOMAIN )?></h2>
              <br>
             
              <div class="form-row">
                  <div class="form-group col-md-12">
                      <label for="telegram_chat_id"><?=__( 'Telegram чат ID', TEXTDOMAIN )?></label>
                      <input type="text" class="form-control" id="telegram_chat_id" placeholder="" name="telegram_chat_id" value="<?=$user_extended['telegram_chat_id']?>">
                      <small id="passwordHelpBlock" class="form-text text-muted">
                        <?=edc__( 'Чтобы мы могли с тобой держать связь в Telegram, выполни инструкции из этой <a href="' . get_permalink( 584 ) . '" target="_blank">статьи</a>.' )?>
                      </small>
                  </div>
              </div>
              
              <div class="text-center">
                  <button type="submit" class="btn btn-primary mb-4 mt-4 mx-auto"><?=__( 'Сохранить', TEXTDOMAIN )?></button>
              </div>
            </form>
            
        </div>
    </div>
        
</div>


<?php get_footer() ?>
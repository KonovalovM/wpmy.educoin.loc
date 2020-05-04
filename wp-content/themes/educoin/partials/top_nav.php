<?php
    global $g_minimum_search_string_length;
    
    // get course lists link
    $taxonomy = get_taxonomy( 'course' ) ;
    $course_lists_url = get_bloginfo( 'url' ) . '/' . $taxonomy->rewrite['slug'];
    
    // get search string
    $search_str = ( isset( $_GET['s'] ) ) ? sanitize_text_field( $_GET['s'] ) : '';  
?>



<nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
  <a class="navbar-brand" href="<?=get_bloginfo( 'url' )?>">
    <span>Educoin</span>
    <span class="user-id"><?=get_current_user_id()?></span>
  </a>
  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarText" aria-controls="navbarText" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>

  <?php if ( is_user_logged_in() ) { ?>
      <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav mr-auto">

          <?php if ( current_user_can( 'edc_view_custom_admin_section' ) ) { ?>

              <li class="nav-item dropdown <?=( edc_is_section_admin() ) ? 'active' : ''?>">
                <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><?=get_the_title( 46 )?></a>
                <div class="dropdown-menu">
                  <a class="dropdown-item" href="<?=get_permalink( 48 )?>"><?=get_the_title( 48 )?></a>
                  <a class="dropdown-item" href="<?=get_permalink( 52 )?>"><?=get_the_title( 52 )?></a>
                  <a class="dropdown-item" href="<?=get_permalink( 771 )?>"><?=get_the_title( 771 )?></a>
                  <a class="dropdown-item" href="<?=get_permalink( 700 )?>"><?=get_the_title( 700 )?></a>
                </div>
              </li>
          <?php } ?>

          <li class="nav-item dropdown <?=( edc_is_section_useful() ) ? 'active' : ''?>">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><?=get_the_title( 59 )?></a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="<?=get_permalink( 61 )?>"><?=get_the_title( 61 )?></a>
              <a class="dropdown-item" href="<?=get_permalink( 76 )?>"><?=get_the_title( 76 )?></a>
              
              <?php if ( current_user_can( 'edc_view_teachers_materials_section' ) ) { ?>
                <!-- Материалы для преподавателей -->
                <a class="dropdown-item" href="<?=get_permalink( 377 )?>"><?=get_the_title( 377 )?></a>
              <?php } ?>
              
              <?php if ( current_user_can( 'edc_view_supports_section' ) ) { ?>
                <!-- Материалы для саппортов -->
                <a class="dropdown-item" href="<?=get_permalink( 378 )?>"><?=get_the_title( 378 )?></a>
              <?php } ?>
              
              <?php if ( current_user_can( 'edc_view_organizers_materials_section' ) ) { ?>
                <!-- Материалы для организаторов -->
                <a class="dropdown-item" href="<?=get_permalink( 384 )?>"><?=get_the_title( 384 )?></a>
              <?php } ?>
              
            </div>
          </li>
              
          <li class="nav-item <?=( edc_is_section_courses() ) ? 'active' : ''?>">
            <a class="nav-link" href="<?=$course_lists_url?>"><?=edc__( 'Курсы' )?></a>
          </li>

          <!-- Зал Славы -->
          <!--
          <li class="nav-item <?=( edc_is_section_fame_hall() ) ? 'active' : ''?>">
            <a class="nav-link" href="<?=get_permalink( 644 )?>"><?=get_the_title( 644 )?></a>
          </li>
          -->
          
          <li class="nav-item dropdown <?=( edc_is_section_other() ) ? 'active' : ''?>">
            <a class="nav-link dropdown-toggle" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false"><?=get_the_title( 592 )?></a>
            <div class="dropdown-menu">
              <a class="dropdown-item" href="<?=get_permalink( 34 )?>"><?=get_the_title( 34 )?></a>
              <a class="dropdown-item" href="<?=get_permalink( 593 )?>"><?=get_the_title( 593 )?></a>
            </div>
          </li>

          <!-- Уведомления -->
          <?php
              $user_notifications_to_read_quantity = edc_get_user_notifications_to_read_quantity();
              
              // should we show item as very markable?
              if ( $user_notifications_to_read_quantity ) {
                  // get random css animation
                  $attention_classes_array = [ 'shake-vertical', 'shake-left', 'heartbeat', 'vibrate-1' ];
                  $rand_num = mt_rand( 0, count($attention_classes_array) - 1 );
                  $item_class = $attention_classes_array[$rand_num];
              }
          ?>
          <li class="nav-item <?=( edc_is_section_notifications() ) ? 'active' : ''?>">
            <a class="nav-link <?=$item_class?>" href="<?=get_permalink( 652 )?>">
              <?=get_the_title( 652 )?> 
              <?php if ( $user_notifications_to_read_quantity > 0 ) { ?>
                <span class="badge badge-primary"><?=$user_notifications_to_read_quantity?></span>
              <?php } ?>
            </a>
          </li>
          
        </ul>
        <ul class="navbar-nav navbar-right">
          <li class="nav-item">
              <form class="form-inline top-search-form" action="/" method="get">                 
                  <input type="hidden" name="task" value="search">
                  <input type="hidden" name="search_type" value="general">

                  <input type="text" name="s" class="form-control form-control-sm input-search" placeholder="<?=edc__( 'Поиск...' )?>" value="<?=$search_str?>" minlength="<?=$g_minimum_search_string_length?>" required />
                  
                  <button type="submit" class="btn-search" name="btn_search" class="btn"></button>
              </form>
          </li>
          <!-- Контакты -->
          <li class="nav-item <?=( edc_is_section_contacts() ) ? 'active' : ''?>">
              <a class="nav-link" href="<?=get_permalink( 508 )?>"><?=get_the_title( 508 )?></a>
          </li>
          <!-- Выход -->
          <li class="nav-item">
              <a class="nav-link" href="<?=wp_logout_url()?>"><?=edc__( 'Выход' )?></a>
          </li>
        </ul>
      </div>
  <?php } else { ?>
      <div class="collapse navbar-collapse" id="navbarText">
        <ul class="navbar-nav mr-auto"></ul>
        <ul class="navbar-nav navbar-right">
          <li class="nav-item">
              <a class="nav-link" href="<?=wp_login_url()?>"><?=edc__( 'Войти' )?></a>
          </li>
        </ul>
      </div>
  <?php } ?>
</nav>
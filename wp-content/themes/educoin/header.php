<?php
    // get theme folder
    $theme_folder_url = get_stylesheet_directory_uri();

    // getting meta tags
    $page_description = 'Тренировочная платформа для проекта Educoin.biz';
    $page_keywords = 'программирование';
    $page_lang_code = 'ru';
    $page_title = $_SERVER['SERVER_NAME'];
    
    // define body classes
    $new_body_classes = array( edc_get_current_page_name() );
    $body_classes = get_body_class( $new_body_classes );
    $body_class = implode( ' ', $body_classes );
    
    // get current page id
    $cur_page_id = get_the_id();
    
    // should we show comfortable reading mode?
    if ( !get_field( 'edc_is_remove_comfortable_reading_mode', $cur_page_id ) &&
         ( is_page() || is_single() )
    ) {
        $body_class = $body_class . ' ' . 'comfortable-reading-mode';  
    }
?>


<!DOCTYPE html>
<html <?php language_attributes(); ?> class="no-js">
    <head>
       
        <?php wp_head(); ?>
        
        <meta charset="utf-8">
        <meta http-equiv="Cache-control" content="public">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
       
        <meta name="description" content="<?php echo $page_description; ?>">
        <meta name="keywords" content="<?php echo $page_keywords; ?>">
        <meta http-equiv="Lang" content="<?php echo $page_lang_code; ?>">
        
        <title><?=$page_title?></title>
        
        <!--SET FAVICON-->
        <link rel="icon" href="<?php echo $theme_folder_url; ?>/images/favicon/favicon.ico" type="image/x-icon" />
        <link rel="shortcut icon" href="<?php echo $theme_folder_url; ?>/images/favicon/favicon.ico" type="image/x-icon" />
        
        <!--INCLUDE CSS-->
        <!-- Bootstrap -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
        <link rel="stylesheet" href="https://getbootstrap.com/docs/4.0/examples/sticky-footer-navbar/sticky-footer-navbar.css">
        <!-- Fancybox -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css" />
        <!-- Main styles -->
        <link rel="stylesheet" href="<?=get_stylesheet_directory_uri()?>/css/main.min.css?v=<?=edc_get_file_version_tail( 'css/main.min.css' )?>">
        
        <!-- Hotjar Tracking Code for https://lessons.educoin.biz -->
        <script>
            (function(h,o,t,j,a,r){
                h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
                h._hjSettings={hjid:1535319,hjsv:6};
                a=o.getElementsByTagName('head')[0];
                r=o.createElement('script');r.async=1;
                r.src=t+h._hjSettings.hjid+j+h._hjSettings.hjsv;
                a.appendChild(r);
            })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
        </script>
        
    </head>
    <body class="<?=$body_class?>">         
    
        <!-- Main overlay -->
        <div id="main-overlay" class="main-overlay hidden">
            <div class="main-overlay__overlay"></div>
            <div class="main-overlay__loader">
              <div class="loadingio-spinner-double-ring-47vm2rwi136"><div class="ldio-5aaut65ixpj"><div></div><div></div><div><div></div></div><div><div></div></div></div></div>       
            </div>
        </div>   
    
        <?php get_template_part( 'partials/top_nav' ); ?>
        
        <div class="main-content">
           
            <?php get_template_part( 'partials/messages' ); ?>
       



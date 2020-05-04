<?php
    // define the constants
    define( 'TEXTDOMAIN', 'educoin' );

    // different helpers
    require_once( 'inc/helpers.php' ); // @todo: refactor and make it as class
    require_once( 'inc/edc-single-execution.php' );
    // emoji library
    use Spatie\Emoji\Emoji as Emoji;
    require_once( 'inc/vendors/Emoji/Emoji.php' );

    // protected posts ids which we want to protect from deletion, viewing by unregistered users
    $protected_post_ids = array();
    $protected_post_ids['delete']['pages'] = array( '32', '34', '46', '48', '52', '54', '61', '76', '23', '395', '592' );
    $protected_post_ids['update']['pages'] = array( '23' );
    
    // when user go to platform at first he must set chat settings in order
    // to have communications with us. Allowed posts:
    // Contacts
    $allowed_posts_ids_while_chat_is_not_set = [ 508, 584 ];
    
    // which of the posts user can view even if he is not registered at the platform
    $allowed_public_posts_ids = [ 936 ];
    
    // when user is blocked in order he should have ways for connection with us.
    // Allowed posts: Contacts
    $g_allowed_posts_ids_while_user_blocked = [ 508 ];
    
    // posts ids
    $posts_ids = [
        'settings' => 34,
        'user_course_certificate_view' => 936,
        'homeworks_checkings' => 48,
    ];
    
    // ID of demonstrative course category for demo purposes
    $demo_category_id = 19;
    // login of demo user
    $user_demo_login = 'DemoUser';
    
    // default queries limit
    $default_db_results_limit = 100;
    
    // minimum search string length
    $g_minimum_search_string_length = 2;
    
    // demo mode variables
    // how many lessons allowed to pass course in demo mode?
    $g_demo_mode_qnty_allowed_lessons = 3;
    // how many days allowed to pass course in demo mode?
    $g_demo_mode_qnty_allowed_days = 7;
    
    // IP-address from which we can get cron jobs 
    $g_allowed_cron_jobs_ip = '78.47.106.50';
    
    // debugging email
    $g_debuggin_email = 'alexey.denisiuk@gmail.com';

    // get Telegram Chatbot object
    global $telegram_bot;
    require_once( get_template_directory() . '/inc/telegram-bot.php' );
    $telegram_chatbot_token = 'testtesttesttesttesttesttest';
    if ( !empty( $telegram_chatbot_token ) ) {
        $telegram_bot = new Telegram_Bot( $telegram_chatbot_token );
    } else {
        $telegram_bot = null;
    }
    
    
    
    





    
// next functions helping in searching text on website ignoring tags in them
add_filter( 'posts_join', 'edc_filter_search_page_request_join', 10, 2 );
function edc_filter_search_page_request_join( $join ) {
    if( is_search() ) {
        global $wpdb;
        $join .= " LEFT JOIN `{$wpdb->prefix}edc_search_data` AS search_data ON {$wpdb->prefix}posts.ID = search_data.post_id";
    }
	return $join;
}
add_filter( 'posts_where', 'edc_filter_search_page_request_where', 10, 2 );
function edc_filter_search_page_request_where( $where ) {
    if( is_search() ) { 
        global $wpdb;
        
        $query = get_search_query();
        $query = like_escape($query );
        
        $where .= " OR ((search_data.plain_text LIKE '%$query%') {$where} )";  
    }
	return $where;
}
add_action( 'save_post', 'edc_save_post', 10, 3 );
function edc_save_post( $post_ID, $post, $update ) {

    global $wpdb;

    $records = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}edc_search_data WHERE post_id = {$post_ID}" );
    $is_present = $records ? true : false;

    // prepare text for storing in database
    $data['plain_text'] = strip_tags( $post->post_content );
    $data['plain_text'] = str_replace( '&nbsp;', ' ', $data['plain_text'] );

    // shoud we insert or update record in database?
    if ( $is_present ) {
        $where = array( 'post_id' => $post_ID );     
        $wpdb->update( "{$wpdb->prefix}edc_search_data", $data, $where );
    } else {
        $data['post_id'] = $post_ID;
        $wpdb->insert( "{$wpdb->prefix}edc_search_data", $data );
    }
}















 
 






    // is website in maintenance mode?
    if ( MAINTENANCE_MODE ) {
        // is current user allowed to work within this mode?
        if ( get_current_user_id() != MAINTENANCE_MODE_ALLOWED_USER_ID ) {
            wp_redirect( get_site_url() . '/maintenance.php' );
            exit;
        }
    }
    
    
    
    

    // routines connected to sessions
    add_action( 'init', 'edc_start_session', 1 );
    add_action( 'wp_logout', 'edc_end_session' );
    add_action( 'wp_login', 'edc_end_session' );

    function edc_start_session() {
        // start session only for authorized users
        if( is_user_logged_in() && ! session_id() ) {
            session_start();
        }
    }

    function edc_end_session() {
        // delete session
        session_unset();
        session_destroy();
        session_write_close();
        setcookie( session_name(), '', 0, '/' );
        session_regenerate_id( true );
      
        // deleting all of the cookies which are used in private area for authorized users
        edc_delete_auth_cookies();
    }
    
    // define the ajax URL for frontend
    add_action( 'wp_head', 'edc_wp_head_ajax_url' );
    function edc_wp_head_ajax_url() {
    
        echo '<script>var ajaxUrl = "' . admin_url( 'admin-ajax.php' ) . '";</script>';
    }
    
    // prevent deletion of necessary posts
    add_action( 'delete_post', 'edc_prevent_page_deletion' );
    add_action( 'wp_trash_post', 'edc_prevent_page_deletion' );
    // function for preventing deletion of necessary pages
    function edc_prevent_page_deletion( $post_id ) {
        global $protected_post_ids;
        if ( in_array( $post_id, $protected_post_ids['delete']['pages'] ) ) {
            exit( 'The page you were trying to delete is protected' );
        }
    }
    // prevent updating of necessary posts
    add_action( 'pre_post_update', 'edc_prevent_page_update' );
    // function for preventing updating of necessary pages
    function edc_prevent_page_update( $post_id ) {
        global $protected_post_ids;
        if ( in_array( $post_id, $protected_post_ids['update']['pages'] ) ) {
            exit( 'The page you were trying to save is protected' );
        }  
    }
    
    // make additional validation of Username for function 'validate_username()'
    // (is used in registration form, on login form, etc.)
    add_filter( 'validate_username', 'edc_additional_username_validation', 10, 2 );
    function edc_additional_username_validation( $is_valid, $username ) {
        // make additional validation in case wordpress from his side tells us that
        // Username is correct
        if ( $is_valid ) {
            // check if username consists only of latin letters and digits and shouldn't
            // start with digits
            $regexp_tmpl = "/^[a-zA-Z]([0-9a-zA-Z])+$/"; 
            $is_valid = ( preg_match( $regexp_tmpl, $username ) ) ? true : false;
        }

        return $is_valid;
    }
    
    // store extended user info after his registration
    add_action( 'user_register', 'edc_store_user_registering_data', 10, 1 );
    function edc_store_user_registering_data( $user_id ) {

        // add extended user information
        edc_add_user_extended_data( $user_id );
    }

    // do not allow to users (non-admins) go to 'wp-admin'
    add_action( 'admin_init', 'edc_not_allow_non_admin_users_go_to_admin_section' );
    function edc_not_allow_non_admin_users_go_to_admin_section() {
        
        // is current user has access to admin section?
        if ( !current_user_can( 'edc_view_admin_section' ) ) {
            
            // search for '/wp-admin' in requested uri
            $is_user_go_to_admin_section = preg_match( '/^[\\/]wp-admin/', $_SERVER['REQUEST_URI'] );

            // redirect user to Dashboard page if tried to go to admin section and
            // the request is not Ajax-request
            if ( $is_user_go_to_admin_section && !wp_doing_ajax() ) {
                wp_redirect( edc_get_default_internal_page_url() );
                exit;
            }
        }
    }
    
    // check whether all required data is set
    add_action( 'template_redirect', 'edc_check_required_data_set' );
    function edc_check_required_data_set() {

        global $current_user;
        $current_user_id = get_current_user_id();
        // get extended user information
        $user_extended = edc_get_user_extended( $current_user_id );
        
        // get current user role
        $cur_user_role = edc_get_user_role();
        
        // is current user has some certain role?
        // do not check some required fields for all users but just for some certain roles of users
        if ( in_array( $cur_user_role, edc_get_students_roles() ) ) {
        
            // check whether user have valid value in Telegram Chat ID
            if ( empty( $user_extended['telegram_chat_id'] ) || 
                 empty( $user_extended['is_valid_telegram_chat_id'] ) ) {

                // get needed data
                global $allowed_posts_ids_while_chat_is_not_set;
                $cur_post_id = get_queried_object_id();

                // if current page is not settings page then redirect to it
                if ( !edc_is_section_user_settings() &&
                     !in_array( $cur_post_id, $allowed_posts_ids_while_chat_is_not_set ) ) {

                    global $posts_ids;

                    // get redirection URL to settings page in order to let the user fill correct chat id value
                    $error_msg = __( 'Заполни ниже текстовое поле "Telegram чат ID" корректным значением. Следуй инструкциям описанных рядом с этим полем. В крайнем случае, если все выполнишь и не получится, то свяжись с нами по нашим контактам в соответствующем разделе.', TEXTDOMAIN );
                    $redirection_url = get_permalink( $posts_ids['settings'] );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $error_msg, 'error' );

                    wp_redirect( $redirection_url );
                    exit();
                }
            }
        }
    }

    // remove admin bar for people who has no access to admin section
    add_action( 'after_setup_theme', 'edc_remove_admin_bar_for_non_admins' );
    function edc_remove_admin_bar_for_non_admins() {
        if ( !current_user_can( 'edc_view_admin_section' ) ) {
            show_admin_bar( false );
        }
    }

    // redirect user to Dashboard page when he Log Ins
    add_action( 'wp_login', 'edc_redirect_after_user_logins', 10 );
    function edc_redirect_after_user_logins() {
        wp_redirect( edc_get_default_internal_page_url() );
        exit;
    }

    // include css styles files for Login Page
    add_action( 'login_enqueue_scripts', 'edc_include_login_page_css_styles' );
    function edc_include_login_page_css_styles() {
        $relat_path_to_file_in_theme = '/css/main.min.css';
        wp_enqueue_style( 'main-styles', get_stylesheet_directory_uri() . $relat_path_to_file_in_theme, null, edc_get_file_version_tail( $relat_path_to_file_in_theme ), 'screen' );
    }

        
    // include javascript-files
    add_action( 'wp_enqueue_scripts', 'edc_include_scripts' );
    function edc_include_scripts() {

        // deregister defafult jquery file
        wp_deregister_script( 'jquery-core' );
    
        // get current page name
        $current_page_name = edc_get_current_page_name();
    
        $js_files = [];
        
        // jquery
        $js_files['jquery-core']    = [ 'path' => '/js/vendors/jquery-3.2.0.min.js', 
                                        'dependency' => [] ];
        // bootstrap
        $js_files['popper.js']      = [ 'path' => '/js/vendors/bootstrap/popper-1.14.7.min.js', 
                                        'dependency' => ['jquery'] ];
        $js_files['bootsrtap']      = [ 'path' => '/js/vendors/bootstrap/bootstrap-4.3.1.min.js', 
                                        'dependency' => ['jquery'] ];
        // JavaScript Cookie
        $js_files['js-cookie']      = [ 'path' => '/js/vendors/js-cookie/js.cookie.js', 
                                        'dependency' => ['jquery'] ];
        // Readmore.js
        $js_files['readmore.js']    = [ 'path' => '/js/vendors/readmore.js/readmore.min.js', 
                                        'dependency' => ['jquery'] ];
        // Fancybox
        $js_files['fancybox']       = [ 'path' => '/js/vendors/fancybox/jquery.fancybox.min.js', 
                                        'dependency' => ['jquery'] ];
                          
        // define on what pages we should include needed scripts
        if ( is_singular( 'lesson' ) ) {
            $js_files['countdown']           = [ 'path' => '/js/vendors/countdown/jquery.countdown.min.js', 
                                                 'dependency' => ['jquery'] ];
            $js_files['edc-countdown']       = [ 'path' => '/js/edc-countdown.js', 
                                                 'dependency' => ['jquery'] ];
                                 
            $js_files['tinymce']             = [ 'path' => '/js/vendors/tinymce/tinymce.min.js', 
                                                 'dependency' => ['jquery'] ];
            $js_files['edc-tinymce']         = [ 'path' => '/js/edc-tinymce.js', 
                                                 'dependency' => ['jquery'] ];    
                                            
            $js_files['page-course-lesson']  = [ 'path' => '/js/page-course-lesson.js', 
                                                 'dependency' => ['jquery'] ];
        }
        // homework checking page
        else if ( ( $current_page_name == 'edc-homework-checking' ) || 
                ( $current_page_name == 'edc-user-profile-in-admin-section' ) ) {
        
            $js_files['tinymce']             = [ 'path' => '/js/vendors/tinymce/tinymce.min.js', 
                                                 'dependency' => ['jquery'] ];
            $js_files['edc-tinymce']         = [ 'path' => '/js/edc-tinymce.js', 
                                                 'dependency' => ['jquery'] ];   
        }
        // search page
        else if ( $current_page_name == 'edc-search' ) {
        
            $js_files['search']             = [ 'path' => '/js/edc-search.js', 
                                                 'dependency' => ['jquery'] ];
        }
       
        // our main JS files
        $js_files['functions.js']   = [ 'path' => '/js/functions.js', 
                                        'dependency' => ['jquery'] ];
        $js_files['main.js']        = [ 'path' => '/js/main.js', 
                                        'dependency' => ['jquery'] ];

        // add JavaScript files to the page
        foreach ( $js_files as $name => $js_file ) {
            wp_enqueue_script( $name, 
                               get_stylesheet_directory_uri() . $js_file['path'], 
                               $js_file['dependency'], 
                               edc_get_file_version_tail( $js_file['path'] ), 
                               true );
        }
        
        // transfer to main.js data from PHP (Current User Id)
        wp_localize_script( 'main.js', 'php_currentUser', [ 'id' => get_current_user_id() ] );  
    }

    add_action( 'init', 'edc_init' );
    function edc_init() {
    
        // save user last visit date
        edc_analytics_save_user_last_visit_date();
        
        // It is a part of client timezone defining routine:
        // set server date to cookie
        edc_tz_set_server_date_to_cookie();
        
        // show featured image
        add_theme_support( 'post-thumbnails' );
        // add excerpt field
        add_post_type_support( 'page', 'excerpt' );
        // turn off visual editor for all users
        add_filter( 'user_can_richedit' , '__return_false', 50 );
        
        // process URL
        add_action( 'template_redirect', 'my_process_url' );
        // change register form translation
        add_action( 'register_form', 'edc_change_register_form_translation' );
        
        // is current user has Teacher role?
        if ( current_user_can( 'edc_teacher' ) ) {
            require_once( 'inc/edc-role-edc-teacher.php' );
            $role_edc_teacher = new EDC_Role_Edc_Teacher();
            $role_edc_teacher->init();
        }
    }




    















        
















    
    
    
    
    
    
    
    
    
    
    
    
    
	
	
	
	// change register form translation
    function edc_change_register_form_translation() {

        $content = ob_get_contents();
        $content = str_replace( __( 'Имя пользователя' ), __( 'Логин' ), $content );
        ob_get_clean();
        echo $content;
    }
    
    
    
    
    
    
    
    
    
    // when user is already authorized and go Register/Login/Reset Password 
    // (except Logout page) pages then he shouldn't see these pages
    if ( is_user_logged_in() ) {

        if ( ( $_SERVER['SCRIPT_NAME'] === '/wp-login.php' ) && 
             ( strpos( $_SERVER['QUERY_STRING'], 'action=logout' ) === false ) ) {
             // make redirection to default internal page
             wp_redirect( edc_get_default_internal_page_url() );
        }
    }
    
    
    
    
    
    
    
    
    function my_process_url() {

        // get important task params
        $task   = ( isset( $_REQUEST['task'] ) ) ? sanitize_key( $_REQUEST['task'] ) : '';
        $action = ( isset( $_REQUEST['action'] ) ) ? sanitize_key( $_REQUEST['action'] ) : '';

        
        
        
        
        
        
        
        
        
        
        
        
        // sent notification in case we got 404 error
        // did we get 404 error page?
         if( is_404() ) {

            // set which 404 errors to ignore
            $ignore_links = [
                '/course',
                '/wp-content/themes/educoin/images/favicon/favicon.ico',
            ];
            // set timeout for sending notifications about 404 errors
            // hours * minutes * seconds
            $sending_period = 1 * 60 * 60;         


            // get current link
            $cur_link = $_SERVER['REQUEST_URI'];
            // should we ignore current link
            if ( !in_array( $cur_link, $ignore_links ) ) {

                // get current time
                $cur_time = time();
                // get recent time when we sent previous notification
                $recent_time_404_notification_sent = get_option( 'edc_recent_time_404_notification_sent' );

                // can we send notification?
                if ( empty( $recent_time_404_notification_sent ) || 
                     ( $cur_time > ( $recent_time_404_notification_sent + $sending_period ) ) ) {

                    // set mail data
                    $email = get_option( 'admin_email' );
                    $headers = "content-type: text/html" . "\r\n";
                    $subject = "Была зафиксирована ошибка 404";
                    $message  = "";
                    $message .= "<p>Был зафиксирован переход на несуществующую страницу <i>" . get_site_url() . "{$cur_link}</i></p>";
                    $message .= "<p>Также могли быть и другие 404ые ошибки.</p>";
                    $message .= "<p>Нужно перепроверить в админке и поставить редиректы.</p>";

                    // send notification about 404 error to administrator by email
                    wp_mail( $email, $subject, $message, $headers );

                    // save to database when we sent notification
                    delete_option( 'edc_recent_time_404_notification_sent' );
                    add_option( 'edc_recent_time_404_notification_sent', $cur_time );
                }
            }
        }
        
        
        
        
        
        
        
        
        
        
        


        // check whether we need to make some cron job
        if ( $task == 'cron_job' ) {
        
            global $g_allowed_cron_jobs_ip;
            
            $cur_user_ip = edc_get_user_ip();
        
            // check whether we have CRON job request from allowed IP-address
            if ( $cur_user_ip !== $g_allowed_cron_jobs_ip ) {
                // get error message
                $msg = "CRON jobs: Error. IP-address '{$cur_user_ip}' is not allowed.";
                // write this issue to error's log file
                edc_log_error( $msg, false );
                
                // show message
                echo $msg;
                    
                // exit
                exit();
            }
        
            // define which type of action we have to do
            switch ( $action ) {
                case 'remove_supports_from_obsolete_students':
                  
                    // removing trash students from supports
                    $qnty_affected_students = edc_remove_supports_from_obsolete_students();
                    
                    // get message
                    $msg = "CRON jobs: Success. 'remove_supports_from_obsolete_students' - done. Affected students: {$qnty_affected_students}.";
                    // write this action to log file
                    edc_log( $msg );
                    
                    // show message
                    echo $msg;
                    
                    // exit
                    exit();
                    break;
                default:  
                    // get error message
                    $msg = "CRON jobs: Error. Action '{$action}' is not found.";
                    // write this issue to error's log file
                    edc_log_error( $msg, false );
                    
                    // show message
                    echo $msg;
                    
                    // exit
                    exit();
            }
        }
        

        
        
        
        
        
        
        
        
        
        

        // check whether we need to make demo loginning
        if ( $task == 'login_demo' ) {
        
            // get needed demo user from request
            $demo_user_login = sanitize_user( $_GET['user'] );
            
            // is user is demo user?
            if ( edc_is_user_demo( $demo_user_login ) ) {
            
                // make loginning
                edc_make_user_loginning( $demo_user_login );
                
                // redirect user to root of the website
                wp_redirect( get_bloginfo( 'url' ) );
                exit();
            }
        }
        
        
        
        
        
        
        
        
        
        // get telegram bot recent message
        if ( ( $task == 'get_bot_recent_messages' ) && 
             ( current_user_can( 'administrator' ) ) ) 
        {
            global $telegram_bot;
        
            $query_data = [ 'offset' => -10 ];
            dd( $telegram_bot->get_updates( $query_data ) );
        }
        
        

        
        
        
        
        
        
        
        
        
        
        
        
        
        // register new students
        if ( ( $task == 'register_new_students' ) && 
             ( current_user_can( 'administrator' ) ) ) {
      
            // open information about adding users
            require_once( 'data/register_new_students.php' );
            global $g_data_register_new_students;
            global $g_data_register_new_students_other_data;        
            
            // read data about adding students
            $students   = $g_data_register_new_students;
            $other_data = $g_data_register_new_students_other_data;

            // create array with statuses
            $statuses = [
                'fail'   => [],
                'success' => [],
            ];

            // register users
            foreach ( $students as $student ) {
            
                $student_status = [];
            
                $cur_time = time();
                $user_email_hash = wp_hash( $student['user_email'] );
                $recent_time_sent = get_option( 'edc_recent_registration_email_sent_' . $user_email_hash );
                $in24hours = 60 * 60 * 24;

                global $g_debuggin_email;

                // is user registered and email already sent today?
                if ( ( $student['user_email'] != $g_debuggin_email ) &&
                     !empty( $recent_time_sent ) && 
                     ( $cur_time < ( $recent_time_sent + $in24hours ) ) ) {
                    // add status info to array
                    $student_status['registration']  = 'False. Canceled.';
                    $student_status['email_sending'] = 'False. It was already sent.';
                    $student_status['student_data']  = $student;
                    // add this operation to fail array
                    $statuses['fail'][] = $student_status;
                    // go to the next student
                    continue;
                }
            
                // is user email not already exist?
                if ( !email_exists( $student['user_email'] ) ) {
                
                    // register user
                    $result = wp_insert_user( $student );
                    
                    // did we get any errors while registering new user?
                    if( is_wp_error( $result ) ) {       
                        // add status info to array
                        $student_status['registration']  = 'False. ' . $result->get_error_code() . '.';
                        $student_status['email_sending'] = 'False. Not sent.';
                        $student_status['student_data']  = $student;
                        // add this operation to fail array
                        $statuses['fail'][] = $student_status;
                        // go to the next student
                        continue;
                    }
                    
                    // get user ID
                    $student_id = $result;
                    // add status info to array
                    $student_status['registration']  = 'True. Registered successfully.';
                    $student_status['student_data']  = $student;

                } 
                // such user email already exists
                else {
                    // add status info to array
                    $student_status['registration']  = 'False. Such user email already exists.';
                    $student_status['student_data']  = $student;
                    
                    // get user ID
                    $student_id = get_user_by( 'email', $student['user_email'] )->ID;
                    
                    // get user's login
                    $student['user_login'] = get_user_by( 'email', $student['user_email'] )->user_login;
                }
                
                // get blocking data
                $blocking_data = ( isset( $student['blocking_data'] ) ) ? $student['blocking_data'] : '';
                $blocking_data = ( isset( $other_data['blocking_data'] ) && empty( $blocking_data ) ) ? $other_data['blocking_data'] : $blocking_data;
                
                // get user password
                $user_pass = ( isset( $student['user_pass'] ) ) ? $student['user_pass'] : '';
                $user_pass = ( isset( $other_data['user_pass'] ) && empty( $user_pass ) ) ? $other_data['user_pass'] : $user_pass;
                // set user's password: we can have situation when user was exist so we need for 
                // all situations just set new password
                wp_set_password( $user_pass, $student_id );
                
                // get user role
                $user_role = ( isset( $student['user_role'] ) ) ? $student['user_role'] : '';
                $user_role = ( isset( $other_data['user_role'] ) && empty( $user_role ) ) ? $other_data['user_role'] : $user_role;
                
                // get telegram chat url
                $group_telegram_chat_url = ( isset( $student['group_telegram_chat_url'] ) ) ? $student['group_telegram_chat_url'] : '';
                $group_telegram_chat_url = ( isset( $other_data['group_telegram_chat_url'] ) && empty( $group_telegram_chat_url ) ) ? $other_data['group_telegram_chat_url'] : $group_telegram_chat_url;
                
                // assign user to course
                foreach ( $other_data['courses'] as $course ) {
                    // add to course
                    edc_add_course_to_user( $course['id'], $student_id );
                    
                    // set access period if needed
                    if ( isset( $course['access_period'] ) ) {
                    
                        if ( isset( $course['course_date_start'] ) ) {
                            $course_date_end = strtotime( $course['course_date_start'] );
                        } else {
                            $course_date_end = time();
                        }
                        $course_date_end = $course_date_end + ( $course['access_period'] * 60 * 60 * 24 );
                        $course_date_end = date( 'd.m.Y', $course_date_end );
                    
                        $data['course_date_end'] = $course_date_end;
                    }
                    if ( isset( $course['is_demo_mode'] ) ) {
                        $data['is_demo_mode'] = $course['is_demo_mode'];
                    }
                    if ( isset( $course['course_date_start'] ) ) {
                        $data['course_date_start'] = $course['course_date_start'];
                    }
                    // set user course role
                    $data['role'] = $user_role;
  
                    // save data to database
                    edc_set_user_course_data( $course['id'], $student_id, $data );
                }
                
                $cur_website = get_site_url();

                // send email
                $headers = "content-type: text/html" . "\r\n";
                
                switch ( $other_data['registration_type'] ) {
                    case 'register_for_quiz':
                        // get email subject
                        $subject = get_field( 'edc_new_registration_quiz_email_subject', 395 );
                        // get email body
                        $message = get_field( 'edc_new_registration_quiz_email_body', 395 );
                        $message = str_replace( '[EDC_USER_FIRST_NAME]', $student['first_name'], $message );
                        $message = str_replace( '[EDC_WEBSITE]', $cur_website, $message );
                        $message = str_replace( '[EDC_USER_LOGIN]', $student['user_login'], $message );
                        $message = str_replace( '[EDC_USER_EMAIL]', $student['user_email'], $message );
                        $message = str_replace( '[EDC_USER_PASS]', $user_pass, $message );
                        break;
                    default:
                        // get email subject
                        $subject = get_field( 'edc_new_registration_course_email_subject', 395 );
                        // get email body
                        $message = get_field( 'edc_new_registration_course_email_body', 395 );
                        $message = str_replace( '[EDC_USER_FIRST_NAME]', $student['first_name'], $message );
                        $message = str_replace( '[EDC_WEBSITE]', $cur_website, $message );
                        $message = str_replace( '[EDC_USER_LOGIN]', $student['user_login'], $message );
                        $message = str_replace( '[EDC_USER_EMAIL]', $student['user_email'], $message );
                        $message = str_replace( '[EDC_USER_PASS]', $user_pass, $message );
                        $message = str_replace( '[EDC_GROUP_TELEGRAM_CHAT_URL]', $group_telegram_chat_url, $message );
                        break;
                }
                
                // send email to user
                wp_mail( $student['user_email'], $subject, $message, $headers );
                
                // save to database when we sent email
                delete_option( 'edc_recent_registration_email_sent_' . $user_email_hash );
                add_option( 'edc_recent_registration_email_sent_' . $user_email_hash , $cur_time );

                // add status info to array
                $student_status['email_sending'] = 'True. Sent.';
                
                // set blocking information about user
                if ( !empty( $blocking_data ) ) {
                    update_field( 'edc_blocking_info', $blocking_data, 'user_' . $student_id );
                } 
                
                // add this operation to success array
                $statuses['success'][] = $student_status;  
            }
        
            // show info messages about result
            d( "Registering of students completed!" );
            d( $statuses );
            exit();
        }









        // define whether user should be authorized or not
        global $allowed_public_posts_ids;
        $cur_post_id = get_queried_object_id();

        // check whether guest is allowed to see the requested page
        if ( !is_user_logged_in() && !in_array( $cur_post_id, $allowed_public_posts_ids ) ) {
            if ( !edc_is_guest_allowed_to_view_requested_page() ) {
                // redirect him to Login page
                wp_redirect( wp_login_url( get_permalink() ) );
                exit();
            }
        }
        
        // check whether user is allowed to see admin section
        if ( edc_is_section_admin() ) {
            if ( !current_user_can( 'edc_view_custom_admin_section' ) ) {
            
                // redirect him to start page
                wp_redirect( get_bloginfo( 'url' ) );
                exit();
            }
        }
        
        // do we got the short URL?
        if ( isset( $_GET['sui'] ) ) {
        
            // get full URL from the short one
            $redirection_url = edc_undo_temp_short_url( sanitize_text_field( $_GET['sui'] ) );
            
            // if short URL is absent then show message about it to user
            if ( is_null( $redirection_url ) ) {
            
                $redirection_url = edc_get_default_internal_page_url();
            
                // link is already expired
                $msg_text = sprintf( __( 'Временная ссылка не существует', TEXTDOMAIN ), sanitize_email( $_POST['user_email'] ) );
                $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'notice' );
            }
            
            wp_redirect( $redirection_url );
            exit();
        }

        // do we have GoTo URL routine?
        if ( isset( $_GET['goto'] ) ) {

            // do we need to do User Attended Webinar routine?
            if ( $task === 'user_attended_webinar' ) {
            
                // get neccessary data
                $lesson_id = intval( $_GET['lesson_id'] );
                
                edc_analytics_save_user_joined_webinar_date( $lesson_id );
            }
            
            // get URL to which we need to redirect
            $redirection_url = hex2bin( sanitize_text_field( $_GET['goto'] ) );
            
            // apply redirect
            wp_redirect( $redirection_url );
            exit(); 
        }
        
        // redirect from start page to default internal page
        if ( edc_get_current_page_name() === 'edc-home' ) {
            wp_redirect( edc_get_default_internal_page_url() );
            exit();
        }
     
     
     
     
     
     
     
     
     
      
        // get blocking data about user
        $blocking_data = edc_get_user_blocking_data();
                
        // is there any info about current user blocking?
        if ( !empty( $blocking_data ) ) {
                    
            // get blocking date
            $blocking_date = strtotime( $blocking_data[0] );
            // get current date
            $current_date = time();
            // get time since which we should notification message about blocking
            $notification_period_days = get_field( 'edc_before_blocking_notification_period_days', 395 );
            $notification_period = $notification_period_days * ( 24 * 60 * 60 );

            // should we just show warning message about blocking?
            if ( $current_date >= ( $blocking_date - $notification_period ) && 
               ( $current_date < $blocking_date ) ) {
            
                // show warning message
                edc_remove_permanent_notification();
                edc_add_permanent_notification( $blocking_data[1], 'warning' );
            } 
            // should we block user?
            else if ( $current_date >= $blocking_date ) {
            
                // get needed data
                global $g_allowed_posts_ids_while_user_blocked;
                $cur_post_id = get_queried_object_id();
            
                if ( ( edc_get_current_page_name() !== 'edc-courses' ) &&
                     !in_array( $cur_post_id, $g_allowed_posts_ids_while_user_blocked )  ) {

                    // get redirection URL
                    $redirection_url = edc_get_default_internal_page_url();

                    // show notification
                    edc_remove_permanent_notification();
                    edc_add_permanent_notification( $blocking_data[2], 'warning' );

                    wp_redirect( $redirection_url );
                    exit();

                } else {
                
                    // show notification
                    edc_remove_permanent_notification();
                    edc_add_permanent_notification( $blocking_data[2], 'warning' );
                }
            } 
            // we do not need to block user
            else {
           
                // clean messages queue
                edc_remove_permanent_notification();
            } 
        }
        // we do not need to block user
        else {
            // clean messages queue
            edc_remove_permanent_notification();
        } 
        
        
        



        
        
        // check whether user have rights to view current object
        if ( !current_user_can( 'administrator' ) && is_singular() ) {
        
            // get current object ID
            $object_id = get_queried_object_id();
            
            $edc_user_roles_access_allowed = get_field( 'edc_user_roles_access_allowed', $object_id );

            if ( !empty( $edc_user_roles_access_allowed ) ) {
            
                $cur_user_role = edc_get_user_roles()[0]['id'];
                
                if ( !in_array( $cur_user_role, $edc_user_roles_access_allowed ) ) {

                    // get redirection URL
                    $redirection_url = edc_get_default_internal_page_url();            

                    $msg_text = edc__( 'Не хватает прав для просмотра' );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );

                    // apply redirect
                    wp_redirect( $redirection_url );
                    exit();
                }
            }
        }










        // check whether we are in course's section
        if ( edc_is_section_courses() && !edc_is_course_lists_page() ) {
        
            if ( is_singular( ['lesson', 'additional_article'] ) ) {
                // get current lesson ID
                $cur_lesson_id = get_queried_object_id();
                // getting course of the lesson
                $cur_course_extra = wp_get_post_terms( $cur_lesson_id, 'course' )[0];
                // get course's information
                $cur_course_id = $cur_course_extra->term_id;
            }
            else {
                // get course's information
                $cur_course_id = edc_get_current_course_id();
                $cur_course = edc_get_user_courses( 0, $cur_course_id );
                // get course's extra information
                $cur_course_extra = get_term( $cur_course_id );
            }
                
            // get user course disabling status
            $course_disabling_status = edc_get_user_course_disabling_status( 0, $cur_course_id );
            
            // is user disabled on the course?
            if ( $course_disabling_status['is_disabled'] ) {

                // get redirection URL
                $redirection_url = edc_get_default_internal_page_url();            

                $msg_text = sprintf( edc__( 'Доступ к курсу "%s" закрыт по причине: "%s"' ), $cur_course_extra->name, $course_disabling_status['message'] );
                $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );

                // apply redirect
                wp_redirect( $redirection_url );
                exit();
            }
        }
        
        
        
        
        
        


     

     
     
        // check whether the user allowed to view current lesson
        // is current page is lesson?
        if ( is_singular( ['lesson', 'additional_article'] ) ) {

            // get current lesson ID
            $cur_lesson_id = get_queried_object_id();
            // get lesson passing status
            $lesson_passing_status = get_lesson_passing_status( $cur_lesson_id );

            // getting course ID of the lesson
            $cur_course_id = wp_get_post_terms( $cur_lesson_id, 'course' )[0]->term_id;
            // get user courses
            $user_course_lists = edc_get_user_course_lists();
            $users_courses_ids = array_column( $user_course_lists, 'term_id' );
            
            // is user not allowed to view current course?
            if ( !in_array( $cur_course_id, $users_courses_ids ) ) {
            
                // get redirection URL
                $redirection_url = edc_get_default_internal_page_url();
            
                // saving wasn't applied: such user email already exists
                $msg_text = edc__( 'Отсутствует доступ к курсу' );
                $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
            
                wp_redirect( $redirection_url );
                exit();
            }
            
            // get current user role
            $cur_user_role = edc_get_user_role();
            
            // get checkers homeworks roles
            $checkers_homeworks_roles = edc_get_checkers_homeworks_roles();
           
            // is course ended and lesson is closed?
            if ( is_singular( 'lesson' ) && edc_is_course_ended( $cur_course_id ) && ( $lesson_passing_status == 'closed' ) && !in_array( $cur_user_role, $checkers_homeworks_roles ) ) {
            
                $is_lesson_closed_after_course_ended = get_field( 'edc_is_closed_after_course_ended', $cur_lesson_id );
            
                // is lesson closed after course ended?
                if ( $is_lesson_closed_after_course_ended ) {
                
                    // get redirection URL
                    $redirection_url = edc_get_default_internal_page_url();

                    $msg_text = edc__( 'Курс закончился, а также доступ к этому уроку закрыт' );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );

                    wp_redirect( $redirection_url );
                    exit();
                }
            }
            // is user not allowed to view current lesson?
            else if ( is_singular( 'lesson' ) && 
                      $lesson_passing_status == 'closed' &&  
                      !edc_is_course_homeworks_disabled( $cur_course_id ) &&
                      !edc_is_user_course_homeworks_disabled( $cur_course_id ) &&
                      !in_array( $cur_user_role, $checkers_homeworks_roles ) ) {
            
                // get redirection URL
                $redirection_url = edc_get_default_internal_page_url();
            
                $msg_text = edc__( 'Отсутствует доступ к уроку' );
                $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
            
                wp_redirect( $redirection_url );
                exit();
            }
        }
 
     
     
     
     
     
     
     
     

        
        
        
        
        
        
        
        
        // is current user is demo user and he wants to make some certain operation for
        // saving, deleting and etc.?
        if ( edc_is_current_user_demo() && !empty( $task ) && ( $task != 'search' ) ) {

            // getting redirection url
            $redirection_url = get_permalink();
            
            $msg_text = __( 'У Демо-пользователя нет прав на эту операцию.', TEXTDOMAIN );
            $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );

            // apply redirect
            wp_redirect( $redirection_url );
            exit();
        }
        
        
        
        
        
        
        
        
        
        
        
        
        // save user settings
        if ( ( $task == 'user_settings' ) && ( $action == 'save' ) ) { 
        
            // get current user data
            global $current_user;
            $current_user_id = get_current_user_id();
            // get extended user information
            $prev_user_extended_data = edc_get_user_extended( $current_user_id );
        
            // save settings
            $result = edc_save_current_user_settings( $_POST );

            // getting redirection url
            $redirection_url = get_permalink();
            switch ( TRUE ) {
                case $result === 1:
                    // saving wasn't applied: such user email already exists
                    $msg_text = sprintf( __( 'Почта "%s" уже зарегистрирована в системе. Выберите пожалуйста другую', TEXTDOMAIN ), sanitize_email( $_POST['user_email'] ) );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'warning' );
                    break;
                case $result === 2:
                    // saving wasn't applied: such telegram chat id is not valid
                    $msg_text = sprintf( __( 'Был введен некорректный Telegram чат ID "%s"', TEXTDOMAIN ), sanitize_text_field( $_POST['telegram_chat_id'] ) );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                    break;
                case $result === TRUE:
                    // settings saved
                    
                    // define whether we should send message to user that he sucessfuly conneted his
                    // Telegram messenger
                    $new_telegram_chat_id = sanitize_text_field( $_POST['telegram_chat_id'] );
                    if ( $prev_user_extended_data['telegram_chat_id'] != $new_telegram_chat_id ) {
                        
                        // forming notification message
                        $msg = __( $current_user->user_login . ', Telegram успешно подключен ' . Emoji::signOfTheHorns(), TEXTDOMAIN );
                        // send message to user
                        edc_send_message_user( $current_user_id, $msg );
                    }                    
                    
                    // show message to user that everything has benn saved
                    $msg_text = __( 'Настройки сохранены', TEXTDOMAIN );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'notice' );
                    break;
            }

            // apply redirect
            wp_redirect( $redirection_url );
            exit(); 
        }
        // save user done lesson homework
        else if ( ( $task == 'done_course_lesson_howemork' ) && ( $action == 'save' ) ) { 
        
            // validate saving data
            // telling what fields are required and what we need to check
            $required_fields = array( 'homework' );
            
            // get current lesson ID
            $cur_lesson_id = get_queried_object_id();

            // are required fields invalid?
            if ( !edc_is_valid_saving_fields( $required_fields, $_POST ) ) {
                $error_counter++;
                $error_msg = __( 'Не сохранено. Заполните пожалуйста правильно необходимые поля.', TEXTDOMAIN );
            }
            // is current lesson is demo lesson?
            else if ( edc_is_demo_lesson( $cur_lesson_id ) ) {
                $error_counter++;
                $error_msg = __( 'Не сохранено. Чтобы подать домашнее задание на проверку оплатите пожалуйста весь курс.', TEXTDOMAIN );
            }
            
            // is there any error?
            if ( $error_counter ) {
                // getting redirection url
                $redirection_url = get_permalink();
                $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $error_msg, 'error' );
              
                // apply redirect
                wp_redirect( $redirection_url );
                exit();
            }
        
            // getting redirection url
            $redirection_url = get_permalink();
        
            // do we need just save data of homework?
            if ( isset( $_POST['btn_save'] ) ) {
            
                // save
                $result = edc_save_user_lesson_data( $_POST );
                
                switch ( TRUE ) {
                    case $result === FALSE:
                        // error appeared
                        $msg_text = __( 'При сохранении возникла ошибка', TEXTDOMAIN );
                        $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                        break;
                    case $result === TRUE:
                        // successfully saved
                        $msg_text = __( 'Сохранено', TEXTDOMAIN );
                        $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'notice' );
                        break;
                }
            } 
            // did user send his homework for check?
            else if ( isset( $_POST['btn_send_for_check'] ) ) {
            
                // save
                $result = edc_save_user_lesson_data( $_POST );
                
                if ( $result ) {
                    // send for checking homework
                    $result = edc_send_user_lesson_homework_for_checking( $_POST );
                }

                // we use here just $result_2 because user can just send homework but the text of homework
                // could be the same and in this case it will return FALSE as the result
                switch ( TRUE ) {
                    case $result === FALSE:
                        // error appeared
                        $msg_text = __( 'При отсылке возникла ошибка', TEXTDOMAIN );
                        $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                        break;
                    case $result === TRUE:

                        // define whether current user have support
                        $cur_course_id = edc_get_current_course_id();
                        $cur_support = edc_get_user_current_support( $cur_course_id );

                        // do current user have support?
                        if ( !empty( $cur_support ) ) {

                            $query_params = [
                                'support_id' => $cur_support['support_id'],
                                'is_not_checking_yet' => true,
                                'is_return_quantity' => true
                            ];
                            $qnty_homeworks_with_cur_support = edc_get_homeworks_for_check( $query_params );
                            
                            // get URL for start checking homeworks checkings
                            global $posts_ids;
                            $start_homeworks_check_url = get_permalink( $posts_ids['homeworks_checkings'] );

                            // forming notification message
                            $msg = sprintf( edc__( "На проверку появилась новая работа ".Emoji::incomingEnvelope()." личного ученика! Есть возможность ".Emoji::signOfTheHorns()." зажечь!\n\r\n\rЛичные ученики: %s\n\r\n\rПриступить к проверке: %s" ), $qnty_homeworks_with_cur_support, $start_homeworks_check_url );
                            
                            // send notification message to user
                            edc_send_message_user( $cur_support['support_id'], $msg );
                        } 
                        else {
                        
                            // get course information
                            $taxonomy_term = get_term( $cur_course_id );

                            // get total amount howemorks to check                        
                            $query_params = [
                                'support_id' => 0,
                                'is_not_checking_yet' => true,
                                'is_return_quantity' => true,
                                'course_id' => [ $cur_course_id ],
                            ];
                            $homeworks_quantity_to_check = edc_get_homeworks_for_check( $query_params );

                            // get URL for start checking homeworks checkings
                            global $posts_ids;
                            $start_homeworks_check_url = get_permalink( $posts_ids['homeworks_checkings'] );
                            
                            // forming notification message
                            $msg = sprintf( edc__( "Курс \"%s\"\n\r\n\rПоявилась новая работа ".Emoji::incomingEnvelope()." на проверку! Есть возможность ".Emoji::signOfTheHorns()." зажечь!\n\r\n\rУченики без куратора: %s\n\r\n\rПриступить к проверке: %s" ), $taxonomy_term->name, $homeworks_quantity_to_check, $start_homeworks_check_url );

                            // send notification message to course team
                            edc_send_message_course_team( $cur_course_id, $msg );
                        }

                        // successfully saved
                        $msg_text = __( 'Выслано на проверку', TEXTDOMAIN );
                        $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'notice' );
                        break;
                }
            }

            // apply redirect
            wp_redirect( $redirection_url );
            exit(); 
        }
        // some certain homework actions
        else if ( ( $task == 'homework' ) && ( $action == 'checking' ) ) { 

            // validate saving data
            // telling what fields are required and what we need to check
            $required_fields = array( 'user_id', 'lesson_id' );
            
            // get current user role
            $cur_user_role = edc_get_user_role();
            
            // doesn't current user have rights for this operation?
            if ( !in_array( $cur_user_role, edc_get_checkers_homeworks_roles() ) ) {
                $error_counter++;
                $error_msg = __( 'У вас нет прав на данную операцию.', TEXTDOMAIN );
            }
            // are required fields invalid?
            else if ( !edc_is_valid_saving_fields( $required_fields, $_REQUEST ) ) {
                $error_counter++;
                $error_msg = __( 'Не хватает необходимых данных для данной операции.', TEXTDOMAIN );
            }
            
            // sanitize inputed user data
            $data = edc_sanitize_users_inputed_db_data( $_REQUEST );
            
            // can user take this homework for checking?
            $is_support_can_take_homework_for_checking = edc_is_support_can_take_homework_for_checking( $data['user_id'], $data['lesson_id'] );
            if ( isset( $_POST['btn_start_checking'] ) && 
                 !$is_support_can_take_homework_for_checking['result'] ) {
                $error_counter++;
                $error_msg = $is_support_can_take_homework_for_checking['message'];
            }
            
            // is there any error?
            if ( $error_counter ) {
                // getting redirection url
                $redirection_url = get_permalink();
                $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $error_msg, 'error' );
              
                // apply redirect
                wp_redirect( $redirection_url );
                exit();
            }
            
            // get information about current homework that wanted be checked
            $user_recent_lesson_homework = edc_get_user_recent_lesson_homework( $data['user_id'], $data['lesson_id'] );

            // define which exact action we should do
            if ( isset( $_POST['btn_start_checking'] ) ) {
                // homework could be taken in case it is not checking by other checker
                if ( empty( $user_recent_lesson_homework['checker_id'] ) ) {
                    $result = edc_start_checking_homework( $data['user_id'], $data['lesson_id'] );
                } else {
                    $result = 1;
                }
            } 
            else if ( isset( $_POST['btn_accept'] ) ) {
                global $current_user;
                // homework could be accepted in case it is the same checker
                if ( $user_recent_lesson_homework['checker_id'] == $current_user->ID ) {
                    $result = edc_mark_lesson_accepted( $data['user_id'], $data['lesson_id'], $_POST );
                } else {
                    $result = 2;
                }
            } 
            else if ( isset( $_POST['btn_decline'] ) ) {
                global $current_user;
                // homework could be declined in case it is the same checker
                if ( $user_recent_lesson_homework['checker_id'] == $current_user->ID ) {
                    $result = edc_mark_lesson_not_accepted( $data['user_id'], $data['lesson_id'], $_POST );
                } else {
                    $result = 3;
                }
            } 
            else {
                $result = false;
            }
                
            // getting redirection url
            $redirection_url = get_permalink();
            switch ( TRUE ) {
            
                case $result === TRUE:

                    // get course category data
                    $post_terms = wp_get_post_terms( $data['lesson_id'], 'course' );
                    $category = $post_terms[0];
                    // get lesson title
                    $lesson_title = get_the_title( $data['lesson_id'] );
                    // get lesson's number
                    $lesson_num = get_field( 'edc_lesson_order_num', $data['lesson_id'] );
                    // get quantity of checking requests
                    $qnty_check_request = edc_get_user_lesson_homeworks_attemps_quantity( $data['lesson_id'], $data['user_id'] );
                    // get lesson URL
                    $lesson_url = get_permalink( $data['lesson_id'] ) . "?is_open_hh=1#hh";
                    
                    // define which exact action had been made in terms of homework checking
                    if ( isset( $_POST['btn_start_checking'] ) ) {
                        // forming the user message
                        $msg_user = sprintf( edc__( "Курс \"%s\" урок №%s \"%s\" попытка №%s\n\r\n\rСтатус: Работа начала проверяться ".Emoji::stopwatch() . "\n\r\n\rДетали по ссылке: %s" ), $category->name, $lesson_num, $lesson_title, $qnty_check_request, $lesson_url );
                    } 
                    else if ( isset( $_POST['btn_accept'] ) ) {

                        // get homework data
                        $homework = edc_get_homeworks_attempts( $data['lesson_id'], $data['user_id'], 'accepted' )[0];

                        // forming the user message
                        $msg_user = sprintf( edc__( "Курс \"%s\" урок №%s \"%s\" попытка №%s\n\r\n\rСтатус: Работа принята " . Emoji::signOfTheHorns().Emoji::partyPopper().Emoji::partyPopper().Emoji::partyPopper() . "\n\r\n\rКомментарий: %s\n\r\n\rПрочитай остальное по ссылке: %s\n\r\n\r" ), $category->name, $lesson_num, $lesson_title, $qnty_check_request, $homework['checker_comments_short'], $lesson_url );
                        
                        // forming notification message for checker user
                        $query_params = [
                            'support_id' => $homework['checker_id'],
                            'is_not_checking_yet' => true,
                            'is_return_quantity' => true
                        ];
                        $qnty_homeworks_with_cur_support = edc_get_homeworks_for_check( $query_params );

                        // get allowed courses to which current user is assigned
                        $allowed_courses = edc_get_user_course_lists( $homework['checker_id'], true, 0, true );
                        
                        // get total amount howemorks to check                        
                        $query_params = [
                            'support_id' => 0,
                            'is_not_checking_yet' => true,
                            'is_return_quantity' => true,
                            'course_id' => array_column( $allowed_courses, 'term_id' ),
                        ];
                        $homeworks_quantity_without_support = edc_get_homeworks_for_check( $query_params );
                        
                        // get URL for start checking homeworks checkings
                        global $posts_ids;
                        $start_homeworks_check_url = get_permalink( $posts_ids['homeworks_checkings'] );
                        
                        // do we have homeworks to check?
                        if ( ( $qnty_homeworks_with_cur_support != 0 ) || 
                             ( $homeworks_quantity_without_support != 0 ) ) {
                        
                            $msg = sprintf( edc__( "Ты только что проверил очередную работу! Красава ".Emoji::signOfTheHorns()."! Еще осталось на проверку столько работ:\n\r\n\rЛичные ученики: %s\n\Ученики без куратора: %s\n\r\n\rПриступить к проверке: %s" ), $qnty_homeworks_with_cur_support, $homeworks_quantity_without_support, $start_homeworks_check_url );
                            
                        } 
                        // there are no homeworks to check
                        else {
                            $msg = sprintf( edc__( "Ура! Ты только что проверил последнюю работу! Молодчага ".Emoji::flexedBiceps().Emoji::flexedBiceps().Emoji::flexedBiceps()."! Все работы проверены! ".Emoji::clappingHands().Emoji::signOfTheHorns().Emoji::fire() ) );
                        }
                        
                        // send notification message to user
                        edc_send_message_user( $homework['checker_id'], $msg );

                        // get user name of checker
                        $checker_user_name = get_userdata( $homework['checker_id'] )->display_name;
                        // should we send message to the group chat?
                        if ( isset( $data['is_send_to_group_chat_checker_comments_short'] ) && $data['is_send_to_group_chat_checker_comments_short'] ) {
                            // get student name
                            $user_name = get_userdata( $data['user_id'] )->display_name;
                            // forming notification message
                            $msg = sprintf( __( "Курс \"%s\" урок №%s \"%s\" \n\r\n\rСтатус: Работа принята " . Emoji::signOfTheHorns().Emoji::partyPopper().Emoji::partyPopper().Emoji::partyPopper() . "\n\r\n\rКомментарий куратора ".Emoji::manTechnologist()." %s: %s, %s", TEXTDOMAIN ), $category->name, $lesson_num, $lesson_title, $checker_user_name, $user_name, $homework['checker_comments_short'] );
                            
                            // send notification message to group chat
                            edc_send_message_course( $msg, $category->term_id );
                        }
                    } 
                    else if ( isset( $_POST['btn_decline'] ) ) {

                        // get homework data
                        $homework = edc_get_homeworks_attempts( $data['lesson_id'], $data['user_id'], 'declined' )[0];

                        // forming the user message
                        $msg_user = sprintf( edc__( "Курс \"%s\" урок №%s \"%s\" попытка №%s\n\r\n\rСтатус: Работа отклонена " . Emoji::loudlyCryingFace() . "\n\r\n\rКомментарий: %s\n\r\n\rПрочитай остальное по ссылке: %s" ), $category->name, $lesson_num, $lesson_title, $qnty_check_request, $homework['checker_comments_short'], $lesson_url );

                        // forming notification message for checker user
                        $query_params = [
                            'support_id' => $homework['checker_id'],
                            'is_not_checking_yet' => true,
                            'is_return_quantity' => true
                        ];
                        $qnty_homeworks_with_cur_support = edc_get_homeworks_for_check( $query_params );

                        // get allowed courses to which current user is assigned
                        $allowed_courses = edc_get_user_course_lists( $homework['checker_id'], true, 0, true );
                        
                        // get total amount howemorks to check                        
                        $query_params = [
                            'support_id' => 0,
                            'is_not_checking_yet' => true,
                            'is_return_quantity' => true,
                            'course_id' => array_column( $allowed_courses, 'term_id' ),
                        ];
                        $homeworks_quantity_without_support = edc_get_homeworks_for_check( $query_params );
                        
                        // get URL for start checking homeworks checkings
                        global $posts_ids;
                        $start_homeworks_check_url = get_permalink( $posts_ids['homeworks_checkings'] );
                        
                        // do we have homeworks to check?
                        if ( ( $qnty_homeworks_with_cur_support != 0 ) || 
                             ( $homeworks_quantity_without_support != 0 ) ) {
                        
                            $msg = sprintf( edc__( "Ты только что проверил очередную работу! Молодчага ".Emoji::signOfTheHorns()."! Еще осталось на проверку столько работ:\n\r\n\rЛичные ученики: %s\n\Ученики без куратора: %s\n\r\n\rПриступить к проверке: %s" ), $qnty_homeworks_with_cur_support, $homeworks_quantity_without_support, $start_homeworks_check_url );
                            
                        } 
                        // there are no homeworks to check
                        else {
                            $msg = sprintf( edc__( "Ура! Ты только что проверил последнюю работу! Молодчага ".Emoji::flexedBiceps().Emoji::flexedBiceps().Emoji::flexedBiceps()."! Все работы проверены! ".Emoji::clappingHands().Emoji::signOfTheHorns().Emoji::fire() ) );
                        }
                        
                        // send notification message to user
                        edc_send_message_user( $homework['checker_id'], $msg );
                    } 

                    // send message to user
                    edc_send_message_user( $data['user_id'], $msg_user );
                        
                    // successfully saved
                    $msg_text = __( 'Сохранено', TEXTDOMAIN );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'notice' );
                    break;
                    
                case $result === FALSE:
                    // error appeared
                    $msg_text = __( 'Не сохранено. Возникла ошибка. Возможно в тексте есть недопустимые символы.', TEXTDOMAIN );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                    break;
                    
                case $result === 1:
                    // error appeared
                    $msg_text = __( 'Эта работа уже проверяется другим человеком.', TEXTDOMAIN );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                    break;
                    
                case $result === 2:
                    // error appeared
                    $msg_text = __( 'Работа может быть принята только человеком, который взял ее на проверку.', TEXTDOMAIN );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                    break;
                    
                case $result === 3:
                    // error appeared
                    $msg_text = __( 'Работа может быть отклонена только человеком, который взял ее на проверку.', TEXTDOMAIN );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                    break;
            }

            // apply redirect
            wp_redirect( $redirection_url );
            exit(); 
        }
        // take next homework for checking
        else if ( ( $task == 'homework' ) && ( $action == 'take_next_homework' ) ) { 

            // get current user role
            $cur_user_role = edc_get_user_role();
            // doesn't current user have rights for this operation?
            if ( !in_array( $cur_user_role, edc_get_checkers_homeworks_roles() ) ) {
                $error_counter++;
                $error_msg = __( 'У тебя не хватает прав на данную операцию.', TEXTDOMAIN );
            }

            // is user already checking any homework?
            if ( edc_is_user_already_checking_any_homework() ) {
                $error_counter++;
                $error_msg = __( 'Ты не можешь взять на проверку одновременно больше одной работы.', TEXTDOMAIN );
            }
            
            // is there any error?
            if ( $error_counter ) {
                // getting redirection url
                $redirection_url = get_permalink();
                $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $error_msg, 'error' );
              
                // apply redirect
                wp_redirect( $redirection_url );
                exit();
            }
        
            // sanitize inputed user data
            $data = edc_sanitize_users_inputed_db_data( $_REQUEST );

            // taking next homework for checking
            $result = edc_take_next_homework_for_checking();

            // get current checking homework
            $current_homework = edc_get_user_current_checking_homework(); 

            // getting redirection url
            $redirection_url = get_permalink();
            switch ( TRUE ) {
                case $result === TRUE:

                    // get course category data
                    $post_terms = wp_get_post_terms( $current_homework['lesson_id'], 'course' );
                    $category = $post_terms[0];
                    // get lesson title
                    $lesson_title = get_the_title( $current_homework['lesson_id'] );
                    // get lesson's number
                    $lesson_num = get_field( 'edc_lesson_order_num', $current_homework['lesson_id'] );
                    // get quantity of checking requests
                    $qnty_check_request = edc_get_user_lesson_homeworks_attemps_quantity( $current_homework['lesson_id'], $current_homework['user_id'] );
                    // get lesson URL
                    $lesson_url = get_permalink( $current_homework['lesson_id'] ) . "?is_open_hh=1#hh";

                    // forming the user message
                    $msg_user = sprintf( edc__( "Курс \"%s\" урок №%s \"%s\" попытка №%s\n\r\n\rСтатус: Работа начала проверяться ".Emoji::stopwatch() . "\n\r\n\rДетали по ссылке: %s" ), $category->name, $lesson_num, $lesson_title, $qnty_check_request, $lesson_url );

                    // send message to user
                    edc_send_message_user( $current_homework['user_id'], $msg_user );

                    // successfully saved
                    $msg_text = __( 'Сохранено', TEXTDOMAIN );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'notice' );
                    break;
                    
                case $result === 0:

                    // successfully saved
                    $msg_text = __( 'Список работ на проверку пуст. Можешь пока отдохнуть.', TEXTDOMAIN );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'notice' );
                    break;
                    
                case $result === FALSE:
                    // error appeared
                    $msg_text = __( 'Не получилось. Возникла ошибка.', TEXTDOMAIN );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                    break;
            }

            // apply redirect
            wp_redirect( $redirection_url );
            exit(); 
        }
        // some certain search actions
        else if ( ( $task == 'search' ) ) { 
        
            global $g_minimum_search_string_length;
        
            // sanitize inputed user data
            $data = edc_sanitize_users_inputed_db_data( $_REQUEST );
               
            // define which exact action we should do
            if ( isset( $_REQUEST['btn_search'] ) && 
                 ( mb_strlen( $data['s'] ) >= $g_minimum_search_string_length ) ) {
            
                global $current_user;
                
                // save search query
                edc_save_user_search_query( $current_user->ID, $data['s'], $data['search_type'] );
            }  
        }
        // some certain notification actions
        else if ( ( $task == 'notification' ) && ( $action == 'read' ) ) { 

            // validate saving data
            // telling what fields are required and what we need to check
            $required_fields = array( 'id' );
            
            // are required fields invalid?
            if ( !edc_is_valid_saving_fields( $required_fields, $_REQUEST ) ) {
                $error_counter++;
                $error_msg = __( 'Не хватает необходимых данных для данной операции.', TEXTDOMAIN );
            }
            
            // sanitize inputed user data
            $data = edc_sanitize_users_inputed_db_data( $_REQUEST );
            
            // can user mark notification as read?
            if ( empty( edc_get_user_notification( $data['id'] ) ) ) {
                $error_counter++;
                $error_msg = __( 'У вас нет прав на данную операцию или такого уведомления не существует.', TEXTDOMAIN );
            }
            
            // is there any error?
            if ( $error_counter ) {
                // getting redirection url
                $redirection_url = get_permalink();
                $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $error_msg, 'error' );
              
                // apply redirect
                wp_redirect( $redirection_url );
                exit();
            }
            
            // mark user notification as read
            $result = edc_mark_user_notification_as_read( $data['id'] );

            // getting redirection url
            $redirection_url = get_permalink();
            switch ( TRUE ) {
                case $result === true:
                    $msg_text = edc__( 'Уведомление отмечено прочтенным.' );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'notice' );
                    break;
                
                case $result === false:
                    // error appeared
                    $msg_text = edc__( 'Не сохранено. Возникла ошибка.' );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                    break;
            }

            // apply redirect
            wp_redirect( $redirection_url );
            exit(); 
        }
        // user's courses adding
        else if ( ( $task == 'user' ) && ( $action == 'add_course' ) ) { 

            // validate saving data
            // telling what fields are required and what we need to check
            $required_fields = array( 'user_id', 'course_id' );
            
            // are required fields invalid?
            if ( !edc_is_valid_saving_fields( $required_fields, $_REQUEST ) ) {
                $error_counter++;
                $error_msg = __( 'Не хватает необходимых данных для данной операции.', TEXTDOMAIN );
            }
            
            // sanitize inputed user data
            $data = edc_sanitize_users_inputed_db_data( $_REQUEST );

            // do user have necessary rights for this operation?
            if ( !current_user_can( 'edc_edit_user_course_data' ) ) {
                $error_counter++;
                $error_msg = __( 'У вас нет прав на данную операцию.', TEXTDOMAIN );
            }

            // is there any error?
            if ( $error_counter ) {
                // getting redirection url
                $redirection_url = $_SERVER['HTTP_REFERER'];
                $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $error_msg, 'error' );
              
                // apply redirect
                wp_redirect( $redirection_url );
                exit();
            }

            // mark user notification as read
            $result = edc_add_course_to_user( $data['course_id'], $data['user_id'] );

            // getting redirection url
            $redirection_url = $_SERVER['HTTP_REFERER'];
            switch ( TRUE ) {
                case $result === true:
                    $msg_text = edc__( 'Курс добавлен.' );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'notice' );
                    break;
                
                case $result === false:
                    // error appeared
                    $msg_text = edc__( 'Не сохранено. Возникла ошибка.' );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                    break;
            }

            // apply redirect
            wp_redirect( $redirection_url );
            exit(); 
        }
        // update user's courses data
        else if ( ( $task == 'user' ) && ( $action == 'update_course' ) ) { 

            // validate saving data
            // telling what fields are required and what we need to check
            $required_fields = array( 'user_id', 'course_id' );
            
            // are required fields invalid?
            if ( !edc_is_valid_saving_fields( $required_fields, $_REQUEST ) ) {
                $error_counter++;
                $error_msg = __( 'Не хватает необходимых данных для данной операции.', TEXTDOMAIN );
            }
            
            // sanitize inputed user data
            $data = edc_sanitize_users_inputed_db_data( $_REQUEST );

            // do user have necessary rights for this operation?
            if ( ( ( $data['user_id'] != get_current_user_id() ) && 
                   !current_user_can( 'edc_edit_user_course_data' ) ) ||
                 ( ( $data['user_id'] == get_current_user_id() ) && 
                   !current_user_can( 'edc_edit_user_course_data_in_his_profile' ) ) ) {
                $error_counter++;
                $error_msg = __( 'У вас нет прав на данную операцию.', TEXTDOMAIN );
            }

            // is there any error?
            if ( $error_counter ) {
                // getting redirection url
                $redirection_url = $_SERVER['HTTP_REFERER'];
                $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $error_msg, 'error' );
              
                // apply redirect
                wp_redirect( $redirection_url );
                exit();
            }
            
            // set support for the user
            $result = edc_set_support_for_user( $data['course_id'], $data['user_id'], $data['support_id'] );

            // set user's course data
            if ( !isset( $data['is_homeworks_disabled'] ) ) $data['is_homeworks_disabled'] = false;
            if ( !isset( $data['is_demo_mode'] ) ) $data['is_demo_mode'] = false;
            if ( !isset( $data['is_low_priority'] ) ) $data['is_low_priority'] = false;
            $result = edc_set_user_course_data( $data['course_id'], $data['user_id'], $data );

            // set user's course certificate data
            $data_arr = [
                'comments' => $data['cert_comments']
            ];
            
            $result_2 = edc_set_user_course_certificate_data( $data['course_id'], $data['user_id'], $data_arr );
            
            // getting redirection url
            $redirection_url = $_SERVER['HTTP_REFERER'];
            switch ( TRUE ) {
                case $result === true:
                    $msg_text = edc__( 'Данные обновлены.' );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'notice' );
                    break;
                
                case $result === false:
                    // error appeared
                    $msg_text = edc__( 'Не сохранено. Возникла ошибка.' );
                    $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg_text, 'error' );
                    break;
            }

            // apply redirect
            wp_redirect( $redirection_url );
            exit(); 
        }
     
     
     
     
     
     
        // is user viwing singular post?
        if ( is_singular() ) {
        
            $query_string = $_SERVER['QUERY_STRING'];
            $query_string = htmlentities( $query_string );

            // save made user action 'visit' to analytics system
            edc_analytics_save_user_action( get_the_id(), 1, '', $query_string );
        }
        
     
     
     
     
  
        
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
      * Create the record in database in extended user data table.
      * It should be called just once for user after his registration.
      *
      * @global    wpdb       $wpdb
      * 
      * @param     int        $user_id         User ID
      */
    function edc_add_user_extended_data( $user_id ) {
    
        global $wpdb;
        
        $data = [];

        // set necessary fields
        $data['user_id'] = $user_id;

        // execute insert operation
        $result = $wpdb->insert( "{$wpdb->prefix}edc_users_extended", $data );

        // return the result
        return ( $result ) ? true : false;
    }

    /**
      * Save current user settings
      *
      * @global    wpdb       $wpdb
      * @global    WP_User    $current_user
      * 
      * @param     array          $post_array         It is the $_POST variable
      * @return    boolean|int    $result             TRUE if settings saved, or if data not 
      *                                               saved then it returns: 1 - email exists
      */
    function edc_save_current_user_settings( $post_array ) {
    
        global $wpdb;
        global $current_user;

        // sanitize inputed user data
        $data = edc_sanitize_users_inputed_db_data( $post_array );
        
        $update_user_data = [];
        $data_user_extended = [];
        
        $update_user_data['ID'] = $current_user->get( 'id' );
        // check whether we should update user's password
        if ( ! empty( $data['user_pass'] ) ) $update_user_data['user_pass'] = $data['user_pass'];

        // check whether such telegram chat exist
        global $telegram_bot;
        if ( !$telegram_bot->get_chat( $data['telegram_chat_id'] )->ok ) {
            return 2;
        }
        $data_user_extended['telegram_chat_id'] = $data['telegram_chat_id'];
        $data_user_extended['is_valid_telegram_chat_id'] = true;
        
        // save user info
        $wp_update_user_result = wp_update_user( $update_user_data );
      
        // save extended user information
        $data_user_extended['phone'] = $data['phone'];
        $data_user_extended['skype'] = $data['skype'];
        $data_user_extended['social_network'] = $data['social_network'];
        $data_user_extended['telegram_login'] = $data['telegram_login'];
        $data_user_extended['about_me'] = $data['about_me'];
        $data_user_extended['course_goals'] = $data['course_goals'];
      
        // execute update operation
        edc_save_user_extended_info( $current_user->get( 'id' ), $data_user_extended );
      
        $result = TRUE;

        return $result;        
    }

    /**
      * Allow to user to take certain lesson in the course
      * 
      * @global    wpdb       $wpdb
      *
      * @param     int        $user_id            User ID
      * @param     int        $lesson_id          Lesson ID
      */
    function edc_allow_user_to_take_lesson( $user_id, $lesson_id ) {
        
        global $wpdb;
        
        // set necessary fields
        $data = [];
        $data['user_id'] = $user_id;
        $data['lesson_id'] = $lesson_id;
        // execute insert operation
        $result = $wpdb->insert( "{$wpdb->prefix}edc_users_lesson_progress", $data );

        // set necessary fields
        $data = [];
        $data['users_lesson_progress_id'] = $wpdb->insert_id;
        // execute insert operation
        $result = $wpdb->insert( "{$wpdb->prefix}edc_homeworks", $data );

        // return the result
        return ( $result ) ? true : false;
    }

    /**
      * Send course lesson homework of the current user for checking
      *
      * @global    wpdb       $wpdb
      * @global    WP_User    $current_user
      * 
      * @param     array          $post_array         It is the $_POST variable
      * @return    boolean|int    $result             TRUE if settings saved, or if data not 
      *                                               saved then it returns: FALSE - some error
      */
    function edc_send_user_lesson_homework_for_checking( $post_array ) {
    
        global $wpdb;
        global $current_user;
        
        // sanitize inputed user data
        $data = edc_sanitize_users_inputed_db_data( $post_array );
        
        // get info about the lesson homework
        $lesson_progress = edc_get_user_lesson_progress( $data['lesson_id'] );
        
        // set necessary fields
        $cur_date = date( edc_get_mysql_date_format(), time() );
        
        // update data in database
        $query = $wpdb->prepare( "
            UPDATE `{$wpdb->prefix}edc_homeworks`             
            SET `is_need_to_check` = 1, 
                `sent_for_checking_date` = '{$cur_date}'
            WHERE `users_lesson_progress_id` = '{$lesson_progress['users_lesson_progress_id']}'
               AND is_need_to_check IS NULL
            ", array() );
        $result = $wpdb->query( $query );
        
        // return the result
        return ( $result ) ? true : false;
    }

    /**
      * Save data of course lesson homework of the current user
      *
      * @global    wpdb       $wpdb
      * @global    WP_User    $current_user
      * 
      * @param     array          $post_array         It is the $_POST variable
      * @return    boolean|int    $result             TRUE if settings saved, or if data not 
      *                                               saved then it returns: FALSE - some error
      */
    function edc_save_user_lesson_data( $post_array ) {
    
        global $wpdb;
        global $current_user;
        
        // sanitize inputed user data
        $data = edc_sanitize_users_inputed_db_data( $post_array );
        
        // get info about the lesson homework
        $lesson_progress = edc_get_user_lesson_progress( $data['lesson_id'] );
        
        // remove unnecessary fields
        unset( $data['id'] );
        unset( $data['lesson_id'] );

        // set necessary fields
        $cur_date = date( edc_get_mysql_date_format(), time() );

        // forming of Where clause
        $where = array( 'id' => $lesson_progress['homework_id'] );
        // execute update operation
        $result = $wpdb->update( "{$wpdb->prefix}edc_homeworks", $data, $where );

        // return the result
        return ( empty( $wpdb->last_error ) ) ? true : false;
    }

    /**
      * Get user lesson progress
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $lesson_id         Lesson Id
      * @param  int     $user_id           User Id
      * @return array                      Associative array with data
      */
    function edc_get_user_lesson_progress( $lesson_id, $user_id = 0 ) {
    
        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
        
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $query = $wpdb->prepare( "
            SELECT lesson_progress.*,
                   homeworks.*,
                   homeworks.id AS homework_id            
            FROM `{$wpdb->prefix}edc_users_lesson_progress` AS lesson_progress
            INNER JOIN `{$wpdb->prefix}edc_homeworks` AS homeworks ON
                homeworks.users_lesson_progress_id = lesson_progress.id  
            WHERE lesson_progress.user_id = '{$user_id}'
               AND lesson_progress.lesson_id = '{$lesson_id}'
            ORDER BY homework_id DESC
            LIMIT 1
            ", array() );
        $lesson_progress = $wpdb->get_row( $query, ARRAY_A );
    
        // unescape all values
        $lesson_progress = stripslashes_deep( $lesson_progress );
    
        // return the result
        return $lesson_progress;
    }

    /**
      * Get current user attended real webinars on some course
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $user_id           User Id
      * @return array                      Associative array with data
      */
    function edc_get_user_attended_course_webinars( $user_id, $course_id ) {
    
        global $wpdb;
    
        $query = $wpdb->prepare( "
            SELECT lesson_progress.*
            FROM `{$wpdb->prefix}edc_users_lesson_progress` AS lesson_progress
            INNER JOIN {$wpdb->prefix}term_relationships AS term_relationships ON
                term_relationships.object_id = lesson_progress.lesson_id  
            WHERE lesson_progress.user_id = '{$user_id}'
                AND lesson_progress.date_webinar_joined IS NOT NULL 
                AND term_relationships.term_taxonomy_id = '{$course_id}'
            ", array() );
        $webinars = $wpdb->get_results( $query, ARRAY_A );

        // unescape all values
        $webinars = stripslashes_deep( $webinars );
    
        // return the result
        return $webinars;
    }

    /**
      * Get allowed lessons for current user
      * 
      * @global       wpdb    $wpdb
      * 
      * @return       array         
      */
    function edc_get_user_allowed_lessons() {
    
        global $wpdb;
        global $current_user;
    
        $query = $wpdb->prepare( "
                             SELECT lesson_id
                             FROM `{$wpdb->prefix}edc_users_lesson_progress`
                             WHERE user_id = '{$current_user->ID}'
                             ", array() );
        $allowed_lessons_assoc = $wpdb->get_results( $query, ARRAY_A );
    
        // make plain array
        $allowed_lessons = [];
        foreach ( $allowed_lessons_assoc as $allowed_lesson ) {
            $allowed_lessons[] = $allowed_lesson['lesson_id'];
        }

        // return the result
        return $allowed_lessons;
    }

    /**
      * Get user homeworks history
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $lesson_id         Lesson Id
      * @param  int     $user_id           User Id
      * @return array                      Associative array with data
      */
    function edc_get_user_lesson_homeworks_history( $lesson_id, $user_id = 0 ) {
    
        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
        
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $query = $wpdb->prepare( "
            SELECT homeworks.*,
                   homeworks.id AS homework_id
            FROM `{$wpdb->prefix}edc_users_lesson_progress` AS lesson_progress
            INNER JOIN `{$wpdb->prefix}edc_homeworks` AS homeworks ON
                homeworks.users_lesson_progress_id = lesson_progress.id  
            WHERE lesson_progress.user_id = '{$user_id}'
               AND lesson_progress.lesson_id = '{$lesson_id}'
            ORDER BY homeworks.id DESC
            ", array() );
        $history = $wpdb->get_results( $query, ARRAY_A );
    
        // unescape all values
        $history = stripslashes_deep( $history );
    
        // return the result
        return $history;
    }

    /**
      * Get accepted and declined homeworks history
      * 
      * @global wpdb    $wpdb
      * 
      * @return array                      Associative array with data
      */
    function edc_get_homeworks_history( $filters = [] ) {
    
        global $wpdb;
        global $default_db_results_limit;
        
        // reset      
        $limit = '';    
        $where_arr = [];
        
        // set default values
        $limit = 'LIMIT ' . $default_db_results_limit;
        // get only accepted and declined homeworks
        $where_arr[] = "(homeworks.is_accepted = FALSE OR homeworks.is_accepted = TRUE)";
        
        // should we get homeworks from some course?
        if ( isset( $filters['course_id'] ) && !empty( $filters['course_id'] ) ) {

            $course_id = implode( "','", $filters['course_id'] );
            $course_id = "'" . $course_id . "'";

            $where_arr[] = "(courses_to_users.course_id IN ({$course_id}))";
        }
        
        // define for which type of users we should get homeworks
        $students_roles = edc_get_students_roles();
        $where_item = "";
        $where_arr_item = [];
        foreach ( $students_roles as $role ) {
            $where_arr_item[] = "(usermeta.meta_value LIKE '%{$role}%' AND usermeta.meta_key = '{$wpdb->prefix}capabilities')";
        }
        $where_item = '(' . implode( ' OR ', $where_arr_item ) . ')';
        // add query to $where variable
        $where_arr[] = $where_item;

        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }
        
        // I removed here '$wpdb->prepare(...)', because it doesn't normal work
        // in case we have '%' in query, I guesss prepare() uses 'sprintf()', that is because
        $query = "
            SELECT homeworks.*,
                   homeworks.id AS homework_id,
                   users.display_name AS user_display_name,
                   users.id AS user_id,
                   lesson_progress.lesson_id AS lesson_id
            FROM `{$wpdb->prefix}edc_homeworks` AS homeworks
            INNER JOIN `{$wpdb->prefix}edc_users_lesson_progress` AS lesson_progress ON
                homeworks.users_lesson_progress_id = lesson_progress.id  
            INNER JOIN `{$wpdb->prefix}usermeta` AS usermeta ON
                lesson_progress.user_id = usermeta.user_id  
            INNER JOIN {$wpdb->prefix}users AS users ON
                lesson_progress.user_id = users.id  
            INNER JOIN {$wpdb->prefix}term_relationships AS term_relationships ON
                term_relationships.object_id = lesson_progress.lesson_id  
            INNER JOIN {$wpdb->prefix}edc_courses_to_users AS courses_to_users ON
                lesson_progress.user_id = courses_to_users.user_id AND
                term_relationships.term_taxonomy_id = courses_to_users.course_id
            LEFT JOIN {$wpdb->prefix}edc_supports_to_users AS supports_to_users ON
                courses_to_users.supports_to_users_id = supports_to_users.id
            {$where}
            ORDER BY homeworks.end_date_checking DESC
            {$limit}
            ";
        $history = $wpdb->get_results( $query, ARRAY_A );

        // unescape all values
        $history = stripslashes_deep( $history );
    
        // return the result
        return $history;
    }

    /**
      * Get user homeworks attemps for certain lesson
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $lesson_id         Lesson Id
      * @param  int     $user_id           User Id
      * @return int                        
      */
    function edc_get_user_lesson_homeworks_attemps_quantity( $lesson_id, $user_id = 0 ) {
    
        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
        
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $query = $wpdb->prepare( "
            SELECT COUNT(homeworks.id)
            FROM `{$wpdb->prefix}edc_users_lesson_progress` AS lesson_progress
            INNER JOIN `{$wpdb->prefix}edc_homeworks` AS homeworks ON
                homeworks.users_lesson_progress_id = lesson_progress.id  
            WHERE lesson_progress.user_id = '{$user_id}'
               AND lesson_progress.lesson_id = '{$lesson_id}'
               AND homeworks.is_need_to_check IS NOT NULL
            ORDER BY homeworks.id DESC
            ", array() );
        $quantity = $wpdb->get_var( $query );
    
        // return the result
        return $quantity;
    }

    /**
      * Save user's search query
      */
    function edc_save_user_search_query( $user_id, $search_str, $search_type ) {
    
        global $wpdb;

        // set necessary fields
        $cur_date = date( edc_get_mysql_date_format(), time() );


        // set necessary fields
        $data = [];

        switch ( $search_type ) {
            case 'general':
                $data['type'] = 'general';
                break;
            case 'google':
                $data['type'] = 'google';
                break;
            default:
                $data['type'] = 'general';
                break;
        }
        
        $data['user_id'] = $user_id;
        $data['query'] = $search_str;
        $data['date'] = $cur_date;
        
        // execute insert operation
        $result = $wpdb->insert( "{$wpdb->prefix}edc_searchings", $data );

        // return the result
        return ( empty( $wpdb->last_error ) ) ? true : false;
    }

    /**
      * Get users search queries                 
      */
    function edc_get_search_queries( $user_id ) {
    
        global $wpdb;
        global $default_db_results_limit;
        
        $limit = 'LIMIT ' . $default_db_results_limit;
        
        $query = $wpdb->prepare( "
            SELECT searchings.*,
                   users.display_name AS user_display_name
            FROM `{$wpdb->prefix}edc_searchings` AS searchings 
            INNER JOIN {$wpdb->prefix}users AS users ON
                searchings.user_id = users.id  
            WHERE searchings.user_id = '{$user_id}'
            ORDER BY searchings.date DESC
            {$limit}
            ", array() );
        $queries = $wpdb->get_results( $query, ARRAY_A );
    
        // unescape all values
        $queries = stripslashes_deep( $queries );
    
        // return the result
        return $queries;
    }

    
    
    
    
    
    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    



    /**
      * Mark that the lesson for certain user is accepted
      *
      * @global    wpdb       $wpdb
      *
      * @param     int        $user_id            User ID
      * @param     int        $lesson_id          Lesson ID
      */
    function edc_mark_lesson_accepted( $user_id, $lesson_id, $post_array ) {

        global $wpdb;
        global $current_user;

        $data = [];
        
        // get current date
        $cur_date = date( edc_get_mysql_date_format(), time() );
        
        // sanitize inputed user data
        $sanitized_post_array = edc_sanitize_users_inputed_db_data( $post_array );

        // set necessary fields
        $data['is_need_to_check'] = false;
        $data['is_accepted'] = true;
        $data['end_date_checking'] = $cur_date;
        $data['checker_comments'] = $sanitized_post_array['checker_comments'];
        $data['checker_comments_short'] = mb_substr( $sanitized_post_array['checker_comments_short'], 0, 1300 );
        $data['checker_comments_for_teachers'] = $sanitized_post_array['checker_comments_for_teachers'];

        // get user's lesson progress data
        $lesson_progress = edc_get_user_lesson_progress( $lesson_id, $user_id );

        // forming of Where clause
        $where = [ 'id' => $lesson_progress['homework_id'] ];
        // execute update operation
        $result = $wpdb->update( "{$wpdb->prefix}edc_homeworks", $data, $where );
        
        // do we have any errors while update operation?
        if ( !$result ) return false;        
        
        // getting course ID
        $course_id = wp_get_post_terms($lesson_id, 'course')[0]->term_id;

        // get list of lessons from the course
        $lessons = edc_get_course_lessons( $course_id );

        // define the next lesson
        $prev_key = -1;
        $next_lesson_id = null;
        foreach ( $lessons as $key => $lesson ) {
        
            if ( ( $prev_key !== -1 ) && ( $lessons[$prev_key]->ID === $lesson_id ) ) {
                $next_lesson_id = $lessons[$key]->ID;
            }
            
            $prev_key = $key;
        }
        
        // do we have the next lesson?
        if ( $next_lesson_id  ) {
            // allow user to take next lesson
            edc_allow_user_to_take_lesson( $user_id, $next_lesson_id );
        }
        
        // return the result
        return ( $result ) ? true : false;
    }
    
    /**
      * Mark that the lesson for certain user is not accepted
      *
      * @global    wpdb       $wpdb
      *
      * @param     int        $user_id            User ID
      * @param     int        $lesson_id          Lesson ID
      */
    function edc_mark_lesson_not_accepted( $user_id, $lesson_id, $post_array ) {

        global $wpdb;
        global $current_user;

        $data = [];
        
        // get current date
        $cur_date = date( edc_get_mysql_date_format(), time() );

        // sanitize inputed user data
        $sanitized_post_array = edc_sanitize_users_inputed_db_data( $post_array );
        
        // set necessary fields
        $data['is_need_to_check'] = false;
        $data['is_accepted'] = false;
        $data['end_date_checking'] = $cur_date;
        $data['checker_comments'] = $sanitized_post_array['checker_comments'];
        $data['checker_comments_short'] = mb_substr( $sanitized_post_array['checker_comments_short'], 0, 1300 );
        $data['checker_comments_for_teachers'] = $sanitized_post_array['checker_comments_for_teachers'];
        
        // get user's lesson progress data
        $lesson_progress = edc_get_user_lesson_progress( $lesson_id, $user_id );
        
        // forming of Where clause
        $where = [ 'id' => $lesson_progress['homework_id'] ];
        // execute update operation
        $result = $wpdb->update( "{$wpdb->prefix}edc_homeworks", $data, $where );
        
        // do we have any errors while update operation?
        if ( !$result ) return false;        
        
        // set necessary fields
        $data = [];
        $data['users_lesson_progress_id'] = $lesson_progress['users_lesson_progress_id'];
        $data['homework'] = $lesson_progress['homework'];
        // execute insert operation
        $result = $wpdb->insert( "{$wpdb->prefix}edc_homeworks", $data );
        
        // return the result
        return ( $result ) ? true : false;
    }
    
    /**
      * Start checking homework
      *
      * @global    wpdb       $wpdb
      *
      * @param     int        $user_id            User ID
      * @param     int        $lesson_id          Lesson ID
      */
    function edc_start_checking_homework( $user_id, $lesson_id ) {

        global $wpdb;
        global $current_user;

        $data = [];
        
        // get current date
        $cur_date = date( edc_get_mysql_date_format(), time() );

        // set necessary fields
        $data['checker_id'] = $current_user->ID;
        $data['start_date_checking'] = $cur_date;
        
        // get user's lesson progress data
        $lesson_progress = edc_get_user_lesson_progress( $lesson_id, $user_id );
        
        // forming of Where clause
        $where = [ 'id' => $lesson_progress['homework_id'] ];
        // execute update operation
        $result = $wpdb->update( "{$wpdb->prefix}edc_homeworks", $data, $where );
        
        // return the result
        return ( $result ) ? true : false;
    }
    
    /**
      * Get list of homeworks we need to check
      * 
      * @global wpdb    $wpdb
      * 
      * @param  array         $query_params      Such params as: 
      *                                            'is_not_checking_yet' - boolean
      *                                            'is_low_priority'     - boolean
      *                                            'support_id'          - integer
      *                                            'course_id'           - array
      *                                            'is_return_quantity'  - boolean
      * @return array|int            Associative array with data OR Quantity of records
      */
    function edc_get_homeworks_for_check( $query_params = [] ) {
     
        global $wpdb;
        
        $where_arr = [];
        // set default values for WHERE clause
        $where_arr[] = "(homeworks.is_need_to_check = TRUE)";

        // should we get homeworks of some certain support?
        if ( isset( $query_params['support_id'] ) && ( $query_params['support_id'] > 0 ) ) {
            $where_arr[] = "(supports_to_users.support_id = '{$query_params['support_id']}')";
        } 
        // should we get homeworks which is not have support?
        else if ( isset( $query_params['support_id'] ) && ( $query_params['support_id'] === 0 ) ) {
            $where_arr[] = "(courses_to_users.supports_to_users_id IS NULL)";
        } 

        // should we get homeworks just which should be taken for checking?
        if ( isset( $query_params['is_not_checking_yet'] ) && !empty( $query_params['is_not_checking_yet'] ) ) {
            $where_arr[] = "(homeworks.start_date_checking IS NULL)";
        }

        // should we get just homeworks with low priority?
        if ( isset( $query_params['is_low_priority'] ) && !empty( $query_params['is_low_priority'] ) ) {
            $where_arr[] = "(courses_to_users.is_low_priority = 1)";
        } 
        // should we get just homeworks with general priority?
        else if ( isset( $query_params['is_low_priority'] ) && empty( $query_params['is_low_priority'] ) ) {
            $where_arr[] = "(courses_to_users.is_low_priority = 0)";
        } 

        // should we get homeworks from some course?
        if ( isset( $query_params['course_id'] ) && !empty( $query_params['course_id'] ) ) {

            $course_id = implode( "','", $query_params['course_id'] );
            $course_id = "'" . $course_id . "'";

            $where_arr[] = "(courses_to_users.course_id IN ({$course_id}))";
        }

        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }
    
        // should we return just quantity of records?
        if ( isset( $query_params['is_return_quantity'] ) && !empty( $query_params['is_return_quantity'] ) ) {

            $query = $wpdb->prepare( "
                SELECT COUNT(*)
                FROM `{$wpdb->prefix}edc_users_lesson_progress` AS lsn_progress
                INNER JOIN {$wpdb->prefix}users AS users ON
                    lsn_progress.user_id = users.id  
                INNER JOIN {$wpdb->prefix}edc_homeworks AS homeworks ON
                    lsn_progress.id = homeworks.users_lesson_progress_id  
                INNER JOIN {$wpdb->prefix}term_relationships AS term_relationships ON
                    term_relationships.object_id = lsn_progress.lesson_id  
                INNER JOIN {$wpdb->prefix}edc_courses_to_users AS courses_to_users ON
                    lsn_progress.user_id = courses_to_users.user_id AND
                    term_relationships.term_taxonomy_id = courses_to_users.course_id
                LEFT JOIN {$wpdb->prefix}edc_supports_to_users AS supports_to_users ON
                    courses_to_users.supports_to_users_id = supports_to_users.id
                {$where}
                ", array() );

            $qnty_records = $wpdb->get_var( $query );

            // return the result
            return $qnty_records;
        }
        
        else {
        
            $query = $wpdb->prepare( "
                SELECT lsn_progress.*,
                       lsn_progress.id AS lsn_progress_id,
                       users.user_login,
                       users.display_name,
                       homeworks.*,
                       homeworks.id AS homework_id,
                       supports_to_users.support_id
                FROM `{$wpdb->prefix}edc_users_lesson_progress` AS lsn_progress
                INNER JOIN {$wpdb->prefix}users AS users ON
                    lsn_progress.user_id = users.id  
                INNER JOIN {$wpdb->prefix}edc_homeworks AS homeworks ON
                    lsn_progress.id = homeworks.users_lesson_progress_id  
                INNER JOIN {$wpdb->prefix}term_relationships AS term_relationships ON
                    term_relationships.object_id = lsn_progress.lesson_id  
                INNER JOIN {$wpdb->prefix}edc_courses_to_users AS courses_to_users ON
                    lsn_progress.user_id = courses_to_users.user_id AND
                    term_relationships.term_taxonomy_id = courses_to_users.course_id
                LEFT JOIN {$wpdb->prefix}edc_supports_to_users AS supports_to_users ON
                    courses_to_users.supports_to_users_id = supports_to_users.id
                {$where}
                ORDER BY homeworks.sent_for_checking_date ASC
                ", array() );

            $list = $wpdb->get_results( $query, ARRAY_A );

            // unescape all values
            $list = stripslashes_deep( $list );

            // return the result
            return $list;
        }
    }
    
    /**
      * Check whether user is already checking any homework
      * 
      * @global wpdb    $wpdb
      * 
      * @return bool
      */
    function edc_is_user_already_checking_any_homework( $user_id = 0 ) {
    
        global $wpdb;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // getting current user
            global $current_user;
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $query = $wpdb->prepare( "
            SELECT COUNT(*)
            FROM `{$wpdb->prefix}edc_users_lesson_progress` AS lsn_progress
            INNER JOIN {$wpdb->prefix}users AS users ON
                lsn_progress.user_id = users.id  
            INNER JOIN {$wpdb->prefix}edc_homeworks AS homeworks ON
                lsn_progress.id = homeworks.users_lesson_progress_id  
            WHERE homeworks.is_need_to_check = TRUE
                AND homeworks.checker_id = '{$user_id}'
            ", array() );
            
        $quantity = $wpdb->get_var( $query );

        // return the result
        return ( $quantity ) ? true : false;
    }
    
    /**
      * Is user can take homework for checking?
      * 
      * @global wpdb    $wpdb
      * 
      * @param     int        $student_id         Student ID
      * @param     int        $lesson_id          Lesson ID
      * @param     int        $support_id         Support ID
      * @return    mixed
      */
    function edc_is_support_can_take_homework_for_checking( $student_id, $lesson_id, $support_id = 0 ) {
    
        global $wpdb;
        
        // do we have setted user id?
        if ( empty( $support_id ) ) {
            // getting current user
            global $current_user;
            // set user ID as current user ID
            $support_id = $current_user->ID;
        }
        
        $result = [
            'result' => true,
            'message' => edc__( 'Ok' ),
        ];
        
        // is user already checking any homework? user can check only 1 homework.
        if ( edc_is_user_already_checking_any_homework( $support_id ) ) {
        
            $result = [
                'result' => false,
                'message' => edc__( 'Проверять одновременно можно лишь одну работу' ),
            ];
        }
      
        // get user's courses
        $user_course_lists = edc_get_user_course_lists( $support_id );
        $users_courses_ids = array_column( $user_course_lists, 'term_id' );
        // get course ID of the lesson
        $post_terms = wp_get_post_terms( $lesson_id, 'course' );
        $lesson_course_id = $post_terms[0]->term_id;
        
        // is user assigned to course from which this lesson is?
        if ( !in_array( $lesson_course_id, $users_courses_ids ) ) {
        
            $result = [
                'result' => false,
                'message' => edc__( 'Тебя нет на этом курсе' ),
            ];
        }
        
        // get current support
        $cur_support = edc_get_user_current_support( $lesson_course_id, $student_id );
        
        // support can take for checking only his students
        if ( !empty( $cur_support ) && 
             ( $cur_support['support_id'] != $support_id ) ) {

            $result = [
                'result' => false,
                'message' => edc__( 'Ты можешь проверять только своих учеников' ),
            ];
         }
        
        // support cannot for checking his own homework
        if ( $student_id == $support_id ) {
            
            $result = [
                'result' => false,
                'message' => edc__( 'Самому себя нельзя проверять' ),
            ];
         }

        // return the result
        return $result;
    }
    
    /**
      * Get next homework for checking. Algorithm of checking homeworks:
      *    1) Homeworks where User is individual Support
      *    2) Homeworks which is without individual Support
      *    3) Homeworks with low priority 
      *
      * @global    wpdb       $wpdb
      *
      * @param     int        $support_id       Support ID
      * @return    array
      */
    function edc_get_next_homework_for_checking( $support_id = 0 ) {
    
        global $wpdb;
        
        // do we have setted user id?
        if ( empty( $support_id ) ) {
            // getting current user
            $support_id = get_current_user_id();
        }
    
        // get allowed courses to which Support is assigned
        $allowed_courses = edc_get_user_course_lists( $support_id, true, 0, true );
        $allowed_courses_ids = array_column( $allowed_courses, 'term_id' );

        $homeworks = [];
        
        if ( empty( $homeworks ) ) {
            // get list of howemorks to check with Support in $support_id                     
            $query_params = [
                'support_id' => $support_id,
                'is_not_checking_yet' => true,
                'is_low_priority' => false,
                'course_id' => $allowed_courses_ids,
            ];
            $homeworks = edc_get_homeworks_for_check( $query_params );
        }
        
        if ( empty( $homeworks ) ) {
            // get list of howemorks to check without Supports               
            $query_params = [
                'support_id' => 0,
                'is_not_checking_yet' => true,
                'is_low_priority' => false,
                'course_id' => $allowed_courses_ids,
            ];
            $homeworks = edc_get_homeworks_for_check( $query_params );
        }
        
        if ( empty( $homeworks ) ) {
            // get list of howemorks to check with Support in $support_id and 
            // where is low priority
            $query_params = [
                'support_id' => $support_id,
                'is_not_checking_yet' => true,
                'is_low_priority' => true,
                'course_id' => $allowed_courses_ids,
            ];
            $homeworks = edc_get_homeworks_for_check( $query_params );
        }
        
        if ( empty( $homeworks ) ) {
            // get list of howemorks to check without Supports and where is low priority              
            $query_params = [
                'support_id' => 0,
                'is_not_checking_yet' => true,
                'is_low_priority' => true,
                'course_id' => $allowed_courses_ids,
            ];
            $homeworks = edc_get_homeworks_for_check( $query_params );
        }
        
        // do we have homeworks to check?
        if ( !empty( $homeworks ) ) {
            // return the result
            return $homeworks[0];
        } 
        // we do not have homeworks to check
        else {
            // return the result
            return $homeworks;
        }
        
    }
    
    /**
      * Take next homework for checking.
      *
      * @global    wpdb       $wpdb
      *
      * @param     int        $support_id       Support ID
      * @return    bool
      */
    function edc_take_next_homework_for_checking( $support_id = 0 ) {
    
        global $wpdb;
        
        // do we have setted user id?
        if ( empty( $support_id ) ) {
            // getting current user
            $support_id = get_current_user_id();
        }
        
        // get homeworks list
        $homeworks = edc_get_next_homework_for_checking( $support_id );
        
        // do we have homeworks to check?
        if ( !empty( $homeworks ) ) {
        
            $data = [];

            // get current date
            $cur_date = date( edc_get_mysql_date_format(), time() );

            // set necessary fields
            $data['checker_id'] = $support_id;
            $data['start_date_checking'] = $cur_date;

            // forming of Where clause
            $where = [ 'id' => $homeworks['homework_id'] ];
            // execute update operation
            $result = $wpdb->update( "{$wpdb->prefix}edc_homeworks", $data, $where );

            // return the result
            return ( $result ) ? true : false;
        } 
        // we do not have homeworks to check
        else {
        
            // return the result
            return 0;
        }  
    }
    
    /**
      * Get user's current checking homework
      * 
      * @global wpdb    $wpdb
      * 
      * @return bool
      */
    function edc_get_user_current_checking_homework( $user_id = 0 ) {
    
        global $wpdb;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // getting current user
            global $current_user;
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $query = $wpdb->prepare( "
            SELECT 
                lsn_progress.*,
                lsn_progress.id AS lsn_progress_id,
                users.user_login,
                users.display_name,
                homeworks.*,
                homeworks.id AS homework_id
            FROM `{$wpdb->prefix}edc_users_lesson_progress` AS lsn_progress
            INNER JOIN {$wpdb->prefix}users AS users ON
                lsn_progress.user_id = users.id  
            INNER JOIN {$wpdb->prefix}edc_homeworks AS homeworks ON
                lsn_progress.id = homeworks.users_lesson_progress_id  
            WHERE homeworks.is_need_to_check = TRUE
                AND homeworks.checker_id = '{$user_id}'
            ", array() );
            
        $result = $wpdb->get_row( $query, ARRAY_A );

        // unescape all values
        $result = stripslashes_deep( $result );
        
        // return the result
        return $result;
    }
    
    /**
      * Get list of homeworks we need to check
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $id                Homework Id
      * @return string                     Status: making/waiting_for_check/checking/declined/accepted
      */
    function edc_get_homework_status( $id ) {
    
        global $wpdb;
        
        $query = $wpdb->prepare( "
            SELECT homeworks.*
            FROM `{$wpdb->prefix}edc_homeworks` AS homeworks 
            WHERE homeworks.id = '{$id}'
            ", array() );

        $homework = $wpdb->get_row( $query, ARRAY_A );

        // define the status of homework
        $status = '';
        if ( empty( $homework['sent_for_checking_date'] ) ) $status = 'making';
        else if ( empty( $homework['start_date_checking'] ) ) $status = 'waiting_for_check';
        else if ( !empty( $homework['start_date_checking'] ) && ( empty( $homework['end_date_checking'] ) ) ) $status = 'checking';
        else if ( $homework['is_accepted'] == false ) $status = 'declined';
        else if ( $homework['is_accepted'] == true ) $status = 'accepted';

        // return the result
        return $status;
    }
    
    /**
      * Get homeworks attempts
      * 
      * @global wpdb        $wpdb
      * 
      * @param     int         $lesson_id         Lesson Id
      * @param     int         $user_id           User ID
      * @param     string      $type              current/declined/accepted/all
      * @return    mixed
      */
    function edc_get_homeworks_attempts( $lesson_id, $user_id = 0, $type = 'all' ) {
    
        global $wpdb;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // getting current user
            global $current_user;
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        // reset
        $limit = '';
        $where = '';
        $where_arr = [];

        $where_arr[] = "progress.user_id = '{$user_id}'";
        $where_arr[] = "progress.lesson_id = '{$lesson_id}'";

        switch( $type ) {
        
            case 'current':
                $limit = 'LIMIT 1';
                break;
        
            case 'declined':
                $where_arr[] = "homeworks.is_accepted = FALSE";
                $limit = 'LIMIT 1';
                break;
        
            case 'accepted':
                $where_arr[] = "homeworks.is_accepted = TRUE";
                $limit = 'LIMIT 1';
                break;
        
            case 'all':
                break;
        }
        
        
        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }

        $query = $wpdb->prepare( "
                    SELECT progress.*,
                           homeworks.*,
                           homeworks.id AS homework_id
                    FROM `{$wpdb->prefix}edc_users_lesson_progress` AS progress
                    INNER JOIN `{$wpdb->prefix}edc_homeworks` AS homeworks ON
                        homeworks.users_lesson_progress_id = progress.id  
                    {$where}
                    ORDER BY homeworks.id DESC
                    {$limit}
                    ", array() );

        $homework = $wpdb->get_results( $query, ARRAY_A );

        // return the result
        return $homework;
    }

    /**
      * Get recent homework of some lesson
      * 
      * @global wpdb    $wpdb
      * 
      * @param     int         $user_id           User ID
      * @param     int         $lesson_id         Lesson Id
      * @return array                             Associative array with data
      */
    function edc_get_user_recent_lesson_homework( $user_id, $lesson_id ) {
    
        global $wpdb;
        
        $query = $wpdb->prepare( "
            SELECT lsn_progress.*,
                   lsn_progress.id AS lsn_progress_id,
                   homeworks.*,
                   homeworks.id AS homework_id
            FROM `{$wpdb->prefix}edc_users_lesson_progress` AS lsn_progress
            INNER JOIN {$wpdb->prefix}users AS users ON
                lsn_progress.user_id = users.id  
            INNER JOIN {$wpdb->prefix}edc_homeworks AS homeworks ON
                lsn_progress.id = homeworks.users_lesson_progress_id  
            WHERE 
                lsn_progress.lesson_id = '{$lesson_id}'
                AND lsn_progress.user_id = '{$user_id}'
            ORDER BY homeworks.id DESC
            LIMIT 1
            ", array() );

        $result = $wpdb->get_row( $query, ARRAY_A );

        // unescape all values
        $result = stripslashes_deep( $result );

        // return the result
        return $result;
    }
































    /**
      * Get user passed lessons quantity
      * 
      * @global wpdb    $wpdb
      * 
      * @param      int     $user_id           User Id
      * @param      int     $course_id         Course Id
      *
      * @return     int                      
      */
    function edc_get_user_passed_lessons_qnty( $user_id, $course_id ) {
    
        global $wpdb;
        
        $query = $wpdb->prepare( "
                    SELECT COUNT(*) AS quantity
                    FROM `{$wpdb->prefix}edc_users_lesson_progress` AS progress
                    INNER JOIN {$wpdb->prefix}term_relationships AS term_relationships ON
                        term_relationships.object_id = progress.lesson_id  
                    INNER JOIN `{$wpdb->prefix}edc_homeworks` AS homeworks ON
                        homeworks.users_lesson_progress_id = progress.id  
                    WHERE progress.user_id = '{$user_id}'
                        AND term_relationships.term_taxonomy_id = '{$course_id}'
                        AND homeworks.is_accepted = TRUE
                    ", array() );

        $quantity = $wpdb->get_row( $query, ARRAY_A )['quantity'];

        // return the result
        return $quantity;
    }

    /**
      * Get extended user info
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $user_id           User Id
      * @return array                      Associative array with data
      */
    function edc_get_user_extended( $user_id ) {
    
        global $wpdb;
        
        $query = $wpdb->prepare( "
                             SELECT `users_extended`.*
                             FROM `{$wpdb->prefix}edc_users_extended` AS `users_extended`
                             WHERE `users_extended`.`user_id` = '{$user_id}'
                             ", array() );

        $extended_user = $wpdb->get_row( $query, ARRAY_A );

        // unescape all values
        $extended_user = stripslashes_deep( $extended_user );

        // return the result
        return $extended_user;
    }

    /**
      * Save extended user information
      *
      * @global wpdb    $wpdb
      * 
      * @param int      $user_id            User Id
      * @param array    $data_array         It can be $_REQUEST, $_POST, $_GET or just associative array with fields and
      *                                     their values
      * @return int     $result             Id of inserted/updated record
      */
    function edc_save_user_extended_info( $user_id, $data_array ) {
    
        global $wpdb;

        // sanitize inputed user data
        $data = edc_sanitize_users_inputed_db_data( $data_array );

        // forming of Where clause
        $where = array( 'user_id' => $user_id );
        // execute update operation
        $result = $wpdb->update( "{$wpdb->prefix}edc_users_extended", $data, $where );

        return $result;        
    }

    /**
      * Get for the current user his course lists
      * 
      * @global wpdb    $wpdb
      * 
      * @param       int          $id                User ID
      * @param       bool         $is_active_items   Should we return all courses or just active?
      * @return      array                           Associative array with data
      */
    function edc_get_user_course_lists( $user_id = 0, $is_active_items = true, $course_id = 0, $is_where_is_support = false ) {
    
        // do we have setted user id?
        if ( empty( $user_id ) ) {
        
            // set user ID as current user ID
            $user_id = get_current_user_id();
        }
    
        $search_params = [
            'taxonomy' => 'course', 
            'hide_empty' => true,
            'orderby' => 'name', 
            'order' => 'ASC',
        ];

        $courses = edc_user_courses( $is_active_items, $user_id, $course_id, $is_where_is_support );
        $courses = array_column( $courses, 'course_id' );

        // if there are no curses the exit from the function
        if ( empty( $courses ) ) {
            return [];
        }
        
        $search_params['include'] = $courses;

        // get courses
        $course_lists = get_terms( $search_params );

        // return the result
        return $course_lists;
    }

    /**
      * Is user's course homeworks disabled?
      * 
      * @global wpdb    $wpdb
      * 
      * @param       int          $course_id           Course ID
      * @param       int          $user_id             User ID
      * @return bool                 
      */
    function edc_is_user_course_homeworks_disabled( $course_id, $user_id = 0 ) {

        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        // get user's course info
        $user_course = edc_get_user_courses( $user_id, $course_id );

        // return result
        return ( $user_course['is_homeworks_disabled'] ) ? true : false;
    }

    /**
      * Get courses
      * 
      * @global wpdb    $wpdb
      * 
      * @param      int      $is_hide_empty     Should we hide empty courses?
      *
      * @return     array   
      */
    function edc_get_courses( $is_hide_empty = true ) {
    
        $search_params = [
            'taxonomy' => 'course', 
            'hide_empty' => $is_hide_empty,
            'orderby' => 'name', 
            'order' => 'ASC',
        ];

        // get courses
        $courses = get_terms( $search_params );

        // return the result
        return $courses;
    }

    /**
      * Check whether course ended
      * 
      * @global wpdb    $wpdb
      * 
      * @return bool                 
      */
    function edc_is_course_ended( $course_id, $user_id = 0 ) {

        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // getting current user
            global $current_user;
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }  
    
        // getting course end date
        // get user's course data
        $user_course_data = edc_get_user_courses( $user_id, $course_id );
        // get user's course date end
        $course_date_end = $user_course_data['course_date_end'];
        // in case user's course date end is not set then we should get
        // general course date end
        if ( empty( $course_date_end ) ) {
            $course_date_end = get_field( 'edc_course_date_end', 'course_' . $course_id  );
        }
    
        // is course ended?
        if ( empty( $course_date_end ) ) {
            // return the result
            return false;
        }
    
        $course_date_end = strtotime( $course_date_end );

        // get current date
        $cur_date = time();

        // is course ended?
        if ( $cur_date < $course_date_end ) {
            // return the result
            return false;
        } else {
            // return the result
            return true;
        }
    }

    /**
      * Is course homeworks disabled?
      * 
      * @global wpdb    $wpdb
      * 
      * @param       int          $course_id           Course ID
      * @return bool                 
      */
    function edc_is_course_homeworks_disabled( $course_id ) {
    
        // get course end date
        $is_course_homeworks_disabled = get_field( 'edc_is_course_homeworks_disabled', 'course_' . $course_id  );

        return ( !empty( $is_course_homeworks_disabled ) ) ? true : false;
    }

    /**
      * Get list of lessons from course
      * 
      * @global wpdb    $wpdb
      * 
      * @return array                      Associative array with data
      */
    function edc_get_course_lessons( $course_id ) {
    
        $categ = get_term( $course_id );

        $lessons_args = [
            'post_type' => 'lesson',
            $categ->taxonomy => $categ->slug,
            'posts_per_page' => -1
        ];
        $lessons_query = new WP_Query( $lessons_args );

        // sorting items
        $lessons = $lessons_query->posts;

        foreach ( $lessons as $lesson ) {
            $order = get_field( 'edc_lesson_order_num', $lesson->ID );
            $lesson->order = $order ? $order : 9999;

            $lesson->progress = edc_get_user_lesson_progress( $lesson->ID );
        }
        usort( $lessons, 'edc_order_title_arr_sort' );

        // return the result
        return $lessons;
    }

    /**
      * Get list of lessons from course which user can already can take
      * 
      * @global wpdb    $wpdb
      *
      * @param     int         $course_id       Course ID 
      * @param     boolean     $is_all          Should we return all of the lessons?
      * @return    array                        Associative array with data
      */
    function edc_get_course_lessons_to_take( $course_id, $is_all = false ) {
    
        $categ = get_term( $course_id );

        $lessons_args = [
            'post_type' => 'lesson',
            $categ->taxonomy => $categ->slug,
            'posts_per_page' => -1
        ];
        $lessons_query = new WP_Query( $lessons_args );

        // sorting and delete not needed items
        $lessons = $lessons_query->posts;

        // should we return all of the lessons?
        if ( !$is_all ) {
            foreach ( $lessons as $key => $lesson ) {

                // if lesson is not opened for passing and completed for passing then remove it from the list
                $lesson->providing_status = get_lesson_providing_status( $lesson->ID );
                if ( !( ( in_array( $lesson->providing_status, ['ended', 'undefined'] ) ) && 
                     ( !empty( $lesson->post_content ) ) ) ) {

                    unset( $lessons[$key] );
                    continue;
                }

                $order = get_field( 'edc_lesson_order_num', $lesson->ID );
                $lesson->order = $order ? $order : 9999;
            }
        }
        usort( $lessons, 'edc_order_title_arr_sort' );

        // return the result
        return $lessons;
    }

    /**
      * Get course webinars quanity
      * 
      * @global wpdb    $wpdb
      *
      * @param     int         $course_id       Course ID 
      * @return    int
      */
    function edc_get_course_webinars_quantity( $course_id ) {
    
        $categ = get_term( $course_id );

        $lessons_args = [
            'post_type' => 'lesson',
            $categ->taxonomy => $categ->slug,
            'posts_per_page' => -1
        ];
        $lessons_query = new WP_Query( $lessons_args );

        // sorting and delete not needed items
        $lessons = $lessons_query->posts;

        foreach ( $lessons as $key => $lesson ) {
        
            // if lesson is not completed then remove it from the list
            $lesson->providing_status = get_lesson_providing_status( $lesson->ID );
            if ( !( ( $lesson->providing_status === 'ended' ) || 
                    ( $lesson->providing_status === 'in_progress' ) ) ) {
                
                unset( $lessons[$key] );
                continue;
            }
        }
        
        // return the result
        return count( $lessons );
    }
    
    /**
      * Get status of user's disabling on some certain course
      */
    function edc_get_user_course_disabling_status( $user_id = 0, $course_id ) {
    
        // get demo mode variables
        global $g_demo_mode_qnty_allowed_lessons;
        global $g_demo_mode_qnty_allowed_days;

        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // getting current user
            global $current_user;
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }  
        
        // get user's course info
        $user_course = edc_get_user_courses( $user_id, $course_id );  
        
        // get current time
        $cur_time = time();
            
        // -------------------------
        // is user disconnected from the course?
        if ( $user_course['course_to_user_status_id'] == 2 ) {
            $status['is_disabled'] = true;
            $msg_text = $user_course['disabling_reason'];
            $status['message'] = $msg_text;
            return $status;
        }
        
        // ------------------------
        // check whether whether we have disabling in case course start date is not had come
        // do we have course's date of start?
        if ( !empty( $user_course['course_date_start'] ) ) {
            
            $course_date_start = strtotime( $user_course['course_date_start'] );
            if ( $course_date_start > $cur_time ) {
            
                $course_date_start_in_client_tz = edc_date_show_in_client_tz( $course_date_start );
            
                $status['is_disabled'] = true;
                $msg_text = sprintf( 
                    edc__( 'Время старта курса %s еще не наступило' ), 
                    $course_date_start_in_client_tz );
                $status['message'] = $msg_text;
                return $status;
            }
        }       
        
        // ------------------------
        // check whether whether we have disabling connected to demo mode end
        // do we have course's date of start?
        if ( $user_course['is_demo_mode'] && !empty( $user_course['course_date_start'] ) ) {
        
            $course_date_start = strtotime( $user_course['course_date_start'] );
            // don't we have allowed time for passing course in demo mode?
            // we use extra 1 day in order user can full $g_demo_mode_qnty_allowed_days days and we be blocked on 
            // next day
            if ( ( $course_date_start + ( ( $g_demo_mode_qnty_allowed_days + 1 ) * 24*60*60 ) ) < $cur_time ) {
            
                $status['is_disabled'] = true;
                $msg_text = sprintf( 
                    edc__( 'Закончился демо режим, а именно прошло уже %s дней обучения' ), 
                    $g_demo_mode_qnty_allowed_days );
                $status['message'] = $msg_text;
                return $status;
            }
        }
        // don't we have allowed quantity of lessons for passing course in demo mode?
        $passed_lessons_quantity = edc_get_user_passed_lessons_qnty( $user_id, $course_id );
        if ( $user_course['is_demo_mode'] && ( $passed_lessons_quantity >= $g_demo_mode_qnty_allowed_lessons ) ) {
        
            $status['is_disabled'] = true;
            $msg_text = sprintf( 
                edc__( 'Закончился демо режим, а именно было пройдено %s уроков' ), 
                $g_demo_mode_qnty_allowed_lessons );
            $status['message'] = $msg_text;
            return $status;
        }

        // user is not disabled
        $status['is_disabled'] = false;
        $status['message'] = '';
        return $status;
    }
    
    /**
      * Get list of roles who can study lessons
      */
    function edc_get_students_roles() {

        $roles = [];

        // get user's roles data
        $user_roles_data = get_option( 'myedc_user_roles' );

        foreach ( $user_roles_data as $role => $role_data ) {
        
            // do we have setted capability 'edc_can_study' for the role in $role?
            if ( $role_data['capabilities']['edc_can_study'] ) {
                // add role to roles stack
                $roles[] = $role;
            }
        }

        return $roles;
    }
    
    /**
      * Get list of roles who can check homeworks of students
      */
    function edc_get_checkers_homeworks_roles() {
    
        $roles = [];

        // get user's roles data
        $user_roles_data = get_option( 'myedc_user_roles' );

        foreach ( $user_roles_data as $role => $role_data ) {
        
            // do we have setted capability 'edc_can_check_homeworks' for the role in $role?
            if ( $role_data['capabilities']['edc_can_check_homeworks'] ) {
                // add role to roles stack
                $roles[] = $role;
            }
        }

        return $roles;
    }
    
    /**
      * Get list of roles which can be assigned to user at certain course
      */
    function edc_get_list_of_course_roles() {
    
        $roles = [];

        // get user's roles data
        $user_roles_data = get_option( 'myedc_user_roles' );

        foreach ( $user_roles_data as $role => $role_data ) {
        
            // do we have setted capability 'edc_one_of_the_course_main_roles' for the role in $role?
            if ( ( $role != 'administrator' ) && $role_data['capabilities']['edc_one_of_the_course_main_roles'] ) {
                // add role to roles stack
                $roles[] = $role;
            }
        }

        return $roles;
    }

    /**
      * Remove supports from users that is not taking already part in our courses
      *
      * @global wpdb    $wpdb
      *
      * @return int     $qnty_affected_students   Quantity of affected students
      */ 
    function edc_remove_supports_from_obsolete_students() {

        global $wpdb;
        
        // get query with student roles
        $student_roles = edc_get_students_roles();
        $where_arr[] = "(usermeta_roles.meta_value REGEXP '" . implode( '|', $student_roles ) . "')";
        
        // get all students that have supports
        $where_arr[] = "(supports_to_users.support_id <> '')";
        $where_arr[] = "(supports_to_users.end_date IS NULL)";
        
        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }
        
        $query = $wpdb->prepare( "
            SELECT 
                users.*,
                courses_to_users.course_id AS course_id,
                supports_to_users.support_id AS support_id
            FROM `{$wpdb->prefix}users` AS users
            INNER JOIN {$wpdb->prefix}usermeta AS usermeta_roles ON
                usermeta_roles.user_id = users.id 
            LEFT JOIN {$wpdb->prefix}edc_courses_to_users AS courses_to_users ON
                users.id = courses_to_users.user_id 
            LEFT JOIN {$wpdb->prefix}edc_courses_to_users_statuses AS courses_to_users_statuses ON
                courses_to_users.course_to_user_status_id = courses_to_users_statuses.id 
            LEFT JOIN {$wpdb->prefix}edc_supports_to_users AS supports_to_users ON
                supports_to_users.user_id = users.id  
                AND supports_to_users.course_id = courses_to_users.course_id
            {$where}
            ", array() );
        // get students list
        $students = $wpdb->get_results( $query, OBJECT );
        
        // go through students list
        $qnty_affected_students = 0;
        foreach ( $students as $student ) {
            
            // get user course disabling status
            $course_disabling_status = edc_get_user_course_disabling_status( $student->ID, $student->course_id );

            // is course inactive for student?
            if ( edc_is_course_ended( $student->course_id, $student->ID ) || 
                 $course_disabling_status['is_disabled'] ||
                 edc_is_user_blocked( $student->ID ) ) {

               // remove support from the user
               edc_set_support_for_user( $student->course_id, $student->ID, '' );           
               // increase counter of affected students
               $qnty_affected_students++;
            }
        }
        
        // return result
        return $qnty_affected_students;
    }
    
    /**
      * Get list of users who can check homeworks
      * 
      * @global wpdb    $wpdb
      * 
      * @param      int     $course_id         Course Id
      *
      * @return     int                      
      */
    function edc_get_course_homeworks_checkers( $course_id ) {
    
        global $wpdb;
        
        $where_arr = [];     
        // set default values
        $where_arr[] = "(courses_to_users.course_to_user_status_id = 1)";
        $where_arr[] = "(courses_to_users.course_id = {$course_id})";
        
        // shoud we show courses where the user is support?
        $roles = edc_get_checkers_homeworks_roles();
        $roles_str = implode( "','", $roles );
        $roles_str = "'" . $roles_str . "'";
        $where_arr[] = "(courses_to_users.role IN ({$roles_str}))";
        
        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }
        
        // making request to database
        $query = $wpdb->prepare( "
            SELECT 
                courses_to_users.user_id
            FROM `{$wpdb->prefix}edc_courses_to_users` AS courses_to_users
            {$where}
            ", array() );
        $items = $wpdb->get_results( $query, ARRAY_A );
        $items = array_column( $items, 'user_id' );

        // return the result
        return $items;
    }

    /**
      * Get users
      * 
      * @global wpdb    $wpdb
      * 
      * @param  array   $query_params      Such params as: 
      *                                        'course_id' - array
      *                                        'role'      - array
      * @return array                    
      */
    function edc_get_users( $query_params = [] ) {
    
        global $wpdb;
        global $default_db_results_limit;
        
        // reset
        $where_arr = [];     
            
        // shoud we show pages history related to certain user(s)?
        if ( isset( $query_params['course_id'] ) && !empty( $query_params['course_id'] ) ) {

            $course_ids_str = implode( "','", $query_params['course_id'] );
            $course_ids_str = "'" . $course_ids_str . "'";

            $where_arr[] = "(courses_to_users.course_id IN ({$course_ids_str}))";    
        }
        // should we get users which belong to some certain role(s)?
        if ( isset( $query_params['role'] ) && !empty( $query_params['role'] ) ) {

            $roles_str = implode( "','", $query_params['role'] );
            $roles_str = "'" . $roles_str . "'";

            $where_arr[] = "(courses_to_users.role IN ({$roles_str}))";    
        }
        
        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }

        $query = $wpdb->prepare( "
            SELECT 
                users.*
            FROM `{$wpdb->prefix}users` AS users
            INNER JOIN {$wpdb->prefix}edc_courses_to_users AS courses_to_users ON
                courses_to_users.user_id = users.ID  
            {$where}
            GROUP BY users.ID
            ORDER BY users.ID ASC
            {$limit}
            ", array() );
        $items = $wpdb->get_results( $query, ARRAY_A );

        // unescape all values
        $items = stripslashes_deep( $items );
        
        // return result
        return $items;
    }

    /**
      * Get students
      * 
      * @global wpdb    $wpdb
      * 
      * @param       int     $course_id              If '0' - then we get all the students from all courses
      * @param       bool    $is_with_all_statuses   Should we get students with all course statuses?
      * @return      array                    
      */
    function edc_get_students( $course_id = 0, $is_with_all_statuses = false ) {
    
        global $wpdb;
        
        // get users
        $args = array(
            'role__in'     => edc_get_students_roles(),
            'orderby'      => 'login',
            'order'        => 'ASC',
         ); 
        $students = get_users( $args );

        // get extended data about student
        foreach ( $students as $student ) {
          
          $student->extended_info = edc_get_user_extended( $student->ID );
          // unescape all values
          $student->extended_info = stripslashes_deep( $student->extended_info );
          
          $course_status = ( $is_with_all_statuses ) ? false : true;
          
          $courses = edc_user_courses( $course_status, $student->ID );
          $courses = array_column( $courses, 'course_id' );
          $student->courses = $courses;
        }
        
        // should we filter only students from certain course?
        if ( $course_id ) {
            
            foreach ( $students as $key => $student ) {

              if ( !in_array( $course_id, (array) $student->courses ) ) unset( $students[$key] );
            }
        }
        
        // return the result
        return $students;
    }

    /**
      * Filter students
      * 
      * @global wpdb    $wpdb
      * 
      * @return      array                    
      */
    function edc_filter_students( $filters ) {

        global $wpdb;
        
        // get query with student roles
        $student_roles = edc_get_students_roles();
        $where_arr[] = "(usermeta_roles.meta_value REGEXP '" . implode( '|', $student_roles ) . "')";
        
        // should we get students from some course?
        if ( isset( $filters['course_id'] ) && !empty( $filters['course_id'] ) ) {
        
            $course_id = implode( "','", $filters['course_id'] );
            $course_id = "'" . $course_id . "'";
            
            $where_arr[] = "(courses_to_users.course_id IN ({$course_id}))";
        }

        // should we get students of some support?
        if ( isset( $filters['support_id'] ) && !empty( $filters['support_id'] ) ) {
            $where_arr[] = "(supports_to_users.support_id = '{$filters['support_id']}')";
            $where_arr[] = "(supports_to_users.end_date IS NULL)";
        }
        // should we get students taking into account their status?
        if ( isset( $filters['students_status'] ) && !empty( $filters['students_status'] ) ) {
        
            // get just active students
            if ( $filters['students_status'] == 1 ) {
                // users has active status on course(s)
                $where_arr[] = "(courses_to_users.course_to_user_status_id = '1')";
                // users is not blocked
                // we use such query because some users have in database field 'edc_blocking_info' but
                // someone does not have
               $where_arr[] = "(users.id NOT IN (SELECT user_id FROM {$wpdb->prefix}usermeta WHERE (meta_key = 'edc_blocking_info') AND (meta_value <> '')))";
            }
        }
        
        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }
        
        $query = $wpdb->prepare( "
            SELECT 
                users.*
            FROM `{$wpdb->prefix}users` AS users
            INNER JOIN {$wpdb->prefix}usermeta AS usermeta_roles ON
                usermeta_roles.user_id = users.id 
            LEFT JOIN {$wpdb->prefix}edc_courses_to_users AS courses_to_users ON
                users.id = courses_to_users.user_id 
            LEFT JOIN {$wpdb->prefix}edc_courses_to_users_statuses AS courses_to_users_statuses ON
                courses_to_users.course_to_user_status_id = courses_to_users_statuses.id 
            LEFT JOIN {$wpdb->prefix}edc_supports_to_users AS supports_to_users ON
                supports_to_users.user_id = users.id  
                AND supports_to_users.course_id = courses_to_users.course_id
            LEFT JOIN {$wpdb->prefix}usermeta AS usermeta_blocking_data ON
                usermeta_blocking_data.user_id = users.id 
            {$where}
            GROUP BY users.id
            ", array() );
            
        $students = $wpdb->get_results( $query, OBJECT );

        // unescape all values
        $students = stripslashes_deep( $students );
        
        // get extended data about student
        foreach ( $students as $student ) {
          // get extended data about student
          $student->extended_info = edc_get_user_extended( $student->ID );
          // unescape all values
          $student->extended_info = stripslashes_deep( $student->extended_info );
        }
        
        // return the result
        return $students; 
    }

    /**
      * Get lesson status of providing: not started, running, ended or undefined
      * 
      * @param       int          $id                Lesson ID
      * @param       int          $duration_minutes  Duration of lessson in minutes
      * @return      string       $status            'not_started', 'running', 'ended', 'undefined'             
      */
    function get_lesson_providing_status( $id, $duration_minutes = 120 ) {
    
        $status = '';
        
        // get lesson start date
        $date_start = get_field( 'edc_lesson_date_start', $id );
        // get lesson content
        $lesson_content = edc_get_post_content( $id );
        // get the lesson webinar link
        $webinar_url = get_field( 'edc_lesson_webinar_url', $id );

        // if date is empty then status is Undefined
        if ( empty( $date_start ) ) {
            $status = 'undefined';
            return $status;
        }
        
        $date_start = strtotime( get_field( 'edc_lesson_date_start', $id ) );

        // define variables
        $duration = $duration_minutes * 60;
        $date_end = $date_start + $duration;
        $cur_date = time();
        
        // is lesson not started yet?
        if ( $cur_date <= $date_start ) $status = 'not_started';
        
        
        // is lesson started/ended?
        // etot kod mojno perepisat', on byl dobavlen kogda my pereshli v format obucheniia 
        // po videozapisiam (miniuroki s minizadaniiami)
        else if ( ( $cur_date >= $date_start ) && !empty( $lesson_content ) && ( empty( $webinar_url ) ) )  $status = 'ended';
        
        
        // is lesson running?
        else if ( ( $cur_date >= $date_start ) && ( $cur_date <= $date_end ) ) $status = 'running';
        // is lesson ended?
        else if ( $cur_date > $date_end ) $status = 'ended';

        return $status;
    }

    /**
      * Get lesson status of passing by some user
      * 
      * @param       int          $lesson_id         Lesson ID
      * @param       int          $user_id           User ID
      * @return      string       $status            'completed', 'in_progress', 'closed'             
      */
    function get_lesson_passing_status( $lesson_id, $user_id = 0 ) {
    
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // getting current user
            global $current_user;
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }   
    
        // get lesson progress
        $lesson_progress = edc_get_user_lesson_progress( $lesson_id, $user_id );
        
        // is lesson completed?
        if ( $lesson_progress['is_accepted'] ) $status = 'completed';
        // is lesson in progress?
        else if ( !empty( $lesson_progress ) ) $status = 'in_progress';
        // is lesson not allowed yet?
        else $status = 'closed';

        return $status;
    }
    
    /**
      * Getting to know whether user is blocked
      * 
      * @param       int          $id                User ID
      * @return      bool         
      */
    function edc_is_user_blocked( $user_id = 0 ) {
    
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // getting current user
            global $current_user;
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        // get blocking information about user
        $blocking_data = edc_get_user_blocking_data( $user_id );
        if ( !empty( $blocking_data ) ) {
        
            // get current date
            $current_date = time();
            // get blocking date
            $blocking_date = strtotime( $blocking_data[0] );
            
            if ( $current_date >= $blocking_date ) return true;
            else return false;
        } else {
        
            return false;
        }
    }
    
    /**
      * Get user blocking data
      * 
      * @param       int          $id                User ID
      * @return      array        $blocking_data
      */
    function edc_get_user_blocking_data( $user_id = 0 ) {
    
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // getting current user
            global $current_user;
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        // get blocking information about user
        $blocking_info = get_field( 'edc_blocking_info', 'user_' . $user_id );
        
        $blocking_data = [];
        if ( !empty( $blocking_info ) ) {
            // getting blocking information as array
            $blocking_data = explode( ';', $blocking_info );
            
        }
        
        return $blocking_data;
    }
    
    /**
      * Check whether current user is demo user
      * 
      * @return      bool
      */
    function edc_is_current_user_demo() {
    
        global $current_user;
        global $user_demo_login;
        
        if ( $current_user->user_login == $user_demo_login ) {

            return true;
        } else {
        
            return false;
        }
    }
    
    /**
      * Check whether user is demo user
      * 
      * @param       string      $username    Username (login)
      * @return      bool
      */
    function edc_is_user_demo( $username ) {
    
        global $user_demo_login;
        
        if ( $username === $user_demo_login ) {
            return true;
        } else {
            return false;
        }     
    }
    
    /**
      * Make user loginning
      * 
      * @param       string      $login    User login
      * @return      bool
      */
    function edc_make_user_loginning( $login ) {
    
        global $current_user;

        if ( $current_user->user_login !== $login ) {

            $creds = [];
            $creds['user_login'] = $login;
            $creds['user_password'] = '11111111';
            $creds['remember'] = TRUE;
            $user = wp_signon( $creds, FALSE );
        }
    }

    /**
      * Get supports rating table data
      * 
      * @global wpdb    $wpdb
      *
      * @param       timestamp      $start_date    Start date
      * @param       timestamp      $end_date      End date
      * @return array                              Associative array with data
      */
    function edc_get_supports_rating_table_data( $start_date, $end_date ) {
    
        global $wpdb;
        
        $start_date_str = date( edc_get_mysql_date_format(), $start_date );
        $end_date_str   = date( edc_get_mysql_date_format(), $end_date );
        
        $query = $wpdb->prepare( "
            SELECT COUNT(*) AS quantity, 
                   users.display_name as checker_display_name, 
                   users.user_login as checker_user_login    
            FROM `{$wpdb->prefix}edc_homeworks` AS homeworks
            INNER JOIN `{$wpdb->prefix}users` AS users ON
                homeworks.checker_id = users.id
            WHERE (homeworks.is_accepted IS NOT NULL)
               AND (homeworks.end_date_checking > '{$start_date_str}') 
               AND (homeworks.end_date_checking < '{$end_date_str}')
            GROUP BY homeworks.checker_id
            ORDER BY quantity  DESC
            ", array() );
        $supports_rating_table_data = $wpdb->get_results( $query, ARRAY_A );

        // unescape all values
        $supports_rating_table_data = stripslashes_deep( $supports_rating_table_data );
    
        // return the result
        return $supports_rating_table_data;
    }
    
    /**
      * Set support for user
      * 
      * @param       int      $course_id     ID of the course
      * @param       int      $user_id       ID of the user
      * @param       int      $support_id    ID of the support
      * @return      bool
      */
    function edc_set_support_for_user( $course_id, $user_id, $support_id ) {
    
        global $wpdb;
            
        // get current time
        $cur_time = time();
            
        // get current user's support ID
        $cur_support = edc_get_user_current_support( $course_id, $user_id );
            
        if ( $cur_support['support_id'] != $support_id ) {
        
            // get data for query
            $end_date = date( edc_get_mysql_date_format(), $cur_time );

            // remove current support
            $query = $wpdb->prepare( "
                UPDATE `{$wpdb->prefix}edc_supports_to_users`             
                SET `end_date` = '{$end_date}'
                WHERE `user_id` = '{$user_id}'
                   AND `course_id` = '{$course_id}'
                   AND `end_date`IS NULL
                ", array() );
            $result = $wpdb->query( $query );

            // add new support?
            if ( !empty( $support_id ) ) {
                // set necessary fields
                $data = [];
                $data['course_id'] = $course_id;
                $data['user_id'] = $user_id;
                $data['support_id'] = $support_id;
                $data['start_date'] = date( edc_get_mysql_date_format(), $cur_time );

                // execute insert operation
                $result = $wpdb->insert( "{$wpdb->prefix}edc_supports_to_users", $data );

                // set current support in courses_to_users DB table
                $data = [];
                $data['supports_to_users_id'] = $wpdb->insert_id;

                // forming of Where clause
                $where = [
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                ];
                // execute update operation in database
                $result = $wpdb->update( "{$wpdb->prefix}edc_courses_to_users", $data, $where );
                
            } else {
            
                // remove support in courses_to_users DB table
                $data = [];
                $data['supports_to_users_id'] = null;

                // forming of Where clause
                $where = [
                    'user_id' => $user_id,
                    'course_id' => $course_id,
                ];
                // execute update operation in database
                $result = $wpdb->update( "{$wpdb->prefix}edc_courses_to_users", $data, $where );
            }
            
            
        }
        
        $result = true; 

        // return result
        $result = ( $result ) ? true : false;
        return $result; 
    }
    
    /**
      * Set user's course data
      * 
      * @param       int      $course_id     ID of the course
      * @param       int      $user_id       ID of the user
      * @param       array    $data_arr      Array with data
      * @return      bool
      */
    function edc_set_user_course_data( $course_id, $user_id, $data_arr ) {
    
        global $wpdb;
            
        $data = [];
        
        // get current date
        $cur_date = date( edc_get_mysql_date_format(), time() );
        
        // set necessary fields
        if ( isset( $data_arr['course_to_user_status_id'] ) ) {
          $data['course_to_user_status_id'] = $data_arr['course_to_user_status_id'];
        }
        if ( isset( $data_arr['disabling_reason'] ) ) {
          $data['disabling_reason'] = $data_arr['disabling_reason'];
        }
        if ( isset( $data_arr['is_homeworks_disabled'] ) ) {
          $data['is_homeworks_disabled'] = $data_arr['is_homeworks_disabled'];
        }
        if ( isset( $data_arr['is_demo_mode'] ) ) {
          $data['is_demo_mode'] = $data_arr['is_demo_mode'];
        }
        if ( isset( $data_arr['is_low_priority'] ) ) {
          $data['is_low_priority'] = $data_arr['is_low_priority'];
        }
        if ( isset( $data_arr['role'] ) ) {
          $data['role'] = $data_arr['role'];
        }
        
        if ( !empty( $data_arr['course_date_end'] ) ) {        
            $data['course_date_end'] = date( edc_get_mysql_date_format(), strtotime( $data_arr['course_date_end'] ) );
        }
        
        if ( !empty( $data_arr['course_date_start'] ) ) {        
            $data['course_date_start'] = date( edc_get_mysql_date_format(), strtotime( $data_arr['course_date_start'] ) );
        }

        // forming of Where clause
        $where = [ 
            'course_id' => $course_id,
            'user_id' => $user_id,
        ];
        // execute update operation
        $result = $wpdb->update( "{$wpdb->prefix}edc_courses_to_users", $data, $where );
        $result = true; 

        // return result
        $result = ( $result ) ? true : false;
        return $result;     
    }
    
    /**
      * Getting to know whether user is blocked
      * 
      * @param       int          $course_id              Course ID
      * @param       int          $user_id                User ID
      * @return      bool         
      */
    function edc_is_user_disabled_at_course( $course_id, $user_id = 0 ) {
    
        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // getting current user
            global $current_user;
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $query = $wpdb->prepare( "
            SELECT COUNT(*)
            FROM `{$wpdb->prefix}edc_courses_to_users` AS courses_to_users
            WHERE 
                courses_to_users.user_id = '{$user_id}'
                AND courses_to_users.course_id = '{$course_id}'
                AND courses_to_users.course_to_user_status_id <> '1'
            ", array() );
        $result = $wpdb->get_var( $query );

        // return result
        $result = ( $result ) ? true : false;
        return $result; 
    }
    
    /**
      * Get current user's support ID
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $course_id         Course Id
      * @param  int     $user_id           User Id
      * @return array                      Associative array with data
      */
    function edc_get_user_current_support( $course_id, $user_id = 0 ) {
    
        global $wpdb;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = get_current_user_id();
        }
        
        $query = $wpdb->prepare( "
            SELECT 
                supports_to_users.*,
                users.display_name AS support_name
            FROM `{$wpdb->prefix}edc_courses_to_users` AS courses_to_users
            INNER JOIN {$wpdb->prefix}edc_supports_to_users AS supports_to_users ON
                courses_to_users.supports_to_users_id = supports_to_users.id 
            INNER JOIN {$wpdb->prefix}users AS users ON
                supports_to_users.support_id = users.id  
            WHERE 
               courses_to_users.course_id = '{$course_id}' AND
               courses_to_users.user_id = '{$user_id}'
            ", array() );
        $support = $wpdb->get_row( $query, ARRAY_A );

        // unescape all values
        $support = stripslashes_deep( $support );
    
        // return the result
        return $support;
    }

    /**
      * Get user's courses lists or some course
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $user_id           User Id
      * @param  int     $course_id         Course Id
      * @return array                      Associative array with data
      */
    function edc_get_user_courses( $user_id = 0, $course_id = 0 ) {

        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $where_arr = [];     
        // set default values
        $where_arr[] = "(courses_to_users.user_id = '{$user_id}')";
            
        // should we get some certain course data?
        if ( $course_id ) {
            $where_arr[] = "(courses_to_users.course_id = '{$course_id}')";
        }
        
        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }
        
        $query = $wpdb->prepare( "
            SELECT 
                courses_to_users.*,
                courses_to_users_statuses.title AS courses_to_users_status,
                terms.name AS course_title
            FROM `{$wpdb->prefix}edc_courses_to_users` AS courses_to_users
            INNER JOIN {$wpdb->prefix}edc_courses_to_users_statuses AS courses_to_users_statuses ON
                courses_to_users_statuses.id = courses_to_users.course_to_user_status_id 
            INNER JOIN {$wpdb->prefix}terms AS terms ON
                terms.term_id = courses_to_users.course_id 
            {$where}
            ORDER BY terms.term_id
            ", array() );
            
        if ( $course_id ) {
            $courses = $wpdb->get_row( $query, ARRAY_A );
        } else {
            $courses = $wpdb->get_results( $query, ARRAY_A );
        }

        // unescape all values
        $courses = stripslashes_deep( $courses );
    
        // return the result
        return $courses;
    }
    
    /**
      * Add course to user
      * 
      * @param       int      $course_id     ID of the course
      * @param       int      $user_id       ID of the user
      * @return      bool
      */
    function edc_add_course_to_user( $course_id, $user_id ) {

        global $wpdb;
            
        // get current time
        $cur_time = time();

        // get current user's support ID
        $user_course = edc_get_user_course( $course_id, $user_id );

        if ( empty( $user_course ) ) {

            // set necessary fields
            $data = [];
            $data['course_id'] = $course_id;
            $data['user_id'] = $user_id;
            $data['role'] = edc_get_default( 'user_role' );
            $data['course_to_user_status_id'] = 1;

            // execute insert operation
            $result = $wpdb->insert( "{$wpdb->prefix}edc_courses_to_users", $data ); 
        } else {
            $result = false;
        }

        // return result
        $result = ( $result ) ? true : false;
        return $result; 
    }

    /**
      * Get some certain user's course
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $course_id         Course Id
      * @param  int     $user_id           User Id
      * @return array                      Associative array with data
      */
    function edc_get_user_course( $course_id, $user_id = 0 ) {
    
        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $query = $wpdb->prepare( "
            SELECT 
                courses_to_users.*,
                courses_to_users_statuses.title AS user_course_status
            FROM `{$wpdb->prefix}edc_courses_to_users` AS courses_to_users
            INNER JOIN {$wpdb->prefix}edc_courses_to_users_statuses AS courses_to_users_statuses ON
                courses_to_users_statuses.id = courses_to_users.course_to_user_status_id  
            WHERE courses_to_users.course_id = '{$course_id}'
               AND courses_to_users.user_id = '{$user_id}'
            ", array() );
        $course = $wpdb->get_row( $query, ARRAY_A );

        // unescape all values
        $course = stripslashes_deep( $course );
    
        // return the result
        return $course;
    }

    /**
      * Get user's courses
      * 
      * @global wpdb    $wpdb
      * 
      * @param  bool    $is_active_items   Should we return all courses or just active?
      * @param  int     $user_id           User Id
      * @return array                      Associative array with data
      */
    function edc_user_courses( $is_active_items = false, $user_id = 0, $course_id = 0, $is_where_is_support = false, $role = '' ) {
    
        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $where_arr = [];     
        // set default values
        $where_arr[] = "(courses_to_users.user_id = '{$user_id}')";
            
        // shoud we show pages history related to certain user?
        if ( $is_active_items ) {
            $where_arr[] = "(courses_to_users.course_to_user_status_id = 1)";
        }
        
        if ( !empty( $course_id ) ) {
            $where_arr[] = "(courses_to_users.course_id = '{$course_id}')";
        }
        
        if ( !empty( $role ) ) {
            $where_arr[] = "(courses_to_users.role = '{$role}')";
        }
        
        // shoud we show courses where the user is support?
        if ( $is_where_is_support ) {
            $roles = edc_get_checkers_homeworks_roles();
            $roles_str = implode( "','", $roles );
            $roles_str = "'" . $roles_str . "'";
            $where_arr[] = "(courses_to_users.role IN ({$roles_str}))";
        }
        
        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }
        
        // making request to database
        $query = $wpdb->prepare( "
            SELECT 
                courses_to_users.*,
                courses_to_users_statuses.title AS user_course_status
            FROM `{$wpdb->prefix}edc_courses_to_users` AS courses_to_users
            INNER JOIN {$wpdb->prefix}edc_courses_to_users_statuses AS courses_to_users_statuses ON
                courses_to_users_statuses.id = courses_to_users.course_to_user_status_id  
            {$where}
            ", array() );
        $course = $wpdb->get_results( $query, ARRAY_A );

        // unescape all values
        $course = stripslashes_deep( $course );

        // return the result
        return $course;
    }

    /**
      * Get course statuses
      * 
      * @global wpdb    $wpdb
      * 
      * @return array                      Associative array with data
      */
    function edc_get_course_statuses() {
    
        global $wpdb;

        $query = $wpdb->prepare( "
            SELECT courses_to_users_statuses.*
            FROM `{$wpdb->prefix}edc_courses_to_users_statuses` AS courses_to_users_statuses
            ", array() );
        $result = $wpdb->get_results( $query, ARRAY_A );

        // unescape all values
        $result = stripslashes_deep( $result );
    
        // return the result
        return $result;
    } 

    /**
      * Get user's course role
      *
      * @return    array     $role_data     Associative array
      */ 
    function edc_get_user_course_role( $course_id, $user_id = 0 ) {
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = get_current_user_id();
        }
        
        $user_course = edc_get_user_course( $course_id, $user_id );
        
        if ( empty( $user_course['role'] ) ) {
            $role = edc_get_user_roles( $course_id, $user_id )[0]['id'];
        } else {
            $role = $user_course['role'];
        }
        
        $roles_info = wp_roles()->roles;
        $role_data = [
            'id' => $role,
            'title' => translate_user_role( $roles_info[$role]['name'] ),
        ];

        return $role_data;
    }   
    
    /**
      * Get user's main role
      * 
      * @return    string
      */ 
    function edc_get_user_role( $user_id = 0 ) {
    
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = get_current_user_id();
        }
    
        $user = $user_id ? new WP_User( $user_id ) : wp_get_current_user();
        return $user->roles ? $user->roles[0] : '';
    }

    /**
      * Get user's roles
      * 
      * @return    array     $roles     Associative array
      */ 
    function edc_get_user_roles( $user_id = 0 ) {
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = get_current_user_id();
        }
        
        $roles = array();
        $user = new WP_User( $user_id );
        if ( !empty( $user->roles ) && is_array( $user->roles ) ) {
        
            $roles_info = wp_roles()->roles;
            
            foreach ( $user->roles as $role ) {
            
                $role_data = [
                    'id' => $role,
                    'title' => translate_user_role( $roles_info[$role]['name'] ),
                ];
            
                $roles[] = $role_data;
            }
        }
        
        return $roles;
    }  

    /**
      * Get translation of user role
      * 
      * @return    string
      */ 
    function edc_get_translate_user_role( $role ) {

        $roles_info = wp_roles()->roles;
        return translate_user_role( $roles_info[$role]['name'] );
    }   

    /**
      * Set user's course certificate data
      * 
      * @param       int      $course_id     ID of the course
      * @param       int      $user_id       ID of the user
      * @param       array    $data_arr      Array with data
      * @return      bool
      */
    function edc_set_user_course_certificate_data( $course_id, $user_id, $data_arr ) {
    
        global $wpdb;
            
        $data = [];
        
        // set necessary fields
        if ( isset( $data_arr['comments'] ) ) {
          $data['comments'] = $data_arr['comments'];
        }
        
        // forming of Where clause
        $where = [ 
            'course_id' => $course_id,
            'user_id' => $user_id,
        ];
        // execute update operation
        $result = $wpdb->update( "{$wpdb->prefix}edc_certificates", $data, $where );
        $result = true; 

        // return result
        $result = ( $result ) ? true : false;
        return $result;     
    }
    
    /**
      * Is user can view course certificate?
      * 
      * @global wpdb    $wpdb
      * 
      * @param     int        $course_id         Course ID
      * @param     int        $user_id           User ID
      * @return    mixed
      */
    function edc_get_data_is_user_can_view_course_certificate( $course_id, $user_id ) {
    
        global $wpdb;
        
        $result = [
            'result' => true,
            'message' => edc__( 'Ok' ),
        ];
        
        // define whether user passed all of the lessons from the course?
        $user_passed_lessons_qnty = edc_get_user_passed_lessons_qnty( $user_id, $course_id );
        $qnty_lessons = count( edc_get_course_lessons_to_take( $course_id, true ) );
    
        if ( $user_passed_lessons_qnty < $qnty_lessons ) {
            $result = [
                'result' => false,
                'message' => edc__( 'Для получения сертификата необходимо пройти все уроки из курса' ),
            ];
        }
    
        // return the result
        return $result;
    }
    
    /**
      * Is we can show course certificate to user in general?
      * 
      * @param     int        $course_id         Course ID
      * @param     int        $user_id           User ID
      * @return    mixed
      */
    function edc_is_can_show_course_certificate_to_user_in_general( $course_id, $user_id ) {
    
        $is_can_show_course_certificate_in_general = get_field( 'edc_is_can_show_course_certificate_in_general', 'course_' . $course_id );
        // return the result in case we cannot show certificate to user
        if ( empty( $is_can_show_course_certificate_in_general ) ) return false;
        
        // return the result FALSE in case we have disabled homeworks for user
        if ( edc_is_user_course_homeworks_disabled( $course_id, $user_id ) ) return false;
        
        // return the result FALSE in case we have disabled homeworks
        if ( edc_is_course_homeworks_disabled( $course_id ) ) return false;
        
        // return the result TRUE
        return true;
    }
    
    /**
      * Get user's course certificate
      * 
      * @param     array      $query_arr         Query associative array 
      * @return    mixed
      */
    function edc_get_user_course_certificates( array $query_arr ) {

        global $wpdb;
        
        $where_arr = [];     
            
        if ( !empty( $query_arr['user_id'] ) ) {
            $where_arr[] = "(edc_certificates.user_id = '{$query_arr['user_id']}')";
        }
        
        if ( !empty( $query_arr['course_id'] ) ) {
            $where_arr[] = "(edc_certificates.course_id = '{$query_arr['course_id']}')";
        }
            
        if ( !empty( $query_arr['uid'] ) ) {
            $where_arr[] = "(edc_certificates.uid = '{$query_arr['uid']}')";
        }
        
        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }
        
        $query = $wpdb->prepare( "
            SELECT 
                edc_certificates.*
            FROM `{$wpdb->prefix}edc_certificates` AS edc_certificates
            {$where}
            ", array() );

        $certificates = $wpdb->get_results( $query, ARRAY_A );

        // unescape all values
        $certificates = stripslashes_deep( $certificates );
        
        // return result
        return $certificates;
    }
    
    /**
      * Create certificate for user for some course
      * 
      * @param     int      $course_id     Course ID
      * @param     int      $user_id       User's ID
      * @return    mixed
      */
    function edc_create_user_course_certificate( $course_id, $user_id ) {

        // check whether we already have certificate
        $query_arr = [
            'user_id' => $user_id,
            'course_id' => $course_id,
        ];
        $certificate = edc_get_user_course_certificates( $query_arr );
        if ( !empty( $certificate ) ) {
            // return result
            return false;
        }

        // create certificate
        global $wpdb;
        $data = [];
        // set necessary fields
        $data['course_id'] = $course_id;
        $data['user_id'] = $user_id;
        $data['generated_date'] = date( edc_get_mysql_date_format(), time() );
        // generate unique ID based on current date with time
        $data['uid'] = wp_hash( time() );
        // execute insert operation
        $result = $wpdb->insert( "{$wpdb->prefix}edc_certificates", $data );

        // get certificate
        $query_arr = [
            'user_id' => $user_id,
            'course_id' => $course_id,
        ];
        $certificate = edc_get_user_course_certificates( $query_arr );

        // return the result
        return $certificate;
    
        return false;
    }
    
    /**
      * Get date of start course learning for user
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $course_id         Course Id
      * @param  int     $user_id           User Id
      * @return string
      */
    function edc_get_user_start_course_date( $course_id, $user_id = 0 ) {
    
        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $first_lesson_id = edc_get_course_lessons_to_take( $course_id )[0]->ID;
        
        $query = $wpdb->prepare( "
            SELECT 
                analytics_objects.*
            FROM `{$wpdb->prefix}edc_analytics_objects` AS analytics_objects
            WHERE 
                analytics_objects.wp_object_id = {$first_lesson_id} AND
                analytics_objects.user_id = {$user_id}
            ORDER BY analytics_objects.date ASC
            LIMIT 1
            ", array() );
            
        $records = $wpdb->get_results( $query, ARRAY_A );
        
        // return result
        return $records[0]['date'];
    }
    
    /**
      * Get date of end course learning for user
      * 
      * @global wpdb    $wpdb
      * 
      * @param  int     $course_id         Course Id
      * @param  int     $user_id           User Id
      * @return string
      */
    function edc_get_user_end_course_date( $course_id, $user_id = 0 ) {
    
        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $lessons_to_take = edc_get_course_lessons_to_take( $course_id );
        $last_lesson_id = $lessons_to_take[count($lessons_to_take)-1]->ID;
        
        $query = $wpdb->prepare( "
            SELECT 
                homeworks.end_date_checking
            FROM `{$wpdb->prefix}edc_users_lesson_progress` AS users_lesson_progress
            INNER JOIN {$wpdb->prefix}edc_homeworks AS homeworks ON
                homeworks.users_lesson_progress_id = users_lesson_progress.id  
            WHERE 
                users_lesson_progress.lesson_id = {$last_lesson_id} AND
                users_lesson_progress.user_id = {$user_id} AND
                homeworks.is_need_to_check = FALSE AND
                homeworks.is_accepted = TRUE
            ORDER BY homeworks.end_date_checking DESC
            LIMIT 1
            ", array() );
            
        $records = $wpdb->get_results( $query, ARRAY_A );

        // return result
        return $records[0]['end_date_checking'];
    }
    
    
   



























    /**
     * Get default internal page URL
     *
     * @return     string       URL
     */
    function edc_get_default_internal_page_url() {
        
        // get page URL of 'Courses'
        $taxonomy = get_taxonomy( 'course' );
        $url = get_bloginfo( 'url' ) . '/' . $taxonomy->rewrite['slug'];
        
        return $url;
    }
    
    /**
      * Get default values
      *
      * @param   string       $field        Name of the field
      * @return  mixed|bool                 If such field is absent then it returns 'false'.
      */
    function edc_get_default( $field ) {
    
        $defaults = [];
        
        // Manager ID from WordPress list of users
        $defaults['user_role'] = 'student'; 

        // return result
        return ( isset( $defaults[$field] ) ) ? $defaults[$field] : false;
    }

    /**
     * Is guest allowed to view requested page
     *
     * @return     boolean
     */
    function edc_is_guest_allowed_to_view_requested_page() {
        
        return false;
    }

    /**
     * Sanitize data. If function face unknown field then she unset it.
     *
     * @param array $data  It must be associative array
     * @return array Associative array with sanitized data
     */
    function edc_sanitize_users_inputed_db_data( $data ) {
        
        // say what field by what function must be sanitized
        $sanitize_field_maps = array();
        $sanitize_field_maps['id']                         = 'intval';
        $sanitize_field_maps['lesson_id']                  = 'intval';
        $sanitize_field_maps['is_valid_telegram_chat_id']  = 'intval';
        $sanitize_field_maps['user_id']                    = 'intval';
        $sanitize_field_maps['course_id']                  = 'intval';
        $sanitize_field_maps['support_id']                 = 'intval';
        $sanitize_field_maps['is_homeworks_disabled']      = 'intval';
        $sanitize_field_maps['is_low_priority']            = 'intval';
        $sanitize_field_maps['is_demo_mode']               = 'intval';
        $sanitize_field_maps['course_to_user_status_id']   = 'intval';
        $sanitize_field_maps['is_send_to_group_chat_checker_comments_short'] = 'intval';
        
        $sanitize_field_maps['display_name']        = 'sanitize_text_field';
        $sanitize_field_maps['phone']               = 'sanitize_text_field';
        $sanitize_field_maps['skype']               = 'sanitize_text_field';
        $sanitize_field_maps['telegram_login']      = 'sanitize_text_field';
        $sanitize_field_maps['social_network']      = 'sanitize_text_field';
        $sanitize_field_maps['telegram_chat_id']    = 'sanitize_text_field';
        $sanitize_field_maps['s']                   = 'sanitize_text_field';
        $sanitize_field_maps['search_type']         = 'sanitize_text_field';
        $sanitize_field_maps['search_query']        = 'sanitize_text_field';
        $sanitize_field_maps['disabling_reason']    = 'sanitize_text_field';
        $sanitize_field_maps['course_date_start']   = 'sanitize_text_field';
        $sanitize_field_maps['course_date_end']     = 'sanitize_text_field';
        $sanitize_field_maps['role']                = 'sanitize_text_field';
        
        $sanitize_field_maps['about_me']         = 'sanitize_textarea_field';
        $sanitize_field_maps['course_goals']     = 'sanitize_textarea_field';
        $sanitize_field_maps['homework']         = 'edc_htmlentities';
        $sanitize_field_maps['checker_comments'] = 'edc_htmlentities';
        $sanitize_field_maps['checker_comments_short']        = 'edc_htmlentities';
        $sanitize_field_maps['checker_comments_for_teachers'] = 'edc_htmlentities';
        $sanitize_field_maps['cert_comments']                 = 'edc_htmlentities';
        
        $sanitize_field_maps['user_email']       = 'sanitize_email';
        $sanitize_field_maps['user_pass']        = 'esc_attr';
        
        // if field is unknown then unset it
        $sanitized_data = array();
        foreach ( $data as $field => $value ) {
            if ( isset( $sanitize_field_maps[$field] ) ) {
                $sanitized_data[$field] = call_user_func( $sanitize_field_maps[$field], $value );
            }
        }
        
        return $sanitized_data;
    }
    
    /**
     * Make HTML entities
     *
     * @param      string        $str      Text
     * @return     string                       
     */
    function edc_htmlentities( $str ) {

        $str = htmlentities( $str );
        $str = wp_encode_emoji( $str );

        return $str;
    }

    /**
     * Validate saving data. If function face unknown field then she unset it.
     *
     * @param      array     $required_fields      Must contain names of fields to check (reired fields)
     * @return     boolean                         TRUE is fields are valid, or FALSE in case at least 
     *                                             1 field is invalid
     */
    function edc_is_valid_saving_fields( $required_fields, $data_to_check ) {
        // telling what fields with which function we need to check
        $fields_validation_map = array( 
            'homework' => 'edc_is_empty',
            'checker_comments' => 'edc_is_empty',
            'user_email' => 'is_email',
            'user_id' => 'edc_is_true',
            'lesson_id' => 'edc_is_true',
            'course_id' => 'edc_is_true',
            'support_id' => 'edc_is_true',
            'id' => 'edc_is_true',
            'course_to_user_status_id' => 'edc_is_true',
        );                                                        
        // taking into account that each function has its own logic - we need to
        // fill map which will tell us What result from what function will tell us
        // that validation of some field failed
        $failed_field_validation_map = array( 
            'edc_is_empty' => TRUE,
            'is_email' => FALSE,
            'edc_is_true' => FALSE,
        );

        // validation
        foreach ( $required_fields as $required_field ) {
            // is this required field is absent?
            if ( ! isset( $_REQUEST[$required_field] ) ) {
                return FALSE; 
            } else {
                // this required field is present so validate it

                $field_value = $data_to_check[$required_field];
                $field_validation_func_name = $fields_validation_map[$required_field];
                $field_validation_failed_value = $failed_field_validation_map[$field_validation_func_name];
                
                // is this required field is invalid?
                if ( call_user_func( $field_validation_func_name, $field_value ) == $field_validation_failed_value ) {
                    return FALSE;
                }
            }
        }
        
        // all required field are valid
        return TRUE;
    }
    function edc_is_empty( $var ) {
        return empty( $var );
    }
    function edc_is_true( $var ) {
        return ( intval( $var ) != 0 ) ? TRUE : FALSE;
    }
    function edc_is_date( $var ) {
        // tesing for format 'dd.mm.YYYY'
        // @refactor: need to improve it, cause for example this rule pass such date as '33.19.9999'
        return preg_match( '/^[0-3][0-9].[0-1][0-9].[1-2][0-9]{3}$/', $var );
    }
    function edc_is_float( $var ) {
        return preg_match( '/^[-]?[0-9]*[.]?[0-9]*$/', $var );
    }

    /**
      * Generate unique ID
      *
      * @dependency    wp_hash()       Wordpress function
      * 
      * @param     integer             $length           Length of needed unique ID. Maximum length is 32
      * @return    string              $unique_id                  
      */
    function edc_generate_unique_id( $length = 32 ) {
      
      // generate unique ID based on current date with time
      $unique_id = wp_hash( time() );
      $unique_id = substr( $unique_id, 0, $length );
      
      return $unique_id;
    }


    // when we want to append to URL some parameter then we need to define what concatenation
    // char we should use: '?' or '&'
    function edc_get_parameter_concatenation_symbol( $url ) {
        $url_parameter_concatenation_symbol = ( ( strpos( $url, '&' ) !== FALSE ) || 
                                                ( strpos( $url, '?' ) !== FALSE ) ) ?  '&' : '?';
        return $url_parameter_concatenation_symbol;
    }
    
    // add parameter to some URL
    function edc_add_param_to_url( $url, $param ) {
    
        if ( !empty( $param ) ) {
            return $url . edc_get_parameter_concatenation_symbol( $url ) . $param;
        } else {
            return $url;
        }
    }
    
    /**
      * Do temporary short URL
      * 
      * @param     string         $url       URL to make short
      * @return    string                    Returns parameter 'sui' (Short URL ID)                  
      */
    function edc_do_temp_short_url( $url ) {
        
        // set length of short URL ID
        $hash_len = 6;
        
        // get hash of the URL
        $sui = wp_hash( $url );
        $sui = substr( $sui, 0, $hash_len );
        
        // save URL to session using URL's hash
        $_SESSION['edc']['short_urls'][$sui] = $url;
        
        return edc_add_param_to_url( get_bloginfo( 'url' ), 'sui=' . $sui );
    }
    
    /**
      * Undo temporary short URL. It is opposite to function 'edc_do_temp_short_url()'
      * 
      * @param     string         $sui        Short URL ID
      * @return    string|null                Full URL or NULL if such short URL is absent                 
      */
    function edc_undo_temp_short_url( $sui ) {
        
        if ( isset( $_SESSION['edc']['short_urls'][$sui] ) ) {
            return $_SESSION['edc']['short_urls'][$sui];
        } else {
            return null;
        }
    }
    
    /**
      * Check whether lesson belongs to demonstrative course
      * 
      * @param     int         $lesson_id      ID of the lesson
      * @return    bool            
      */
    function edc_is_demo_lesson( $lesson_id ) {
        
        // get category Id of demo course
        global $demo_category_id;
        
        // get category Id of the lesson
        $post_terms = wp_get_post_terms( $lesson_id, 'course' );
        
        // check whether lesson's category is demo or at least its parent demo category
        if ( ( $post_terms[0]->term_id == $demo_category_id ) || 
             ( $post_terms[0]->parent == $demo_category_id ) ) {

             return true;
         } else {
             return false;
         }
    }
    
    function edc_get_empty_value_table_cell() {
        return '&mdash;';
    }

    
    
    
    
    
    
    
    
    
    
    
    
    /**
      * Some time ago we used rich text editor for making checker comments, but now we
      * use plain text editor
      * 
      * @refactor: This code most likely is not necessary because we returned back 
      *            rich text editor
      */
    function edc_legacy_get_showing_checker_comments( $comment ) {
    
        $processed_comment = '';
    
        // is this comment belongs to old checker comments type?
        // if first letters is started with '<' then Yes (its a begin part of tag)
        if ( substr( $comment, 0, 4 ) == '&lt;' ) {
        
            $processed_comment = $comment;
            $processed_comment = html_entity_decode( $processed_comment );
        } 
        // is this comment belongs to new checker comments type?
        else {
        
            $processed_comment = $comment;
            $processed_comment = nl2br( $processed_comment );
        }
        
        return $processed_comment;
    }
    
    
    
    
    
    
    
    
    
    
    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    


    /**
      * Set cookie for authorized user. This functions add prefix to needed cookie and this cookie will
      * be delete after user LogOut
      * Parameters of the function same as for 'setcookie()'
      */
    function edc_set_auth_cookie( $name, $value, $expire = 0, $path = '/' ) {
        $cookies_auth_prefix = 'edc_auth_';
        $cookie_name = $cookies_auth_prefix . $name;
      
        // set cookie
        setcookie( $cookie_name, $value, $expire, $path );  
    }

    /**
      * Get cookie of authorized user
      *
      * @param     string            $name         Cookie name
      * @return    mixed|boolean                   Returns cookie value or FALSE in case such cookie is absent
      */
    function edc_get_auth_cookie( $name ) {
        $cookies_auth_prefix = 'edc_auth_';
        $cookie_name = $cookies_auth_prefix . $name;
      
        if ( ! isset( $_COOKIE[$cookie_name] ) ) {
            return FALSE;
        } else {
            return $_COOKIE[$cookie_name];
        }
    }

    /**
      * Deleting all of the cookies which are used in private area for authorized users
      */
    function edc_delete_auth_cookies() {
    
        $cookies_auth_prefix = 'edc_auth_';
      
        // get cookie names to delete
        $cookie_names_to_delete = array();
        foreach( $_COOKIE as $name => $value ) {
            if ( strpos( $name, $cookies_auth_prefix ) === 0 ) {
                $cookie_names_to_delete[] = $name;
            }
        }
      
        // deleting cookies
        foreach( $cookie_names_to_delete as $cookie_name ) {
            setcookie( $cookie_name, '', time()-1 );
        }
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
      * Send message to user
      *
      * @param     int               $user_id      User ID
      * @param     string            $msg          Message
      * @return    int|boolean                   
      */
    function edc_send_message_user( $user_id, $msg ) {
    
        // get Telegram bot object
        global $telegram_bot;
    
        // get extended user information
        $user_extended = edc_get_user_extended( $user_id );
        
        // !!! well in fact we should not do this however in case we have message:
        // &lt;a href=\&quot;google.com2\&quot;&gt;google.com2&lt;/a&gt;
        // it will trigger error
        // but if we will have message:
        // &lt;a href=&quot;google.com2&quot;&gt;google.com2&lt;/a&gt;
        // then everything will be Ok
        $msg = stripslashes_deep( $msg );
        
        // send message via Telegram
        $result = $telegram_bot->send_message( $user_extended['telegram_chat_id'], $msg );
        
        // did we get an error?
        if ( $result->ok != TRUE ) {
            // log info about this issue
            edc_log_error( $result );
            // return funtion's result
            return false;
        }
        
        // return funtion's result
        return true;
    }
    
    /**
      * Send message to team chat
      *
      * @param     string            $msg          Message
      * @return    int|boolean                   
      */
    function edc_send_message_team( $msg ) {
    
        // get Telegram bot object
        global $telegram_bot;
    
        // get chat ID from general settings page
        $chat_id = get_field( 'edc_telegram_team_chat_id', 395 );

        // !!! well in fact we should not do this however in case we have message:
        // &lt;a href=\&quot;google.com2\&quot;&gt;google.com2&lt;/a&gt;
        // it will trigger error
        // but if we will have message:
        // &lt;a href=&quot;google.com2&quot;&gt;google.com2&lt;/a&gt;
        // then everything will be Ok
        $msg = stripslashes_deep( $msg );
        
        // send message via Telegram
        $result = $telegram_bot->send_message( $chat_id, $msg );
        
        // did we get an error?
        if ( $result->ok != TRUE ) {
            // log info about this issue
            edc_log_error( $result );
            // return funtion's result
            return false;
        }
        
        // return funtion's result
        return true;
    }
    
    /**
      * Send message to course team chat
      *
      * @param     int               $course_id    Course ID
      * @param     string            $msg          Message
      * @return    int|boolean                   
      */
    function edc_send_message_course_team( $course_id, $msg ) {
    

        // get course category information
        $course_categ_info = get_term( $course_id );
        
        // is such course not exist?
        if ( empty( $course_categ_info ) ) return false;
    
        // get telegram group teachers chat ID
        $telegram_group_teachers_chat_id = get_field( 'edc_telegram_group_teachers_chat_id', $course_categ_info->taxonomy . '_' . $course_id  );
    
        // get Telegram bot object
        global $telegram_bot;
    
        // !!! well in fact we should not do this however in case we have message:
        // &lt;a href=\&quot;google.com2\&quot;&gt;google.com2&lt;/a&gt;
        // it will trigger error
        // but if we will have message:
        // &lt;a href=&quot;google.com2&quot;&gt;google.com2&lt;/a&gt;
        // then everything will be Ok
        $msg = stripslashes_deep( $msg );
        
        // send message via Telegram
        $result = $telegram_bot->send_message( $telegram_group_teachers_chat_id, $msg );
        
        // did we get an error?
        if ( $result->ok != TRUE ) {
            // log info about this issue
            edc_log_error( $result );
            // return funtion's result
            return false;
        }
        
        // return funtion's result
        return true;
    }
    
    /**
      * Send message to course group chat
      *
      * @param     string            $msg          Message
      * @param     int               $course_id    Course ID
      * @return    int|boolean                   
      */
    function edc_send_message_course( $msg, $course_id ) {
    
        // get course category information
        $course_categ_info = get_term( $course_id );
        
        // is such course not exist?
        if ( empty( $course_categ_info ) ) return false;
        
        // get teleggram group chat ID
        $telegram_group_chat_id = get_field( 'edc_telegram_group_chat_id', $course_categ_info->taxonomy . '_' . $course_id  );
    
        // get Telegram bot object
        global $telegram_bot;
    
        // !!! well in fact we should not do this however in case we have message:
        // &lt;a href=\&quot;google.com2\&quot;&gt;google.com2&lt;/a&gt;
        // it will trigger error
        // but if we will have message:
        // &lt;a href=&quot;google.com2&quot;&gt;google.com2&lt;/a&gt;
        // then everything will be Ok
        $msg = stripslashes_deep( $msg );
        
        // send message via Telegram
        $result = $telegram_bot->send_message( $telegram_group_chat_id, $msg );
        
        // did we get an error?
        if ( $result->ok != TRUE ) {
            // log info about this issue
            edc_log_error( $result );
            // return funtion's result
            return false;
        }
        
        // return funtion's result
        return true;
    }
    

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    /**
      * Save searchin request of the curent user
      */
    add_action('wp_ajax_edc_making_search_request', 'edc_ajax_making_search_request');
    function edc_ajax_making_search_request() {
    
        global $current_user;
        
        // get data from ajax request
        $post_data = $_POST['data'];
        $post_data = str_replace( "\\", "", $post_data );
        $data = json_decode( $post_data );

        // sanitize inputed user data
        $data = edc_sanitize_users_inputed_db_data( $data );

        // save request t odatabase
        edc_save_user_search_query( $current_user->ID, $data['search_query'], $data['search_type'] );

        $sendData = [
            'status' => 'Ok'
        ];

        // send data
        echo json_encode( $sendData );
        
        // exit
        wp_die();
    }
    
    /**
      * Same as function edc_analytics_save_user_last_visit_date() but
      * for Ajax
      */
    add_action('wp_ajax_edc_analytics_save_user_last_visit_date', 'edc_ajax_analytics_save_user_last_visit_date');
    function edc_ajax_analytics_save_user_last_visit_date() {
    
        // get data from ajax request
        $post_data = $_POST['data'];
        $post_data = str_replace( "\\", "", $post_data );
        $data = json_decode( $post_data );

        // date will be saved every "$timeout_sec" seconds
        $timeout_sec = 300;

        $user_last_visit_date = intval( $data->user_last_visit_date );
        $cur_time = time();
        
        if ( empty( $user_last_visit_date ) || 
             ( ( $cur_time - $user_last_visit_date ) >= $timeout_sec ) ) {

            global $wpdb;
            global $current_user;
            
            $data = [];
            $data['last_visit_date'] = date( edc_get_mysql_date_format(), $cur_time );

            // forming of Where clause
            $where = array( 'user_id' => $current_user->ID );
            // execute update operation in database
            $result = $wpdb->update( "{$wpdb->prefix}edc_users_extended", $data, $where );
            
            
            $sendData = [
                'isUpdated' => true,
                'time' => $cur_time
            ];
        } else {

            $sendData = [
                'isUpdated' => false
            ];
        }

        // send data
        echo json_encode( $sendData );
        
        // exit
        wp_die();
    }
    
    /**
      * Save date of the recent visit of current user
      *
      * @param  int    $timeout_sec    Date will be saved every "$timeout_sec" seconds
      */
    function edc_analytics_save_user_last_visit_date( $timeout_sec = 300 ) {

        $user_last_visit_date = intval( edc_get_auth_cookie( 'user_last_visit_date' ) );
        $cur_time = time();
        
        if ( empty( $user_last_visit_date ) || 
             ( ( $cur_time - $user_last_visit_date ) >= $timeout_sec ) ) {

            // save to cookies last visit date
            edc_set_auth_cookie( 'user_last_visit_date', $cur_time );
             
            global $wpdb;
            global $current_user;
            
            $data = [];
            $data['last_visit_date'] = date( edc_get_mysql_date_format(), $cur_time );

            // forming of Where clause
            $where = array( 'user_id' => $current_user->ID );
            // execute update operation in database
            $result = $wpdb->update( "{$wpdb->prefix}edc_users_extended", $data, $where );
        }
    }
    
    /**
      * Save date of when user joined to webinar
      *
      * @param  int    $lesson_id    Lesson ID
      */
    function edc_analytics_save_user_joined_webinar_date( $lesson_id ) {

        global $wpdb;
        global $current_user;
            
        // get current lesson progress
        $lesson_progress = edc_get_user_lesson_progress( $lesson_id );
        
        $data = [];
        $data['webinar_joined_times'] = $lesson_progress['webinar_joined_times'] + 1;
        
        // write only the first date when user joined to webinar
        if ( empty( $lesson_progress['date_webinar_joined'] ) ) {
            $data['date_webinar_joined'] = date( edc_get_mysql_date_format(), time() );
        }

        // forming of Where clause
        $where = array( 
            'user_id' => $current_user->ID,
            'lesson_id' => $lesson_id );
        // execute update operation in database
        $result = $wpdb->update( "{$wpdb->prefix}edc_users_lesson_progress", $data, $where );
        
    }
    
    /**
      * Generate special URL for analytics system
      *
      * @param     string       $url_go_to             URL go to
      * @param     array        $params                Associative array with URL parameters to add
      * @return    string                              Temporary short URL
      */
    function edc_generate_url_analytics( $url_go_to, $params ) {

        // generate URL for Analytics system
        $url = get_bloginfo( 'url' );                    
        $url = edc_add_param_to_url( $url, 'goto=' . bin2hex( $url_go_to ) );
        
        // add parameters to URL
        foreach ( $params as $param => $value ) {
            $url = edc_add_param_to_url( $url, $param . '=' . $value );
        }

        // get temp short url
        $url_short = edc_do_temp_short_url( $url );

        return $url_short;
    }
    
    /**
      * Save date of the recent visit of current user
      *
      * @param   int       $wp_object_id     ID of page/post etc.
      * @param   int       $event_id         Event ID: click/visit/element click etc.
      * @param   string    $query_string     Query string, tail of the URL, params and its attributes
      * @param   string    $details          Details of the event
      */
    function edc_analytics_save_user_action( $wp_object_id, $event_id, $details = '', $query_string = '' ) {

        global $wpdb;
        global $current_user;

        // get current date
        $date = date( edc_get_mysql_date_format(), time() );

        // set necessary fields to save to database
        $data = [];
        $data['wp_object_id'] = $wp_object_id;
        $data['user_id']      = $current_user->ID;
        $data['event_id']     = $event_id;
        $data['query_string'] = $query_string;
        $data['details']      = $details;
        $data['date']         = $date;

        // execute insert operation
        $result = $wpdb->insert( "{$wpdb->prefix}edc_analytics_objects", $data );
    }
    
    /**
      * Get visiting pages history of some user
      *
      * @param  array     $query_params      Such params as: 
      *                                            'user_id'   - array
      * @param   array                       Associative array with data
      */
    function edc_analytics_get_visiting_pages_history( $query_params = [] ) {

        global $wpdb;
        global $default_db_results_limit;
        
        // reset
        $where_arr = [];        
        $limit = '';    
        
        // set default values
        $where_arr[] = "objects.event_id";
        $limit = 'LIMIT ' . $default_db_results_limit;
            
        // shoud we show pages history related to certain user(s)?
        if ( isset( $query_params['user_id'] ) && !empty( $query_params['user_id'] ) ) {

            $user_ids_str = implode( "','", $query_params['user_id'] );
            $user_ids_str = "'" . $user_ids_str . "'";

            $where_arr[] = "(objects.user_id IN ({$user_ids_str}))";
        }
        
        // get Where clause string
        if ( !empty( $where_arr ) ) {
            $where = 'WHERE ' . implode( ' AND ', $where_arr );
        }

        $query = $wpdb->prepare( "
            SELECT 
                objects.*,
                posts.post_title,
                posts.post_type,
                users.display_name,
                objects.query_string
            FROM `{$wpdb->prefix}edc_analytics_objects` AS objects
            INNER JOIN {$wpdb->prefix}posts AS posts ON
                posts.id = objects.wp_object_id  
            INNER JOIN {$wpdb->prefix}users AS users ON
                objects.user_id = users.id  
            {$where}
            ORDER BY objects.date DESC
            {$limit}
            ", array() );
        $items = $wpdb->get_results( $query, ARRAY_A );
        
        // unescape all values
        $items = stripslashes_deep( $items );
        
        // return result
        return $items;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    /**
      * Get popup content of modal warning demonstration window
      *
      * @param   string
      */
    function edc_get_modal_popup_demo_warning() {

        $content = '<div class="modal fade" id="demo_modal_warning_popup" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered" role="document"><div class="modal-content"><div class="modal-body">Чтобы воспользоваться этой функцией оплати пожалуйста весь курс</div></div></div></div> ';

        return $content;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /**
      * Generate HTML code for accordion
      *
      * @param   array      $items    Associative array: 
      *                               [ 
      *                                 [ 
      *                                   ['title'] => 'Title 1', 
      *                                   ['content'] => 'Content 1', 
      *                                   ['is_disabled_opening'] => true, 
      *                                   ['is_expanded'] => true, 
      *                                 ], 
      *                                 [ ... ] 
      *                               ]
      * @param   string
      */
    function edc_html_generate_accordion( $items ) {

        $html = '';
        if ( count( $items ) ) {
        
            // get unique accordion Id attribute
            $accordion_id = 'accordion' . wp_hash( rand(0, 9999) );
            
            // insert start tag for accordion
            $html .= '<div class="accordion" id="' . $accordion_id . '">';
        
            foreach ( $items as $key => $item ) {
            
                $heading_id  = $accordion_id . '_heading_' . $key;
                $collapse_id = $accordion_id . '_collapse_' . $key;
            
                if ( isset( $item['is_disabled_opening'] ) && $item['is_disabled_opening'] ) {
                
                    $html .= '<div class="card"><div class="card-header" id=""><h2 class="mb-0"><button class="btn btn-link" type="button" data-toggle="collapse" data-target="#" aria-expanded="true" aria-controls="">' . $item['title'] . '</button></h2></div><div id="" class="collapse" aria-labelledby="" data-parent="#' . $accordion_id . '"><div class="card-body">' . $item['content'] . '</div></div></div>';
                    
                } else {
                
                    $expanded_class = ( $item['is_expanded'] ) ? 'show' : '';

                    $html .= '<div class="card"><div class="card-header" id="' . $heading_id . '"><h2 class="mb-0"><button class="btn btn-link" type="button" data-toggle="collapse" data-target="#' . $collapse_id . '" aria-expanded="true" aria-controls="' . $collapse_id . '">' . $item['title'] . '</button></h2></div><div id="' . $collapse_id . '" class="collapse ' . $expanded_class . '" aria-labelledby="' . $heading_id . '" data-parent="#' . $accordion_id . '"><div class="card-body">' . $item['content'] . '</div></div></div>';
                }
            
            }
        
            // insert end tag for accordion
            $html .= '</div>';
        }

        return $html;
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    

    /**
      * Get notifications to read quantity
      *
      * @param   int       $user_id          User's ID
      * @return  int       $quantity          
      */
    function edc_get_user_notifications_to_read_quantity( $user_id = 0 ) {

        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $query = $wpdb->prepare( "
            SELECT COUNT(*)
            FROM `{$wpdb->prefix}edc_notifications_to_users` AS notifications_to_users
            WHERE notifications_to_users.user_id = '{$user_id}'
                AND notifications_to_users.read_date IS NULL
            ", array() );
        $quantity = $wpdb->get_var( $query );
    
        // return the result
        return $quantity;
    }
    
    /**
      * Get user's notifications
      *
      * @param   int       $user_id          User's ID
      * @return  array     $notifications    Associative array with data
      */
    function edc_get_user_notifications( $user_id = 0, $notification_id = 0 ) {

        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $query = $wpdb->prepare( "
            SELECT 
                notifications.*,
                notifications_to_users.user_id,
                notifications_to_users.read_date,
                notifications_to_users.priority,
                users_senders.display_name AS sender_display_name,
                users.display_name AS user_display_name
            FROM `{$wpdb->prefix}edc_notifications` AS notifications
            INNER JOIN {$wpdb->prefix}edc_notifications_to_users AS notifications_to_users ON
                notifications.id = notifications_to_users.notification_id  
            INNER JOIN {$wpdb->prefix}users AS users_senders ON
                notifications.sender_id = users_senders.id  
            INNER JOIN {$wpdb->prefix}users AS users ON
                notifications_to_users.user_id = users.id  
            WHERE notifications_to_users.user_id = '{$user_id}'
            ORDER BY notifications.sent_date DESC
            ", array() );
        $notifications = $wpdb->get_results( $query, ARRAY_A );

        // unescape all values
        $notifications = stripslashes_deep( $notifications );
    
        // return the result
        return $notifications;
    }
    
    /**
      * Get user's notification
      *
      * @param   int       $notification_id  Notification ID
      * @param   int       $user_id          User's ID
      * @return  array     $notification          
      */
    function edc_get_user_notification( $notification_id, $user_id = 0 ) {

        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }
        
        $query = $wpdb->prepare( "
            SELECT 
                notifications.*,
                notifications_to_users.user_id,
                notifications_to_users.read_date,
                notifications_to_users.priority,
                users_senders.display_name AS sender_display_name,
                users.display_name AS user_display_name
            FROM `{$wpdb->prefix}edc_notifications` AS notifications
            INNER JOIN {$wpdb->prefix}edc_notifications_to_users AS notifications_to_users ON
                notifications.id = notifications_to_users.notification_id  
            INNER JOIN {$wpdb->prefix}users AS users_senders ON
                notifications.sender_id = users_senders.id  
            INNER JOIN {$wpdb->prefix}users AS users ON
                notifications_to_users.user_id = users.id  
            WHERE notifications_to_users.user_id = '{$user_id}'
                AND notifications.id = '{$notification_id}'
            ", array() );
        $notification = $wpdb->get_row( $query );
    
        // return the result
        return $notification;
    }
    

    /**
      * Mark that the lesson for certain user is accepted
      *
      * @global    wpdb       $wpdb
      *
      * @param   int       $notification_id  Notification ID
      * @param   int       $user_id          User's ID
      * @return  bool              
      */
    function edc_mark_user_notification_as_read( $notification_id, $user_id = 0 ) {

        global $wpdb;
        global $current_user;
        
        // do we have setted user id?
        if ( empty( $user_id ) ) {
            // set user ID as current user ID
            $user_id = $current_user->ID;
        }

        $data = [];
        
        // get current date
        $cur_date = date( edc_get_mysql_date_format(), time() );
        
        // set necessary fields
        $data['read_date'] = $cur_date;

        // forming of Where clause
        $where = [ 
            'notification_id' => $notification_id,
            'user_id' => $user_id
          ];
        // execute update operation
        $result = $wpdb->update( "{$wpdb->prefix}edc_notifications_to_users", $data, $where );
        
        // return result
        $result = ( $result ) ? true : false;
        return $result; 
    }
    
    /**
      * Add notifications to user(s)
      *
      * @global    wpdb       $wpdb
      *
      * @param   array     $user_list_id     Array with recipients IDs
      * @param   int       $sender_id        Sender ID
      * @param   string    $subject          Subject of the message
      * @param   string    $message          Message to send
      * @param   int       $priority         Priority: 0 - general, 1 - warning
      * @return  bool              
      */
    function edc_add_user_notification( $user_list_id, $sender_id, $subject, $message, $priority = 0 ) {
    
        global $wpdb;
        
        $data = [];
        // set necessary fields
        $data['sender_id'] = $sender_id;
        $data['subject'] = $subject;
        $data['message'] = $message;
        $data['sent_date'] = date( edc_get_mysql_date_format(), time() );
        // execute insert operation
        $result = $wpdb->insert( "{$wpdb->prefix}edc_notifications", $data ); 
        $notification_id = $wpdb->insert_id; 
        
        foreach ( $user_list_id as $user_id ) { 

            $data = [];
            // set necessary fields
            $data['priority'] = $priority;
            $data['user_id'] = $user_id;
            $data['notification_id'] = $notification_id;
            // execute insert operation
            $result = $wpdb->insert( "{$wpdb->prefix}edc_notifications_to_users", $data );  
        }
    }
    
    /**
      * Add massive notifications to users
      *
      * @global    wpdb       $wpdb
      *
      * @param   array     $users_goups      Associative array:
      *                                        [
      *                                          'course_ids' => [ 1, 5, 7, ... ],
      *                                          ...
      *                                        ]
      *                                      If not set then it will sent to all users
      * @param   int       $sender_id        Sender ID
      * @param   string    $subject          Subject of the message
      * @param   string    $message          Message to send
      * @param   int       $priority         Priority: 0 - general, 1 - warning
      * @return  bool              
      */
    function edc_add_mass_user_notification( $users_goups, $sender_id, $subject, $message, $priority = 0 ) {
    
        global $wpdb;
        
        // get users
        $args = array(
            'orderby'      => 'login',
            'order'        => 'ASC',
         ); 
        $users = get_users( $args );
        
        // get extended data about student
        foreach ( $users as $user ) {
        
          $courses = edc_user_courses( true, $user->ID );
          $courses = array_column( $courses, 'course_id' );
        
          $user->courses = $courses;
        }
        
        // should we filter only students from certain course?
        if ( count( $users_goups['course_ids'] ) ) {
            
            $recipients = [];
            foreach ( $users as $key => $user ) {

                if ( count( array_intersect( $users_goups['course_ids'], (array) $user->courses ) ) ) {
                    $recipients[] = $user;

                }
            }
        } 
        // send to all users in case not set recipients group
        else if ( empty( $users_goups ) ) {
            $recipients = $users;
        }
        
        // get recipients Ids
        $recipients_ids = array_column( $recipients, 'id' );
        
        // add notifications to users
        edc_add_user_notification( $recipients_ids, $sender_id, $subject, $message, $priority );
        
        // return result
        return true;
    }
    
    
    
    
    
    
    

    
    
    
    
    
    
    
    
    
    
    

    
    
    
    
    
    
    
    
    
    
    

/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!MAKE FOR THIS FUNCTIONS SEPARATE CLASS - Edc_Messages: START*/

    /**
      * Add message Id to redirection url
      * 
      * @param  string    $url                 Redirection URL
      * @param  string    $msg_text            Message text
      * @param  string    $msg_type            Message type: 'notice', 'error' or 'warning'
      * @return string    $processed_url       Associative array with data
      */
    function edc_add_status_message_id_to_redirect_url( $url, $msg_text, $msg_type ) {
      
        $processed_url = $url;
        $unique_id = edc_generate_unique_id( 5 );
      
        // prepare message text to be displayed correctly on a page
        $msg_text = stripslashes( $msg_text );
        $msg_text = htmlspecialchars( $msg_text );
      
        // add message data to a status messages stack
        $_SESSION['edc_status_messages'][$unique_id][] = array(
            'msg_text' => $msg_text,
            'msg_type' => $msg_type,
        );

        $url_param_conc_symb = edc_get_parameter_concatenation_symbol( $processed_url );
        $processed_url .= $url_param_conc_symb . 'msg_id=' . $unique_id;
        
        return $processed_url;
    }

    /**
      * Retrives status message data. NOTICE: after the first message reading it will be removed,
      * because we should show such message only once, but not every time especially when user refreshes 
      * page. For now function retrives the first message from a common status messages stack related to some 
      * message ID
      * 
      * @return   array     Associative array with data which contains message type and message text
      */
    function edc_get_status_message() {
        
        $message_id = ( isset( $_REQUEST['msg_id'] ) ) ? sanitize_key( $_REQUEST['msg_id'] ) : NULL;

        if ( ! is_null( $message_id ) ) {
            // retrive the first message
            $message_data = $_SESSION['edc_status_messages'][$message_id][0];
            // after first reading delete status message
            unset( $_SESSION['edc_status_messages'][$message_id] );
              
            return $message_data;
        } else {
            return array();
        }
    }
    
    /**
      * Add message Id to redirection url
      * 
      * @param  string    $msg_text            Message text
      * @param  string    $msg_type            Message type: 'notice', 'error' or 'warning'
      * @return bool  
      */
    function edc_add_permanent_notification( $msg_text, $msg_type ) {
      
        $msg_hash = wp_hash( $msg_text );
      
        // prepare message text to be displayed correctly on a page
        $msg_text = stripslashes( $msg_text );
        $msg_text = htmlspecialchars( $msg_text );
      
        // add message data to a status messages stack
        $_SESSION['edc_notification']['permanent'][$msg_hash] = [
            'text' => $msg_text,
            'type' => $msg_type,
        ];

        return true;
    }
    
    /**
      * Retrives status message data
      * 
      * @return   array     Associative array with data which contains message type and message text
      */
    function edc_get_permanent_notifications() {

        // do we have any notifications?
        if ( count( $_SESSION['edc_notification']['permanent'] ) ) {
        
            $message_data = [];
            foreach ( $_SESSION['edc_notification']['permanent'] as $key => $notification ) {
                $message_data[] = $notification;
            }

            return $message_data;
        } else {
            return [];
        }
    }
    
    /**
      * Removes permanent notification
      * 
      * @return bool  
      */
    function edc_remove_permanent_notification() {
      
        // add message data to a status messages stack
        $_SESSION['edc_notification']['permanent'] = [];

        return true;
    }

/*MAKE FOR THIS FUNCTIONS SEPARATE CLASS - Edc_Messages: END*/

    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
   
   
   
/*!!!!!!!!!!!!!!!!!!!!!!!!!!!Adding columns in admin panel in preview mode in tables with records*/
    
// adding columns for 'lessons'
add_filter('manage_lesson_posts_columns', 'edc_lesson_add_column');
add_action('manage_lesson_posts_custom_column', 'edc_lesson_add_value_to_column', 10, 2);
// add new column
function edc_lesson_add_column( $defaults ) {

	$column_name = 'order';         //column slug
	$column_heading = 'Порядок';    //column heading
	$defaults[$column_name] = $column_heading;
    
	return $defaults;
}
// show the column
function edc_lesson_add_value_to_column( $name, $post_id ) {

    $column_name = 'order';                    //column slug	
    $column_field = 'edc_lesson_order_num';    //field slug	
    
    if ( $name == $column_name ) {
        $post_meta = get_post_meta( $post_id, $column_field, true );
        if ( $post_meta ) echo $post_meta;
    }
}

// adding columns for 'personal_development'
add_filter('manage_personal_development_posts_columns', 'edc_personal_development_add_column');
add_action('manage_personal_development_posts_custom_column', 'edc_personal_development_add_value_to_column', 10, 2);
// add new column
function edc_personal_development_add_column( $defaults ) {

	$column_name = 'order';         //column slug
	$column_heading = 'Порядок';    //column heading
	$defaults[$column_name] = $column_heading;
    
	return $defaults;
}
// show the column
function edc_personal_development_add_value_to_column( $name, $post_id ) {

    $column_name = 'order';                    //column slug	
    $column_field = 'edc_personal_development_order_num';    //field slug	
    
    if ( $name == $column_name ) {
        $post_meta = get_post_meta( $post_id, $column_field, true );
        if ( $post_meta ) echo $post_meta;
    }
}
    
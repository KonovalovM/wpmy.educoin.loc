<?php

// function for debugging and die
function dd( $variable ) {
    echo '<pre>';
    print_r( $variable );
    echo '</pre>';
    die();
}

// function for debugging
function d( $variable ) {
    echo '<pre>';
    print_r( $variable );
    echo '</pre>';
}

/** 
 * Function logs to folder 'logs' in theme directory logging data
 *
 * @param    string      $text          Message
 * @param    string      $filename      Name of file
 */
function edc_log( $text, $filename = 'main-logfile.log' ) {
    // open log file
    $filename = get_template_directory() . '/logs/' . $filename ;
    $fh = fopen( $filename, "a" );
    fwrite( $fh, date( "d-m-Y, H:i:s" ) . "(TZ: " . date_i18n('d-m-Y, H:i:s') . ") - $text\r\n----------------------------------------------\r\n" );
    fclose( $fh );
}


/** 
 * Function logs to folder 'logs' in theme directory logging data to errors file
 */
function edc_log_error( $text, $is_debug_trace = true ) {
  
    $final_text_data = [];
    $final_text_data['text'] = $text;
    
    if ( $is_debug_trace ) {
        $final_text_data['debug_backtrace'] = debug_backtrace();   
    }
    
    $final_text = json_encode( $final_text_data );
  
    edc_log( $final_text, 'errors.log' );
}

/**
  * It is same function as WordPress function '__()', however it is shorter
  */
function edc__( $str ) {
    return __( $str, TEXTDOMAIN );
}

/**
  * Get post content by id outside the Loop
  *
  */
function edc_get_post_content( $post_id ) {

  $post_object = get_post( $post_id );

  if ( ! $post_object ) { return ''; }

  return apply_filters( 'the_content', $post_object->post_content );
}

/**
  * Translitiration of a text
  *
  * @param     string      $string      String to translitirate
  * @return    string      
  */ 
function edc_transliterate( $string ) {
    $replace = [
		"'"=>"",
		"`"=>"",
		"а"=>"a","А"=>"A",
		"б"=>"b","Б"=>"B",
		"в"=>"v","В"=>"V",
		"г"=>"g","Г"=>"G",
		"д"=>"d","Д"=>"D",
		"е"=>"e","Е"=>"E",
		"ё"=>"yo","Ё"=>"Yo",
		"ж"=>"zh","Ж"=>"Zh",
		"з"=>"z","З"=>"Z",
		"и"=>"i","И"=>"I",
		"й"=>"y","Й"=>"Y",
		"к"=>"k","К"=>"K",
		"л"=>"l","Л"=>"L",
		"м"=>"m","М"=>"M",
		"н"=>"n","Н"=>"N",
		"о"=>"o","О"=>"O",
		"п"=>"p","П"=>"P",
		"р"=>"r","Р"=>"R",
		"с"=>"s","С"=>"S",
		"т"=>"t","Т"=>"T",
		"у"=>"u","У"=>"U",
		"ф"=>"f","Ф"=>"F",
		"х"=>"h","Х"=>"H",
		"ц"=>"c","Ц"=>"C",
		"ч"=>"ch","Ч"=>"Ch",
		"ш"=>"sh","Ш"=>"Sh",
		"щ"=>"sch","Щ"=>"Sch",
		"ъ"=>"","Ъ"=>"",
		"ы"=>"y","Ы"=>"Y",
		"ь"=>"","Ь"=>"",
		"э"=>"e","Э"=>"E",
		"ю"=>"yu","Ю"=>"Yu",
		"я"=>"ya","Я"=>"Ya",
		"і"=>"i","І"=>"I",
		"ї"=>"yi","Ї"=>"Yi",
		"є"=>"e","Є"=>"E"
	];
    
	return $str=iconv( "UTF-8", "UTF-8//IGNORE", strtr( $string, $replace ) );
}

/**
  * Get current course from cookies
  * 
  * @return    string      Breadcrumbs
  */ 
function edc_get_breadcrumbs() {

    $archive_post_type_translation_map = [];
    $archive_post_type_translation_map['lesson'] = 'Уроки';
    $archive_post_type_translation_map['book'] = 'Книги';
    $archive_post_type_translation_map['personal_development'] = 'Личностный рост';
    $archive_post_type_translation_map['additional_article'] = 'Вспомогательные статьи';

    $breadcrumbs = '';
    $breadcrumbs .= '<ol class="breadcrumb mt-3">';
    
    $breadcrumb_item = '<li class="breadcrumb-item"><a href="%s">%s</a></li>';
    $breadcrumb_item_active = '<li class="breadcrumb-item active">%s</li>';
    
    // page with course
    if ( is_tax( 'course' ) ) {
    
        $taxonomy_term = get_term( get_queried_object_id() );
        
        // fill the breadcrumbs
        $breadcrumbs .= sprintf( $breadcrumb_item, get_permalink( 699 ), get_the_title( 699 ) );
        $breadcrumbs .= sprintf( $breadcrumb_item_active, $taxonomy_term->name );        
    } 
    // page with list of lessons/books/personal development items
    else if ( is_post_type_archive( ['lesson', 'book', 'personal_development', 'additional_article'] ) ) {

        $cur_post_type = get_post_type();
        $taxonomy_term = get_term( edc_get_current_course_id() );
        
        // fill the breadcrumbs
        $breadcrumbs .= sprintf( $breadcrumb_item, get_permalink( 699 ), get_the_title( 699 ) );
        $breadcrumbs .= sprintf( $breadcrumb_item, get_term_link( $taxonomy_term->term_id ), $taxonomy_term->name );
        $breadcrumbs .= sprintf( $breadcrumb_item_active, $archive_post_type_translation_map[$cur_post_type] );
    
    } 
    // page with some certain lesson/book/personal development item
    else if ( is_singular( ['lesson', 'book', 'personal_development', 'additional_article'] ) ) {

        $cur_post_type = get_post_type();
        $taxonomy_term = get_term( edc_get_current_course_id() );
        $post = get_post( get_queried_object_id() );
        
        // fill the breadcrumbs
        $breadcrumbs .= sprintf( $breadcrumb_item, get_permalink( 699 ), get_the_title( 699 ) );
        $breadcrumbs .= sprintf( $breadcrumb_item, get_term_link( $taxonomy_term->term_id ), $taxonomy_term->name );
        $breadcrumbs .= sprintf( $breadcrumb_item, get_post_type_archive_link( $cur_post_type ), $archive_post_type_translation_map[$cur_post_type] );
        $breadcrumbs .= sprintf( $breadcrumb_item_active, $post->post_title );
        
    } 
    // page with list of courses
    else if ( edc_is_course_lists_page() ) {

        $breadcrumbs .= sprintf( $breadcrumb_item_active, get_the_title( 699 ) );
    }
    // page with list of students
    else if ( is_page_template( 'templates/adms_students.php' ) ) {

        $breadcrumbs .= sprintf( $breadcrumb_item, '#', get_the_title( 46) );
        $breadcrumbs .= sprintf( $breadcrumb_item_active, get_the_title( 52 ) );
    }
    // page with profile of certain student
    else if ( is_page_template( 'templates/adms_students_profile.php' ) ) {

        // get user data
        $user = get_userdata( intval( $_GET['id'] ) );
        $user_login = ( !empty( $user ) ) ? $user->user_login : __( 'None', TEXTDOMAIN );
        
        // fill the breadcrumbs
        $breadcrumbs .= sprintf( $breadcrumb_item, '#', get_the_title( 46) );
        $breadcrumbs .= sprintf( $breadcrumb_item, get_permalink( 52 ), get_the_title( 52 ) );
        $breadcrumbs .= sprintf( $breadcrumb_item_active, $user_login );
    }
    // page with list of supports
    else if ( is_page_template( 'templates/adms_supports.php' ) ) {

        $breadcrumbs .= sprintf( $breadcrumb_item, '#', get_the_title( 46) );
        $breadcrumbs .= sprintf( $breadcrumb_item_active, get_the_title( 771 ) );
    }
    // page with homework checking
    else if ( is_page_template( 'templates/adms_homework-check.php' ) ) {

        $breadcrumbs .= sprintf( $breadcrumb_item, '#', get_the_title( 46) );
        $breadcrumbs .= sprintf( $breadcrumb_item_active, get_the_title( 48 ) );
    }
    // page with events in custom admin section
    else if ( is_page_template( 'templates/adms_events.php' ) ) {

        $breadcrumbs .= sprintf( $breadcrumb_item, '#', get_the_title( 46) );
        $breadcrumbs .= sprintf( $breadcrumb_item_active, get_the_title( 700 ) );
    }
    // is page from Useful section?
    else if ( edc_is_section_useful() ) {
    
        $post = get_post( get_queried_object_id() );
        
        $breadcrumbs .= sprintf( $breadcrumb_item, '#', get_the_title( 59 ) );
        
        if ( $post->post_parent === 76 ) {
            $breadcrumbs .= sprintf( $breadcrumb_item, get_permalink( 76 ), get_the_title( 76 ) );
        }
        else if ( $post->post_parent === 377 ) {
            $breadcrumbs .= sprintf( $breadcrumb_item, get_permalink( 377 ), get_the_title( 377 ) );
        }
        else if ( $post->post_parent === 378 ) {
            $breadcrumbs .= sprintf( $breadcrumb_item, get_permalink( 378 ), get_the_title( 378 ) );
        }
        else if ( $post->post_parent === 384 ) {
            $breadcrumbs .= sprintf( $breadcrumb_item, get_permalink( 384 ), get_the_title( 384 ) );
        }
        
        $breadcrumbs .= sprintf( $breadcrumb_item_active, $post->post_title );
    }
    // is page from Other section?
    else if ( edc_is_section_other() ) {
    
        $post = get_post( get_queried_object_id() );
        
        $breadcrumbs .= sprintf( $breadcrumb_item, '#', get_the_title( 592 ) );
        $breadcrumbs .= sprintf( $breadcrumb_item_active, $post->post_title );
    }
    // is page from Contacts section?
    else if ( edc_is_section_contacts() ) {

        $post = get_post( get_queried_object_id() );

        $breadcrumbs .= sprintf( $breadcrumb_item_active, $post->post_title );
    }
    // searching page
    else if ( edc_get_current_page_name() == 'edc-search' ) {
    
        // get search string
        $search_str = ( isset( $_GET['s'] ) ) ? sanitize_text_field( $_GET['s'] ) : '';

        $breadcrumbs .= sprintf( $breadcrumb_item_active, edc__( 'Поиск' ) );
        $breadcrumbs .= sprintf( $breadcrumb_item_active, $search_str );
    }
    // other cases
    else  {
    
        // get get the title of the page
        $title = get_the_title( get_queried_object_id() );

        $breadcrumbs .= sprintf( $breadcrumb_item_active, $title );
    }
    
    $breadcrumbs .= '</ol>';

    // return result
    return $breadcrumbs;
}

/**
  * Get date in client timezone
  *
  * @param     int         $date                     Date in timestamp
  * @param     string      $format                   Date format to return in
  * @return    string      $date_in_client_tz        Formatted date
  */ 
function edc_date_show_in_client_tz( $date, $format = null ) {

    $format = ( !is_null( $format ) ) ? $format : edc_get_date_format();

    // get timezones
    $server_timezone = date_default_timezone_get();
    $client_timezone = edc_get_client_timezone();
    
    // set timezone to client's timezone
    date_default_timezone_set( $client_timezone );

    // get date in client timezone
    $date_in_client_tz = date( $format, $date );
    
    // return back server timezone
    date_default_timezone_set( $server_timezone );

    return $date_in_client_tz;
}























/**
  * Get client's timezone. This function should work in pair with JavaScript code:
  *
  * --------------------------------
  *  // define client's timezone
  *  var timezone_offset_minutes = new Date().getTimezoneOffset();
  *  timezone_offset_minutes = timezone_offset_minutes == 0 ? 0 : -timezone_offset_minutes;
  *  // save timezone to cookies
  *  Cookies.set( 'edc_timezone_offset_minutes', timezone_offset_minutes );
  * --------------------------------
  *
  * @return    string      Timezone name or NULL in case we do not have timezone offset
  *                        in cookies
  */ 
function edc_get_client_timezone() {

    // check whether we already have timezone in session
    if ( isset( $_SESSION['edc']['client_timezone'] ) ) {
        
        return $_SESSION['edc']['client_timezone'];
    }
    // check whether we have timezone offset minutes in cookies
    else if ( isset( $_COOKIE['edc_tz_timezone_offset_minutes'] ) ) {

        $timezone_offset_minutes = intval( $_COOKIE['edc_tz_timezone_offset_minutes'] );

        // correcting timezone offset
        // it could be the situation when clock of user is on 1-5 minutes goes faster or
        // or slower than world clock
        $tz_step_minutes = 15;
        $rounded = round( $timezone_offset_minutes / $tz_step_minutes );
        $timezone_offset_minutes_corrected = $tz_step_minutes * $rounded;
        
        // Convert minutes to seconds
        $timezone_name = timezone_name_from_abbr( "", $timezone_offset_minutes_corrected * 60, false );

        // save timezone to the session
        $_SESSION['edc']['client_timezone'] = $timezone_name;

        return $timezone_name;
    } 
    // we do not have timezone offset in cookies
    else {
    
        return null;
    }
}

// It is a part of client timezone defining routine:
// set server date to cookie
function edc_tz_set_server_date_to_cookie() {

    // It is a part of getting to know client timezone routine
    $cur_date = date( 'd M Y H:i:00', time() );
    // encode symbols in string
    $cur_date = str_replace( ' ', '__s__', $cur_date );
    $cur_date = str_replace( ':', '__dts__', $cur_date );
    // send server date to client to cookie
    setcookie( 'edc_tz_server_date', $cur_date, 0, '/' );
}






























//-----------------------------------------------------------------------------------

/**
  * Get current course from cookies
  * 
  * @return    int      Current course ID
  */ 
function edc_get_current_course_id() {

    // is current page singular post?
    if ( is_singular() ) {

        // get course ID
        $id = wp_get_object_terms( get_queried_object_id(), 'course' )[0]->term_id;

        // save course ID to cookies
        // we cannot save to cookies in this place unfortunately, cookies not set here
        setcookie( 'edc_current_course_id', $id, 0, '/' );

        // save course ID to session
        $_SESSION['edc']['edc_current_course_id'] = $id;
    }
    // do we have in cookies value of current course ID?
    else if ( isset( $_COOKIE['edc_current_course_id'] ) ) {
    
        $id = intval( $_COOKIE['edc_current_course_id'] );
    } 
    // do we have in session value of current course ID?
    else if ( isset( $_SESSION['edc']['edc_current_course_id'] ) ) {
    
        $id = intval( $_SESSION['edc']['edc_current_course_id'] );
    } 
    // we cannot get course id
    else {

        // get course lists link
        $taxonomy = get_taxonomy( 'course' );
        $course_lists_url = get_bloginfo( 'url' ) . '/' . $taxonomy->rewrite['slug'];

        $msg = __( 'Выберите вначале курс', TEXTDOMAIN );

        // getting redirection url
        $redirection_url = $course_lists_url;
        $redirection_url = edc_add_status_message_id_to_redirect_url( $redirection_url, $msg, 'notice' );

        // apply redirect
        wp_redirect( $redirection_url );
        exit();
        
    }

    return $id;
}

/**
  * Check whether current page is a course lists page
  * 
  * @return    boolean
  */ 
function edc_is_course_lists_page() {

    if ( is_page_template( 'templates/courses.php' ) ) {
         
         return true;
    } else {
    
         return false;
    }
}

/**
  * Get user's IP-address
  */
function edc_get_user_ip() {
    if ( isset( $_SERVER ) ) {
        if ( isset( $_SERVER["HTTP_X_FORWARDED_FOR"] ) ) return $_SERVER["HTTP_X_FORWARDED_FOR"];
        if ( isset( $_SERVER["HTTP_CLIENT_IP"] ) ) return $_SERVER["HTTP_CLIENT_IP"];
        return $_SERVER["REMOTE_ADDR"];
    }
    if ( getenv('HTTP_X_FORWARDED_FOR') ) return getenv( 'HTTP_X_FORWARDED_FOR' );
    if ( getenv('HTTP_CLIENT_IP') ) return getenv( 'HTTP_CLIENT_IP' );
    return getenv( 'REMOTE_ADDR' );
}

/**
  * Get file version tail. Used for JavaScript and CSS files to eliminate issue with
  * caching them by browser.
  * 
  * @return    string      
  */ 
function edc_get_file_version_tail( $relative_file_path ) {

    return date( 'dmYHis', filemtime( get_stylesheet_directory() . '/' . $relative_file_path ) );  
}

/**
  * Function for sorting items. It is used 
  * like "usort( $lessons, 'edc_order_title_arr_sort' );"    
  */ 
function edc_order_title_arr_sort( $a, $b ) {
    if ( $a->order == $b->order ){
        return strcmp( $a->title, $b->title );
    } else {
        return $a->order - $b->order;
    }
}

function edc_get_mysql_date_format() {
    return 'Y-m-d H:i:s'; 
}

function edc_get_date_format() {
    return 'd.m.y H:i:s'; 
}

function edc_get_short_date_format() {
    return 'd.m.y'; 
}

/** 
   * Get current page name
   *
   * @return   string                      
   */
function edc_get_current_page_name() {

    // search page
    if ( is_search() ) {
        return 'edc-search'; 
    } 
    // front page
    else if ( is_home() ) {
        return 'edc-home';
    } 
    // course lesson
    else if ( is_singular( 'lesson' ) ) {
        return 'edc-course-lesson';
    } 
    // homework checking page
    else if ( is_page_template( 'templates/adms_homework-check.php' ) ) {
        return 'edc-homework-checking';
    } 
    // lesson lists page
    else if ( is_post_type_archive( 'lesson' ) ) {
        return 'edc-lesson-lists';
    } 
    // courses root
    else if ( edc_is_course_lists_page() ) {
        return 'edc-courses';
    } 
    // events in admin section
    else if ( is_page_template( 'templates/adms_events.php' ) ) {
        return 'edc-events-in-admin-section';
    } 
    else if ( is_page_template( 'templates/adms_students.php' ) ) {
        return 'edc-students-in-admin-section';
    } 
    else if ( is_page_template( 'templates/adms_supports.php' ) ) {
        return 'edc-supports-in-admin-section';
    } 
    else if ( is_page_template( 'templates/adms_students_profile.php' ) ) {
        return 'edc-user-profile-in-admin-section';
    } 
}

/** 
   * Is all items should be expanded on the page?
   *
   * @return   bool                      
   */
function edc_is_all_items_shoud_be_expaned() {

    if ( isset( $_GET['is_all_expand'] ) && intval( $_GET['is_all_expand'] ) ) {
        return true;
    } else {
        return false;
    }
}

/**
  * Generate unique user's login name
  */ 
function edc_get_unique_user_login( $first_name, $last_name ) {
    
    $user_login = '';
    
    $first_name = edc_transliterate( $first_name );
    // remove whitespaces
    $first_name = str_replace( ' ', '', $first_name );
    
    $last_name = edc_transliterate( $last_name );
    // remove whitespaces
    $last_name = str_replace( ' ', '', $last_name );
    
    
    $user_login = $last_name . $first_name;
    if ( !username_exists( $user_login ) ) {
        return $user_login;
    }
    
    $user_login = $first_name . $last_name;
    if ( !username_exists( $user_login ) ) {
        return $user_login;
    }
    
    $user_login = $last_name . $first_name;
    $user_exists = 1;
    do {
       $rnd_str = sprintf( "%0d", mt_rand( 1, 999 ) );
       $user_exists = username_exists( $prefix . $rnd_str );
    } while( $user_exists > 0 );
    $user_login = $user_login . $rnd_str;
    return $user_login;
}











// --------------------------------------------------------------------------

function edc_is_section_courses() {

    if ( is_tax( 'course' ) ||
         is_post_type_archive( ['lesson', 'book', 'personal_development', 'additional_article'] ) ||
         is_singular( ['lesson', 'book', 'personal_development', 'additional_article'] ) ||
         edc_is_course_lists_page() ) {
         
         return true;
    } else {
    
         return false;
    }
}

function edc_is_section_user_settings() {

    if ( is_page_template( 'templates/user_settings.php' ) ) {
         
         return true;
    } else {
    
         return false;
    }
}

function edc_is_section_contacts() {

    if ( is_page_template( 'templates/contacts.php' ) ) {
         
         return true;
    } else {
    
         return false;
    }
}

function edc_is_section_admin() {

    if ( strpos( $_SERVER['REQUEST_URI'], 'admin-section' ) !== false  ) {
         
         return true;
    } else {
    
         return false;
    }
}

function edc_is_section_useful() {

    if ( strpos( $_SERVER['REQUEST_URI'], 'useful-section' ) !== false  ) {
         
         return true;
    } else {
    
         return false;
    }
}

function edc_is_section_other() {

    if ( strpos( $_SERVER['REQUEST_URI'], 'raznoe-section' ) !== false  ) {
         
         return true;
    } else {
    
         return false;
    }
}

function edc_is_section_fame_hall() {

    if ( is_page_template( 'templates/fame-hall.php' ) ) {
         
         return true;
    } else {
    
         return false;
    }
}

function edc_is_section_notifications() {

    if ( is_page_template( 'templates/notifications.php' ) ) {
         
         return true;
    } else {
    
         return false;
    }
}

## Читает CSV файл и возвращает данные в виде массива.
## @param string $file_path Путь до csv файла.
## string $col_delimiter Разделитель колонки (по умолчанию автоопределине)
## string $row_delimiter Разделитель строки (по умолчанию автоопределине)
## ver 6
function edc_parse_csv_file( $file_path, $file_encodings = ['cp1251','UTF-8'], $col_delimiter = '', $row_delimiter = "" ){

	if( ! file_exists($file_path) )
		return false;

	$cont = trim( file_get_contents( $file_path ) );

	$encoded_cont = mb_convert_encoding( $cont, 'UTF-8', mb_detect_encoding($cont, $file_encodings) );

	unset( $cont );

	// определим разделитель
	if( ! $row_delimiter ){
		$row_delimiter = "\r\n";
		if( false === strpos($encoded_cont, "\r\n") )
			$row_delimiter = "\n";
	}

	$lines = explode( $row_delimiter, trim($encoded_cont) );
	$lines = array_filter( $lines );
	$lines = array_map( 'trim', $lines );

	// авто-определим разделитель из двух возможных: ';' или ','. 
	// для расчета берем не больше 30 строк
	if( ! $col_delimiter ){
		$lines10 = array_slice( $lines, 0, 30 );

		// если в строке нет одного из разделителей, то значит другой точно он...
		foreach( $lines10 as $line ){
			if( ! strpos( $line, ',') ) $col_delimiter = ';';
			if( ! strpos( $line, ';') ) $col_delimiter = ',';

			if( $col_delimiter ) break;
		}

		// если первый способ не дал результатов, то погружаемся в задачу и считаем кол разделителей в каждой строке.
		// где больше одинаковых количеств найденного разделителя, тот и разделитель...
		if( ! $col_delimiter ){
			$delim_counts = array( ';'=>array(), ','=>array() );
			foreach( $lines10 as $line ){
				$delim_counts[','][] = substr_count( $line, ',' );
				$delim_counts[';'][] = substr_count( $line, ';' );
			}

			$delim_counts = array_map( 'array_filter', $delim_counts ); // уберем нули

			// кол-во одинаковых значений массива - это потенциальный разделитель
			$delim_counts = array_map( 'array_count_values', $delim_counts );

			$delim_counts = array_map( 'max', $delim_counts ); // берем только макс. значения вхождений

			if( $delim_counts[';'] === $delim_counts[','] )
				return array('Не удалось определить разделитель колонок.');

			$col_delimiter = array_search( max($delim_counts), $delim_counts );
		}

	}

	$data = [];
	foreach( $lines as $key => $line ){
		$data[] = str_getcsv( $line, $col_delimiter ); // linedata
		unset( $lines[$key] );
	}

	return $data;
}










<?php

class EDC_Role_Edc_Teacher {

    //constructor
    public function __construct() {

        // get DB handler
        global $wpdb;
        $this->wpdb = $wpdb;
    }
    
    /**
      * Initialization function
      */
    public function init() {
    
        // Показывать лишь посты которые находятся в категории в которой человек является Активным (действующим) Преподавателем с этой же ролью. Так как нужно предохраниться от того чтобы он случайно поменял категорию поста и чтобы он случайно не добавился в один из наших курсов.
        // ---- запретить ему менять категорию поста
        // ---- разрешить ему редактировать и просматривать только эти посты
        // запретить человеку создавать новые посты
        // разрешить человеку Публиковать посты и Снимать с публикации посты
    
        // does user have access to wp-admin section?
        // are we in wp-admin section?
        if ( current_user_can( 'edc_view_admin_section' ) && is_admin() ) {
        
            // edit wp-admin menu items
            add_action( 'admin_menu', array( &$this, 'edc_hook_edit_admin_menu_items' ), 999 );
            // process saving post
            add_action( 'save_post', array( &$this, 'edc_hook_save_post' ), 10, 3 );
            // add CSS 
            add_action( 'admin_head', array( &$this, 'edc_hook_add_css' ), 999 );
            // prevent deletion of necessary posts
            add_action( 'delete_post', array( &$this, 'edc_hook_prevent_page_deletion' ) );
            add_action( 'wp_trash_post', array( &$this, 'edc_hook_prevent_page_deletion' ) );
            // make correction of list outputting posts (search post results)
            add_filter('pre_get_posts', array( &$this, 'edc_hook_process_posts_lists' ) );
        }

        // does user have access to wp-admin section?
        if ( current_user_can( 'edc_view_admin_section' ) ) {
            // edit admin bar menu items
            add_action( 'admin_bar_menu', array( &$this, 'edc_hook_edit_admin_bar' ), 999 );
        }
    }
    
    /**
      * Edit wp-admin menu items
      */
    public function edc_hook_edit_admin_menu_items() {

        // get submenu instance
        global $submenu;

        // set which menu items is allowed
        $menu_items_allowed = [];

        // is current user have rights for editing certain course materials?
        if ( current_user_can( 'edc_wp_admin_section_edit_certain_course_materials' ) ) {
            $menu_items_allowed[] = 'edit.php?post_type=lesson';
            $menu_items_allowed[] = 'edit.php?post_type=additional_article';
        }

        // remove not needed menu items
        foreach ( $submenu as $key => $menu_item ) {

            if ( !in_array( $key, $menu_items_allowed ) ) {
                remove_menu_page( $key );
            }
        }
    }
    
    /**
      * Process saving post
      */
    public function edc_hook_save_post( $post_ID, $post, $update ) {

        // get post data
        $post = get_post($post);

        // some posts can temporarly created in terms of autosaving and for preview mode so
        // we should allow to WordPress delete them
        if ( $post->post_type == 'revision' ) {
            return;
        }

        // is user trying to create new post?
        if ( !$update ) {

            // exit with message
            exit( edc__( 'У вас нет прав на создание постов.' ) );
        }
    }
    
    /**
      * Add CSS 
      */ 
    public function edc_hook_add_css() {

            /* ---- Hide almost all of the categories (courses) in categories meta box ---- */
            
            // get list of all courses
            $search_params = [
                'taxonomy' => 'course', 
                'hide_empty' => false,
                'orderby' => 'name', 
                'order' => 'ASC',
            ];

            // get courses
            $courses = get_terms( $search_params );

            // make an array with ids 
            $course_ids = [];
            foreach ( $courses as $course) {
                $course_ids[] = $course->term_id;
            }

            // get post data
            $post = intval( $_GET['post'] );
            // get course ID to which this post belongs
            $current_post_course_id = wp_get_post_terms( $post, 'course' )[0];

            // make list with courses IDs except ID of course ID to which currnt post belongs
            $course_ids = array_diff( $course_ids, [ $current_post_course_id->term_id ] );

            // making string with CSS selectors
            $course_selectors = '';
            foreach ( $course_ids as $course_id) {
                $course_selectors .= "#coursediv #course-{$course_id},";
            }
            $course_selectors = rtrim( $course_selectors, ',' );

            // output CSS snippet
            ?>
            <style>
                <?=$course_selectors?>, 
                #taxonomy-course .hide-if-no-js, 
                #taxonomy-course input {
                  display: none !important;
                }
                #coursediv * {
                  cursor: default;
                }
            </style>
            <?php
    }
          
    /**
      * Function for preventing deletion of necessary pages
      */
    function edc_hook_prevent_page_deletion( $post_id ) {
    
          // get list of allowed current user's courses where the user has appropriate role
          $allowed_courses = edc_user_courses( false, 0, 0, false, 'edc_teacher' );
          $allowed_courses_ids = array_column( $allowed_courses, 'course_id' );
          // get course ID to which current post belongs
          $current_post_course_id = wp_get_post_terms( $post_id, 'course' )[0];
          
          // get data about current post
          $post = get_post( $post_id );
          
          // some posts can temporarly created in terms of autosaving and for preview mode so
          // we should allow to WordPress delete them
          if ( $post->post_type == 'revision' ) {
              return;
          }
          
          // check whether user is allowed to delete current post
          if ( !in_array( $current_post_course_id->term_id, $allowed_courses_ids ) ) {
              
              // make an exit with a message to the user
              exit( edc__( 'У вас нет прав на удаление этого поста. Вы можете работать лишь с теми постами, которые пренадлежат к курсам в которых вы являетесь действующим преподавателем.' ) );
          }
    }
          
    /**
      * Edit admin bar menu items
      */
    public function edc_hook_edit_admin_bar( $admin_bar ) {

        // remove not needed admin bar menu items
        $admin_bar->remove_node( 'updates' );
        $admin_bar->remove_node( 'comments' );
        $admin_bar->remove_node( 'new-content' );
        $admin_bar->remove_node( 'search' );
        $admin_bar->remove_node( 'archive' );
        $admin_bar->remove_node( 'my-account' );
        $admin_bar->remove_node( 'wp-logo' );

        // add needed admin bar menu items
        // are we in wp-admin section?
        if ( !is_admin() ) {

            $admin_bar->add_menu( array(
                'id'    => 'go-item',
                'title' => edc__( 'Перейти' ),
                'href'  => '#',
                'meta'  => array(
                    'title' => edc__( 'Перейти' ),            
                ),
            ));

            // is current user have rights for editing certain course materials?
            if ( current_user_can( 'edc_wp_admin_section_edit_certain_course_materials' ) ) {
                $admin_bar->add_menu( array(
                    'id'    => 'go-post-type-lesson-item',
                    'parent' => 'go-item',
                    'title' => edc__( 'Перейти к урокам' ),
                    'href'  => get_site_url() . '/wp-admin/edit.php?post_type=lesson',
                    'meta'  => array(
                        'title' => edc__( 'Перейти к урокам' ),  
                    ),
                ));
                $admin_bar->add_menu( array(
                    'id'    => 'go-post-type-additional-article-item',
                    'parent' => 'go-item',
                    'title' => edc__( 'Перейти к вспомогательным статьям' ),
                    'href'  => get_site_url() . '/wp-admin/edit.php?post_type=additional_article',
                    'meta'  => array(
                        'title' => edc__( 'Перейти к вспомогательным статьям' ),  
                    ),
                ));
            }
        }
    }
        
    /**
      * Make correction of list outputting posts (search post results)
      */
    public function edc_hook_process_posts_lists( $query ) {

        global $pagenow;

        // get list of allowed current user's courses where the user has appropriate role
        $allowed_courses = edc_user_courses( false, 0, 0, false, 'edc_teacher' );
        $allowed_courses_ids = array_column( $allowed_courses, 'course_id' );

        // get data from the URL
        $post_type = sanitize_key( $_GET['post_type'] );
        $action = sanitize_key( $_GET['action'] );
        $post = intval( $_GET['post'] );

        // are we in section where we see list of posts with appropriate post type?
        if ( !empty( $post_type ) && empty( $action ) ) {

            if ( $query->query['post_type'] === 'additional_article' ) {

                $query->set( 'tax_query', [
                  'relation' => 'AND', 
                  [
                    'taxonomy' => 'course',
                    'terms' => $allowed_courses_ids,
                    'operator' => 'IN'
                  ],
                ] );
            } else if ( $query->query['post_type'] === 'lesson' ) {

                $query->set( 'tax_query', [
                  'relation' => 'AND', 
                  [
                    'taxonomy' => 'course',
                    'terms' => $allowed_courses_ids,
                    'operator' => 'IN'
                  ],
                ] );
            } 
        }
        // are we in editting post mode?
        else if ( empty( $post_type ) && ( $pagenow == 'post.php' ) && 
                  !empty( $action ) && ( $action == 'edit' ) ) {

            // get course ID to which current post belongs
            $current_post_course_id = wp_get_post_terms( $post, 'course' )[0];

            // is user allowed to edit current post?
            if ( !in_array( $current_post_course_id->term_id, $allowed_courses_ids ) ) {

                // exit with messge to user
                exit( edc__( 'У вас нет прав на просмотр этого поста. Вы можете работать лишь с теми постами, которые пренадлежат к курсам в которых вы являетесь действующим преподавателем.' ) );
            }
        }

        // return query object
        return $query;
    }

}
    
    
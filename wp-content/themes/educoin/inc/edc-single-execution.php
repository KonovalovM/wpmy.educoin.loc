<?php

// Sometimes we need to do something only once. This class for this reason
class EDC_Single_Execution {

    //constructor
    public function __construct() {

        // get DB handler
        global $wpdb;
        $this->wpdb = $wpdb;
    }

    /**
      * Migrate to new course system assignments
      */
    public function migrate_to_new_course_system_assigments() {
      
        // get all users
        $args = []; 
        $users = get_users( $args );
        
        foreach ( $users as $user ) {

            $courses = get_field( 'edc_user_courses', 'user_' . $user->ID );

            foreach ( $courses as $course_id ) {
                edc_add_course_to_user( $course_id, $user->ID );
            }
        }
      
        dd('Execution of "migrate_to_new_course_system_assigments()" finished with success.');
    }

    /**
      * Actualization of column 'supports_to_users_id' in DB table 'myedc_edc_courses_to_users'
      */
    public function actualization_of_db_column_supports_to_users_id() {
      
        $query = $this->wpdb->prepare( "    
            SELECT *
            FROM {$this->wpdb->prefix}edc_supports_to_users 
            WHERE support_id IS NOT NULL AND end_date IS NULL  
        ", array() );

        $records = $this->wpdb->get_results( $query, ARRAY_A );
        
        $i = 0;
        
        foreach ($records as $record) {
            // set necessary fields
            $data['supports_to_users_id'] = $record['id'];


            // forming of Where clause
            $where = [ 
                'course_id' => $record['course_id'],
                'user_id' => $record['user_id'],
            ];
            // execute update operation
            $result = $this->wpdb->update( "{$this->wpdb->prefix}edc_courses_to_users", $data, $where );

            if ( !empty( $result ) ) {
                $i++;
                d('supports_to_users_id= ' . $record['id']);
            }
        }
        
        d( '--------- Processed records: ' . $i );

        dd('Execution of "actualization_of_db_column_supports_to_users_id()" finished with success.');
    }

    /**
      * Actualization of column 'role' in DB table 'myedc_edc_courses_to_users'
      */
    public function actualization_of_db_column_courses_to_users_role() {
      
        // get all users
        $args = []; 
        $users = get_users( $args );
        
        foreach ( $users as $user ) {

            // get user role
            $user_role = edc_get_user_roles( $user->ID )[0]['id'];
            
            // get course lists
            $courses = edc_get_user_course_lists( $user->ID, false );

            // go through all of the user's courses
            foreach ( $courses as $course ) {
            
                // set user course role
                $data['role'] = $user_role;

                // save data to database
                edc_set_user_course_data( $course->term_id, $user->ID, $data );
            }
        }
      
        dd('Execution of "actualization_of_db_column_courses_to_users_role()" finished with success.');
    }

    /**
      * Actualization of column 'is_low_priority' in DB table 'myedc_edc_courses_to_users'
      */
    public function actualization_of_db_column_courses_to_users_is_low_priority() {
      
        // get all users
        $args = []; 
        $users = get_users( $args );
        
        foreach ( $users as $user ) {

            $is_low_priority = get_field( 'edc_is_low_priority_homework_check', 'user_' . $user->ID );

            // get course lists
            $courses = edc_get_user_course_lists( $user->ID, false );

            // go through all of the user's courses
            foreach ( $courses as $course ) {
            
                // set data to save
                $data['is_low_priority'] = $is_low_priority;

                // save data to database
                edc_set_user_course_data( $course->term_id, $user->ID, $data );
            }
        }
      
        dd('Execution of "actualization_of_db_column_courses_to_users_is_low_priority()" finished with success.');
    }
  
}
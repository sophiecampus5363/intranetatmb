<?php

    namespace Wpo\Sync;
        
    // Prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Util\Logger;
    use \Wpo\Util\Helpers;
    use \Wpo\Util\Options;
    use \Wpo\User\User_Manager;
    use \Wpo\User\User;
    use \Wpo\User\User_Details;
    use \Wpo\Aad\Msft_Graph;
    use \Wpo\Sync\Sync_Db;

    if( !class_exists( '\Wpo\Sync\Sync_Manager' ) ) {

        class Sync_Manager {

            const OPTION_JOB_ID = 'wpo_sync_users_job_id';
            const OPTION_LAST_RUN = 'wpo_sync_users_job_last_run';

            private $sync_job_id = null;
            private $create_users = false;
            private $update_users = false;
            private $delete_users = false;
            private $only_internal_users = true;
            private $require_email_address = true;

            /**
             * Creates manually selected users in WordPress when the user clicked the "create users" button.
             * 
             * @since 3.0
             * 
             * @param array $upns array of selected user principal names
             */
            public static function create_users( $upns ) {

                if( !is_array( $upns ) ) {

                    return new \WP_Error( '6000', 'Error occurred whilst trying to create users: argument exception' );
                }

                global $wpdb;

                $table_name = Sync_Db::get_user_sync_table_name();
                $upns_with_quotes = array_map( function( $value ) {

                    return '\''. $value . '\'';

                }, $upns );

                $upns_as_string = join( ',', $upns_with_quotes);
                $records = $wpdb->get_results( "SELECT * FROM $table_name WHERE upn IN ( $upns_as_string )", ARRAY_A );

                foreach( $records as $record ) {

                    $wpo_usr = new User();
                
                    $wpo_usr->first_name = $record[ 'first_name' ];
                    $wpo_usr->last_name = $record[ 'last_name' ];
                    $wpo_usr->full_name = $record[ 'full_name' ];
                    $wpo_usr->email = $record[ 'email' ];
                    $wpo_usr->upn = $record[ 'upn' ];
                    $wpo_usr->name = $record[ 'upn' ];

                    $wpo_usr = apply_filters( 'wpo_graph_get_group_info', $wpo_usr );

                    // create a new WordPress user 
                    $wp_usr = apply_filters( 'wpo_add_user', $wpo_usr );
                    $sync_job_status = empty( $wp_usr ) ? 'error' : 'created';
                    $wp_id = empty( $wp_usr ) ? -1 : $wp_usr->ID;
                    $wpdb->update( $table_name, array( 'sync_job_status' => $sync_job_status, 'wp_id' => $wp_id ), array( 'upn' => $record[ 'upn' ] ) );
                    Logger::write_log( 'DEBUG', 'Status after adding new WordPress user for ' . $wpo_usr->upn . ': ' . $sync_job_status );
                    
                    if( empty( $wp_usr ) ) {
                        continue;
                    }
                    
                    do_action( 'wpo_update_user_roles', $wp_usr, $wpo_usr );
                    apply_filters( 'wpo_graph_get_user_info', $wp_usr );
                }
            }

            /**
             * Deletes manually selected users  from WordPress when the user clicked the "delete users" button.
             * 
             * @since 3.0
             * 
             * @param array $wp_ids array of selected user ids
             */
            public static function delete_users( $wp_ids ) {

                if( !is_array( $wp_ids ) ) {
                    return new \WP_Error( '6010', 'Error occurred whilst trying to delete users: argument exception' );
                }

                global $wpdb;

                $table_name = Sync_Db::get_user_sync_table_name();
                include_once( ABSPATH . 'wp-admin/includes/user.php' );

                foreach( $wp_ids as $wp_id ) {
                    $user = get_user_by( 'ID', $wp_id );

                    if( is_wp_error( $user ) ) {
                        Logger::write_log( 'DEBUG', 'Cannot delete user with ID ' . $wp_id . ' because user cannot be found' );
                        continue;
                    }

                    if( Helpers::user_is_admin( $user ) ) {
                        Logger::write_log( 'DEBUG', 'Not deleting user with ID ' . $wp_id . ' because user has administrator capabilities' );
                        $sync_job_status = 'is_admin';
                    }
                    else {
                        $sync_job_status = wp_delete_user( intval( $wp_id ) ) ? 'deleted' : 'error';
                    }
                    
                    $wpdb->update( $table_name, array( 'sync_job_status' => $sync_job_status ), array( 'wp_id' => $wp_id ) );
                    Logger::write_log( 'DEBUG', 'Status after deleting WordPress user with ID ' . $wp_id . ': ' . $sync_job_status );
                }
            }

            /**
             * Starts the user synchronization by calling the first collection / page of 
             * users from Office 365 and then recursively continues until finished. The
             * results are stored in custom WordPress table.
             * 
             * @since 3.0
             * 
             * @return mixed(bool|WP_Error) true if synchronization was successful otherwise WP_Error
             */
            public static function sync_users() {

                $settings = array();

                // read the posted variable
                $settings[ 'create_users' ] = isset( $_POST[ 'wpo_create_users' ] ) && $_POST[ 'wpo_create_users' ] == 'on' ? true : false;
                $settings[ 'update_users' ] = isset( $_POST[ 'wpo_update_users' ] ) && $_POST[ 'wpo_update_users' ] == 'on' ? true : false;
                $settings[ 'only_internal_users' ] = isset( $_POST[ 'wpo_internal_users' ] ) && $_POST[ 'wpo_internal_users' ] == 'on' ? true : false;
                $settings[ 'delete_users' ] = isset( $_POST[ 'wpo_delete_users' ] ) && $_POST[ 'wpo_delete_users' ] == 'on' ? true : false;
                $settings[ 'sync_user_id' ] = get_current_user_id();

                self::delete_job_data();
                
                // Create a unique sync job id 
                $settings[ 'sync_job_id' ] = uniqid();
                
                // Remember timestamp for last user sync run
                update_site_option( self::OPTION_JOB_ID, $settings[ 'sync_job_id' ] );
                update_site_option( self::OPTION_LAST_RUN, time() );

                // Since v9.1 admins can define their own sync query
                $query = ltrim( Options::get_global_string_var( 'user_sync_query' ), '/' );
                
                if( empty( $query ) ) {
                    $query = 'myorganization/users?$filter=accountEnabled+eq+true+and+userType+eq+%27member%27&$top=10';
                }

                $fetch_result = self::fetch_users( "/$query", $settings );
            }

            /**
             * Fetches users from Microsoft Graph using the query supplied. Can be called recursively.
             *  
             * @since 3.0
             * 
             * @param string $graph_query query to call Microsoft Graph
             */
            public static function fetch_users( $graph_query, $settings ) {

                $settings[ 'create_users' ] == 1 ? $settings[ 'create_users' ] = true : false;
                $settings[ 'update_users' ] == 1 ? $settings[ 'update_users' ] = true : false;
                $settings[ 'only_internal_users' ] == 1 ? $settings[ 'only_internal_users' ] = true : false;
                $settings[ 'delete_users' ] == 1 ? $settings[ 'delete_users' ] = true : false;

                self::process_fetch_result( Msft_Graph::fetch( $graph_query, 'GET', false, array( 'Accept: application/json;odata.metadata=minimal' ) ), $settings );
            }

            /**
             * Processes a collection of Office 365 users returned from the corresponding Microsoft Graph query. Recursively
             * calls for the next collection when finished processing with the current collection.
             * 
             * @since 3.0
             * 
             * @param stdClass  $response   Response returned by the MS Graph client that needs to be processed
             * @param array     $settings   Sync settings e.g. create users, delete users etc.
             * 
             * @return void
             */
            private static function process_fetch_result( $response, $settings ) {

                if( is_wp_error( $response ) || $response[ 'response_code' ] != 200 || !is_array( $response[ 'payload' ] ) ) {
                    Logger::write_log( 'ERROR', 'Could not fetch users for synchronization from the graph:' );
                    Logger::write_log( 'DEBUG', $response );
                    return;
                }

                if( !is_array( $response[ 'payload' ][ 'value' ] ) ) {
                    Logger::write_log( 'ERROR', 'No users returned from the graph.' );
                    return;
                }

                foreach( $response[ 'payload' ][ 'value' ] as $o365_user ) {

                    // transform user to our own internal format
                    $wpo_usr = User::user_from_graph_user( $o365_user );

                    // user without upn cannot be processed
                    if( !isset( $wpo_usr->upn ) ) {

                        Logger::write_log( 'DEBUG', 'O365 user without userPrincipalName' );
                        continue;
                    }

                    if( true === $settings[ 'only_internal_users' ] ) {

                        // ensure domain users
                        $email_domain = Helpers::get_smtp_domain_from_email_address( $wpo_usr->upn );

                        if( empty( $email_domain ) ) {
                            Logger::write_log( 'DEBUG', 'O365 user\'s userPrincipalName is not an email address' );
                            continue;
                        }

                        if( false === Helpers::is_tenant_domain( $email_domain ) ) {
                            Logger::write_log( 'DEBUG', "Email domain $email_domain does not match any of the default or custom domains" );
                            continue;
                        }
                    }
                    
                    $wp_usr = User_Manager::try_get_user_by( $wpo_usr->upn, $wpo_usr->email );

                    $action_performed = 0;

                    // found a new Office 365 user
                    if( NULL === $wp_usr ) {

                        if( $settings[ 'create_users' ] ) {

                            $wp_usr = apply_filters( 'wpo_add_user', $wpo_usr );

                            if( empty( $wp_usr ) )
                                continue;

                            $action_performed = 1;

                            Logger::write_log( 'DEBUG', 'Created new WordPress user for ' . $wpo_usr->upn );
                        }
                    }

                    // update new and / or existing wp users with group and user info
                    if( NULL !== $wp_usr ) {
                        
                        $action_performed = $action_performed == 0 ? 2 : $action_performed;

                        if( $action_performed == 1 || $settings[ 'update_users' ] ) {

                            // Update role(s) assignment and extra user details
                            $wpo_usr = apply_filters( 'wpo_graph_get_group_info', $wpo_usr );
                            do_action( 'wpo_update_user_roles', $wp_usr, $wpo_usr );
                            apply_filters( 'wpo_graph_get_user_info', $wp_usr );
                            
                            $user_id = $wp_usr->ID;

                            // Update addtional O365 profile fields
                            User_Details::process_extra_user_fields( function( $name, $title ) use ( &$user_id, &$o365_user ) {

                                if ( !empty( $o365_user[ $name ] ) ) {
            
                                    update_user_meta(
                                        $user_id,
                                        $name,
                                        $o365_user[ $name ] );
                                }
                            } );
                        }

                        // tag wp user with sync job ID
                        update_user_meta(
                            $wp_usr->ID,
                            self::OPTION_JOB_ID,
                            $settings[ 'sync_job_id' ] );
                    }

                    // remember new user
                    Logger::write_log( 'DEBUG', 'WordPress user found for ' . $o365_user[ 'userPrincipalName' ] );

                    // log the new Office 365 user in our table
                    global $wpdb;

                    $table_name = Sync_Db::get_user_sync_table_name();

                    switch( $action_performed ) {
                        case 1:
                            $record_type = 'new_domain_user';
                            $sync_job_status = $settings[ 'create_users' ] ? 'created' : 'logged';
                            break;
                        case 2:
                            $record_type = 'existing_domain_user';
                            $sync_job_status = $settings[ 'update_users' ] ? 'updated' : 'logged';
                            break;
                        default:
                            $record_type = 'new_domain_user';
                            $sync_job_status = 'logged';
                    }

                    $wpdb->insert( 
                        $table_name, 
                        array( 
                            'wp_id'             => NULL !== $wp_usr ? $wp_usr->ID : -1,
                            'upn'               => $wpo_usr->upn, 
                            'first_name'        => $wpo_usr->first_name,
                            'last_name'         => $wpo_usr->last_name,
                            'full_name'         => $wpo_usr->full_name,
                            'email'             => $wpo_usr->email,
                            'sync_job_id'       => $settings[ 'sync_job_id' ],
                            'name'              => $wpo_usr->name,
                            'sync_job_status'   => $sync_job_status,
                            'record_type'       => $record_type,
                        ) 
                    );
                }

                // continue with the next batch of users
                if( array_key_exists( '@odata.nextLink', $response[ 'payload' ] ) ) {
                    $graph_version = Options::get_global_string_var( 'graph_version' );
                    $graph_version = empty( $graph_version ) || $graph_version == 'current' ? 'v1.0' : $graph_version;
                    $graph_url = 'https://graph.microsoft.com/' . $graph_version;

                    ob_start();
                    include( $GLOBALS[ 'WPO365_PLUGIN_DIR' ] . '/templates/fetch-users.php' );
                    echo ob_get_clean();
                    
                    exit();
                }
                else {
                    // finally read all the untagged users after the current run and persist
                    self::untagged_users( $settings );

                    // And inform the site admin that user sync has completed
                    self::sync_completed_notification();
                }
            }

            /**
             * Queries all users for the current job tag and if not found will add those users
             * to the user sync table as untagged users (no matching Office 365 user was found).
             * 
             * @since 3.0
             * 
             * @return void
             */
            private static function untagged_users( $settings ) {

                $untagged_users = new \WP_User_Query(
                    array(
                        // 'fields'     => array( 'ID', 'user_login', 'display_name', 'user_email' ),
                        'meta_query' => array(
                            'relation' => 'OR',
                            array(
                                'key'       => self::OPTION_JOB_ID,
                                'value'     => $settings[ 'sync_job_id' ],
                                'compare'   => '!=',
                            ),
                            array(
                                'key'       => self::OPTION_JOB_ID,
                                'compare'   => 'NOT EXISTS',
                            )
                        )
                    )
                );

                global $wpdb;

                $table_name = Sync_Db::get_user_sync_table_name();
                $sync_job_status = 'logged';
            
                // and fill it with the results of the last run
                $untagged_users = $untagged_users->get_results();
                $wp_ids = array();

                foreach( $untagged_users as $untagged_user ) {

                    $wpdb->insert( 
                        $table_name, 
                        array( 
                            'wp_id'             => isset( $untagged_user->ID ) ? $untagged_user->ID : '', 
                            'upn'               => isset( $untagged_user->user_login ) ? $untagged_user->user_login : '', 
                            'first_name'        => '', // defined in user meta
                            'last_name'         => '', // defined in user meta
                            'full_name'         => isset( $untagged_user->display_name ) ? $untagged_user->display_name : '',
                            'email'             => isset( $untagged_user->user_email ) ? $untagged_user->user_email : '',
                            'sync_job_id'       => $settings[ 'sync_job_id' ],
                            'name'              => isset( $untagged_user->user_login ) ? $untagged_user->user_login : '',
                            'sync_job_status'   => $sync_job_status,
                            'record_type'       => 'untagged_user',
                        ) 
                    );

                    $wp_ids[] = $untagged_user->ID;
                }

                // finally commit deletion of WP users if requested
                if( true === $settings[ 'delete_users' ] ) {

                    self::delete_users( $wp_ids );
                }
            }

            /**
             * Helper method to truncate the table and remove the job id and last run time.
             * 
             * @since 3.0
             * 
             * @return void
             */
            public static function delete_job_data() {

                delete_site_option( self::OPTION_JOB_ID );
                delete_site_option( self::OPTION_LAST_RUN );
                
                global $wpdb;

                $table_name = Sync_Db::get_user_sync_table_name();
                $wpdb->query( "TRUNCATE TABLE $table_name" );
            }

            /**
             * Sends the admin of the site an email to inform that user synchronization has completed.
             * 
             * @since 7.11
             * 
             * @return void
             */
            private static function sync_completed_notification() {

                // The blogname option is escaped with esc_html on the way into the database in sanitize_option
                // we want to reverse this for the plain text arena of emails.
                $blogname = wp_specialchars_decode( get_option( 'blogname' ), ENT_QUOTES );

                /* translators: %s: site title */
                $message = sprintf( __( 'User Sync completed on your site %s.' ), $blogname ) . "\r\n\r\n";
        
                $sync_completed_email_admin = array(
                    'to'      => get_option( 'admin_email' ),
                    'subject' => __( '[%s] User Sync completed' ),
                    'message' => $message,
                    'headers' => '',
                );

                @wp_mail(
                    $sync_completed_email_admin['to'],
                    wp_specialchars_decode( sprintf( $sync_completed_email_admin['subject'], $blogname ) ),
                    $sync_completed_email_admin['message'],
                    $sync_completed_email_admin['headers']
                );
            }
        }
    }
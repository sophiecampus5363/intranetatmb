<?php

    namespace Wpo\User;
    
    // Prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Util\Options;
    use \Wpo\Util\Helpers;
    use \Wpo\Util\Logger;
    use \Wpo\Util\Notifications;
    
    if( !class_exists( '\Wpo\User\User_Manager_Premium' ) ) {
    
        class User_Manager_Premium {

            /**
             * Creates a new Wordpress user
             *
             * @since   1.0
             * @param   User    usr => User instance holding all necessary data
             * @return  mixed(WPUser|NULL)
             */
            public static function add_user( $wpo_usr ) {

                $userdata = array( 
                    'user_login'    => $wpo_usr->upn,
                    'user_pass'     => wp_generate_password( 16, true, false ),
                    'display_name'  => $wpo_usr->full_name,
                    'user_email'    => $wpo_usr->email,
                    'first_name'    => $wpo_usr->first_name,
                    'last_name'     => $wpo_usr->last_name,
                    'role'          => '', // role will be added separately
                );

                /**
                 * @since 9.4 
                 * 
                 * Optionally removing any user_register hooks as these more often than
                 * not interfer and cause unexpected behavior.
                 */

                $user_regiser_hooks = NULL;
                
                if( Options::get_global_boolean_var( 'skip_user_register_action' ) && isset( $GLOBALS[ 'wp_filter' ] ) && isset( $GLOBALS[ 'wp_filter' ][ 'user_register' ] ) ) {
                    Logger::write_log( 'DEBUG', 'Temporarily removing all filters for the user_register action to avoid interference' );
                    $user_regiser_hooks = $GLOBALS[ 'wp_filter' ][ 'user_register' ];
                    unset( $GLOBALS[ 'wp_filter' ][ 'user_register' ] );
                }

                // Insert in Wordpress DB
                $wp_usr_id = wp_insert_user( $userdata );

                if( !empty( $GLOBALS[ 'wp_filter' ] ) && !empty( $user_regiser_hooks ) ) {
                    $GLOBALS[ 'wp_filter' ][ 'user_register' ] = $user_regiser_hooks;
                }

                if( is_wp_error( $wp_usr_id ) ) {
                    Logger::write_log( 
                        'ERROR', 
                        'Could not create wp user. See next line for erroneous user information.' );
                    Logger::write_log( 
                        'ERROR', 
                        $wp_usr_id );
                    return NULL;
                }

                Logger::write_log( 'DEBUG', 'Created new user with ID ' . $wp_usr_id );

                self::memorize_new_user_id( $wp_usr_id );

                // Try and retrieve the user's avatar if selected
                if( Options::get_global_boolean_var( 'use_avatar' ) && class_exists( '\Wpo\User\Avatar' ) ) {
                    $default_avatar = get_avatar( $wp_usr_id );
                    \Wpo\User\Avatar::get_O365_avatar( $default_avatar, $wp_usr_id, 96, true );
                }

                // Try and send new user email
                if( Options::get_global_boolean_var( 'new_usr_send_mail' ) ) {
                    $notify = Options::get_global_boolean_var( 'new_usr_send_mail_admin_only' )
                        ? 'admin'
                        : 'both';
                    Notifications::new_user_notification( $wp_usr_id, NULL, $notify );
                    Logger::write_log( 'DEBUG', 'Sent new user notification' );
                }
                else {
                    Logger::write_log( 'DEBUG', 'Did not sent new user notification' );
                }
                
                // Add an extra meta information that this user is in fact a user created by WPO365
                add_user_meta( $wp_usr_id, 'auth_source', 'AAD', true );
                $wp_usr = get_user_by( 'ID', $wp_usr_id );
                return $wp_usr;
            }

            /**
             * Helper method to create / update a new user id cache that
             * can be referenced by other parts of the application e.g. 
             * when deciding to redirect a newly created user to a Welcome
             * page.
             * 
             * @since 9.1
             * 
             * @param $user_id int The WP_User's id
             * 
             * @return void
             */
            public static function memorize_new_user_id( $user_id ) {

                $new_user_ids = Helpers::mu_get_transient( 'wpo365_new_user_ids' );

                if( !empty( $new_user_ids ) && is_array( $new_user_ids ) ) {
                    $new_user_ids[] = $user_id;
                }
                else {
                    $new_user_ids = array( $user_id );
                }

                Helpers::mu_set_transient( 'wpo365_new_user_ids', $new_user_ids, 300 );
            }

            /**
             * Simple helper that will check whether a given user id has been
             * recently created and optionally removes the user from the new 
             * user id cache.
             * 
             * @since 9.1
             * 
             * @param $user_id int User id to check
             * @param $remove bool True if the user id should be deleted
             * 
             * @return bool True if the user has been created within the last 5 minutes
             */
            public static function is_new_user( $user_id, $remove = false ) {

                $new_user_ids = Helpers::mu_get_transient( 'wpo365_new_user_ids' );

                if( !empty( $new_user_ids ) && is_array( $new_user_ids ) ) {
                    $is_new_user = in_array( $user_id, $new_user_ids );

                    if( $is_new_user && $remove ) {
                        $to_remove = array( $user_id );
                        $_new_user_ids = array_diff( $new_user_ids, $to_remove );
                        Helpers::mu_set_transient( 'wpo365_new_user_ids', $_new_user_ids, 300 );
                    }

                    return $is_new_user;
                }

                return false;
            }
        }
    }

<?php

    namespace Wpo\User;
    
    // Prevent public access to this script
    defined( 'ABSPATH' ) or die();
    
    use \Wpo\Util\Logger;
    use \Wpo\Util\Helpers;
    use \Wpo\Util\Options;
    use \Wpo\Aad\Auth;
    use \Wpo\User\User;

    if( !class_exists( '\Wpo\User\User_Manager' ) ) {
    
        class User_Manager {

            const USER_NOT_LOGGED_IN = 0;
            const IS_NOT_O365_USER = 1;
            const IS_O365_USER = 2;

            /**
             * Tries to find the user by upn, accountname or email.
             * 
             * @since 9.4
             * 
             * @param $needle
             * 
             * @return WP_User or NULL
             */
            public static function try_get_user_by( $login = '', $email = '' ) {

                // Check arguments
                if( empty( $login ) && empty( $email ) ) {
                    return NULL;
                }

                // 1st - Try find by upn
                if( is_string( $login ) ) {
                    $wp_usr = get_user_by( 'login', $login );

                    if( !empty( $wp_usr ) ) {
                        return $wp_usr;
                    }                    
                }
                
                
                // 2nd - Try find by email
                if( is_string( $email ) ) {
                    $wp_usr = get_user_by( 'email', $email );

                    if( !empty( $wp_usr ) ) {
                        return $wp_usr;
                    }
                }

                // 3rd - Try find by accountname
                if( is_string( $login ) ) { 

                    $atpos = strpos( $login, '@' );

                    if( false !== $atpos ) {
                        $accountname = substr( $login, 0, $atpos );
                        $wp_usr = get_user_by( 'login', $accountname );

                        if( !empty( $wp_usr ) ) {
                            return $wp_usr;
                        }
                    }

                }

                return NULL;
            }

            /**
             * Checks whether a user identified by an id_token received from
             * Microsoft matches with an existing Wordpress user and if not creates it
             *
             * @since   1.0
             * @param   string  id_token => received from Microsoft's openidconnect endpoint
             * @return  mixed(WP_User|WP_Error) WP_User when user could be ensured or else WP_Error
             */
            public static function ensure_user( $decoded_id_token ) {
                if( empty( $decoded_id_token ) ) {
                    return Helpers::handle_error( 'ERROR', '4010', 'Cannot ensure user because id_token empty' );
                }

                // Translate id_token in a Wpo\User\User object
                $use_id_token_parser_v1 = Options::get_global_boolean_var( 'use_id_token_parser_v1' );
                $wpo_usr = $use_id_token_parser_v1
                    ? User::user_from_id_token( $decoded_id_token )
                    : User::user_from_id_tokenV2( $decoded_id_token );

                if( is_wp_error( $wpo_usr) ) {
                    return $wpo_usr;
                }

                // Check whether the user's domain is white listed (if empty this check is skipped)
                $domain_white_list = Options::get_global_list_var( 'domain_whitelist' );

                if( sizeof( $domain_white_list ) > 0 ) {
                    $white_listed_domains = implode( ';', $domain_white_list ); 
                    $smtp_domain = Helpers::get_smtp_domain_from_email_address( $wpo_usr->email );

                    if( empty( $smtp_domain )
                        || false === stripos( $white_listed_domains, $smtp_domain ) ) {
                            return Helpers::handle_error( 'DEBUG', '4030', 'Cannot continue since the domain the user is coming from is not whitelisted or the smtp domain could not be determined (' . $wpo_usr->upn . ')' );
                    }
                }

                $wp_usr = self::try_get_user_by( $wpo_usr->upn, $wpo_usr->email );

                // Get target site info
                $site_info = Helpers::target_site_info( $_POST[ 'state' ] );

                if( $site_info == null ) {
                    return Helpers::handle_error( 'DEBUG', '4040', 'Could not retrieve necessary site info needed to continue' );
                }

                $create_users_and_add = Options::get_global_boolean_var( 'create_and_add_users' );
                // Create a new WP user if not found but only if desired
                if( empty( $wp_usr ) ) {

                    if( false === has_filter( 'wpo_add_user' ) ) {

                        // User not found and no filter found to create the user
                        return Helpers::handle_error( 'DEBUG', '4050', 'The basic edition of the plugin does not automatically create new WordPress users' );
                    }

                    if( true === $create_users_and_add ) {
                        $wp_usr = apply_filters( 'wpo_add_user', $wpo_usr );
                        if( empty( $wp_usr ) ) {
                            // User not found and new users could not be created
                            return Helpers::handle_error( 'DEBUG', '4060', 'Could not create user with user principal name ' . $wpo_usr->upn );
                        }
                    }
                    else {
                        // User not found and new users shall not be created
                        return Helpers::handle_error( 'DEBUG', '4070', 'User not found and settings prevented creating a new user on-demand' );
                    }
                } // else wp user already created so continue

                // In case of multi site add user to target site but only if desired
                if( $site_info[ 'is_multi' ] ) {
                    $mu_new_usr_default_role = Options::get_global_string_var( 'mu_new_usr_default_role' );
                    
                    if( !empty( $mu_new_usr_default_role ) 
                        && !is_user_member_of_blog( 
                            $wp_usr->ID, 
                            $site_info[ 'blog_id' ] ) 
                        && $create_users_and_add ) {
                            add_user_to_blog( 
                                $site_info[ 'blog_id' ], 
                                $wp_usr->ID, 
                                $mu_new_usr_default_role );
                    } // else user already added to target site so continue
                } // else not multi site so no need to add user target site explicitely

                // Update a user's role when a mapping between WP roles and AD (security) groups exists
                do_action( 'wpo_update_user_roles', $wp_usr, $wpo_usr );
                
                // Now log on the user
                wp_set_auth_cookie( $wp_usr->ID, true );  // Both log user on
                wp_set_current_user( $wp_usr->ID );       // And set current user

                // Mark the user as AAD in case he/she isn't ( because manually added but still using AAD to authenticate )
                $usr_meta = get_user_meta( $wp_usr->ID, 'auth_source', true );
                
                if( empty( $usr_meta ) || strtolower( $usr_meta ) != 'aad' ) {
                    // Add an extra meta information that this user is in fact a user created by WPO365
                    add_user_meta( $wp_usr->ID, 'auth_source', 'AAD', true );
                }

                // Save the user's ID in a session var
                Logger::write_log( 'DEBUG', 'found user with ID ' . $wp_usr->ID );
                
                // Session valid until
                $session_duration = Options::get_global_numeric_var( 'session_duration' );
                $session_duration = empty( $session_duration ) ? 3480 : $session_duration;
                $expiry = time() + intval( $session_duration );

                // Obfuscated user's wp id
                $obfuscated_user_id = $expiry + $wp_usr->ID;
                $wpo_auth = new \stdClass();
                $wpo_auth->expiry = $expiry;
                $wpo_auth->ouid = $obfuscated_user_id;
                $wpo_auth->url = $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_url' ];

                update_user_meta( 
                    get_current_user_id(),
                    Auth::USR_META_WPO365_AUTH,
                    json_encode( $wpo_auth ) );

                return $wp_usr;
            }

            /**
             * Gets the user's default role or if a mapping exists overrides that default role 
             * and returns the role according to the mapping.
             * 
             * @since 3.2
             * 
             * 
             * @return mixed(array|WP_Error) user's role as string or an WP_Error if not defined
             */
            private static function get_user_roles( $wp_usr, $wpo_usr ) {
                // Get fresh copy of existing roles
                $usr_meta = get_userdata( $wp_usr->ID );
                $replace_or_update_user_role = Options::get_global_string_var( 'replace_or_update_user_roles' );
                
                // Empty any existing roles when configured to do so
                if( $replace_or_update_user_role == 'replace' ) {
                    foreach( $usr_meta->roles as $current_user_role )
                        $wp_usr->remove_role( $current_user_role );

                    // refresh the user meta for
                    $usr_meta = get_userdata( $wp_usr->ID );
                }

                // Start with the current roles
                $user_roles = $usr_meta->roles;

                // Add new roles as per AD Group > WP role mapping
                $group_role_settings = Options::get_global_list_var( 'groups_x_roles' );

                foreach( $group_role_settings as $kv_pair ) {
                    if( array_key_exists( $kv_pair[ 'key' ], $wpo_usr->groups ) ) {
                        $role_from_role_mapping = strtolower( $kv_pair[ 'value' ] );

                        // Check if the role exists (if not it is not added)
                        if( null === get_role( $role_from_role_mapping ) ) {
                            Logger::write_log( 'ERROR', 'Group mapping for WordPress role ' . $role_from_role_mapping .' was found for user ' . $wpo_usr->upn . ' but this role does not exist in WordPress' );
                            continue;
                        }

                        // Only add new WordPress role
                        if( false === in_array( $role_from_role_mapping, $user_roles ) ) {
                            $user_roles[] = $role_from_role_mapping;
                            Logger::write_log( 'DEBUG', "Found group mapping for WordPress role ' . $role_from_role_mapping .' and added it to the user's roles array" );
                        }                        
                    }
                }

                /**
                 * @since 9.4
                 * 
                 * Duplicated logic to map user's login domain suffix to a WP role.
                 */

                $user_domain = "";
                $atpos = strpos( $wpo_usr->upn, '@' );

                if( false !== $atpos ) {
                    $user_domain = substr( $wpo_usr->upn, ($atpos + 1) );
                }

                // Add new roles as per domain > WP role mapping
                $domain_role_settings = Options::get_global_list_var( 'domains_x_roles' );

                foreach( $domain_role_settings as $kv_pair ) {
                    if( !empty( $user_domain ) && false !== stripos( $user_domain, $kv_pair[ 'key' ] ) ) {
                        $role_from_role_mapping = strtolower( $kv_pair[ 'value' ] );

                        // Check if the role exists (if not it is not added)
                        if( null === get_role( $role_from_role_mapping ) ) {
                            Logger::write_log( 'ERROR', 'Domain mapping for WordPress role ' . $role_from_role_mapping .' was found for user ' . $wpo_usr->upn . ' but this role does not exist in WordPress' );
                            continue;
                        }

                        // Only add new WordPress role
                        if( false === in_array( $role_from_role_mapping, $user_roles ) ) {
                            $user_roles[] = $role_from_role_mapping;
                            Logger::write_log( 'DEBUG', "Found domain mapping for WordPress role ' . $role_from_role_mapping .' and added it to the user's roles array" );
                        }                        
                    }
                }

                // Add default role if needed / configured
                if( empty( $user_roles ) 
                    || ( !empty( $user_roles ) 
                         && false === Options::get_global_boolean_var( 'default_role_as_fallback' ) ) ) {
                            $usr_default_role = Options::get_global_string_var( 'new_usr_default_role' );

                            if( !empty( $usr_default_role ) ) {
                                $usr_default_role = strtolower( $usr_default_role );
                                $wp_role = get_role( $usr_default_role );

                                if( empty( $wp_role ) )
                                    Logger::write_log( 'ERROR', 'Trying to add the default role but it appears undefined' );
                                else
                                    $user_roles[] = $usr_default_role;
                            }
                }

                // Return error if no roles
                if( empty( $user_roles ) )
                    return new \WP_Error( '4000', 'Could not retrieve at least one WordPress role for user with UPN ' . $wpo_usr->upn );

                // Return user roles
                return $user_roles;
            }

            /**
             * Updates a user role using wp role / ad security group information (or fallback to default role)
             * 
             * @since 3.2
             * 
             * @param WP_User $wp_usr 
             * @param User $sur the Open Connect ID token, optionally with AAD groups  
             * 
             * @return void
             */
            public static function update_user_roles( $wp_usr, $wpo_usr ) {

                // Don't update the user role when user is already an administrator
                if( Helpers::user_is_admin( $wp_usr ) ) {
                    Logger::write_log( 'DEBUG', 'Not updating the role for a user that is already an administrator.' );
                    return;
                }

                // Get all possible roles for user
                $user_roles = self::get_user_roles( $wp_usr, $wpo_usr );

                // If no roles are found then return
                if( is_wp_error( $user_roles ) ) {
                    Logger::write_log( 'DEBUG', 'Target role for user could not be determined e.g. because of user not in AD group or default role for main site unconfigured' );
                    return;
                }

                // Get fresh copy of user's current roles
                $usr_meta = get_userdata( $wp_usr->ID );

                // Add from new roles if not already added
                foreach( $user_roles as $user_role ) {
                    if( false === in_array( $user_role, $usr_meta->roles ) ) {
                        $wp_usr->add_role( $user_role );
                    }
                }
            }

            /**
             * Checks whether current user is O365 user
             *
             * @since   1.0
             * @return  int One of the following User_Manager class constants 
             *              USER_NOT_LOGGED_IN, IS_O365_USER or IS_NOT_O365_USER
             */
            public static function user_is_o365_user( $user_id, $email = '' ) {
                $wp_usr = get_user_by( 'ID', intval( $user_id ) );
                
                if( !empty( $email ) && false === $wp_usr ) {
                    $wp_usr = get_user_by( 'email', $email );
                }

                if( $wp_usr === false ) {
                    Logger::write_log( 'DEBUG', 'Checking whether user is O365 user -> Not logged on' );
                    return self::USER_NOT_LOGGED_IN;
                }

                $email_domain = Helpers::get_smtp_domain_from_email_address( $wp_usr->user_email );

                if( Helpers::is_tenant_domain( $email_domain ) ) {
                    Logger::write_log( 'DEBUG', 'Checking whether user is O365 user -> YES' );
                    return self::IS_O365_USER;
                }

                Logger::write_log( 'DEBUG', 'Checking whether user is O365 user -> NO' );
                return self::IS_NOT_O365_USER;
            }

            /**
             * Returns true when a user is allowed to change the password
             *
             * @since   1.0
             * @return  void
             * 
             * @return boolean true when a user is allowed to change the password otherwise false
             */
            public static function show_password_fields( $show, $user ) {

                return !User_Manager::block_password_update( $user->ID );
            }

            /**
             * Returns true when a user is allowed to change the password
             * 
             * @since 1.5
             * 
             * @param boolean  $allow whether allowed or not
             * @param int      $user_id id of the user for which the action is triggered
             * 
             * @return boolean true when a user is allowed to change the password otherwise false
             */
            public static function allow_password_reset( $allow, $user_id ) {
                return !User_Manager::block_password_update( $user_id );
            }

            /**
             * Helper method to determin whether a user is allowed to change the password
             * 
             * @since 1.5
             * 
             * @param int   $user_id id of the user for which the action is triggered
             * 
             * @return boolean true when a user is not allowed to change the password otherwise false
             */
            private static function block_password_update( $user_id ) {
                $block_password_change = Options::get_global_boolean_var( 'block_password_change' );

                // Not configured or not blocked
                if( false === $block_password_change ) { // user is not logged on
                    Logger::write_log( 'DEBUG', 'Not blocking password update' );
                    return false;
                }

                // Limit the blocking of password update only for O365 users
                return User_Manager::user_is_o365_user( $user_id ) === User_Manager::IS_O365_USER ? true : false;
            }

            /**
             * Prevents users who cannot create new users to change their email address
             *
             * @since   1.0
             * @param   array   errors => Existing errors ( from Wordpress )
             * @param   bool    update => true when updating an existing user otherwise false
             * @param   WPUser  usr_new => Updated user
             * @return  void
             */
            public static function prevent_email_change( $user_id ) {

                // Don't block as per global settings configuration
                if( false === Options::get_global_boolean_var( 'block_email_change' ) 
                    || User_Manager::user_is_o365_user( $user_id ) !== User_Manager::IS_O365_USER )
                        return;

                $usr_old = get_user_by( 'ID', intval( $user_id ) );

                if( $usr_old === false )
                    return;

                // At this point the user is an O365 user and email change should be blocked as per config
                if( isset( $_POST[ 'email' ] ) && $_POST[ 'email' ] != $usr_old->user_email ) {

                    // Prevent update
                    $_POST[ 'email' ] = $usr_old->user_email;
                    
                    add_action( 'user_profile_update_errors', function( $errors ) {
                        $errors->add( 'email_update_error' ,__( 'Updating your email address is currently not allowed' ) );
                    });
                }
            }
        }
    }

?>
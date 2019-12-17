<?php

    namespace Wpo\User;
    
    // Prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Util\Helpers;
    use \Wpo\Util\Logger;
    use \Wpo\Util\Options;
    use \Wpo\User\User_Manager;
    use \Wpo\Aad\Msft_Graph;

    if( !class_exists( '\Wpo\User\User_Details' ) ) {
    
        class User_Details {

            /**
             * Gets additional user information from Microsoft Office Graph
             * 
             * @since 2.0
             * 
             * @param int $user_id identifier for the user to update
             * @return void
             */
            public static function update_user_details( $wp_usr ) {

                if( false === Options::get_global_boolean_var( 'graph_user_details' ) ) {
                    return;
                }
                
                $user_data = get_userdata( $wp_usr->ID );

                // If the user name is an email address we get the domain otherwise false
                $user_resource_id = $user_data->user_login;
                $smtp_domain = Helpers::get_smtp_domain_from_email_address( $user_resource_id );

                // User's login cannot be used to identify the user resource
                if( empty( $smtp_domain ) || !Helpers::is_tenant_domain( $smtp_domain ) ) {
                    $user_resource_id = $user_data->user_email;
                    $smtp_domain = Helpers::get_smtp_domain_from_email_address( $user_resource_id );

                    if( empty( $smtp_domain ) || !Helpers::is_tenant_domain( $smtp_domain ) ) {
                        Logger::write_log( 'DEBUG', 'Cannot retrieve O365 user resource for a user with user name / email "' . $user_resource_id . '" that is not a valid O365 user principal name.' );
                        return;
                    }
                }
                
                $user_details = Msft_Graph::fetch( '/users/' . $user_resource_id . '/', 'GET', false, array( 'Accept: application/json;odata.metadata=minimal' ) );

                if( !self::is_fetch_result_ok( $user_details ) ) {
                    return;
                }

                // Update "core" WP_User fields
                $wp_user_data = array( 'ID' => $wp_usr->ID );

                if( isset( $user_details[ 'payload' ][ 'mail' ] ) && !empty( $user_details[ 'payload' ][ 'mail' ] ) ) {
                    $wp_user_data[ 'user_email' ] = $user_details[ 'payload' ][ 'mail' ];
                }

                if( isset( $user_details[ 'payload' ][ 'givenName' ] ) && !empty( $user_details[ 'payload' ][ 'givenName' ] ) ) {
                    $wp_user_data[ 'first_name' ] = $user_details[ 'payload' ][ 'givenName' ];
                }

                if( isset( $user_details[ 'payload' ][ 'surname' ] ) && !empty( $user_details[ 'payload' ][ 'surname' ] ) ) {
                    $wp_user_data[ 'last_name' ] = $user_details[ 'payload' ][ 'surname' ];
                }

                if( isset( $user_details[ 'payload' ][ 'displayName' ] ) && !empty( $user_details[ 'payload' ][ 'displayName' ] ) ) {
                    $wp_user_data[ 'display_name' ] = $user_details[ 'payload' ][ 'displayName' ];
                }

                wp_update_user( $wp_user_data );

                // Try and retrieve the user's avatar if selected
                if( class_exists( '\Wpo\User\Avatar' ) ) {
                    $default_avatar = get_avatar( $wp_usr->ID );
                    \Wpo\User\Avatar::get_O365_avatar( $default_avatar, $wp_usr->ID, 96, true );
                }    

                // Check to see if expanded properties need to be loaded (currently only manager is supported)
                $extra_user_fields = Options::get_global_list_var( 'extra_user_fields' );
                $expanded_fields = array();

                // Iterate over the configured graph fields and identify any supported expandable properties
                array_map( function ( $kv_pair  ) use ( &$expanded_fields ) {
                    if( false !== stripos( $kv_pair[ 'key' ], 'manager' ) ) {
                        $expanded_fields[] = 'manager';
                    }
                }, $extra_user_fields );
                
                // Query to expand property
                if( in_array( 'manager', $expanded_fields ) ) {
                    $user_manager = Msft_Graph::fetch( '/users/' . $user_data->user_login . '/manager', 'GET', false, array( 'Accept: application/json;odata.metadata=minimal' ) );

                    // Expand user details
                    if( self::is_fetch_result_ok( $user_manager ) ) {
                        $user_details[ 'payload' ][ 'manager' ] = $user_manager[ 'payload' ];
                    }
                }

                self::process_extra_user_fields( function( $name, $title ) use ( &$user_details, &$wp_usr ) {

                    if( isset( $user_details[ 'payload' ][ $name ] ) && !empty( $user_details[ 'payload' ][ $name ] ) ) {
                        update_user_meta(
                            $wp_usr->ID,
                            $name,
                            $name == 'manager'
                                ? self::parse_manager_details( $user_details[ 'payload' ][ 'manager' ] )
                                : $user_details[ 'payload' ][ $name ] );
                    }
                } );
            }

            /**
             * Gets additional group membership information from Microsoft Office Graph
             * 
             * @since 4.1
             * 
             * @param int $user_id identifier for the user to update
             * @return array with group ids
             */
            public static function get_user_member_groups( $wpo_usr ) {

                $group_role_settings = Options::get_global_list_var( 'groups_x_roles' );
                
                if( sizeof( $group_role_settings ) == 0 ) {
                    Logger::write_log( 'DEBUG', 'No role mappings defined - therefore no need to retrieve user group memberships from Microsoft Graph' );
                    return $wpo_usr;
                }

                if( empty( $wpo_usr->upn ) ) {
                    Logger::write_log( 'DEBUG', 'Argument exception whilst trying to retrieve group memberships' );
                    return $wpo_usr;
                }

                $security_enabled_groups_only = false === Options::get_global_boolean_var( 'all_group_memberships' );

                $data = json_encode( array( 'securityEnabledOnly' => $security_enabled_groups_only ) );
                $content_length = strlen( $data);
                $headers = array(
                    'Accept: application/json;odata.metadata=minimal',
                    'Content-Type: application/json',                                                                                
                    'Content-Length: ' . $content_length,
                );

                $raw = Msft_Graph::fetch( '/users/' . $wpo_usr->upn . '/getMemberGroups', 'POST', false, $headers, $data );

                if( self::is_fetch_result_ok( $raw ) ) {
                        $wpo_usr->groups = array_flip( $raw[ 'payload' ][ 'value' ] );
                        return $wpo_usr;
                }

                Logger::write_log( 'ERROR', 'Could not retrieve group memberships for user with upn ' . $wpo_usr->upn );
                return $wpo_usr;
            }

            /**
             * Allow users to save their updated extra user fields
             * 
             * @since 4.0
             * 
             * @return mixed(boolean|void)
             */
            public static function save_user_details( $user_id ) {

                if ( !current_user_can( 'edit_user', $user_id ) ) {

                    return false;
                }

                self::process_extra_user_fields( function( $name, $title ) use ( &$user_id ) {

                    if ( !empty( $_POST[ $name ] ) ) {

                        update_user_meta(
                            $user_id,
                            $name,
                            sanitize_text_field( $_POST[ $name ] ) );
                        return;
                    }

                    $flipped_post = array_flip( $_POST );

                    $array_of_user_meta = array_filter( $flipped_post, function( $key ) use ( &$name ) {
                        
                        return ( false !== strpos( $key, $name . "__##__" ) );
                    } );

                    if ( false === empty( $array_of_user_meta ) ) {

                        $array_of_user_meta = array_flip( $array_of_user_meta );
                        $array_of_user_meta_values = array_values( $array_of_user_meta );
                        
                        update_user_meta(
                            $user_id,
                            $name,
                            $array_of_user_meta_values );
                        return;
                    }
                } );
            }

            /**
             * Adds an additional section to the bottom of the user profile page
             * 
             * @since 2.0
             * 
             * @param WP_User $user whose profile is being shown
             * @return void
             */
            public static function show_extra_user_fields( $user ) { 

                if ( false === Options::get_global_boolean_var( 'graph_user_details' ) ) {
                    Logger::write_log( 'DEBUG', 'Extra user fields disabled as per configuration' );
                    return;
                }
                else {

                    echo( "<h3>" . __( 'Office 365 Profile Information' ) . "</h3>" );
                    echo( "<table class=\"form-table\">" );

                    self::process_extra_user_fields( function( $name, $title ) use ( &$user ) {

                        $value = get_user_meta( $user->ID, $name, true );

                        echo ( "<tr><th><label for=\"$name\">$title</label></th>" );

                        if( is_array( $value ) ) {

                            echo( "<td>" );

                            foreach( $value as $idx => $val ) {

                                if( empty( $val ) ) {
                                    continue;
                                }

                                echo "<input type=\"text\" name=\"$name". "__##__" ."$idx\" id=\"$name$idx\" value=\"$val\" class=\"regular-text\" /><br />";
                            }

                            echo( "</td>" );
                        }
                        else {

                            echo ( "<td><input type=\"text\" name=\"$name\" id=\"$name\" value=\"$value\" class=\"regular-text\" /><br/></td>" );
                        }

                        echo( "</tr>" );
                    } );

                    echo( '</table>' );
                }
            }

            /**
             * 
             * @param function callback with signature ( $name, $title ) => void
             * 
             * @return void
             */
            public static function process_extra_user_fields( $callback ) {

                $extra_user_fields = Options::get_global_list_var( 'extra_user_fields' );

                if( sizeof( $extra_user_fields ) == 0 )
                    return;

                foreach( $extra_user_fields as $kv_pair )
                    $callback( $kv_pair[ 'key' ], $kv_pair[ 'value' ] );
            }

            /**
             * Adds an additional section to the bottom of the user profile page
             * 
             * @since 5.3
             * 
             * @param WP_User $user whose profile is being shown
             * @return void
             */
            public static function bp_show_extra_user_fields( $user ) { 

                if ( false === Options::get_global_boolean_var( 'graph_user_details' ) ) {
                    Logger::write_log( 'DEBUG', 'Extra user fields disabled as per configuration' );
                    return;
                }
                else {

                    echo( '<div class="bp-widget base">' );
                    echo( '<h3 class="screen-heading profile-group-title">' . __( 'Directory Info' ) . '</h3>' );
                    echo( '<table class="profile-fields bp-tables-user"><tbody>' );

                    self::process_extra_user_fields( function( $name, $title ) use ( &$user ) {

                        $value = get_user_meta( bp_displayed_user_id(), $name, true );
                        echo( '<tr class="field_1 field_name required-field visibility-public field_type_textbox"><td class="label">' . $title . '</td>' );
                        
                        if( is_array( $value ) ) {
                            echo( '<td class="data">' );

                            foreach( $value as $idx => $val )
                                echo( '<p>' . $val . '</p>' );

                            echo( '</td>' );
                        }
                        else
                            echo ( '<td class="data"><p>' . $value . '</p></td>' );

                        echo( "</tr>" );
                    } );
                    
                    echo( '</tbody></table></div>' );
                }
            }

            /**
             * Quick test to see if the result fetched from Microsoft Graph is valid.
             * 
             * @since 7.17
             * 
             * @param $fetch_result mixed(array|wp_error)
             * 
             * @return bool True if valid otherwise false
             */
            private static function is_fetch_result_ok( $fetch_result ) {
                if( is_wp_error( $fetch_result ) || $fetch_result[ 'response_code' ] != 200 ) {
                    Logger::write_log( 'ERROR', 'Could not retrieve O365 user details' );
                    Logger::write_log( 'DEBUG', $fetch_result );
                    return false;
                }

                if( !isset( $fetch_result[ 'payload' ] ) || !is_array( $fetch_result[ 'payload' ] ) ) {
                    Logger::write_log( 'ERROR', 'Payload with user details not found in graph response' );
                    Logger::write_log( 'DEBUG', $fetch_result );
                    return false;
                }

                return true;
            }

            /**
             * Parses the manager details fetched from Microsoft Graph.
             * 
             * @since 7.17
             * 
             * @return array Assoc. array with the most important manager details.
             */
            private static function parse_manager_details( $manager ) {
                if( empty( $manager ) ) {
                    return array();
                }
                $displayName = !empty( $manager[ 'displayName' ] )
                    ? $manager[ 'displayName' ]
                    : '';
                $mail = !empty( $manager[ 'mail' ] )
                    ? $manager[ 'mail' ]
                    : '';
                $officeLocation = !empty( $manager[ 'officeLocation' ] )
                    ? $manager[ 'officeLocation' ]
                    : '';
                $department = !empty( $manager[ 'department' ] )
                    ? $manager[ 'department' ]
                    : '';
                $businessPhones = !empty( $manager[ 'businessPhones' ] )
                    ? $manager[ 'businessPhones' ][0]
                    : '';
                $mobilePhone = !empty( $manager[ 'mobilePhone' ] ) 
                    ? $manager[ 'mobilePhone' ][0]
                    : '';
                return array(
                    'displayName' => $displayName,
                    'mail' => $mail,
                    'officeLocation' => $officeLocation,
                    'department' => $department,
                    'businessPhones' => $businessPhones,
                    'mobilePhone' => $mobilePhone,
                );
            }
        }
    }
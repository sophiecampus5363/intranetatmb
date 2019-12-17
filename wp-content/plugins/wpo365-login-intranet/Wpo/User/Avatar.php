<?php

    namespace Wpo\User;
    
    // Prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Util\Logger;
    use \Wpo\Util\Helpers;
    use \Wpo\Util\Options;
    use \Wpo\User\User_Manager;
    use \Wpo\Aad\Msft_Graph;

    if( !class_exists( '\Wpo\User\Avatar' ) ) {
    
        class Avatar {

            const USR_META_AVATAR           = 'wpo_avatar';
            const USR_META_AVATAR_UPDATED   = 'wpo_avatar_updated';

            /**
             * WordPress filter hook to replace the default WordPress avatar with the Office 365 user image. When the 
             * requested avatar is for the currently logged in user it will check to see whether the avatar was previously
             * loaded and if not or if it needs to be refreshed, it will try and get it using Microsoft Graph.
             * 
             * @since 1.0
             * 
             * @param $avatar Image tag for the user's avatar.
             * @param $id_or_email A user ID, email address, or comment object.
             * @param $size Square avatar width and height in pixels to retrieve.
             * @param $default (Optional) URL for the default image or a default type.
             * @param $alt Alternative text to use in the avatar image tag.
             * @param $args @since 4.2.0 (Optional) Extra arguments to retrieve the avatar.
             * @return string Image tag for the user's avatar
             */
            public static function get_O365_avatar( $wp_avatar, $id_or_email, $size, $for_other_user = false ) {

                // Check if O365 avatar is enabled
                if( false === Options::get_global_boolean_var( 'use_avatar' ) ) {
                    Logger::write_log( 'DEBUG', 'Avatar function not enabled so returning default WordPress' );
                    return $wp_avatar;
                }

                $wp_usr = null;

                $id_or_email = is_object( $id_or_email ) ? $id_or_email->comment_author_email : $id_or_email;

                // If provided an email and a corresponding WP_User cannot be found, return default avatar
                if( is_email( $id_or_email ) ) {

                    if ( !email_exists( $id_or_email ) ) {
                        Logger::write_log( 'DEBUG', 'Email not found therefore falling back to default avatar for ' . $id_or_email );
                        return $wp_avatar; // Avatar for "email" user requested but user "unknown"
                    }
                    else {
                        $wp_usr = get_user_by( 'email', $id_or_email ); // Avatar for "known" user by "email"
                    }
                }
                else {
                    $wp_usr = get_user_by( 'ID', $id_or_email ); // Assume we have received a user ID
                }

                // Could not resolve a WP_User  return default avatar

                if( empty( $wp_usr ) ) {
                    Logger::write_log( 'DEBUG', 'User not found therefore falling back to default avatar' );
                    return $wp_avatar;
                }

                // Try and retrieve O365 avatar from user meta

                $user_avatar = get_user_meta( $wp_usr->ID , self::USR_META_AVATAR, true );

                if( !empty( $user_avatar ) ) {
                    // If found, update the HTML string with the requested width and height
                    $user_avatar = substr_replace( $user_avatar, ' width="' . $size . '" height="' . $size . '" ', 4, 0 );
                }

                // Since avatar requested is not for current user we cannot connect to O365 to refresh it so return O365 avatar
                $wp_current_user = wp_get_current_user();
                
                if( ( $wp_usr->ID != $wp_current_user->ID && !$for_other_user ) || User_Manager::user_is_o365_user( $wp_current_user->ID, $wp_current_user->user_email ) == User_Manager::IS_NOT_O365_USER ) {
                    Logger::write_log( 'DEBUG', 'Avatar for other user or current user is not an Office 365 user therefore returning whatever available avatar' );
                    return !empty( $user_avatar ) ? $user_avatar : $wp_avatar;
                }

                // Check if avatar requires updating when current user is user for which avatar is requested

                $last_updated = intval( get_user_meta( $wp_usr->ID, self::USR_META_AVATAR_UPDATED, true ) );
                $avatar_refresh = Options::get_global_numeric_var( 'avatar_updated' );
                $avatar_refresh = empty( $avatar_refresh ) ? 1296000 : $avatar_refresh;

                if( !empty( $user_avatar ) && time() - $last_updated < $avatar_refresh ) {
                    Logger::write_log( 'DEBUG', 'Returning cached O365 avatar' );
                    return $user_avatar;
                }

                // At this point requested avatar 
                // 1. Either does not exist
                // 2. Or should be refreshed

                // If the user name is an email address we get the domain otherwise false
                $user_resource_id = $wp_usr->user_login;
                $smtp_domain = Helpers::get_smtp_domain_from_email_address( $user_resource_id );

                // User's login cannot be used to identify the user resource
                if( empty( $smtp_domain ) || !Helpers::is_tenant_domain( $smtp_domain ) ) {
                    $user_resource_id = $wp_usr->user_email;
                    $smtp_domain = Helpers::get_smtp_domain_from_email_address( $user_resource_id );

                    if( empty( $smtp_domain ) || !Helpers::is_tenant_domain( $smtp_domain ) ) {
                        Logger::write_log( 'DEBUG', 'Cannot determine the user resource identifier to obtain a user photo from Microsoft Graph.' );
                        return $wp_avatar;
                    }
                }

                /** 
                 * The beta endpoint will return the profile picture from Exchange OR AAD 
                 * whereas the v1.0 only takes the profile picture from Exchange.
                 */

                $graph_version = Options::get_global_string_var( 'graph_version' );

                if( $graph_version != 'beta' ) {
                    $GLOBALS[ 'wpo365_options' ][ 'graph_version' ] = 'beta';
                }
                    
                $raw = Msft_Graph::fetch( '/users/' . $user_resource_id . '/photo/$value', 'GET', true, array( 'Accept: application/json;odata.metadata=minimal' ) );

                $GLOBALS[ 'wpo365_options' ][ 'graph_version' ] = $graph_version;

                // Take the default WordPress avatar because something went wrong
                if( is_wp_error( $raw ) 
                    || $raw === false 
                    || $raw[ 'response_code' ] != 200 ) {

                        if( !empty( $user_avatar ) ) {
                            delete_user_meta(
                                $wp_usr->ID,
                                self::USR_META_AVATAR
                            );
                            delete_user_meta(
                                $wp_usr->ID,
                                self::USR_META_AVATAR_UPDATED
                            );
                        }
                        
                        Logger::write_log( 'DEBUG', 'Could not retrieve O365 avatar therefore returning default avatar' );
                        Logger::write_log( 'DEBUG', $raw );

                        return $wp_avatar;  
                }

                $user_avatar = '<img src="data:image/jpg;base64,' . base64_encode( $raw[ 'payload' ] ) . '" class="ui avatar image">';

                update_user_meta(
                    $wp_usr->ID,
                    self::USR_META_AVATAR,
                    $user_avatar );
                update_user_meta(
                    $wp_usr->ID,
                    self::USR_META_AVATAR_UPDATED,
                    time() );

                Logger::write_log( 'DEBUG', 'Returning fresh O365 avatar' );
                return substr_replace( $user_avatar, ' width="' . $size . '" height="' . $size . '" ', 4, 0 );
            }

            /**
             * Helper method that returns the O365 avatar for Buddy Press.
             * 
             * @since 9.0
             * 
             * @param $avatar Image tag for the user's avatar.
             * @return string Image tag for the user's avatar possibly with img URL replaced with O365 profile image URL.
             */
            public static function fetch_buddy_press_avatar( $bp_avatar, $params ) {

                if( false === Options::get_global_boolean_var( 'use_bp_avatar' ) ) {
                    return $bp_avatar;
                }

                if( !is_array( $params ) || !isset( $params[ 'item_id' ] ) ) {
                    return $bp_avatar;
                }

                $o365_avatar_url = self::get_o365_avatar_url( $params[ 'item_id' ] );

                return empty( $o365_avatar_url )
                    ? $bp_avatar
                    : \preg_replace( '/src=".+?"/', 'src="' . $o365_avatar_url . '"', $bp_avatar );
            }

            /**
             * Helper method to get the O365 profile image URL.
             * 
             * @since 9.0
             * 
             * @return string O365 profile image URL otherwise empty.
             */
            private static function get_o365_avatar_url( $wp_usr_id ) {
                $o365_avatar = get_user_meta( ( intval( $wp_usr_id ) ) , self::USR_META_AVATAR, true );

                if( empty( $o365_avatar ) ) {
                    return '';
                }

                $matches = array();
                preg_match('/src=".+?"/', $o365_avatar, $matches);
                
                return sizeof( $matches ) > 0 && stripos( $matches[0], 'data:image' ) !== false
                    ? substr( $matches[0], 5, ( strlen( $matches[0] ) - 6 ) )
                    : '';
            }
        }
    }
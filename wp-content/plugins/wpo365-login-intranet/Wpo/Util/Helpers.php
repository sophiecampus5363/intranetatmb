<?php

    namespace Wpo\Util;

    // Prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Util\Logger;
    use \Wpo\Util\License;
    use \Wpo\Util\Options;
    use \Wpo\Aad\Auth;
    use \Wpo\Util\Error_Handler;

    if( !class_exists( '\Wpo\Util\Helpers' ) ) {

        class Helpers {

            /**
             * @since 9.2
             * 
             * enumeration of possible multisite configs
             */
            const WPMU_NOT_CONFIGURED = 1;
            const WPMU_SHARED = 2;
            const WPMU_NOT_SHARED = 3;

            /**
             * Helper to determine whether WordPress has been configured as
             * multisite and if yes whether it is configured to use a 
             * separate WPO365 config per subsite.
             * 
             * @since 9.2
             * 
             * @return int One of the class const WPMU_NOT_CONFIGURED, WPMU_SHARED or WPMU_NOT_SHARED
             */
            public static function get_type_of_multisite() {
                
                if( is_multisite() ) {
                    if( Options::mu_use_subsite_options() ) {
                        return self::WPMU_NOT_SHARED;
                    }
                    return self::WPMU_SHARED;
                }

                return self::WPMU_NOT_CONFIGURED;
            }

            /**
             * Helper to get the global or local transient based on the
             * WPMU configuration.
             * 
             * @since 9.2
             * 
             * @return mixed Returns the value of transient or false if not found
             */
            public static function mu_get_transient( $name ) {
                $type_of_multisite = self::get_type_of_multisite();

                return $type_of_multisite === self::WPMU_NOT_CONFIGURED 
                    || $type_of_multisite === self::WPMU_NOT_SHARED
                        ? get_transient( $name )
                        : get_site_transient( $name );
            }

            /**
             * Helper to set the global or local transient based on the
             * WPMU configuration.
             * 
             * @since 9.2
             * 
             * @param $name string Name of transient
             * @param $value mixed Value of transient
             * @param $duration int Time transient should be cached in seconds
             * 
             * @return void
             */
            public static function mu_set_transient( $name, $value, $duration = 21600 ) {
                $type_of_multisite = self::get_type_of_multisite();

                if( $type_of_multisite === self::WPMU_NOT_CONFIGURED 
                    || $type_of_multisite === self::WPMU_NOT_SHARED) {
                        set_transient( $name, $value, $duration );
                    }
                    else {
                        set_site_transient( $name, $value, $duration );
                    }
            }

            /**
             * Checks whether headers are sent before trying to redirect and if sent falls
             * back to an alternative method
             * 
             * @since 4.3
             * 
             * @param string $url URL to redirect to
             * @return void
             */
            public static function force_redirect( $url ) {

                $url = self::ensure_trailing_slash_url( $url );

                if( headers_sent() ) {
                    Logger::write_log( 'DEBUG', 'Headers sent when trying to redirect user to ' . $url );
                    echo '<script type="text/javascript">';
                    echo 'window.location.href="'. $url . '";';
                    echo '</script>';
                    echo '<noscript>';
                    echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
                    echo '</noscript>';                    
                    exit();
                }

                wp_redirect( $url );
                exit();
            }

            /**
             * Helper method to ensure that short codes are initialized
             * 
             * @since 7.0
             * 
             * @return void
             */
            public static function ensure_pintra_short_code() {

                if( !shortcode_exists( 'pintra' ) )
                    add_shortcode( 'pintra', '\Wpo\Util\Helpers::add_pintra_shortcode' );
            }

            /**
             * Helper method to ensure that short codes are initialized
             * 
             * @since 7.0
             * 
             * @return void
             */
            public static function ensure_login_button_short_code() {

                if( !shortcode_exists( 'wpo365-sign-in-with-microsoft-sc' ) )
                    add_shortcode( 'wpo365-sign-in-with-microsoft-sc', '\Wpo\Util\Helpers::add_sign_in_with_microsoft_shortcode' );
                
            }

            /**
             * Helper method to ensure that short codes are initialized
             * 
             * @since 8.0
             * 
             * @return void
             */
            public static function ensure_login_button_short_code_V2() {

                if( !shortcode_exists( 'wpo365-sign-in-with-microsoft-v2-sc' ) )
                    add_shortcode( 'wpo365-sign-in-with-microsoft-v2-sc', '\Wpo\Util\Helpers::add_sign_in_with_microsoft_shortcode_V2' );
                
            }

            /**
             * Helper method to ensure that short code for displaying errors is initialized
             * 
             * @since 7.8
             */
            public static function ensure_display_error_message_short_code() {

                if( !shortcode_exists( 'wpo365-display-error-message-sc' ) )
                    add_shortcode( 'wpo365-display-error-message-sc', '\Wpo\Util\Helpers::add_display_error_message_shortcode' );
            }

            /**
             * Adds the Sign in with Microsoft short code 
             * 
             * @deprecated since 8.0 => use self::add_sign_in_with_microsoft_shortcode_V2 instead.
             * 
             * @since 4.0
             * 
             * @param array short code parameters according to Wordpress codex
             * @param string content found in between the short code start and end tag
             * @param string text domain
             */
            public static function add_sign_in_with_microsoft_shortcode( $params = array(), $content = null, $tag = '' ) {
                $oauth_url = Auth::get_oauth_url();
                $dom = new \DOMDocument();
                $dom->loadHTML( $content );
                $script = $dom->getElementsByTagName( 'script' );
                $remove = array();

                foreach( $script as $item )
                    $remove[] = $item;
                
                foreach( $remove as $item )
                    $item->parentNode->removeChild( $item );
                
                $content = $dom->saveHTML();
                $content = str_replace( "__##OAUTH_URL##__", $oauth_url, $content );
                $content = str_replace( "__##PLUGIN_BASE_URLL##__", $GLOBALS[ 'WPO365_PLUGIN_URL' ], $content );
                
                return $content;
            }

            /**
             * Adds the Sign in with Microsoft short code V2
             * 
             * @since 8.0
             * 
             * @param array short code parameters according to Wordpress codex
             * @param string content found in between the short code start and end tag
             * @param string text domain
             */
            public static function add_sign_in_with_microsoft_shortcode_V2( $params = array(), $content = null, $tag = '' ) {
                
                if( empty( $content ) ) {
                    return $content;
                }

                $site_url = $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_url' ];
                
                // Load the js dependency
                ob_start();
                include( $GLOBALS[ 'WPO365_PLUGIN_DIR' ] . '/templates/openid-ssolink.php' );
                $js_lib = ob_get_clean();
                
                // Sanitize the HTML template
                $dom = new \DOMDocument();
                $dom->loadHTML( $content );
                $script = $dom->getElementsByTagName( 'script' );
                $remove = array();

                foreach( $script as $item )
                    $remove[] = $item;
                
                foreach( $remove as $item )
                    $item->parentNode->removeChild( $item );
                
                // Concatenate the two
                $output = $js_lib . $dom->saveHTML();
                return str_replace( "__##PLUGIN_BASE_URL##__", $GLOBALS[ 'WPO365_PLUGIN_URL' ], $output );
            }

            /**
             * Adds a pintra app launcher into the page 
             * 
             * @since 5.0
             * 
             * @param array short code parameters according to Wordpress codex
             * @param string content found in between the short code start and end tag
             * @param string text domain
             */
            public static function add_pintra_shortcode( $atts = array(), $content = null, $tag = '' ) {
                $atts = array_change_key_case( (array)$atts, CASE_LOWER);
                $props = '[]';
                
                if( isset( $atts[ 'props' ] ) 
                    && strlen( trim( $atts[ 'props' ] ) ) > 0 ) {
                        $result = array();
                        $prop_kv_pairs = explode( ';', $atts[ 'props' ] );
                        
                        foreach( $prop_kv_pairs as  $prop_kv_pair ) {
                            $prop_kv_array = explode( ',', $prop_kv_pair );
                            
                            if( sizeof( $prop_kv_array ) == 2)
                                $result[ $prop_kv_array[0] ] = addslashes( utf8_encode( $prop_kv_array[1] ) );
                        }
                        $props = json_encode( $result );
                }

                $script_url = isset( $atts[ 'script_url' ] ) ? $atts[ 'script_url' ] : '';

                ob_start();
                include( $GLOBALS[ 'WPO365_PLUGIN_DIR' ] . '/templates/pintra.php' );
                $content = ob_get_clean();
                return $content;
            }

            /**
             * Adds the error message encapsulated in a div into the page 
             * 
             * @since 7.8
             * 
             * @param array short code parameters according to Wordpress codex
             * @param string content found in between the short code start and end tag
             * @param string text domain
             */
            public static function add_display_error_message_shortcode( $atts = array(), $content = null, $tag = '' ) {
                
                $error_code = isset( $_GET[ 'login_errors' ] ) 
                    ? $_GET[ 'login_errors' ]
                    : '';

                $error_message = Error_Handler::get_error_message( $error_code );

                if( empty( $error_message ) ) {
                    return;
                }

                ob_start();
                include( $GLOBALS[ 'WPO365_PLUGIN_DIR' ] . '/templates/error-message.php' );
                $content = ob_get_clean();
                return $content;
            }

            /**
             * Inspects the array provided whether tenant, application and redirect url have been
             * specified.
             * 
             * @since 7.3
             * 
             * @return boolean True if wpo365 is configured otherwise false
             */
            public static function is_wpo365_configured() {
                // Make sure the options are cached
                Options::ensure_options_cache();
                $options = $GLOBALS[ 'wpo365_options' ];

                $tentant_id_ok = !empty( $options[ 'tenant_id' ] );

                if( !$tentant_id_ok ) {
                    Logger::write_log( 'ERROR', 'WPO365 is not configured -> Tenant ID is missing.' );
                }

                $application_id_ok = !empty( $options[ 'application_id' ] );

                if( !$application_id_ok ) {
                    Logger::write_log( 'ERROR', 'WPO365 is not configured -> Application ID is missing.' );
                }

                $redirect_url_ok = !empty( $options[ 'redirect_url' ] );

                if( !$redirect_url_ok ) {
                    Logger::write_log( 'ERROR', 'WPO365 is not configured -> Redirect URL is missing.' );
                }

                return $tentant_id_ok 
                    && $application_id_ok 
                    && $redirect_url_ok;
            }

            /**
             * Gets the domain (host) part of an email address.
             * 
             * @since 3.1
             * 
             * @param   string  $email_address  email address to analyze
             * @return  string  Returns the email address' host part or an empty string if
             *                  the email address appears to be invalid
             */
            public static function get_smtp_domain_from_email_address( $email_address ) {
                $smpt_domain = '';
                if( filter_var( trim( $email_address ), FILTER_VALIDATE_EMAIL ) !== false )
                    $smpt_domain = strtolower( trim( substr( $email_address, strrpos( $email_address, '@' )  + 1 ) ) );

                return $smpt_domain;
            }

            /**
             * Checks a user's smtp domain against the configured custom and default domains
             * 
             * @since 4.0
             * 
             * @return boolean true if a match is found otherwise false
             */
            public static function is_tenant_domain( $email_domain ) {
                $custom_domain = array_flip( Options::get_global_list_var( 'custom_domain' ) );
                $default_domain = Options::get_global_string_var( 'default_domain' );

                if( array_key_exists( $email_domain, $custom_domain )
                    || strtolower( trim( $default_domain ) ) == $email_domain )
                        return true;
                
                return false;
            }

            /**
             * Will check whether request is for WP REST API and if yes
             * if a basic authentication header is present (without proofing it).
             * 
             * @since 7.12
             * 
             * @return boolean true if found, otherwise false
             */
            public static function is_basic_auth_api_request() {
                if( false === self::is_wp_rest_api() ) {
                    return false;
                }

                $headers = getallheaders();
                $headers_to_lower = array_change_key_case( $headers, CASE_LOWER );
                
                if( isset( $headers_to_lower[ 'authorization' ] ) 
                    && stripos( $headers_to_lower[ 'authorization' ], 'basic' ) === 0 ) {
                    
                        return true;
                }

                return false;
            }

            /**
             * @since 7.12
             */
            public static function user_is_admin( $user ) {

                if( $user instanceof \WP_User ) {
                    return \in_array( 'administrator', $user->roles ) || is_super_admin( $user->ID ) ;
                }
                
                return false;
            }

            /**
             * Creates a nonce using the nonce_secret
             * 
             * @since 1.6
             * 
             * @return (string|WP_Error) nonce as a string otherwise an WP_Error (most likely when dependency are missing)
             */
            public static function get_nonce() {

                $nonce_value = uniqid( '', true );
                $nonce_expiry = time() + 21600; // 60 * 60 * 6 = 6 hours
                
                $nonce = base64_encode(
                    json_encode(
                        array(
                            'nonce'   => $nonce_value,
                            'expires' => $nonce_expiry,
                        )
                    )
                );

                Logger::write_log( 'DEBUG', 'Generated nonce: ' . $nonce );

                $nonces = self::mu_get_transient( 'wpo365_nonces' );

                if( !empty( $nonces ) && is_array( $nonces ) ) {
                    // Drop the last "unused" nonces
                    $nonces_count = count( $nonces );

                    Logger::write_log( 'DEBUG', 'Nonce count: ' . $nonces_count );

                    if( $nonces_count > 1000 ) {
                        $nonces = array_slice( $nonces, ( 700 ) );
                    }
                    $nonces[] = $nonce;
                }
                else {
                    $nonces = array( $nonce );
                }

                self::mu_set_transient( 'wpo365_nonces', $nonces, 21600 );
                
                return $nonce;
            }

            /**
             * Validates a nonce created with Helpers::get_nonce()
             * 
             * @since 1.6
             * 
             * @param string $nonce encoded nonce value to validate
             * @return (boolean|WP_Error) true when valide otherwise WP_Error
             */
            public static function validate_nonce( $nonce ) {

                // Skip validation of the nonce
                if( true === Options::get_global_boolean_var( 'skip_nonce_verification' ) ) {
                    Logger::write_log( 'DEBUG', 'Nonce check has been disabled by the user' );
                    return true;
                }

                $nonces = self::mu_get_transient( 'wpo365_nonces' );

                if( !empty( $nonces ) && is_array( $nonces ) ) {
                    $index = array_search( $nonce, $nonces );
                    
                    if( false !== $index ) {

                        // Pull the nonce from the array (by value)
                        $decoded64 = base64_decode( $nonces[ $index ] );

                        // Delete the nonce from the array and update it
                        array_splice( $nonces, $index, 1 );

                        self::mu_set_transient( 'wpo365_nonces', $nonces, 21600 );

                        // Now validate it
                        if ($decoded64 === false) {
                            Logger::write_log( 'ERROR', 'Login has been tampered with [decoding failed]' );
                            return new \WP_Error( '5010', 'Your login has been tampered with [decoding failed]' );
                        }

                        $decoded_nonce = json_decode( $decoded64 );

                        Logger::write_log( 'DEBUG', 'Decoded nonce: ' );
                        Logger::write_log( 'DEBUG', $decoded_nonce );

                        if( !isset( $decoded_nonce->nonce ) || !isset( $decoded_nonce->expires ) ) {
                            Logger::write_log( 'ERROR', 'Login has been tampered with [incomplete nonce]' );
                            return new \WP_Error( '5020', 'Your login has been tampered with [incomplete nonce]' );
                        }

                        if( time() > intval( $decoded_nonce->expires ) ) {
                            Logger::write_log( 'ERROR', 'Login has been tampered with [nonce expired]' );
                            return new \WP_Error( '5030', 'Your login has been tampered with [nonce expired]' );
                        }

                        return true;
                    }
                }

                Logger::write_log( 'ERROR', 'Login has been tampered with [nonce not found]' );
                return new \WP_Error( '5040', 'Nonce not found' );
            }

            /**
             * Helper method to determine the redirect URL which can either be the last page
             * the user visited before authentication stored in the posted state property, or
             * if configured the goto_after_signon_url or in case none of these apply the WordPress
             * home URL. This method can be called from the wpo_redirect_url filter.
             * 
             * @since 7.1
             * 
             * @return string URL to send the user once authentication completed
             */
            public static function get_redirect_url( $site_url ) {
                $goto_after_signon_url = Options::get_global_string_var( 'goto_after_signon_url' );

                // In case the always use goto after was configured
                if( true === Options::get_global_boolean_var( 'always_use_goto_after' ) ) {
                    
                    if( !empty( $goto_after_signon_url ) )
                        return $goto_after_signon_url;
                }

                // Initially set to state but make sure it's not the login URL and if it is then 
                // take the goto_after_signon_url if configured at all
                if( isset( $_POST[ 'state' ] ) ) {
                    $state_url = trim( $_POST[ 'state' ] );
                    $redirect_url = false === Helpers::is_wp_login( $state_url ) 
                        ? $state_url
                        : (!empty( $goto_after_signon_url ) 
                        ? $goto_after_signon_url
                        : $site_url);
                }
                else {
                    $redirect_url = $site_url;
                }
                
                return $redirect_url;
            }

            /**
             * Get's WordPress default (and possibly custom) login URLs.
             * 
             * @since 7.17
             * 
             * @return array Assoc. array with custom login url (possibly empty string) and default login url. 
             */
            public static function get_login_urls() {
                $default_login_url = wp_login_url();
                $custom_login_url = Options::get_global_string_var( 'custom_login_url' );
                
                // Custom login url must be an absolute URL
                if( stripos( $custom_login_url, 'http' ) !== 0 ) {

                    return array( 
                        'custom_login_url' => '',
                        'default_login_url' => $default_login_url,
                    );
                }

                // Custom login url should not accept a query string
                if( false !== stripos( $custom_login_url, '?' ) ) {
                    $custom_login_url_arr = split( $custom_login_url, '?' );
                    $custom_login_url = $custom_login_url_arr[0];
                }

                // Custom login url should not accept a hash
                if( false !== stripos( $custom_login_url, '#' ) ) {
                    $custom_login_url_arr = split( $custom_login_url, '#' );
                    $custom_login_url = $custom_login_url_arr[0];
                }

                $custom_login_url = self::ensure_trailing_slash_url( $custom_login_url );

                return array( 
                    'custom_login_url' => $custom_login_url,
                    'default_login_url' => $default_login_url,
                );
            }

            /**
             * Gets the custom login url if configured and otherwise the default login URL is returned.
             * 
             * @since 7.17
             * 
             * @return string Returns custom login url if configured and otherwise the default login URL.
             */
            public static function get_preferred_login_url() {
                $login_urls = self::get_login_urls();
                
                return !empty( $login_urls[ 'custom_login_url' ] ) 
                    ? $login_urls[ 'custom_login_url' ]
                    : $login_urls[ 'default_login_url' ];
            }

            /**
             * WPMU aware wp filter extension to show the action link on the plugins page. Will add 
             * the wpo365 configuration action link (for wpmu depending on the global constant 
             * WPO_MU_USE_SUBSITE_OPTIONS )
             * 
             * @since 7.3
             * 
             * @param Array $links The current action link collection
             * 
             * @return Array The new action link collection
             */
            public static function get_configuration_action_link( $links ) {
                // Don't show the configuration link for subsite admin if subsite options shouldn't be used
                if( is_multisite() && !is_network_admin() && false === Options::mu_use_subsite_options() )
                    return $links;
                
                // Don't show the configuration link for main site admin if subsite options should be used
                if( is_network_admin() && true === Options::mu_use_subsite_options() )
                    return $links;
                
                $wizard_link = '<a href="admin.php?page=wpo365-wizard">' . __( 'Configuration' ) . '</a>';
                array_push( $links, $wizard_link );
                
                return $links;
            }

            /**
             * Takes a one dimensional set of results and transforms this into 
             * a mulit dimensional array of rows with a max size equal to $cols
             *
             * @since 2.0
             * 
             * @param   array   $results    one dimensional array which items will be rowified
             * @param   int     $cols       Number of items per row in the resulting array ( when zero all items are placed in a single row )
             * @return  array   Multi dimensional array containing rows of items where max size of a row equals $cols
             */
            public static function rowify_results( $results, $cols ) {
            
                Logger::write_log( 'DEBUG', 'Nr. of results: ' . sizeof( $results ) );
                
                if( !is_array( $results ) ) {
                    return array();
                }
            
                $rowified = array();
                $row = array();
                
                for( $i = 0; $i < sizeof( $results ); $i++ ) {

                    // In case of 0 cols are results are placed in one single row
                    if( $cols == 0 ) {

                        array_push( $row, $results[$i] );
                        continue;
                    }
            
                    if( sizeof( $row ) == $cols ) {
            
                        array_push( $rowified, $row );
                        $row = array();
            
                        Logger::write_log( 'DEBUG', 'Pushed row in to overall result' );
                    }
            
                    array_push( $row, $results[$i] );
                    Logger::write_log( 'DEBUG', 'Pushed item into a row' );
                }
            
                // push the last row
                if( sizeof( $row ) > 0 ) {

                    array_push( $rowified, $row );
                }
            
                return $rowified;
            }

            /**
             * Parses a query string into an associative array
             *
             * @since   2.0
             *
             * @param   string  $str    Query string thus everthing that follows the '?'
             * @return  associative array that may be empty if something went wrong
             */
            public static function parse_query_str( $str ) {

                // Return empty array in case of no query string
                if( empty( $str ) ) {

                    return array();
                }
                
                // Result array
                $arr = array();
            
                // split on outer delimiter
                $pairs = explode( '&', $str );
            
                // loop through each pair
                foreach ( $pairs as $i ) {

                    // split into name and value
                    list( $name,$value ) = explode( '=', $i );
            
                    // if name already exists
                    if( isset( $arr[$name] ) ) {

                        // stick multiple values into an array
                        if( is_array( $arr[$name] ) ) {

                            $arr[$name][] = $value;
                        }
                        else {

                            $arr[$name] = array( $arr[$name], $value );
                        }
                    }
                    // Otherwise, simply stick it in a scalar
                    else {

                        $arr[$name] = $value;
                    }
                }
                // return result array
                return $arr;
            }

            /**
             * Removes query string from string ( there may be an incompatibility with URL rewrite )
             *
             * @since 2.0
             *
             * @return  Current URL as string without query string
             */
            public static function reconstruct_url() {
                
                $reconstructed_url = ( isset( $_SERVER['HTTPS'] ) ? 'https' : 'http' ) . '://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]';
                $pos = strpos( $reconstructed_url, '?' );

                // Remove query string if found
                if( $pos !== false ) {

                    $reconstructed_url = substr( $reconstructed_url, 0, $pos );
                }
            
                return $reconstructed_url;
            }

            /**
             * Analyzes url and tries to discover site details for both single and multisite WP networks
             *
             * @since   3.0
             * @param   string    target $url of the site to analyze (can point to a post, a subsite etc.)
             * @return  array     associative array with blog id, site url, network url, multisite y/n etc.
             */
            public static function target_site_info( $url ) {

                // Ensure url starts with protocol
                if( stripos( $url, 'http' ) !== 0 ) {

                    return null;
                }

                // Multisite has dependencies that are only loaded when needed
                if( is_multisite() ) {
                    
                    return self::ms_target_site_info( $url );
                }

                // Not multisite
                $site_url = site_url();

                $segments = explode( '/', $site_url );
                $nr_of_segments = sizeof( $segments );

                $protocol = str_replace( ':', '', $segments[ 0 ] );
                $domain = $segments[ 2 ];
                $path = $nr_of_segments == 3 ? '/' : ('/' . implode( '/', array_slice( $segments, 3, $nr_of_segments - 3 ) ) . '/' );
                $blogid = get_current_blog_id(); // may be 0 for main site but this may not be target site 
                
                return array(

                    'blog_id'                    => $blogid, // always 1
                    'protocol'                   => $protocol,
                    'domain'                     => $domain,
                    'path'                       => $path,
                    'is_multi'                   => false,
                    'target_site_url'            => $site_url,
                    'network_site_url'           => $site_url,
                    'target_is_network_site'     => true,
                    'subdomain_install'          => false,
                );
            }

            /**
             * Analyzes url and tries to discover site details for both single and multisite WP networks (private, use
             * target_site_info() instead)
             *
             * @since   3.0
             * @param   string    target $url of the site to analyze (can point to a post, a subsite etc.)
             * @return  array     associative array with blog id, site url, network url, multisite y/n etc.
             */
            private static function ms_target_site_info( $url ) {

                $network_site_url = network_site_url(); // if not multisite site_url() is used

                $segments = explode( '/', $url );
                $nr_of_segments = sizeof( $segments );

                if( $nr_of_segments < 3 ) {

                    return false;                    
                }

                $protocol = str_replace( ':', '', $segments[ 0 ] );
                $domain = $segments[ 2 ];
                $path = '/';
                $blogid = get_blog_id_from_url( $domain ); // may be 0 for main site but this may not be target site
                $subdomain_install = get_site_option( 'subdomain_install' );

                if( $nr_of_segments > 3 ) {

                    for( $i = 3; $i < $nr_of_segments; $i++ ) {

                        $path_test = '/' . implode( '/', array_slice( $segments, 3, $nr_of_segments - $i ) ) . '/';
                        $blog_id_test = get_blog_id_from_url( $domain, $path_test );
                        
                        if( $blog_id_test > 0 ) {

                            $blogid = $blog_id_test;
                            $path = $path_test;
                            break;
                        }
                    }
                }
                
                return array(

                    'blog_id'                    => $blogid,
                    'protocol'                   => $protocol,
                    'domain'                     => $domain,
                    'path'                       => $path,
                    'is_multi'                   => true,
                    'target_site_url'            => ( $protocol . '://' . $domain . $path ),
                    'network_site_url'           => $network_site_url,
                    'target_is_network_site'     => ( $protocol . '://' . $domain . $path == $network_site_url ),
                    'subdomain_install'          => $subdomain_install,
                );
            }

            /**
             * Adds custom wp query vars
             * 
             * @since 3.6
             * 
             * @param Array $vars existing wp query vars
             * @return Array updated $vars that now includes custom wp query vars
             */
            public static function add_query_vars_filter( $vars ) {

                $vars[] = 'login_errors';
                $vars[] = 'stnu'; // show table new users
                $vars[] = 'stne'; // show table existing users
                $vars[] = 'stou'; // show table old users
                $vars[] = 'sjs';  // sync job status
                $vars[] = 'redirect_to';  // redirect to after successfull authentication
                return $vars;
            }

            /**
             * Removes query string from string ( there may be an incompatibility with URL rewrite )
             *
             * @since 3.0
             *
             * @return  Current URL as string without query string
             */
            public static function check_version() {

                // Get plugin version from db
                $plugin_db_version = get_site_option( $GLOBALS[ 'WPO365_VERSION_KEY' ] );

                // Add new option if not yet existing
                if( false === $plugin_db_version ) {
                    update_site_option( $GLOBALS[ 'WPO365_VERSION_KEY' ], $GLOBALS[ $GLOBALS[ 'WPO365_VERSION_KEY' ] ] );
                    self::track( 'install' );
                    return;

                }
                // Compare plugin version with db version and track in case of update
                elseif( $plugin_db_version != $GLOBALS[ $GLOBALS[ 'WPO365_VERSION_KEY' ] ] ) {
                    update_site_option( $GLOBALS[ 'WPO365_VERSION_KEY' ], $GLOBALS[ $GLOBALS[ 'WPO365_VERSION_KEY' ] ] );
                    self::track( 'update' );
                }
            }

            /**
             * Removes query string from string ( there may be an incompatibility with URL rewrite )
             *
             * @since 3.0
             *
             * @param   string  Name of event to track (default is install)
             * @return  Current URL as string without query string
             */
            public static function track( $event ) {
                $plugin_version = $GLOBALS[ $GLOBALS[ 'WPO365_VERSION_KEY' ] ];
                $event .= $GLOBALS[ 'WPO365_VERSION_KEY' ];

                $ga = "https://www.google-analytics.com/collect?v=1&tid=UA-5623266-11&aip=1&cid=bb923bfc-cae8-11e7-abc4-cec278b6b50a&t=event&ec=alm&ea=$event&el=$plugin_version";

                $curl = curl_init();

                curl_setopt( $curl, CURLOPT_URL, $ga );
                curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );

                curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); 
                curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 ); 
                
                $result = curl_exec( $curl ); // result holds the keys
                if( curl_error( $curl ) ) {
                    
                    // TODO handle error
                    Logger::write_log( 'ERROR', 'error occured whilst tracking an alm event: ' . curl_error( $curl ) );

                }
                curl_close( $curl );
            }

            /**
             * Check for newer versions of the plugin
             * 
             * @since 2.3
             * 
             * Changed to EDD plugin updater since 7.15
             * 
             * @return void
             */
            public static function check_for_updates() {

                // setup the updater
                $edd_updater = new EDD_SL_Plugin_Updater( 
                    $GLOBALS[ 'WPO365_STORE' ],
                    $GLOBALS[ 'WPO365_PLUGIN_FILE' ],
                    array(
                        'version' => $GLOBALS[ $GLOBALS[ 'WPO365_VERSION_KEY' ] ],      // current version number
                        'license' => Options::get_global_string_var( 'license_key' ),   // license key (used get_option above to retrieve from DB)
                        'item_id' => $GLOBALS[ 'WPO365_STORE_ITEM_ID' ],                // ID of the product
                        'author'  => 'marco@wpo365.com',                                // author of this plugin
                        'beta'    => false,
                    )
                );
            }

            /**
             * Shows admin notices when the plugin is not configured correctly
             * 
             * @since 2.3
             * 
             * @return void
             */
            public static function show_admin_notices( ) {

                if( !is_admin() && !is_network_admin() )        
                    return;

                if( false === self::is_wpo365_configured( ) )
                    echo '<div class="notice notice-error" style="margin-left: 2px;"><p>Click <strong><a href="' . get_admin_url() . ( is_network_admin() ? 'network/admin.php' : 'admin.php' ) . '?page=wpo365-wizard">here</a></strong> to configure WordPress + Office 365 Single Sign-on. The configuration must - at the very least - provide a valid <strong>Directory (tenant) ID</strong>, <strong>Application ID</strong> and a so-called <strong>Redirect URI</strong>. Please review <a target="_blank" href="https://www.wpo365.com/azure-application-registration/">this article</a> for details.</p></div>';
                
                if( is_super_admin() ) {

                    if ( false === Options::get_global_boolean_var( 'hide_error_notice' ) 
                        && false !== get_transient( 'wpo365_has_errors' ) 
                        && false === get_transient( 'wpo365_has_errors_dismissed' ) )  {
                            echo '<div class="notice notice-error" style="margin-left: 2px;"><p>The <strong>WordPress + Office 365</strong> log contains errors that you should address. Please take the time to review those errors (see WP Admin > WPO365 > Debug). Once errors have been addressed you can safely <a href="./?wpo365_has_errors_dismissed">dismiss</a> this notice for now or check <strong>Hide error notice</strong> on the <strong>Debug</strong> tab of the <strong>WPO365</strong> wizard to hide this notice permanently. If this message re-appears a few days after you dismissed it, new errors have been encountered.</p></div>';
                    }

                    if( is_multisite() && !defined( 'WPO_MU_USE_SUBSITE_OPTIONS' ) ) {
                        echo '<div class="notice notice-warning" style="margin-left: 2px;"><p>You can add <strong>define( \'WPO_MU_USE_SUBSITE_OPTIONS\', false );</strong> to your wp-config.php file to force all subsites to authenticate their users via the main site and hide the WPO365 configuration link on all subsites. Vice versa you can change its value to <strong>true</strong> and configure Single Sign-on for each subsite separately.</p></div>';
                    }

                    if( $GLOBALS[ 'WPO365_VERSION_KEY' ] == 'PLUGIN_VERSION_wpo365_login' 
                        && true === Helpers::is_wpo365_configured()
                        && isset( $_GET[ 'page' ] )
                        && $_GET[ 'page' ] === 'wpo365-wizard'
                        && false === get_transient( 'wpo365_upgrade_dismissed' ) ) {
                            echo '<div class="notice notice-info" style="margin-left: 2px;"><p>Thank you for using <strong>WordPress + Office 365</strong>. I would be incredibly grateful if you could take a couple of minutes to write a quick WordPress review! To submit your review, simply <a target="_blank" href="https://wordpress.org/plugins/wpo365-login/#reviews">click this link</a>. Your feedback is highly appreciated and important to me as well as others looking for the right plugin to integrate <strong>WordPress + Office 365</strong>. For a limited period of time you can save 10% off the purchase of a <a target="_blank" href="https://www.wpo365.com/?promo=v718">PROFESSIONAL or PREMIUM edition</a>. To claim your discount, enter <strong>UPGRADE2019</strong> in the discount field on checkout. You can <a href="./?wpo365_upgrade_dismissed">dismiss</a> this notice for 2 weeks.</p><p>- Marco van Wieren | Downloads by van Wieren | <a href="https://www.wpo365.com/">https://www.wpo365.com/</a></p></div>';
                    }
                }
            }

            /**
             * Helper to configure a transient to surpress admoin notices when the user clicked dismiss.
             * 
             * @since 7.18
             * 
             * @return void
             */
            public static function dismiss_admin_notices() {
                if ( isset( $_GET[ 'wpo365_has_errors_dismissed' ] ) ) {
                    set_transient( 'wpo365_has_errors_dismissed', date( 'd' ), 172800 );
                }
                if ( isset( $_GET[ 'wpo365_upgrade_dismissed' ] ) ) {
                    set_transient( 'wpo365_upgrade_dismissed', date( 'd' ), 1209600 );
                }
            }

            /**
             * Helper to write an error to the log file and at the same time return a WP Error object
             * 
             * @since 7.7
             * 
             * @param   $error_level    string  'DEBUG' or 'ERROR'
             * @param   $error_code     string  '1000', '1001' etc.
             * @param   $error_message  string  'An error occurred' etc...
             * 
             * @return  WP_Error
             * 
             */
            public static function handle_error( $error_level, $error_code, $error_message ) {
                Logger::write_log( $error_level, $error_message );
                return new \WP_Error( $error_code, $error_message );
            }

            /**
             * Overrides the Wordpress sanitize_user filter to allow special character # for AAD external users.
             * 
             * @since 7.11
             * 
             * @param $username string
             * @param $raw_username string
             * @param $strict boolean
             * 
             * @return string sanitized user name
             */
            public static function sanitize_user_name( $username, $raw_username )
            {
                Logger::write_log( 'DEBUG', 'Sanitized:  ' . $username );
                Logger::write_log( 'DEBUG', 'Sanizitzing: ' . $raw_username );

                $username = $raw_username;

                $username     = wp_strip_all_tags( $username );
                Logger::write_log( 'DEBUG', 'Sanizitzing 1: ' . $username );

                $username     = remove_accents( $username );
                Logger::write_log( 'DEBUG', 'Sanizitzing 2: ' . $username );

    	        // Kill octets
                $username = preg_replace( '|%([a-fA-F0-9][a-fA-F0-9])|', '', $username );
                Logger::write_log( 'DEBUG', 'Sanizitzing 3: ' . $username );

                $username = preg_replace( '/&.+?;/', '', $username ); // Kill entities
                Logger::write_log( 'DEBUG', 'Sanizitzing 4: ' . $username );
                
    	        // If strict, reduce to ASCII for max portability.
    	        if ( $strict ) {
                        $username = preg_replace( '|[^a-z0-9 _.\-@]|i', '', $username );
                        Logger::write_log( 'DEBUG', 'Sanizitzing 5: ' . $username );
    	        }
            
                $username = trim( $username );
                Logger::write_log( 'DEBUG', 'Sanizitzing 6: ' . $username );

    	        // Consolidate contiguous whitespace
                $username = preg_replace( '|\s+|', ' ', $username );
                Logger::write_log( 'DEBUG', 'Sanizitzing 7: ' . $username );

                return $username;
            }

            /**
             * Sets a number of URL related globals (all normalized and not ending with a trailing space).
             * Whether or not to force SSL is determined by the user override (option) use_ssl. If this
             * option hast been configured, the plugin will assume the same protocol as used for the 
             * redirect url. If the redirect utl hasn't been configured yet, the plugin will assume the
             * same protocol as used for the home url.
             * 
             * @since 7.11
             * 
             * @return void
             */
            public static function ensure_url_info_cache() {
                $home = get_option( 'home' );
                $scheme = ( isset( $_SERVER['HTTPS'] ) && ( $_SERVER['HTTPS'] == 'on' || $_SERVER['HTTPS'] == 1 ) ) ||
                          ( isset( $_SERVER['HTTP_X_FORWARDED_PROTO'] ) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' )
                    ? 'https'
                    : 'http';
                $request_uri = self::ensure_trailing_slash_path( $_SERVER[ 'REQUEST_URI' ] );
                $home_path = self::ensure_trailing_slash_path( parse_url( $home, PHP_URL_PATH ) );
                $current_url = $scheme . '://' . $_SERVER['HTTP_HOST'] . $request_uri;
                $GLOBALS[ 'WPO365_URL_INFO_CACHE' ] = array(
                    'request_uri'       => $request_uri,
                    'wp_site_url'       => self::ensure_trailing_slash_url( $home ),
                    'wp_site_path'      => $home_path,
                    'current_url'       => $current_url,
                );
            }

            /**
             * Helper method to (try) help ensure that the path segment given ends with a trailing slash.
             * 
             * @since 8.0
             * 
             * @param $url string Path that should end with a slash
             * @return string Path with trailing slash if appropriate
             */
            public static function ensure_trailing_slash_path( $path ) {
                $path = trim( $path, '/' );
                $path_segments = explode( '/', $path );
                $segments_count = count( $path_segments );
                if( $segments_count > 0 && false === stripos( $path_segments[ $segments_count -1 ], '.' ) ) {
                    $is_root = empty( $path );
                    return $is_root 
                        ? '/' 
                        : '/' . implode( '/', $path_segments ) . '/';
                } 
                return '/' . $path;
            }

            /**
             * Helper method to (try) help ensure that the url given ends with a trailing slash.
             * 
             * @since 8.0
             * 
             * @param $url string Url that should end with a slash
             * @return string Url with trailing slash if appropriate
             */
            public static function ensure_trailing_slash_url( $url ) {

                if( empty( $url ) || !is_string( $url ) ) {
                    return null;
                }

                $parsed_url = parse_url( $url );
                $resulting_url = '';
                
                if( !empty( $parsed_url[ 'scheme' ] ) ) {
                    $resulting_url .= $parsed_url[ 'scheme' ];
                }
                else {
                    return null;
                }

                $resulting_url .= ( '://' );

                if( !empty( $parsed_url[ 'user' ] ) && !empty( $parsed_url[ 'pass' ] ) ) {
                    $resulting_url .= ( $parsed_url[ 'user' ] . ':' . $parsed_url[ 'pass' ] . '@' );
                }

                if( !empty( $parsed_url[ 'host' ] ) ) {
                    $resulting_url .= $parsed_url[ 'host' ];
                }
                else {
                    return null;
                }

                if( !empty( $parsed_url[ 'port' ] ) ) {
                    $resulting_url .= ( ':' . $parsed_url[ 'port' ] );
                }

                if( !empty( $parsed_url[ 'path' ] ) ) {
                    $resulting_url .= self::ensure_trailing_slash_path( $parsed_url[ 'path' ] );
                }
                else {
                    $resulting_url .= '/';
                }

                if( !empty( $parsed_url[ 'query' ] ) ) {
                    $resulting_url .= ( '?' . $parsed_url[ 'query' ] );
                }

                if( !empty( $parsed_url[ 'fragment' ] ) ) {
                    $resulting_url .= ( '#' . $parsed_url[ 'fragment' ] );
                }

                return $resulting_url;
            }

            /**
             * Helper method to determine whether the current URL is the login form.
             * 
             * @since 7.11
             * 
             * @return boolean true if the current form is the wp login form.
             */
            public static function is_wp_login( $uri = NULL ) {
                if( empty( $uri ) ) {
                    $uri = $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'request_uri' ];
                }

                $login_urls = Helpers::get_login_urls();

                array_walk( $login_urls, function( &$value, $key ) {
                    rtrim( $value, '/' );
                } );

                $custom_login_url_path = !empty( $login_urls[ 'custom_login_url' ] )
                    ? parse_url( $login_urls[ 'custom_login_url' ], PHP_URL_PATH )
                    : '';
                $custom_login_url_detected = !empty( $custom_login_url_path ) 
                    &&  false !== stripos( $uri,  $custom_login_url_path );
                
                $default_login_url_path = parse_url( $login_urls[ 'default_login_url' ], PHP_URL_PATH );
                $default_login_url_detected = false !== stripos( $uri,  $default_login_url_path );

                return ( $custom_login_url_detected || $default_login_url_detected );
            }

            /**
             * Helper method to determine whether the current URL is the WP REST API.
             * 
             * @since 7.12
             * 
             * @return boolean true if the current URL is for the WP REST API otherwise false.
             */
            public static function is_wp_rest_api() {
                $path_without_subdir = str_replace( 
                    $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_path' ], 
                    '',
                    $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'request_uri' ] );
                
                if( stripos( $path_without_subdir, '/wp-json' ) === 0 ) {

                    return true;
                }

                return false;
            }

            /**
             * Helper to enqueue the pintra redirect script.
             * 
             * @since 8.6
             * 
             * @return void
             */
            public static function enqueue_pintra_redirect() { 
                wp_enqueue_script( 'pintraredirectjs', $GLOBALS[ 'WPO365_PLUGIN_URL' ] . '/apps/dist/pintra-redirect.js', array(), $GLOBALS[ $GLOBALS[ 'WPO365_VERSION_KEY' ] ], false );
            }
        }
    }

?>
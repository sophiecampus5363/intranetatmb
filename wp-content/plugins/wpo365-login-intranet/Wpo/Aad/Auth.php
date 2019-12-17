<?php
    namespace Wpo\Aad;

    // prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Firebase\JWT\JWT;
    use \Wpo\Util\Error_Handler;
    use \Wpo\Util\Logger;
    use \Wpo\Util\Options;
    use \Wpo\Util\Helpers;
    use \Wpo\User\User_Manager;

    if( !class_exists( '\Wpo\Aad\Auth' ) ) {
    
        class Auth {

            const USR_META_WPO365_AUTH          = 'WPO365_AUTH';
            const USR_META_WPO365_AUTH_CODE     = 'WPO365_AUTH_CODE';

            // Used by AAD v2.0
            const USR_META_REFRESH_TOKEN        = 'wpo_refresh_token';
            const USR_META_ACCESS_TOKEN         = 'wpo_access_tokens';

            // Used by AAD v1.0
            const USR_META_REFRESH_TOKEN_PREFIX = 'wpo_refresh_token_for_';
            const USR_META_ACCESS_TOKEN_PREFIX  = 'wpo_access_token_for_';

            /**
             * Validates each incoming request to see whether user prior to request
             * was authenicated by Microsoft Office 365 / Azure AD.
             *
             * @since   1.0
             *
             * @return  void 
             */
            public static function validate_current_session() {
                // Check if user needs to redirected to Microsoft
                if( !empty( $_REQUEST[ 'action' ] ) && $_REQUEST[ 'action' ] == 'openidredirect' ) {
                    $login_hint = !empty( $_POST[ 'login_hint' ] )
                        ? $_POST[ 'login_hint' ]
                        : null;
                        
                    $redirect_to = !empty( $_POST[ 'redirect_to' ] )
                        ? $_POST[ 'redirect_to' ]
                        : null;

                    $authUrl = Auth::get_oauth_url( $login_hint, $redirect_to );

                    Helpers::force_redirect( $authUrl );
                    exit();
                }

                // Process incoming request
                self::process_openidconnect_error();
                self::process_openidconnect_token();

                // Should we skip authentication
                if( true === self::skip_authentication() ) {
                    return;
                }

                // No? Then let's do it
                self::authenticate();
            }

            /**
             * Destroys any session and authenication artefacts and hooked up with wpo365_logout and should
             * therefore never be called directly to avoid endless loops etc.
             *
             * @since   1.0
             *
             * @return  void 
             */
            public static function destroy_session() {
                Logger::write_log( 
                    'DEBUG', 
                    'Destroying session ' . strtolower( basename( $_SERVER[ 'PHP_SELF' ] ) ) );
                delete_user_meta( get_current_user_id(), Auth::USR_META_WPO365_AUTH );
                delete_user_meta( get_current_user_id(), Auth::USR_META_WPO365_AUTH_CODE );
            }

            /**
             * Same as destroy_session but with redirect to login page (but only if the 
             * login page isn't the current page).
             *
             * @since   1.0
             * 
             * @param   string  $login_error_code   Error code that is added to the logout url as query string parameter.
             * @return  void
             */
            public static function goodbye( $login_error_code ) {
                $error_page_url = Options::get_global_string_var( 'error_page_url' );
                $error_page_path = rtrim( parse_url( $error_page_url, PHP_URL_PATH ), '/' );

                $redirect_to = ( empty( $error_page_url ) || $error_page_path === $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_path' ] )
                    ? Helpers::get_preferred_login_url() 
                    : $error_page_url;

                if( stripos( $redirect_to, basename( $_SERVER[ 'PHP_SELF' ] ) ) === false ) {
                    do_action( 'destroy_wpo365_session' );

                    wp_destroy_current_session();
                    wp_clear_auth_cookie();
                    wp_set_current_user( 0 );
                    unset($_COOKIE[AUTH_COOKIE]);
                    unset($_COOKIE[SECURE_AUTH_COOKIE]);
                    unset($_COOKIE[LOGGED_IN_COOKIE]);

                    $redirect_to = add_query_arg( 'login_errors', $login_error_code, $redirect_to );
                    Helpers::force_redirect( $redirect_to );
                }
            }

            /**
             * Constructs the oauth authorize URL that is the end point where the user will be sent for authorization.
             * If the plugin is configured to use AAD v2.0 it will return an AAD v2.0 authoriation URL instead.
             * 
             * @since 4.0
             * 
             * @since 8.0
             * 
             * @param $login_hint string Login hint that will be added to Open Connect ID link
             * @param $redirect_to string Link where the user will be redirected to
             * 
             * @return string if everthing is configured OK a valid authorization URL
             */
            public static function get_oauth_url( $login_hint = null, $redirect_to = null ) {
                $redirect_to = !empty( $redirect_to )
                    ? $redirect_to
                    : ( isset( $_SERVER[ 'HTTP_REFERER' ] ) 
                        ? $_SERVER[ 'HTTP_REFERER' ]
                        : $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_url' ] );

                $use_v2 = Options::get_global_boolean_var( 'use_v2' );

                $nonce = Helpers::get_nonce();

                $params = true === $use_v2
                    ? self::get_v2_auth_url_params( $redirect_to, $nonce )
                    : self::get_auth_url_params( $redirect_to, $nonce );
                
                if( !empty( $login_hint ) ) {
                    $params[ 'login_hint' ] = $login_hint;
                }

                $directory_id = Options::get_global_string_var( 'tenant_id' );
                $multi_tenanted = Options::get_global_boolean_var( 'multi_tenanted' );
                
                if( true === $multi_tenanted ) {
                    $directory_id = 'common';
                }

                $v2 = $use_v2
                    ? '/v2.0'
                    : '';

                $auth_url = 'https://login.microsoftonline.com/' 
                    . $directory_id 
                    . '/oauth2' 
                    . $v2 
                    . '/authorize?' 
                    . http_build_query( $params, '', '&' );
                
                Logger::write_log( 'DEBUG', "Open ID Connect URL: $auth_url" );

                return $auth_url;
            }

            /**
             * Helper method to get the query string properties for an Azure V1 Open ID Connect link.
             * 
             * @since 8.0
             * 
             * @param $redirect_to string Url the user initially navigated to
             * @param $nonce string The nonce that was passed in by the callee
             * 
             * @return array Azure AD V1 Open ID Connect URL parameters
             */
            private static function get_auth_url_params( $redirect_to, $nonce ) {
                $params = array( 
                    'client_id'     => Options::get_global_string_var( 'application_id' ),
                    'response_type' => 'id_token code',
                    'redirect_uri'  => Options::get_global_string_var( 'redirect_url' ),
                    'response_mode' => 'form_post',
                    'scope'         => 'openid',
                    'resource'      => '00000002-0000-0000-c000-000000000000',
                    'state'         => $redirect_to,
                    'nonce'         => $nonce,
                );

                /**
                 * @since 9.4
                 * 
                 * Add ability to configure a domain hint to prevent Microsoft from
                 * signing in users that are already logged in to a different O365 tenant.
                 */
                $domain_hint = Options::get_global_string_var( 'domain_hint' );

                if( !empty( $domain_hint ) ) {
                    $params[ 'domain_hint' ] = $domain_hint;
                }

                return $params;
            }

            /**
             * Helper method to get the query string properties for an Azure V2 Open ID Connect link.
             * 
             * @since 8.0
             * 
             * @param $redirect_to string Url the user initially navigated to
             * @param $nonce string The nonce that was passed in by the callee
             * 
             * @return array Azure AD V2 Open ID Connect URL parameters
             */
            private static function get_v2_auth_url_params( $redirect_to, $nonce ) {
                $params = array( 
                    'client_id'     => Options::get_global_string_var( 'application_id' ),
                    'response_type' => 'id_token code',
                    'redirect_uri'  => Options::get_global_string_var( 'redirect_url' ),
                    'response_mode' => 'form_post',
                    'scope'         => 'openid email profile',
                    'state'         => $redirect_to,
                    'nonce'         => $nonce,
                );

                /**
                 * @since 9.4
                 * 
                 * Add ability to configure a domain hint to prevent Microsoft from
                 * signing in users that are already logged in to a different O365 tenant.
                 */
                $domain_hint = Options::get_global_string_var( 'domain_hint' );

                if( !empty( $domain_hint ) ) {
                    $params[ 'domain_hint' ] = $domain_hint;
                }

                return $params;
            }

            /**
             * Redirects the user either to Microsoft (Open ID Connect link) or when dual login
             * is configured to the (custom) login form.
             * 
             * @since 8.0
             * 
             * @param $login_hint string Login hint that will be added to the Open ID Connect link if present
             * 
             * @return void
             */
            public static function redirect_to_microsoft( $login_hint = null ) {
                // Allow for dual login (if user isn't on the login form and tries to enter his username)
                if( !Helpers::is_wp_login() ) {
                    $dual_login = true === Options::get_global_boolean_var( 'redirect_to_login' )
                        ? 'DUAL_LOGIN'
                        : ( true === Options::get_global_boolean_var( 'redirect_to_login_v2' )
                            ? 'DUAL_LOGIN_V2'
                            : '' );
                    
                    if( !empty( $dual_login ) ) {
                        $redirect_url = Options::get_global_string_var( 'redirect_url' );
                        $referer = ( stripos( $redirect_url, 'https' ) !== false ? 'https' : 'http' ) . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
                        $login_url = Helpers::get_preferred_login_url();
                        $login_url = add_query_arg( 'login_errors', $dual_login, $login_url );
                        $login_url = add_query_arg( 'redirect_to', rawurlencode( $referer ), $login_url );
                        Helpers::force_redirect(  $login_url );
                        exit();
                    }
                }

                Logger::write_log( 'DEBUG', 'Forwarding the user to Microsoft to get fresh ID and access token(s)' );
                
                // Default redirection
                ob_start();
                include( $GLOBALS[ 'WPO365_PLUGIN_DIR' ] . '/templates/openid-redirect.php' );
                $content = ob_get_clean();
                echo $content;
                exit();
            }

            /**
             * Handles redirect from Microsofts authorization service and tries to detect
             * any wrong doing and if detected redirects wrong-doer to Wordpress login instead
             *
             * @since   1.0
             * @return  void
             */
            public static function process_openidconnect_token() {
                if( false === isset( $_POST[ 'state' ] )
                    || false === isset( $_POST[ 'id_token' ] ) )
                        return;
                
                Logger::write_log( 'DEBUG', 'Processing incoming OpenID Connect id_token' );

                // Decode the id_token
                $id_token = Auth::decode_id_token();

                // Handle if token could not be processed
                if( $id_token === false ) {
                    Logger::write_log( 'ERROR', 'ID token could not be processed and user will be redirected to default Wordpress login.' );
                    Auth::goodbye( Error_Handler::ID_TOKEN_ERROR );
                    exit();
                }

                // Handle if nonce is invalid 
                if( Helpers::validate_nonce( $id_token->nonce ) !== true ) {
                    Logger::write_log( 'ERROR', 'NONCE is invalid and user will be redirected to default Wordpress login. 
                        If the administrator has configured server-side redirection the most likely the plugin\'s response is 
                        being cached and an old request is being served. An administrator can try to skip the NONCE verfication 
                        alltogether (see WP Admin  > WPO365 > Miscellaneous' );
                    Auth::goodbye( Error_Handler::TAMPERED_WITH );
                    exit();
                }

                // Log id token if configured
                if( true === Options::get_global_boolean_var( 'debug_log_id_token' ) )
                        Logger::write_log( 'DEBUG', $id_token );

                // Check whether allowed (Office 365 or Security) Group Ids have been configured
                $allowed_groups_ids = Options::get_global_list_var( 'groups_whitelist' );

                $has_group_ids = property_exists( $id_token, 'groups' );
                $group_ids = true === $has_group_ids 
                    ? $id_token->groups 
                    : array();

                if( sizeof( $allowed_groups_ids ) > 0 ) {
                    Logger::write_log( 'DEBUG', 'Group policy has been defined' );

                    if( !$has_group_ids 
                        || ( $has_group_ids 
                           && !( count( 
                               array_intersect_key( 
                                   array_flip( $allowed_groups_ids ), 
                                   array_flip( $group_ids ) ) ) >= 1 ) ) ) {
                                        Logger::write_log( 'ERROR', 'Access denied error because the administrator has restricted
                                            access to a limited number of Azure AD (security) groups and the user trying to log on 
                                            is not in one of these groups.' );
                                        Auth::goodbye( Error_Handler::NOT_IN_GROUP );
                                        exit();
                    }
                }

                // Ensure user with the information found in the id_token
                $wp_usr = User_Manager::ensure_user( $id_token );
                
                // Handle if user could not be processed
                if( is_wp_error( $wp_usr ) ) {
                    $error_code = $wp_usr->get_error_code();
                    switch( $error_code ) {
                        case '4021':
                            Logger::write_log( 'ERROR', 'Could not retrieve or create a new user. Please check the log.' );
                            Auth::goodbye( Error_Handler::AADAPPREG_ERROR );        
                            exit;
                        case '4050':
                            Logger::write_log( 'ERROR', 'Could not automatically create a new user because the current version
                                of the plugin does not support this feature. The administrator can upgrade to the PROFESSIONAL,
                                PREMIUM or INTRANET edition.' );
                            Auth::goodbye( Error_Handler::BASIC_VERSION );        
                            exit;
                        default: 
                            Logger::write_log( 'ERROR', 'Could not retrieve or create a new user. Please check the log.' );
                            Auth::goodbye( Error_Handler::USER_NOT_FOUND );
                            exit();
                    }
                }

                // Store the Authorization Code for extensions that may need it to obtain access codes for AAD secured resources
                if( isset( $_POST[ 'code' ] ) ) {
                    // Session valid until
                    $auth_code = new \stdClass();
                    $auth_code->expiry = time() + 3480;
                    $auth_code->code = $_POST[ 'code' ];
                    
                    update_user_meta(
                        get_current_user_id(), 
                        Auth::USR_META_WPO365_AUTH_CODE, 
                        json_encode( $auth_code ) );
                }

                // Now finally retrieve user's latest details from Microsoft Graph
                apply_filters( 'wpo_graph_get_user_info', $wp_usr );

                // @deprecated
                do_action( 'wpo365_openid_token_processed' );

                /**
                 * Fires after the user has successfully logged in.
                 *
                 * @since 7.1
                 *
                 * @param string  $user_login Username.
                 * @param WP_User $user       WP_User object of the logged-in user.
                 */
                if( false === Options::get_global_boolean_var( 'skip_wp_login_action' ) ) {
                    do_action( 'wp_login', $wp_usr->user_login, $wp_usr );
                }  

                // Get URL and redirect user (default is the WordPress homepage)
                $redirect_url = $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_url' ];
                $redirect_url = apply_filters( 'wpo_redirect_url', $redirect_url, $group_ids );
                Logger::write_log( 'DEBUG', 'Redirecting to ' . $redirect_url );

                /**
                 * @since 9.0
                 * 
                 * Enforce the same scheme as AAD redirect uri to avoid infite loops.
                 * */ 
                $aad_redirect_url = Options::get_global_string_var( 'redirect_url' );
                
                if( stripos( $aad_redirect_url, 'https://' ) !== false && stripos( $redirect_url, 'http://' ) === 0 ) {
                    Logger::write_log( 'ERROR', 'Please update your htaccess or similar and ensure that users can only access your website via https:// (detected state: ' . $redirect_url . ').' );
                    $redirect_url = str_replace( 'http://', 'https://', $redirect_url );
                }

                Helpers::force_redirect( $redirect_url );
            }

            private static function process_openidconnect_error() {
                if( isset( $_POST[ 'error' ] ) ) {
                    $error_string = $_POST[ 'error' ] . isset( $_POST[ 'error_description' ] ) ? $_POST[ 'error_description' ] : '';
                    Logger::write_log( 'ERROR', $error_string );
                    Auth::goodbye( Error_Handler::CHECK_LOG );
                    exit();
                }
            }

            /**
             * Unraffles the incoming JWT id_token with the help of Firebase\JWT and the tenant specific public keys available from Microsoft.
             * 
             * @since   1.0
             *
             * @return  void 
             */
            private static function decode_id_token() {

                Logger::write_log( 'DEBUG', 'Processing an new id token' );

                // Check whether an id_token is found in the posted payload
                if( !isset( $_POST[ 'id_token' ] ) ) {
                    Logger::write_log( 'ERROR', 'ID token not found.' );
                    return false;
                }

                // Get the token and get it's header for a first analysis
                $id_token = $_POST[ 'id_token' ];
                $jwt_decoder = new JWT();
                $header = $jwt_decoder::header( $id_token );
                
                // Simple validation of the token's header
                if( !isset( $header->kid ) || !isset( $header->alg ) ) {
                    Logger::write_log( 'ERROR', 'JWT header is missing so stop here.' );
                    return false;
                }

                Logger::write_log( 'DEBUG', 'Algorithm found ' . $header->alg );

                // Discover tenant specific public keys
                $keys = Auth::discover_ms_public_keys( false );
                if( $keys == NULL ) {
                    Logger::write_log( 'ERROR', 'Could not retrieve public keys from Microsoft.' );
                    return false;
                }

                // Find the tenant specific public key used to encode JWT token
                $key = Auth::retrieve_ms_public_key( $header->kid, $keys );
                if( $key == false ) {
                    Logger::write_log( 'ERROR', 'Could not find expected key in keys retrieved from Microsoft. Please contact WPO365 support.' );
                    return false;
                }

                $pem_string = "-----BEGIN CERTIFICATE-----\n" . chunk_split( $key, 64, "\n" ) . "-----END CERTIFICATE-----\n";

                // Decode athe id_token
                try {
                    $decoded_token = $jwt_decoder::decode( 
                        $id_token, 
                        $pem_string,
                        array( strtoupper( $header->alg ) )
                    );
                }
                catch( \Exception $e ) {
                    Logger::write_log( 'ERROR', 'Could not decode ID token: ' . $e->getMessage() );
                    return false;
                }

                if( !$decoded_token ) {

                    Logger::write_log( 'ERROR', 'Failed to decode token ' . substr( $pem_string, 0, 35 ) . '...' . substr( $pem_string, -35 ) . ' using algorithm ' . $header->alg );
                    return false;
                }

                return $decoded_token;
            }

            /**
             * Discovers the public keys Microsoft used to encode the id_token
             *
             * @since   1.0
             *
             * @return  mixed(stdClass|NULL)    Cached keys if found and valid otherwise fresh new keys.
             */
            private static function discover_ms_public_keys( $refresh ) {
                if( false === $refresh ) {
                    $cached_keys = get_site_option( 'wpo365_msft_keys' );

                    if( !empty( $cached_keys ) ) {
                        $cached_keys_segments = explode( ',', $cached_keys, 2 );

                        if( sizeof( $cached_keys_segments ) == 2 && intval( $cached_keys_segments[0] ) > time() ) {
                            $keys = json_decode( $cached_keys_segments[1] );
                            Logger::write_log( 'DEBUG', 'Found cached MSFT public keys to decrypt the JWT token' );

                            if( isset( $keys->keys ) )
                                return $keys->keys;

                            return $keys;
                        }
                    }
                }
                
                Logger::write_log( 'DEBUG', 'Retrieving fresh MSFT public keys to decrypt the JWT token' );
                
                $v2 = Options::get_global_boolean_var( 'use_v2' ) 
                    ? '/v2.0' 
                    : '';
                $ms_keys_url = "https://login.microsoftonline.com/common/discovery$v2/keys";
                
                $curl = curl_init();
                curl_setopt( $curl, CURLOPT_URL, $ms_keys_url );
                curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
                
                if( Options::get_global_boolean_var( 'skip_host_verification' ) ) {
                        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); 
                        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 ); 
                }

                $result = curl_exec( $curl ); // result holds the keys
                if( curl_error( $curl ) ) {
                    Logger::write_log( 'ERROR', 'error occured whilst getting MSFT decryption keys: ' . curl_error( $curl ) );
                    curl_close( $curl );
                    return NULL;
                }
                
                curl_close( $curl );
                update_site_option( 'wpo365_msft_keys', strval( time() + 21600 ) . ',' . $result );
                $keys = json_decode( $result );

                if( isset( $keys->keys ) )
                    return $keys->keys;
                
                return $keys;
            }
        
            /**
             * Retrieves the ( previously discovered ) public keys Microsoft used to encode the id_token
             *
             * @since   1.0
             *
             * @param   string  key-id to retrieve the matching keys
             * @param   array   keys previously discovered
             * @param   boolean whether or not to 
             * @return  void 
             */
            private static function retrieve_ms_public_key( $kid, $keys, $allow_refresh = true ) {
                foreach( $keys as $key ) {
                    if( $key->kid == $kid ) {
                        if( is_array( $key->x5c ) ) {
                            return $key->x5c[0];
                        }
                        else {
                            return $key->x5c;
                        }
                    }
                }

                if( true === $allow_refresh ) {
                    $new_keys = self::discover_ms_public_keys( true ); // Keys not found so lets refresh the cache
                    return self::retrieve_ms_public_key( $kid, $new_keys, false );
                }
 
                return false;
            }

            /**
             * Gets an access token in exchange for an authorization token that was received prior when getting
             * an OpenId Connect token or for a fresh code in case available
             *
             * @since   5.0
             *
             * @return mixed(stdClass|WP_Error) access token with expiry as string in format expiry,bearer
             */
            public static function get_bearer_token( $resource_uri ) {
                // Don't even start if the user is not logged in
                if( !is_user_logged_in() )
                    return new \WP_Error( '1000', 'Cannot retrieve a bearer token for a user that is not logged in' );

                // Get resource nice name e.g. https://graph.microsoft.com => graph.microsoft.com
                $resource = self::get_resource_name_from_id( $resource_uri );

                if( is_wp_error( $resource ) )
                    return new \WP_Error( '1010', $resource->get_error_message() );

                // Tokens are stored by default as user metadata
                $cached_access_token_key = Auth::USR_META_ACCESS_TOKEN_PREFIX . $resource;
                $cached_access_token_json = get_user_meta( 
                    get_current_user_id(), 
                    $cached_access_token_key, 
                    true );
                
                
                if( !empty( $cached_access_token_json ) ) {
                    $access_token = json_decode( $cached_access_token_json );
                    
                    // json_decode returns NULL if an "old" token is found
                    if( empty ($access_token ) || ( isset( $access_token->expiry ) && $access_token->expiry < time() ) )
                        delete_user_meta( get_current_user_id(), $cached_access_token_key );
                    else {
                        Logger::write_log( 'DEBUG', 'Found a previously saved access token for resource ' . $resource_uri . ' that is still valid' );
                        Logger::write_log( 'DEBUG', $access_token );
                        return $access_token;
                    }
                }

                $params = array(
                    'client_id' => Options::get_global_string_var( 'application_id' ),
                    'client_secret' => Options::get_global_string_var( 'application_secret' ),
                    'redirect_uri' => Options::get_global_string_var( 'redirect_url' ),
                    'resource' => $resource_uri,
                );

                // Check if we have a refresh token and if not fallback to the auth code
                $refresh_token = self::get_refresh_token();

                if( !empty( $refresh_token) ) {
                    $params[ 'grant_type' ] = 'refresh_token';
                    $params[ 'refresh_token' ] = $refresh_token->refresh_token;
                }
                else {
                    $auth_code = self::get_auth_code();
                    
                    if( !empty( $auth_code ) ) {
                        $params[ 'grant_type' ] = 'authorization_code';
                        $params[ 'code' ] = $auth_code->code;
                        // Delete the code since it can only be used once
                        delete_user_meta( get_current_user_id(), Auth::USR_META_WPO365_AUTH_CODE );
                    }
                }

                if( !isset( $params[ 'grant_type' ] ) ) {
                    $error_message = 'No authorization code and refresh token found when trying to get an access token 
                        for ' . $resource . '. The current user must sign out of the WordPress website and log back in again to 
                        retrieve a fresh authorization code that can be used in exchange for access tokens.';
                    Logger::write_log( 'ERROR', $error_message );
                    return new \WP_Error( '1030', $error_message );
                }

                Logger::write_log( 'DEBUG', 'Requesting access token for ' . $resource . ' using ' . $resource_uri );
                
                $params_as_str = http_build_query( $params, '', '&' ); // Fix encoding of ampersand
                $directory_id = Options::get_global_string_var( 'tenant_id' );
                $authorizeUrl = "https://login.microsoftonline.com/$directory_id/oauth2/token";
                
                $curl = curl_init();
                curl_setopt( $curl, CURLOPT_POST, 1 );
                curl_setopt( $curl, CURLOPT_URL, $authorizeUrl );
                curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $curl, CURLOPT_POSTFIELDS, $params_as_str );
                curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 
                    'Content-Type: application/x-www-form-urlencoded'
                ) );

                if( Options::get_global_boolean_var( 'skip_host_verification' ) ) {
                        Logger::write_log( 'DEBUG', 'Skipping SSL peer and host verification' );
                        curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); 
                        curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 ); 
                }
            
                $result = curl_exec( $curl ); // result holds the tokens
            
                if( curl_error( $curl ) ) {
                    $error_message = 'Error occured whilst getting an access token';
                    Logger::write_log( 'ERROR', $error_message );
                    curl_close( $curl );

                    return new \WP_Error( '1040', curl_error( $curl ) );
                }
            
                curl_close( $curl );

                // Validate the access token and return it
                $access_token = json_decode( $result );
                $access_token = Auth::validate_bearer_token( $access_token );

                if( is_wp_error( $access_token ) ) {
                    Logger::write_log( 'ERROR', 'Access token for ' . $resource . ' appears to be invalid.' );
                    return new \WP_Error( $access_token->get_error_code(), $access_token->get_error_message() );
                }

                $access_token->expiry = time() + intval( $access_token->expires_in );

                // Cache access token with resource specific key defined at the top
                update_user_meta(
                    get_current_user_id(), 
                    $cached_access_token_key, 
                    json_encode( $access_token ) );

                // Save refresh token
                if( isset( $access_token->refresh_token ) )
                    Auth::set_refresh_token( $access_token );

                Logger::write_log( 'DEBUG', 'Successfully obtained a valid access token for ' . $resource );
                Logger::write_log( 'DEBUG', $access_token );

                return $access_token;
            }

            /**
             * Gets an access token in exchange for an authorization token that was received prior when getting
             * an OpenId Connect token or for a fresh code in case available. This method is only compatible with 
             * AAD v2.0
             *
             * @since   5.2
             * 
             * @param $scope string Scope for AAD v2.0 e.g. https://graph.microsoft.com/user.read
             *
             * @return mixed(stdClass|WP_Error) access token as object or WP_Error
             */
            public static function get_bearer_token_v2( $scope ) {
                // Don't even start if the user is not logged in
                if( !is_user_logged_in() )
                    return new \WP_Error( '1000', 'Cannot retrieve a bearer token for a user that is not logged in' );
                
                // Tokens are stored by default as user metadata
                $cached_access_tokens_json = get_user_meta( 
                    get_current_user_id(), 
                    self::USR_META_ACCESS_TOKEN, 
                    true );
                
                $access_tokens = array();

                // Valid access token was saved previously
                if( !empty( $cached_access_tokens_json ) ) {
                    $cached_access_tokens = json_decode( $cached_access_tokens_json );
                    
                    // json_decode returns NULL or it isn't an array if an "old" token is found
                    if( empty( $cached_access_tokens ) || !is_array( $cached_access_tokens ) ) {
                        delete_user_meta( get_current_user_id(), self::USR_META_ACCESS_TOKEN );
                        Logger::write_log( 'DEBUG', 'Deleted an access token that is no longer supported.' );
                        $cached_access_tokens = array();
                    }

                    foreach( $cached_access_tokens as $key => $cached_access_token ) {
                        
                        if( isset( $cached_access_token->expiry ) && intval( $cached_access_token->expiry ) < time() ) {
                            unset( $cached_access_tokens[ $key ] );
                            update_user_meta( 
                                get_current_user_id(), 
                                self::USR_META_ACCESS_TOKEN, 
                                json_encode( $cached_access_tokens ) );
                            Logger::write_log( 'DEBUG', 'Deleted an expired access token.' );
                        }
                        elseif( isset( $cached_access_token->scope ) && false !== stripos( $cached_access_token->scope, $scope ) ) {
                            Logger::write_log( 'DEBUG', 'Found a previously saved access token for scope ' . $scope . ' that is still valid' );
                            Logger::write_log( 'DEBUG', $cached_access_token );
                            return $cached_access_token;
                        }
                    }

                    $access_tokens = $cached_access_tokens;
                }

                $params = array(
                    'client_id' => Options::get_global_string_var( 'application_id' ),
                    'client_secret' => Options::get_global_string_var( 'application_secret' ),
                    'redirect_uri' => Options::get_global_string_var( 'redirect_url' ),
                    'scope' => 'offline_access ' . $scope, // Request offline_access to get a refresh token
                );

                // Check if we have a refresh token and if not fallback to the auth code
                $refresh_token = Auth::get_refresh_token();

                if( !empty( $refresh_token) ) {
                    $params[ 'grant_type' ] = 'refresh_token';
                    $params[ 'refresh_token' ] = $refresh_token->refresh_token;
                }
                else {
                    $auth_code = self::get_auth_code();

                    if( !empty( $auth_code ) ) {
                        $params[ 'grant_type' ] = 'authorization_code';
                        $params[ 'code' ] = $auth_code->code;
                        // Delete the code since it can only be used once
                        delete_user_meta( get_current_user_id(), Auth::USR_META_WPO365_AUTH_CODE );
                    }
                }

                if( !isset( $params[ 'grant_type' ] ) ) {
                    $error_message = 'No authorization code and refresh token found when trying to get an access token 
                        for ' . $scope . '. The current user must sign out of the WordPress website and log back in again to 
                        retrieve a fresh authorization code that can be used in exchange for access tokens.';
                    Logger::write_log( 'ERROR', $error_message );
                    return new \WP_Error( '1030', $error_message );
                }

                Logger::write_log( 'DEBUG', 'Requesting access token for ' . $scope );
                
                $params_as_str = http_build_query( $params, '', '&' ); // Fix encoding of ampersand
                $directory_id = Options::get_global_string_var( 'tenant_id' );
                $authorizeUrl = "https://login.microsoftonline.com/$directory_id/oauth2/v2.0/token";
                
                $curl = curl_init();
                curl_setopt( $curl, CURLOPT_POST, 1 );
                curl_setopt( $curl, CURLOPT_URL, $authorizeUrl );
                curl_setopt( $curl, CURLOPT_RETURNTRANSFER, 1 );
                curl_setopt( $curl, CURLOPT_POSTFIELDS, $params_as_str );
                curl_setopt( $curl, CURLOPT_HTTPHEADER, array( 
                    'Content-Type: application/x-www-form-urlencoded'
                ) );

                if( true === Options::get_global_boolean_var( 'skip_host_verification' ) ) {
                    
                    Logger::write_log( 'DEBUG', 'Skipping SSL peer and host verification' );
                    curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, 0 ); 
                    curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, 0 ); 
                }
            
                $result = curl_exec( $curl ); // result holds the tokens
            
                if( curl_error( $curl ) ) {
                    $error_message = 'Error occured whilst getting an access token';
                    Logger::write_log( 'ERROR', $error_message );
                    curl_close( $curl );

                    return new \WP_Error( '1040', curl_error( $curl ) );
                }
            
                curl_close( $curl );

                // Validate the access token and return it
                $access_token = json_decode( $result );
                $access_token = Auth::validate_bearer_token( $access_token );

                if( is_wp_error( $access_token ) ) {
                    Logger::write_log( 'ERROR', 'Access token for ' . $scope . ' appears to be invalid' );
                    return new \WP_Error( $access_token->get_error_code(), $access_token->get_error_message() );
                }

                // Store the new token as user meta with the shorter ttl of both auth and token
                $access_token->expiry = time() + intval( $access_token->expires_in );

                $access_tokens[] = $access_token;

                update_user_meta( 
                    get_current_user_id(), 
                    self::USR_META_ACCESS_TOKEN, 
                    json_encode( $access_tokens ) );

                // Save refresh token
                if( isset( $access_token->refresh_token ) )
                    Auth::set_refresh_token( $access_token );
                
                Logger::write_log( 'DEBUG', 'Successfully obtained a valid access token for ' . $scope );
                Logger::write_log( 'DEBUG', $access_token );

                return $access_token;
            }

            /**
             * Helper to validate an oauth access token
             *
             * @since   5.0
             *
             * @param   object  access token as PHP std object
             * @return  mixed(stdClass|WP_Error) Access token as standard object or WP_Error when invalid   
             * @todo    make by reference instead by value
             */
            private static function validate_bearer_token( $access_token_obj ) {

                if( isset( $access_token_obj->error ) ) {

                    Logger::write_log( 'ERROR', 'Error found whilst validating access token: ' . $access_token_obj->error_description );
                    return new \WP_Error( implode( ',', $access_token_obj->error_codes), $access_token_obj->error_description );
                }
            
                if( empty( $access_token_obj ) 
                    || $access_token_obj === false
                    || !isset( $access_token_obj->access_token ) 
                    || !isset( $access_token_obj->expires_in ) 
                    || !isset( $access_token_obj->token_type )
                    || !isset( $access_token_obj->scope )
                    || strtolower( $access_token_obj->token_type ) != 'bearer' ) {
            
                    Logger::write_log( 'ERROR', 'Incomplete access code detected.' );
                    return new \WP_Error( '0', 'Unknown error occurred' );
                }
            
                return $access_token_obj;
            }

            /**
             * Tries and find a refresh token for an AAD resource stored as user meta in the form "expiration,token"
             * In case an expired token is found it will be deleted
             *
             * @since   5.2
             * 
             * @param   string  $resource   Name for the resource key used to store that resource in the site options
             * @return  (stdClass|NULL)  Refresh token or an empty string if not found or when expired
             */
            private static function get_refresh_token() {
                $cached_refresh_token_json = get_user_meta( 
                    get_current_user_id(),
                    Auth::USR_META_REFRESH_TOKEN,
                    true );
                
                if( empty( $cached_refresh_token_json ) )
                    return NULL;
                
                $refresh_token = json_decode( $cached_refresh_token_json );
                Logger::write_log( 'DEBUG', 'Found a previously saved refresh token' );
                
                if( isset( $refresh_token->expiry ) && intval( $refresh_token->expiry ) < time() )
                    delete_user_meta( get_current_user_id(), Auth::USR_META_REFRESH_TOKEN );
                else {
                    Logger::write_log( 'DEBUG', 'Found a previously saved valid refresh token' );
                    return $refresh_token;
                }
                
                Logger::write_log( 'DEBUG', 'Could not find a valid refresh token' );
                return NULL;
            }

            /**
             * Helper method to persist a refresh token as user meta.
             * 
             * @since 5.1
             * 
             * @param stdClass $access_token Access token as standard object (from json)
             * @return void
             */
            private static function set_refresh_token( $access_token ) {

                $refresh_token = new \stdClass();
                $refresh_token->refresh_token = $access_token->refresh_token;
                $refresh_token->scope = $access_token->scope;
                $refresh_token->expiry = time( ) + 1209600;
                
                update_user_meta( 
                    get_current_user_id(),
                    self::USR_META_REFRESH_TOKEN,
                    json_encode( $refresh_token ) );

                Logger::write_log( 'DEBUG', 'Successfully stored refresh token' );
            }

            /**
             * Tries and find an authorization code stored as user meta
             * In case an expired token is found it will be deleted
             * 
             * @since 5.2
             * 
             * @return (stdClass|NULL)
             */
            private static function get_auth_code() {
                $auth_code_value = get_user_meta( 
                    get_current_user_id(),
                    Auth::USR_META_WPO365_AUTH_CODE,
                    true );
                
                if( empty( $auth_code_value ) ) 
                    return NULL;

                $auth_code = json_decode( $auth_code_value );
                
                if( empty( $auth_code ) )
                    return NULL;

                $expired = isset( $auth_code->expiry ) && intval( $auth_code->expiry ) < time();
                
                if( $expired )
                    delete_user_meta( get_current_user_id(), Auth::USR_META_WPO365_AUTH_CODE );
                else
                    return $auth_code;
                return NULL;
            }

            /**
             * WordPress authentication hook that will be triggered before the authentication process 
             * is started.
             * 
             * @param   string  $user_name User name (by reference) the user entered in the login form.
             * 
             * @return void
             */
            public static function prevent_default_login_for_o365_users( &$user_name ) {

                if( empty( $user_name ) )
                    return;
                    
                if( false === Options::get_global_boolean_var( 'intercept_wp_login' ) )
                    return;

                // If the user name is an email address we get the domain otherwise false
                $email_domain = Helpers::get_smtp_domain_from_email_address( $user_name );

                if( empty( $email_domain ) ) 
                    return;

                if( true === Helpers::is_tenant_domain( $email_domain ) ) {
                    Logger::write_log( 'DEBUG', 'Authentication attempt detected by O365 user ' . $user_name );
                    Auth::redirect_to_microsoft( $user_name );
                    // -> Script will exit
                }
            }

            /**
             * Hooks into a default logout action and additionally logs out the user from Office 365 before sending
             * the user to the default login page.
             * 
             * @since 3.1
             * 
             * @return void
             */
            public static function logout_O365() {
                if( Options::get_global_boolean_var( 'logout_from_o365' ) ) {
                    $post_logout_redirect_uri = Options::get_global_string_var( 'post_signout_url' );
                    if( empty( $post_logout_redirect_uri ) )
                        $post_logout_redirect_uri = Helpers::get_preferred_login_url();
                    
                    $logout_url = "https://login.microsoftonline.com/common/oauth2/logout?post_logout_redirect_uri=$post_logout_redirect_uri";
                    Helpers::force_redirect(  $logout_url );
                }
            }

            /**
             * Retrieves the sub domain part of a Resource URI e.g. graph.microsoft.com for https://graph.microsoft.com
             * 
             * @since 5.0
             * 
             * @param $resource_uri string e.g. https://yourtenant.sharepoint.com
             * 
             * @return mixed(string|WP_Error)
             */
            public static function get_resource_name_from_id( $resource_uri ) {
 
                if( stripos( $resource_uri, 'http' ) !== 0 )
                    return new \WP_Error( '2000', 'Resource ID must start with http(s)' );

                $resource_uri_segments = explode( '/', $resource_uri );

                if( sizeof( $resource_uri_segments ) >= 3 )
                    return $resource_uri_segments[2];

                return new \WP_Error( '2010', 'Resource ID not formatted as URI' );
            }

            /**
             * Checks the configured scenario and the pages black list settings to
             * decide whether or not authentication of the current page is needed.
             * 
             * @since 5.0
             * 
             * @return  boolean     True if validation should be skipped, otherwise false.
             */
            private static function skip_authentication() {
                // Skip when a basic authentication header is detected
                if( true === Options::get_global_boolean_var( 'skip_api_basic_auth_request' ) 
                    && Helpers::is_basic_auth_api_request() ) {
                        return true;
                }

                // Not logged on and not configured => log in as WP Admin first
                if( !is_user_logged_in() && ( false === Helpers::is_wpo365_configured() ) ) {
                    return true;
                }

                // Check is scenario is 'internet' and validation of current page can be skipped
                $scenario = Options::get_global_string_var( 'auth_scenario' );

                if( !is_admin() && $scenario === 'internet' ) {
                    $private_pages = Options::get_global_list_var( 'private_pages' );

                    $login_urls = Helpers::get_login_urls();
                    
                    // Check if current page is private and cannot be skipped
                    foreach( $private_pages as $private_page ) {
                        $private_page = rtrim( strtolower( $private_page ), '/' );

                        if( $private_page === $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_url' ] ) {
                            Logger::write_log( 'ERROR', 'The following entry in the Private Pages list is illegal because it is the site url: ' . $private_page );
                            continue;
                        }

                        /**
                         * @since 9.0
                         * 
                         * Prevent users from hiding the login page.
                         */

                        if( stripos( $private_page, $login_urls[ 'default_login_url' ] ) !== false || ( !empty( $login_urls[ 'custom_login_url' ] ) && stripos( $private_page, $login_urls[ 'custom_login_url' ] ) !== false ) ) {
                            Logger::write_log( 'ERROR', 'The following entry in the Private Pages list is illegal because it is a login url: ' . $private_page );
                            continue;
                        }
                        
                        if( stripos( $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'current_url' ] , $private_page ) === 0 ) {
                            return false;
                        }
                    }

                    Logger::write_log( 'DEBUG', 'Cancelling session validation for page ' . strtolower( basename( $_SERVER[ 'PHP_SELF' ] ) ) . ' because selected scenario is \'Internet\'' );
                    return true;
                }

                // Check if current page is homepage and can be skipped
                $public_homepage = Options::get_global_boolean_var( 'public_homepage' );

                if( true === $public_homepage && ( $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_path' ] ===  $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'request_uri' ] ) ) {
                    Logger::write_log( 'DEBUG', 'Cancelling session validation for home page because public homepage is selected' );
                    return true;
                }
                
                // Check if current page is blacklisted and can be skipped
                $black_listed_pages = Options::get_global_list_var( 'pages_blacklist' );

                // Always add Error Page URL (if configured)
                $error_page_url = Options::get_global_string_var( 'error_page_url' );

                if( !empty( $error_page_url ) ) {
                    $error_page_url = rtrim( strtolower( $error_page_url ), '/' );
                    $error_page_path = rtrim( parse_url( $error_page_url, PHP_URL_PATH ), '/' );

                    if( empty( $error_page_path ) || $error_page_path === $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_path' ] ) {
                        Logger::write_log( 'ERROR', 'Error page URL must be a page and cannot be the root of the current website (' . $error_page_path . ')' );
                    }
                    else {
                        $black_listed_pages[] = $error_page_path;
                    }
                }

                // Always add Custom Login URL (if configured)
                $custom_login_url = Options::get_global_string_var( 'custom_login_url' );

                if( !empty( $custom_login_url ) ) {
                    $custom_login_url = rtrim( strtolower( $custom_login_url ), '/' );
                    $custom_login_path = rtrim( parse_url( $custom_login_url, PHP_URL_PATH ), '/' );

                    if( empty( $custom_login_path ) || $custom_login_path === $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_path' ] ) {
                        Logger::write_log( 'ERROR', 'Custom Login URL must be a page and cannot be the root of the current website (' . $custom_login_path . ')' );
                    }
                    else {
                        $black_listed_pages[] = $custom_login_path;
                    }
                }

                // Ensure default login path
                $default_login_url_path = parse_url( wp_login_url(), PHP_URL_PATH );

                if( false === array_search( $default_login_url_path, $black_listed_pages ) ) {
                    $black_listed_pages[] = $default_login_url_path;
                }

                // Ensure admin-ajax.php
                $admin_ajax_path = 'admin-ajax.php';

                if( false === array_search( $admin_ajax_path, $black_listed_pages ) ) {
                    $black_listed_pages[] = $admin_ajax_path;
                }

                Logger::write_log( 'DEBUG', 'Pages Blacklist after error page / custom login has verified' );
                Logger::write_log( 'DEBUG', $black_listed_pages );
                
                // Check if current page is blacklisted and can be skipped
                foreach( $black_listed_pages as $black_listed_page ) {

                    $black_listed_page = rtrim( strtolower( $black_listed_page ), '/' );

                    // Filter out empty or mis-configured black page entries
                    if( empty( $black_listed_page ) || $black_listed_page == '/' || $black_listed_page == $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_path' ] ) {
                        Logger::write_log( 'ERROR', 'Black listed page page must be a page and cannot be the root of the current website (' . $black_listed_page . ')' );
                        continue;
                    }
                    
                    // Correction after the plugin switched from basename to path based comparison
                    $starts_with = substr( $black_listed_page, 0, 1);
                    $black_listed_page = $starts_with == '/' || $starts_with == '?' ? $black_listed_page : '/' . $black_listed_page;
                    
                    // Filter out any attempt to illegally bypass authentication
                    if( stripos( $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'request_uri' ], '?/' ) !== false ) {
                        Logger::write_log( 'ERROR', 'Serious attempt to try to bypass authentication using an illegal query string combination "?/" (path used: ' . $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'request_uri' ] . ')');
                        break;
                    }
                    elseif( stripos( $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'request_uri' ], $black_listed_page ) !== false ) {
                        Logger::write_log( 'DEBUG', 'Found [' . $black_listed_page . '] thus cancelling session validation for path ' . $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'request_uri' ] );
                        return true;
                    }
                }
                
                return false;
            }
            
            /**
             * Checks for an existing and not yet expired authorization flag and
             * if not found starts the Sign in with Microsoft authentication flow 
             * by redirecting the user to Microsoft's IDP.
             * 
             * @since 5.0
             * 
             * @return void
             */
            private static function authenticate() {
                $wpo_auth_value = get_user_meta(
                    get_current_user_id(),
                    Auth::USR_META_WPO365_AUTH,
                    true );

                // Logged-on WP-only user
                if( is_user_logged_in() && empty( $wpo_auth_value ) ) {
                    Logger::write_log( 'DEBUG', 'User is a Wordpress-only user so no authentication is required' );
                    return;
                }
                
                // User not logged on
                if( empty( $wpo_auth_value ) ) {
                    Logger::write_log( 'DEBUG', 'User is not logged in and therefore sending the user to Microsoft to sign in' );
                    Auth::redirect_to_microsoft();
                    return;
                }

                // Check if user has expired 
                $wpo_auth = json_decode( $wpo_auth_value );

                $auth_expired = !isset( $wpo_auth->expiry ) || $wpo_auth->expiry < time();

                $different_site = is_multisite() 
                    && Options::mu_use_subsite_options()
                    && isset( $wpo_auth->url ) 
                    && $wpo_auth->url != $GLOBALS[ 'WPO365_URL_INFO_CACHE' ][ 'wp_site_url' ];

                if( $auth_expired || $different_site ) {
                    do_action( 'destroy_wpo365_session' );
                                        
                    // Don't call wp_logout because it may be extended
                    wp_destroy_current_session();
                    wp_clear_auth_cookie();
                    wp_set_current_user( 0 );
                    unset($_COOKIE[AUTH_COOKIE]);
                    unset($_COOKIE[SECURE_AUTH_COOKIE]);
                    unset($_COOKIE[LOGGED_IN_COOKIE]);

                    Logger::write_log( 'DEBUG', 'User logged out because current login not valid anymore (' . $auth_expired . '|' . $different_site . ')' );
                    
                    Auth::redirect_to_microsoft();

                    return;
                }
            }
        }
    }
?>
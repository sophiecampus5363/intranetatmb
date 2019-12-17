<?php

    namespace Wpo\Util;

    // Prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Aad\Auth;
    use \Wpo\Util\Helpers;
    use \Wpo\Util\Logger;
    
    if( !class_exists( '\Wpo\Util\Options' ) ) {

        class Options {

            /**
             * Array with options that should be partially obscured when logged
             * 
             * @since 7.11
             * 
             * @return array with option names that should be obscured when logged
             */
            private static function get_secret_options() {
                return array(
                    'tenant_id',
                    'application_id',
                    'application_secret',
                    'nonce_secret',
                );
            } 

            /**
             * Gets a global variable by its name and depending on global configuration expects this variable
             * to be a redux managed option or a manually setup global wp-config.php variable.
             * 
             * @param   string  $name   Variable name as string
             * @return  object|WP_Error The global variable or WP_Error if not found
             */
            private static function get_global_var( $name ) {
                self::ensure_options_cache();

                // Try return the requested option
                if( isset( $GLOBALS[ 'wpo365_options' ][ $name ] ) 
                    && !empty( $GLOBALS[ 'wpo365_options' ][ $name ] ) ) {
                        $value = $GLOBALS[ 'wpo365_options' ][ $name ];
                        $value_for_log = in_array( $name, self::get_secret_options() ) && is_string( $value )
                            ? substr( $value, 0, strlen( $value ) / 3 ) . '[...]'
                            : ( is_array( $value ) || is_object( $value ) 
                                ? print_r( $value, true ) 
                                : $value );
                }
                else {
                    $value_for_log = "Global variable with name $name not configured.";
                }

                Logger::write_log( 'DEBUG', "Option: $name -> $value_for_log" );

                return empty( $value )
                    ? new \WP_Error( '3030', "Global variable with name $name not configured." )
                    : $value;
            }

            /**
             * Helper function to read the options into a global variable.
             * 
             * @since 7.3
             * 
             * @return void
             */
            public static function ensure_options_cache() {
                // If Options are not yet cached
                if( !isset( $GLOBALS[ 'wpo365_options' ] ) || !is_array( $GLOBALS[ 'wpo365_options' ] ) ) {
                    // WPMU => Should we use subsite options instead of main site options
                    $mu_use_subsite_options = self::mu_use_subsite_options();
                    $options = $mu_use_subsite_options
                        ? get_option( 'wpo365_options', array() )
                        : get_site_option( 'wpo365_options', array() );

                    if( empty( $options ) && is_multisite() && false === $mu_use_subsite_options )
                        $options = self::wpmu_copy_wpo365_options();
                    
                    if( empty( $options ) )
                        $options = self::get_default_options();
                    
                    // Redux / wp-config.php options => upgrade
                    if( !isset( $options[ 'version' ] ) && $options[ 'version' ] != '2019' ) {
                        Logger::write_log( 'DEBUG', 'Options version is not set.' );
    
                        // Using wp-config.php => migrate
                        if( defined( 'WPO_USE_WP_CONFIG' ) && constant( 'WPO_USE_WP_CONFIG' ) === true )
                            $options = self::migrate_options();
                        
                        // Upgrade options
                        $options = self::upgrade_options( $options );

                        // Save options
                        if( $mu_use_subsite_options )
                            update_option( 'wpo365_options', $options );
                        else 
                            update_site_option( 'wpo365_options', $options );
                    }

                    $GLOBALS[ 'wpo365_options' ] = $options;
                }
            }

            /**
             * Tries to get the wpo365_options for the main site (in a WordPress network)
             * 
             * - Changed with v7.3
             * 
             * @since 5.0 
             * 
             * @return Array 
             */
            public static function wpmu_copy_wpo365_options() {
                global $current_site;
                $main_site_blog_id = (int)$current_site->blog_id;

                return get_blog_option( $main_site_blog_id, 'wpo365_options', self::get_default_options() );
            }

            /**
             * Same as get_global_var but will try and interpret the value of the
             * global variable as if it is a boolean. 
             * 
             * @since 4.6
             * 
             * @param   string  $name   Name of the global variable to get
             * @return  boolean         True in case value found equals 1, "1", "true" or true, otherwise false.
             */
            public static function get_global_boolean_var( $name ) {
                $var = self::get_global_var( $name );
                return (
                    $var === true 
                    || $var === "1" 
                    || $var === 1 
                    || ( is_string( $var ) && strtolower( $var ) == 'true' ) ) ? true : false;
            }

            /**
             * Same as get_global_var but will try and cast the value as a 1 dimensional array.
             * 
             * @since 7.0
             * 
             * @param   string $name    name of the global variable to get.
             * @return  array           Value of the global variable as an array or empty if not found.
             */
            public static function get_global_list_var( $name ) {
                $var = self::get_global_var( $name );
                return is_array( $var ) ? $var : array();
            }

            /**
             * Same as get_global_var but will try and cast the value as an integer.
             * 
             * @since 7.0
             * 
             * @param   string $name    name of the global variable to get.
             * @return  int             Value of the global variable as an integer or else -1.
             */
            public static function get_global_numeric_var( $name ) {
                $var = self::get_global_var( $name );
                return is_int( $var ) ? $var : 0;
            }

            /**
             * Same as get_global_var but will try and cast the value as a string.
             * 
             * @since 7.0
             * 
             * @param   string $name    name of the global variable to get.
             * @return  int             Value of the global variable as a string or else an empty string.
             */
            public static function get_global_string_var( $name ) {
                $var = self::get_global_var( $name );
                return is_string( $var ) ? trim( $var ) : '';
            }


            /**
             * Helper to check if this WordPress instance is multisite and 
             * a global boolean constant WPO_MU_USE_SUBSITE_OPTIONS has been 
             * configured
             * 
             * @since 7.3
             * 
             * @return boolean True if subsite options should be used 
             */
            public static function mu_use_subsite_options() {
                if( is_multisite()
                    && defined( 'WPO_MU_USE_SUBSITE_OPTIONS' ) 
                    && true === constant( 'WPO_MU_USE_SUBSITE_OPTIONS' ) )
                        return true;
                return false;
            }

            /**
             * Convert keys in an associative array from php style with underscore to camel case.
             * 
             * @since 5.4
             * 
             * @param $assoc_array string Associative options array with keys following PHP camel case naming convention.
             * 
             * @return string Updated (associative) options array with keys following JSON naming convention.
             */
            public static function to_camel_case( $assoc_array ) {
                $result = array();

                if( !is_array( $assoc_array ) )
                    return $result;

                foreach( $assoc_array as $key => $value ) {
                    $key = str_replace( '-', '', $key );
                    $key = strtolower( $key );
                    $func = create_function('$c', 'return strtoupper($c[1]);');
                    $cc_key = preg_replace_callback('/_([a-z])/', $func, $key);
                    $result[ $cc_key ] = $value;
                }

                return $result;
            }

            /**
             * Convert keys in an associative array from camel case to php style with underscore.
             * 
             * @since 5.4
             * 
             * @param $assoc_array string Associative options array with keys following JSON camel case naming convention.
             * 
             * @return string Updated (associative) options array with keys following PHP naming convention.
             */
            public static function from_camel_case( $assoc_array ) {
                $result = array();

                foreach( $assoc_array as $key => $value ) {
                    $func = create_function('$c', 'return "_".strtolower($c[1]);');
                    $php_key = preg_replace_callback('/([A-Z])/', $func, $key);
                    $result[ $php_key ] = $value;
                }

                return $result;
            }

            /**
             * Helper to get an initial options array.
             * 
             * @since   7.0
             * 
             * @return  array   Array with snake cased options
             */
            public static function get_default_options() {
                $default_login_url_path = parse_url( wp_login_url(), PHP_URL_PATH );

                $pages_blacklist = array(
                    '/login/',
                    'admin-ajax.php', 
                    'wp-cron.php', 
                    'xmlrpc.php',
                    $default_login_url_path,
                );

                $default_options = array(
                    'auth_scenario' => 'internet',
                    'pages_blacklist' => $pages_blacklist,
                    'session_duration' => 3600,
                    'version' => '2019',
                );

                return $default_options;
            }

            /**
             * Helper to get the cached WPO365 options to the Wizard.
             * 
             * @since   7.0
             * 
             * @return  array   Array with camel cased options or an empty one if an error occurred.
             */
            public static function get_options() {
                try {
                    self::ensure_options_cache();    
                    return self::to_camel_case( $GLOBALS[ 'wpo365_options' ] );
                }
                catch(\Exception $e) {
                    return array();
                }
            }

            /**
             * Helper to update the cached WPO365 options with options sent
             * from the Wizard.
             * 
             * @since   7.0
             * 
             * @param   array   $updated_options    camelcased options sent by Wizard
             * @return  bool    True if successfully updated otherwise false
             */
            public static function update_options( $updated_options ) {
                try {
                    $camel_case_options = json_decode( base64_decode( $updated_options ), true );
                    $snake_case_options = self::from_camel_case( $camel_case_options );

                    self::ensure_options_cache();
                    $options = $GLOBALS[ 'wpo365_options' ];

                    if( !empty( $options ) ) {
                        foreach( $snake_case_options as $key => $value )        // add to existing options
                            $options[ $key ] = $value;
                    }
                    else {
                        $options = $snake_case_options;                         // or replace all options
                    }

                    ksort( $options);

                    if( self::mu_use_subsite_options() )
                        update_option( 'wpo365_options', $options );
                    else 
                        update_site_option( 'wpo365_options', $options );

                    unset( $GLOBALS[ 'wpo365_options' ] );
                }
                catch(\Exception $e) {
                    return false;
                }
                return true;
            }

            /**
             * Upgrade options from Redux format to a format compatible with our own
             * options application.
             * 
             * @since 5.4
             * 
             * @return Array upgraded options
             */
            private static function upgrade_options( $options ) {
                Logger::write_log( 'DEBUG', 'Upgrading options.' );

                // Remember we've upgraded the options
                $options[ 'version' ] = '2019';
                
                // Standardize option names if needed
                if( isset( $options[ 'wpo365-login_download_link' ] ) ) {
                    $options[ 'wpo365_login_download_link' ] = $options[ 'wpo365-login_download_link' ];
                    unset( $options[ 'wpo365-login_download_link' ] );
                }

                if( isset( $options[ 'WPO_ERROR_NOT_CONFIGURED' ] ) ) {
                    $options[ 'wpo_error_not_configured' ] = $options[ 'WPO_ERROR_NOT_CONFIGURED' ];
                    unset( $options[ 'WPO_ERROR_NOT_CONFIGURED' ] );
                }

                if( isset( $options[ 'WPO_ERROR_CHECK_LOG' ] ) ) {
                    $options[ 'wpo_error_check_log' ] = $options[ 'WPO_ERROR_CHECK_LOG' ];
                    unset( $options[ 'WPO_ERROR_CHECK_LOG' ] );
                }

                if( isset( $options[ 'WPO_ERROR_TAMPERED_WITH' ] ) ) {
                    $options[ 'wpo_error_tampered_with' ] = $options[ 'WPO_ERROR_TAMPERED_WITH' ];
                    unset( $options[ 'WPO_ERROR_TAMPERED_WITH' ] );
                }

                if( isset( $options[ 'WPO_ERROR_USER_NOT_FOUND' ] ) ) {
                    $options[ 'wpo_error_user_not_found' ] = $options[ 'WPO_ERROR_USER_NOT_FOUND' ];
                    unset( $options[ 'WPO_ERROR_USER_NOT_FOUND' ] );
                }

                if( isset( $options[ 'WPO_ERROR_NOT_IN_GROUP' ] ) ) {
                    $options[ 'wpo_error_not_in_group' ] = $options[ 'WPO_ERROR_NOT_IN_GROUP' ];
                    unset( $options[ 'WPO_ERROR_NOT_IN_GROUP' ] );
                }

                if( isset( $options[ 'logout_from_O365' ] ) ) {
                    $options[ 'logout_from_o365' ] = $options[ 'logout_from_O365' ] == '1' ? true : false; 
                    unset( $options[ 'logout_from_O365' ] );
                }

                // Upgrade 2 dimensional arrays
                if( isset( $options[ 'groups_x_roles' ] ) )
                    $options[ 'groups_x_roles' ] = self::to_keyvalues( $options[ 'groups_x_roles' ] );
                
                if( isset( $options[ 'extra_user_fields' ] ) )
                    $options[ 'extra_user_fields' ] = self::to_keyvalues( $options[ 'extra_user_fields' ] );
                
                // Upgrade 1 dimensional arrays
                if( isset( $options[ 'pages_blacklist' ] ) && is_string( $options[ 'pages_blacklist' ] ) )
                    $options[ 'pages_blacklist' ] = array_filter( explode( ';', trim( $options[ 'pages_blacklist' ] ) ) );
                
                if( isset( $options[ 'custom_domain' ] ) && is_string( $options[ 'custom_domain'] ) )
                    $options[ 'custom_domain' ] = array_filter( explode( ';', trim( $options[ 'custom_domain' ] ) ) );
                
                if( isset( $options[ 'groups_whitelist' ] ) && is_string( $options[ 'groups_whitelist' ] ) ) 
                    $options[ 'groups_whitelist' ] = array_filter( explode( ';', trim( $options[ 'groups_whitelist' ] ) ) );
                
                if( isset( $options[ 'domain_whitelist' ] ) && is_string( $options[ 'domain_whitelist' ] ) )
                    $options[ 'domain_whitelist' ] = array_filter( explode( ';', trim( $options[ 'domain_whitelist' ] ) ) );
                
                // Upgrade numbers
                if( isset( $options[ 'session_duration' ] ) )
                    $options[ 'session_duration' ] = intval( $options[ 'session_duration' ] );                
                
                if( isset( $options[ 'avatar_updated' ] ) ) 
                    $options[ 'avatar_updated' ] = intval( $options[ 'avatar_updated' ] );                
                
                if( isset( $options[ 'leeway' ] ) ) 
                    $options[ 'leeway' ] = intval( $options[ 'leeway' ] );                
                
                // Upgrade booleans
                if( isset( $options[ 'intercept_wp_login' ] ) ) 
                    $options[ 'intercept_wp_login' ] = $options[ 'intercept_wp_login' ] == '1' ? true : false;
                
                if( isset( $options[ 'enable_user_sync' ] ) ) 
                    $options[ 'enable_user_sync' ] = $options[ 'enable_user_sync' ] == '1' ? true : false;
                
                if( isset( $options[ 'default_role_as_fallback' ] ) ) 
                    $options[ 'default_role_as_fallback' ] = $options[ 'default_role_as_fallback' ] == '1' ? true : false;

                if( isset( $options[ 'graph_user_details' ] ) ) 
                    $options[ 'graph_user_details' ] = $options[ 'graph_user_details' ] == '1' ? true : false;

                if( isset( $options[ 'use_avatar' ] ) ) 
                    $options[ 'use_avatar' ] = $options[ 'use_avatar' ] == '1' ? true : false;
                
                if( isset( $options[ 'multi_tenanted' ] ) ) 
                    $options[ 'multi_tenanted' ] = $options[ 'multi_tenanted' ] == '1' ? true : false;

                if( isset( $options[ 'block_email_change' ] ) ) 
                    $options[ 'block_email_change' ] = $options[ 'block_email_change' ] == '1' ? true : false;
                
                if( isset( $options[ 'block_password_change' ] ) ) 
                    $options[ 'block_password_change' ] = $options[ 'block_password_change' ] == '1' ? true : false;
                
                if( isset( $options[ 'create_and_add_users' ] ) ) 
                    $options[ 'create_and_add_users' ] = $options[ 'create_and_add_users' ] == '1' ? true : false;
                
                if( isset( $options[ 'skip_host_verification' ] ) ) 
                    $options[ 'skip_host_verification' ] = $options[ 'skip_host_verification' ] == '1' ? true : false;
                
                if( isset( $options[ 'debug_log_id_token' ] ) ) 
                    $options[ 'debug_log_id_token' ] = $options[ 'debug_log_id_token' ] == '1' ? true : false;
                
                if( isset( $options[ 'always_use_goto_after' ] ) ) 
                    $options[ 'always_use_goto_after' ] = $options[ 'always_use_goto_after' ] == '1' ? true : false;
                
                if( isset( $options[ 'enable_token_service' ] ) ) 
                    $options[ 'enable_token_service' ] = $options[ 'enable_token_service' ] == '1' ? true : false;
                
                if( isset( $options[ 'enable_nonce_check' ] ) ) 
                    $options[ 'enable_nonce_check' ] = $options[ 'enable_nonce_check' ] == '1' ? true : false;
                
                if( isset( $options[ 'use_v2' ] ) ) 
                    $options[ 'use_v2' ] = $options[ 'use_v2' ] == '1' ? true : false;
                
                // Upgrade enums
                if( isset( $options[ 'auth_scenario' ] ) ) 
                    $options[ 'auth_scenario' ] = $options[ 'auth_scenario' ] == '1' ? 'intranet' : 'internet';
                
                if( isset( $options[ 'graph_version' ] ) ) 
                    $options[ 'graph_version' ] = $options[ 'graph_version' ] == '1' ? 'current' : 'beta';
                
                if( isset( $options[ 'replace_or_update_user_roles' ] ) ) 
                    $options[ 'replace_or_update_user_roles' ] = $options[ 'replace_or_update_user_roles' ] == '1' ? 'add' : 'replace';

                // Save upgraded options
                return $options;
            }

            /**
             * Copies the wp-config.php values into an array and saves this array as a WP site option.
             * The result must be upgraded after being migrated.
             * 
             * @since 7.0.0
             * 
             * @return void
             */
            private static function migrate_options() {
                Logger::write_log( 'DEBUG', 'Migrating options.' );

                $options = array();

                if( defined( 'WPO_CUSTOM_DOMAIN' ) )
                    $options[ 'custom_domain' ] = constant( 'WPO_CUSTOM_DOMAIN' );
                
                if( defined( 'WPO_DEFAULT_DOMAIN' ) )
                    $options[ 'default_domain' ] = constant( 'WPO_DEFAULT_DOMAIN' );
                
                if( defined( 'WPO_DIRECTORY_ID' ) )
                    $options[ 'tenant_id' ] = constant( 'WPO_DIRECTORY_ID' );

                if( defined( 'WPO_APPLICATION_ID' ) )
                    $options[ 'application_id' ] = constant( 'WPO_APPLICATION_ID' );

                if( defined( 'WPO_APPLICATION_SECRET' ) )
                    $options[ 'application_secret' ] = constant( 'WPO_APPLICATION_SECRET' );

                if( defined( 'WPO_REDIRECT_URL' ) )
                    $options[ 'redirect_url' ] = constant( 'WPO_REDIRECT_URL' );

                if( defined( 'WPO_NONCE_SECRET' ) )
                    $options[ 'nonce_secret' ] = constant( 'WPO_NONCE_SECRET' );

                if( defined( 'WPO_GOTO_AFTER_SIGNON_URL' ) )
                    $options[ 'goto_after_signon_url' ] = constant( 'WPO_GOTO_AFTER_SIGNON_URL' );

                if( defined( 'WPO_PAGES_BLACKLIST' ) )
                    $options[ 'pages_blacklist' ] = constant( 'WPO_PAGES_BLACKLIST' );

                if( defined( 'WPO_DOMAIN_WHITELIST' ) )
                    $options[ 'domain_whitelist' ] = constant( 'WPO_DOMAIN_WHITELIST' );

                if( defined( 'WPO_GROUPS_WHITELIST' ) )
                    $options[ 'groups_whitelist' ] = constant( 'WPO_GROUPS_WHITELIST' );

                if( defined( 'WPO_GROUP_MAPPINGS' ) )
                    $options[ 'groups_x_roles' ] = constant( 'WPO_GROUP_MAPPINGS' );

                if( defined( 'WPO_SESSION_DURATION' ) )
                    $options[ 'session_duration' ] = constant( 'WPO_SESSION_DURATION' );
                    
                if( defined( 'WPO_BLOCK_EMAIL_UPDATE' ) )
                    $options[ 'block_email_change' ] = constant( 'WPO_BLOCK_EMAIL_UPDATE' );

                if( defined( 'WPO_BLOCK_PASSWORD_UPDATE' ) )
                    $options[ 'block_password_change' ] = constant( 'WPO_BLOCK_PASSWORD_UPDATE' );

                if( defined( 'WPO_GRAPH_VERSION' ) )
                    $options[ 'graph_version' ] = constant( 'WPO_GRAPH_VERSION' );

                if( defined( 'WPO_AUTH_SCENARIO' ) )
                    $options[ 'auth_scenario' ] = constant( 'WPO_AUTH_SCENARIO' );

                if( defined( 'WPO_CREATE_ADD_USERS' ) )
                    $options[ 'create_and_add_users' ] = constant( 'WPO_CREATE_ADD_USERS' );

                if( defined( 'WPO_DEFAULT_ROLE_MAIN_SITE' ) )
                    $options[ 'new_usr_default_role' ] = constant( 'WPO_DEFAULT_ROLE_MAIN_SITE' );

                if( defined( 'WPO_DEFAULT_ROLE_SUB_SITE' ) )
                    $options[ 'mu_new_usr_default_role' ] = constant( 'WPO_DEFAULT_ROLE_SUB_SITE' );

                if( defined( 'WPO_SKIP_SSL_HOST_VERIFICATION' ) )
                    $options[ 'skip_host_verification' ] = constant( 'WPO_SKIP_SSL_HOST_VERIFICATION' );

                if( defined( 'WPO_DEBUG_LOG_ID_TOKEN' ) )
                    $options[ 'debug_log_id_token' ] = constant( 'WPO_DEBUG_LOG_ID_TOKEN' );

                if( defined( 'WPO_MAIL_MIME_TYPE' ) )
                    $options[ 'mail_mime_type' ] = constant( 'WPO_MAIL_MIME_TYPE' );

                if( defined( 'WPO_MAIL_SAVE_TO_SENT' ) )
                    $options[ 'mail_save_on_sent' ] = constant( 'WPO_MAIL_SAVE_TO_SENT' );

                if( defined( 'WPO_LEEWAY' ) )
                    $options[ 'leeway' ] = constant( 'WPO_LEEWAY' );

                if( defined( 'WPO_AVATAR_REFRESH' ) )
                    $options[ 'avatar_updated' ] = constant( 'WPO_AVATAR_REFRESH' );

                if( defined( 'WPO_USE_AVATAR' ) )
                    $options[ 'use_avatar' ] = constant( 'WPO_USE_AVATAR' );

                if( defined( 'WPO_INTERCEPT_WP_LOGIN' ) )
                    $options[ 'intercept_wp_login' ] = constant( 'WPO_INTERCEPT_WP_LOGIN' );

                if( defined( 'WPO_LOGOUT_FROM_O365' ) )
                    $options[ 'logout_from_O365' ] = constant( 'WPO_LOGOUT_FROM_O365' );

                if( defined( 'WPO_POST_SIGNOUT_URL' ) )
                    $options[ 'post_signout_url' ] = constant( 'WPO_POST_SIGNOUT_URL' );

                if( defined( 'WPO_GRAPH_USER_DETAILS' ) )
                    $options[ 'graph_user_details' ] = constant( 'WPO_GRAPH_USER_DETAILS' );

                if( defined( 'WPO_EXTRA_USER_FIELDS' ) )
                    $options[ 'extra_user_fields' ] = constant( 'WPO_EXTRA_USER_FIELDS' );

                if( defined( 'WPO_LOGIN_DOWNLOAD_LINK' ) )
                    $options[ 'wpo365-login_download_link' ] = constant( 'WPO_LOGIN_DOWNLOAD_LINK' );

                if( defined( 'WPO_ENABLE_USER_SYNC' ) )
                    $options[ 'enable_user_sync' ] = constant( 'WPO_ENABLE_USER_SYNC' );

                if( defined( 'WPO_MULTI_TENANTED' ) )
                    $options[ 'multi_tenanted' ] = constant( 'WPO_MULTI_TENANTED' );

                if( defined( 'WPO_REPLACE_OR_UPDATE_USER_ROLE' ) )
                    $options[ 'replace_or_update_user_roles' ] = constant( 'WPO_REPLACE_OR_UPDATE_USER_ROLE' );

                if( defined( 'WPO_DEFAULT_ROLE_AS_FALLBACK' ) )
                    $options[ 'default_role_as_fallback' ] = constant( 'WPO_DEFAULT_ROLE_AS_FALLBACK' );

                if( defined( 'WPO_ALWAYS_USE_GOTO_AFTER' ) )
                    $options[ 'always_use_goto_after' ] = constant( 'WPO_ALWAYS_USE_GOTO_AFTER' );

                if( defined( 'WPO_ENABLE_TOKEN_SERVICE' ) )
                    $options[ 'enable_token_service' ] = constant( 'WPO_ENABLE_TOKEN_SERVICE' );

                if( defined( 'WPO_ERROR_NOT_CONFIGURED' ) )
                    $options[ 'WPO_ERROR_NOT_CONFIGURED' ] = constant( 'WPO_ERROR_NOT_CONFIGURED' );

                if( defined( 'WPO_ERROR_CHECK_LOG' ) )
                    $options[ 'WPO_ERROR_CHECK_LOG' ] = constant( 'WPO_ERROR_CHECK_LOG' );

                if( defined( 'WPO_ERROR_TAMPERED_WITH' ) )
                    $options[ 'WPO_ERROR_TAMPERED_WITH' ] = constant( 'WPO_ERROR_TAMPERED_WITH' );

                if( defined( 'WPO_ERROR_USER_NOT_FOUND' ) )
                    $options[ 'WPO_ERROR_USER_NOT_FOUND' ] = constant( 'WPO_ERROR_USER_NOT_FOUND' );

                if( defined( 'WPO_ERROR_NOT_IN_GROUP' ) )
                    $options[ 'WPO_ERROR_NOT_IN_GROUP' ] = constant( 'WPO_ERROR_NOT_IN_GROUP' );

                if( defined( 'WPO_ENABLE_NONCE_CHECK' ) )
                    $options[ 'enable_nonce_check' ] = constant( 'WPO_ENABLE_NONCE_CHECK' );

                if( defined( 'WPO_USE_V2' ) )
                    $options[ 'use_v2' ] = constant( 'WPO_USE_V2' );

                return $options;
            }

            /**
             * Converts a string based key and value pair to an object style key value pair
             * e.g. from "c93f6d7c-1a8b-4421-b87b-bbc67ed396a3,author;" to 
             * { key: "c93f6d7c-1a8b-4421-b87b-bbc67ed396a3", value="author" }
             * 
             * @since 5.4
             * 
             * @param $option string Key value pairs as string e.g. key1,value1;key2,value2;...
             * 
             * @return Array associative array in the form of array( 'key1' => 'value2', 'key2' => 'value2' )...
             */
            private static function to_keyvalues( $option ) {
                if( false === is_string( $option ) )
                    return array();
                
                $kv_str_arr = array_filter( explode( ';', trim( $option ) ) );
                $kv_arr = array_map( function( $kv_str ) {
                    $kv = array_filter( explode( ',', $kv_str ) );
                    return array( 'key' => $kv[0], 'value' => $kv[1] );
                }, $kv_str_arr );
                $kv_arr = is_array( $kv_arr ) ? $kv_arr : array();

                return $kv_arr;
            }
        }
    }

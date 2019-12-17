<?php

    namespace Wpo\API;

    use \Wpo\Aad\Auth;
    use \Wpo\Util\Helpers;
    use \Wpo\Util\Logger;
    use \Wpo\Util\License;
    use \Wpo\Util\Options;

    if( !class_exists( '\Wpo\API\Services' ) ) {
    
        class Services  {

            /**
             * Gets the tokencache with all available bearer tokens
             *
             * @since 5.0
             *
             * @return void
             */
            public static function get_tokencache() {
                if( false === Options::get_global_boolean_var( 'enable_token_service' ) )
                    wp_die();

                // Verify AJAX request
                $current_user = Services::verify_ajax_request( 'to get the tokencache for a user' );
                $use_v2 = Options::get_global_boolean_var( 'use_v2' );

                // Verify data POSTed
                $posted_vars = $use_v2
                    ? array( 'action', 'scope' )
                    : array( 'action', 'resource' );
                
                self::verify_POSTed_data( $posted_vars ); // -> wp_die()

                $access_token = $use_v2
                    ? Auth::get_bearer_token_v2( $_POST[ 'scope' ] )
                    : Auth::get_bearer_token( $_POST[ 'resource' ] );
                    
                if( is_wp_error( $access_token ) )
                    self::AJAX_response( 'NOK', $access_token->get_error_code(), $access_token->get_error_message(), null );

                $result = new \stdClass();
                $result->expiry = $access_token->expiry;
                $result->accessToken = $access_token->access_token;

                self::AJAX_response( 'OK', '', '', json_encode( $result ) );
            }

            /**
             * Delete all access and refresh tokens.
             *
             * @since xxx
             */
            public static function delete_tokens() {
                // Verify AJAX request
                $current_user = Services::verify_ajax_request( 'to delete access and refresh tokens' );
                if( false === Helpers::user_is_admin( $current_user ) ) {
                    Logger::write_log( 'ERROR', 'User has no permission to get wpo365_options from AJAX service' );
                    wp_die();
                }

                try {
                    global $wpdb;

                    $query_result = $wpdb->query( 
                        $wpdb->prepare( 
                            "DELETE FROM $wpdb->usermeta
                            WHERE meta_key like %s 
                            OR meta_key like %s", 
                                'wpo_access%', 'wpo_refresh%' 
                        )
                    );

                    if( false === $query_result )
                        self::AJAX_response( 'NOK', '', '', null);
                    else
                        self::AJAX_response( 'OK', '', '', null );
                }
                catch( \Exception $e ) {
                    $error_message = $e->getMessage;
                    Logger::write_log( 'ERROR', 'AJAX request for settings failed: ' . $error_message );
                    self::AJAX_response( 'NOK', '', $error_message, null );
                }
            }

            /**
             * Gets the tokencache with all available bearer tokens
             *
             * @since 6.0
             *
             * @return void
             */
            public static function get_settings() {
                // Verify AJAX request
                $current_user = Services::verify_ajax_request( 'to get the wpo365-login settings' );
                if( false === Helpers::user_is_admin( $current_user ) ) {
                    Logger::write_log( 'ERROR', 'User has no permission to get wpo365_options from AJAX service' );
                    wp_die();
                }

                $camel_case_options = Options::get_options();
                self::AJAX_response( 'OK', '', '', json_encode( $camel_case_options ) );
            }

            /**
             * Gets the tokencache with all available bearer tokens
             *
             * @since 6.0
             *
             * @return void
             */
            public static function update_settings() {
                // Verify AJAX request
                $current_user = Services::verify_ajax_request( 'to update the wpo365-login settings' );
                if( false === Helpers::user_is_admin( $current_user ) ) {
                    Logger::write_log( 'ERROR', 'User has no permission to get wpo365_options from AJAX service' );
                    wp_die();
                }

                self::verify_POSTed_data( array( 'settings' ) ); // -> wp_die()

                $updated = Options::update_options( $_POST[ 'settings' ] );

                self::AJAX_response( true === $updated ? 'OK' : 'NOK', '', '', null );
            }

            /**
             * Tries to activate the license using the previously saved license key.
             *
             * @since 6.0
             *
             * @return void
             */
            public static function activate_license() {
                // Verify AJAX request
                $current_user = Services::verify_ajax_request( 'to activate license' );
                if( false === Helpers::user_is_admin( $current_user ) ) {
                    Logger::write_log( 'ERROR', 'User has no permission to get wpo365_options from AJAX service' );
                    wp_die();
                }

                self::verify_POSTed_data( array() ); // -> wp_die()

                $activation_result = License::check_license();
                $status = $activation_result === true ? 'OK' : 'NOK';
                $error_message = $activation_result === true ? '' : $activation_result;

                self::AJAX_response( $status, '', $error_message, null );
            }

            /**
             * Gets the debug log
             *
             * @since 7.11
             *
             * @return void
             */
            public static function get_log() {
                // Verify AJAX request
                $current_user = Services::verify_ajax_request( 'to get the wpo365-login debug log' );

                if( false === Helpers::user_is_admin( $current_user ) ) {
                    Logger::write_log( 'ERROR', 'User has no permission to get wpo365_log from AJAX service' );
                    wp_die();
                }

                $log = get_option( 'wpo365_log', array() );
                $log = array_reverse( $log );
                self::AJAX_response( 'OK', '', '', json_encode( $log ) );
            }

            /**
             * Checks for valid nonce and whether user is logged on and returns WP_User if OK or else
             * writes error response message and return it to requester
             *
             * @since 5.0
             *
             * @param   string      $error_message_fragment used to write a specific error message to the log
             * @return  WP_User if verified or else error response is returned to requester
             */
            public static function verify_ajax_request( $error_message_fragment )  {
                $error_message = '';

                if ( !is_user_logged_in() )
                    $error_message = 'Attempt ' . $error_message_fragment . ' by a user that is not logged on';

                if ( Options::get_global_boolean_var( 'enable_nonce_check' ) 
                    && ( !isset( $_POST[ 'nonce' ] )
                    || !wp_verify_nonce( $_POST[ 'nonce' ], 'wpo365_fx_nonce' ) ) )
                        $error_message = 'Request ' . $error_message_fragment . ' has been tampered with (invalid nonce)';

                if (strlen($error_message) > 0) {
                    Logger::write_log('DEBUG', $error_message);

                    $response = array('status' => 'NOK', 'message' => $error_message, 'result' => array());
                    wp_send_json($response);
                    wp_die();
                }

                return wp_get_current_user();
            }

            /**
             * Stops the execution of the program flow when a key is not found in the the global $_POST
             * variable and returns a given error message
             *
             * @since 5.0
             *
             * @param   array   $keys array of keys to search for
             * @return void
             */
            public static function verify_POSTed_data( $keys, $sanitize = true ) {

                foreach ( $keys as $key ) {

                    if ( !array_key_exists( $key, $_POST ) ) 
                        self::AJAX_response( 'NOK', '1000', 'Incomplete data posted to complete request: ' . implode( ', ', $keys ), array() );

                    if( $sanitize ) {
                        $_POST[ $key ] = sanitize_text_field( $_POST[ $key ] );
                    }
                }
            }

            /**
             * Helper method to standardize response returned from a Pintra AJAX request
             *
             * @since 5.0
             *
             * @param   string  $status OK or NOK
             * @param   string  $message customer message returned to requester
             * @param   mixed   $result associative array that is parsed as JSON and returned
             * @return void
             */
            public static function AJAX_response($status, $error_codes, $message, $result) {
                Logger::write_log('DEBUG', "Sending an AJAX response with status $status and message $message");
                wp_send_json(array('status' => $status, 'error_codes' => $error_codes, 'message' => $message, 'result' => $result));
                wp_die();
            }
        }
    }

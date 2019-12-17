<?php

    namespace Wpo\Aad;

    // prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Util\Logger;
    use \Wpo\Util\Options;

    if( !class_exists( '\Wpo\Aad\Msft_Graph' ) ) {

        class Msft_Graph {

            const REST_API = "https://graph.microsoft.com/";
            const GRAPH_VERSION = "v1.0";
            const GRAPH_VERSION_BETA = "beta";
            const RESOURCE = 'graph_resource';
            const RESOURCE_ID = 'https://graph.microsoft.com';

            /**
             * Connects to Microsoft Graph REST api to get retrieve data on the basis of the query presented
             *
             * @since 0.1
             *
             * @param   string  $query  query part of the Graph query e.g. '/me/photo/$'
             * @param   string  $method HTTP Method (default GET)
             * @param   boolean $binary Get binary data e.g. when getting user profile image
             * @param   array   $headers
             * @param   string  $post_fields
             * @param   string  $scope
             * @return  mixed(object|WP_Error) JSON string as PHP object or false
             *
             */
            public static function fetch( $query, $method = 'GET', $binary = false, $headers = array(), $post_fields = "", $scope = 'https://graph.microsoft.com/user.read' ) {

                $access_token = Options::get_global_boolean_var( 'use_v2' )
                    ? Auth::get_bearer_token_v2( $scope )
                    : Auth::get_bearer_token( self::RESOURCE_ID );

                if( is_wp_error( $access_token ) ) {
                    $error_message = 'Could not retrieve an access token for ' . self::RESOURCE_ID . '. Innerexception: ' . $access_token->get_error_message();
                    Logger::write_log( 'ERROR', $error_message );
                    return new \WP_Error( '7000', $error_message );
                }

                $bearer = 'Authorization: Bearer ' . $access_token->access_token;
                $headers[] = $bearer;
                
                $graph_version = Options::get_global_string_var( 'graph_version' );
                $graph_version = empty( $graph_version) || $graph_version == 'current' 
                                 ? self::GRAPH_VERSION 
                                 : ( $graph_version == 'beta' ? self::GRAPH_VERSION_BETA : self::GRAPH_VERSION );

                $url = self::REST_API . $graph_version . $query;

                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, $url );
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );

                if( $method == 'POST' )
                    curl_setopt( $curl, CURLOPT_POSTFIELDS, $post_fields );

                if( $binary )
                    curl_setopt($curl, CURLOPT_BINARYTRANSFER, 1);

                if( Options::get_global_boolean_var( 'skip_host_verification' ) ) {
                    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0); 
                    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0); 
                }

                Logger::write_log( 'DEBUG', 'Requesting data from Microsoft Graph using query: ' . $url );
                $raw = curl_exec( $curl );
                $curl_response_code = curl_getinfo( $curl, CURLINFO_HTTP_CODE );

                if( curl_error( $curl ) ) {
                    $error_message = 'Error occured whilst fetching from Microsoft Graph: ' . curl_error( $curl );
                    Logger::write_log( 'ERROR', $error_message );
                    curl_close( $curl );
                    return new \WP_Error( '7020', $error_message );
                }

                curl_close( $curl );

                if( !$binary)
                    $raw = json_decode( $raw, true );
                    
                return array( 'payload' => $raw, 'response_code' => $curl_response_code );
            }
        }
    }
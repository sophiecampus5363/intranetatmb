<?php

    namespace Wpo\Util;

    // Prevent public access to this script
    defined( 'ABSPATH' ) or die();

    if( !class_exists( '\Wpo\Util\Logger' ) ) {

        class Logger {
            /**
             * Writes a message to the Wordpress debug.log file
             *
             * @since   1.0
             * 
             * @param   string  level => The level to log e.g. DEBUG or ERROR
             * @param   string  log => Message to write to the log
             */
            public static function write_log( $level, $log ) {
                // Using Options class causes circular reference
                $debug_log = isset( $GLOBALS[ 'wpo365_options' ] ) 
                    && isset( $GLOBALS[ 'wpo365_options' ][ 'debug_log' ] ) 
                    && $GLOBALS[ 'wpo365_options' ][ 'debug_log' ] === true
                        ? true
                        : false;
                
                if( $level == 'DEBUG' && false === $debug_log ) {
                    return; 
                }
                
                $body = is_array( $log ) || is_object( $log ) ? print_r( $log, true ) : $log;
                $now = \DateTime::createFromFormat( 'U.u', number_format( microtime( true ), 6, '.', '' ) );
                $message = '[' . $now->format( 'm-d-Y H:i:s.u' ) . '] ' . $level . ' ( ' . phpversion() .' ): ' . $body;

                $request_log = !empty( $GLOBALS[ 'WPO365_LOG' ] )
                    ? $GLOBALS[ 'WPO365_LOG' ]
                    : array();

                $request_log[] = $message;

                // Once every day a flag - used to show an admin notice - is set in case an error occurred
                if( $level == 'ERROR' && false === get_transient( 'wpo365_has_errors' ) ) {
                    set_transient( 'wpo365_has_errors', date( 'd' ), 172800 );
                }

                $GLOBALS[ 'WPO365_LOG' ] = $request_log;
            }

            /**
             * Writes the log file to the defined output stream
             * 
             * @since 7.11
             * 
             * @return void
             */
            public static function flush_log() {

                // Nothing to flush
                if( empty( $GLOBALS[ 'WPO365_LOG' ] ) ) {
                    return;
                }

                $wpo365_log = get_option( 'wpo365_log' );
                
                if( !is_array( $wpo365_log ) ) {
                    $wpo365_log = array();
                }
                
                $wpo365_log = array_merge( $wpo365_log, $GLOBALS[ 'WPO365_LOG' ] );
                $count = sizeof( $wpo365_log );
                
                if( $count > 500 ) {
                    $wpo365_log = array_slice( $wpo365_log, ( $count - 500 ) );
                }
                
                // Store the log in the wp_options table
                update_option( 'wpo365_log', $wpo365_log );
                
                // Still also write it to default debug output
                if( defined( 'WP_DEBUG' ) && constant( 'WP_DEBUG' ) === true ) {
                    
                    foreach( $GLOBALS[ 'WPO365_LOG' ] as $message ) {
                        error_log( $message );
                    }
                }
            }
        }
    }

?>
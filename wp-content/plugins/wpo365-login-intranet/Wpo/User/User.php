<?php

    namespace Wpo\User;

    // prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Util\Logger;

	if( !class_exists( '\Wpo\User\User' ) ) {
    
		class User {

			/**
			 * Email address of user
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			public $email = null;

			/**
			 * Unique user's principal name
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			public $upn = null;

			/**
			 * Name of user
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			public $name = null;

			/**
			 * User's first name
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			public $first_name = null;

			/**
			 * User's last name incl. middle name etc.
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			public $last_name = null;

			/**
			 * User's full ( or display ) name
			 *
			 * @since 1.0.0
			 *
			 * @var string
			 */
			public $full_name = null;
			
			/**
			 * Office 365 and/or Azure AD group ids 
			 */
			public $groups = array();
            
            /**
             * Primarily now relying on unique_name (v1.0) and preferred_username (v2.0).
             * 
             * @since 7.17
             * 
             * @param $id_token string The open ID connect token received.
             * @return mixed(User|WP_Error) A new User object created from the id_token or WP_Error if the ID token could not be parsed
             */
            public static function user_from_id_tokenV2( $id_token ) {
                $unique_name = isset( $id_token->unique_name ) 
                    ? trim( strtolower( $id_token->unique_name ) )
                    : NULL;
                
                $preferred_username = isset( $id_token->preferred_username ) 
                    ? trim( strtolower( $id_token->preferred_username ) )
                    : NULL;
                
                $upn = self::get_property_or_default( 
                    $id_token, 
                    'upn',
                    '', 
                    true,
                    'The ID token does not contain the user principal name. The administrator should consult 
                    https://www.wpo365.com/azure-application-registration/#upn on how to manually update the 
                    Azure AD application registration manifest and request the optional upn claim.' );
                
                if( empty( $upn ) || false !== stripos( $upn, '#ext#' ) ) {
                    
                    // ID Token Azure AD v1.
                    if( !empty( $unique_name ) ) {
                        $upn = $unique_name;
                    }
                    // ID Token Azure AD v2.0
                    else if( !empty( $preferred_username ) ) {
                        $upn = $preferred_username;
                    }
                }

                $email = self::get_property_or_default( 
                    $id_token, 
                    'email', 
                    '',
                    true,
                    'The ID token does not contain the user email address. The administrator should consult 
                     https://www.wpo365.com/azure-application-registration/#upn on how to manually update the 
                     Azure AD application registration manifest and request the optional email claim. Alternatively,
                     the plugin can create fake email addresses based on a user\'s login name when you check 
                     WP Admin > WPO365 > Miscellaneous > Use older ID token parser.' );
                
                if( empty( $upn ) || empty( $email ) ) {
                    Logger::write_log( 'ERROR', 'Could not parse the ID token: ' );
                    Logger::write_log( 'ERROR', array( 
                        'unique_name' => $unique_name, 
                        'preferred_name' => $preferred_username,
                        'upn' => $upn,
                        'email' => $email,
                        ) 
                    );
                    
                    return new \WP_Error( '4021', 'Could not parse the ID token. Please check the log for errors.' );
                }

                $first_name = self::get_property_or_default( 
                    $id_token, 
                    'given_name', 
                    '',
                    false,
                    'The ID token does not contain the user first name. The administrator should consult 
                    https://www.wpo365.com/azure-application-registration/#upn on how to manually update the 
                    Azure AD application registration manifest and request the optional first name claim.' );
                
                $last_name = self::get_property_or_default( 
                    $id_token, 
                    'family_name', 
                    '',
                    false,
                    'The ID token does not contain the user last name. The administrator should consult 
                    https://www.wpo365.com/azure-application-registration/#upn on how to manually update the 
                    Azure AD application registration manifest and request the optional last name claim.' );
                
                $full_name = isset( $id_token->name ) 
                    ? trim( $id_token->name )
                    : '';

                $usr = new User();
				$usr->first_name = $first_name;
				$usr->last_name = $last_name;
				$usr->full_name = $full_name;
				$usr->email = $email;
				$usr->upn = $upn;
                $usr->name = $upn;
                				
				if( property_exists( $id_token, 'groups' ) ) {
                    $usr->groups = array_flip( $id_token->groups );
                }
                else {
                    Logger::write_log( 'DEBUG', 'No Azure AD groups were received as part of the ID token' );
                }

				return $usr;
            }
            
			/**
			 * Parse id_token received from Azure Active Directory and return User object
			 *
			 * @since 1.0
			 *
			 * @param string 	$id_token  token received from Azure Active Directory
			 * @return mixed(User|WP_Error) A new User object created from the id_token or WP_Error if the ID token could not be parsed
			 */
			public static function user_from_id_token( $id_token ) {
                // Try and detect an MSA account that has no upn but instead an email property
                $email = isset( $id_token->email ) && !empty( $id_token->email)
                    ? $id_token->email 
                    : ( 
                        isset( $id_token->upn ) && !empty( $id_token->upn)
                            ? $id_token->upn 
                            : (
                                isset( $id_token->preferred_username ) && !empty( $id_token->preferred_username)
                                    ? $id_token->preferred_username
                                    : NULL
                            ) 
                    );
				
				if( empty( $email ) ) {
                    return new \WP_Error( '4022', 'Could not parse the ID token (using older ID token parser).' );
                }

                $upn = isset( $id_token->upn ) && !empty( $id_token->upn)
                    ? $id_token->upn 
                    : (
                        isset( $id_token->preferred_username ) && !empty( $id_token->preferred_username)
                            ? $id_token->preferred_username
                            : $email
                    );
                
                if( false !== stripos( $upn, '#ext#' ) ) {
                    $upn = $email;
                }

				$unique_name = isset( $id_token->unique_name ) && !empty( $id_token->unique_name) ? $id_token->unique_name : $upn;

                $usr = new User();
				$usr->first_name = isset( $id_token->given_name ) && !empty( $id_token->given_name) ?  $id_token->given_name : '';
				$usr->last_name = isset( $id_token->family_name ) && !empty( $id_token->family_name) ? $id_token->family_name : '';
				$usr->full_name = isset( $id_token->name ) && !empty( $id_token->name) ? $id_token->name : '';
				$usr->email = $email;
				$usr->upn = $upn;
				$usr->name = $unique_name;
				
				if( property_exists( $id_token, 'groups' ) )
					$usr->groups = array_flip( $id_token->groups );

				return $usr;
			}
			
			/**
			 * Parse graph user response received and return User object. This method may return a user
			 * without an email address.
			 *
			 * @since 2.2
			 *
			 * @param string 	$user  received from Microsoft Graph
			 * @return User  	A new User Object created from the graph response
			 */
			public static function user_from_graph_user( $graph_user ) {

                $usr = new User();
                
                $is_aad_guest = isset( $graph_user[ 'userPrincipalName' ] ) && false !== stripos( $graph_user[ 'userPrincipalName' ], '#ext#' );

				$usr->first_name = isset( $graph_user[ 'givenName' ] ) ?  $graph_user[ 'givenName' ] : '';
				$usr->last_name = isset( $graph_user[ 'surname' ] ) ? $graph_user[ 'surname' ] : '';
				$usr->full_name = isset( $graph_user[ 'displayName' ] ) ? $graph_user[ 'displayName' ] : '';
				$usr->email = isset( $graph_user[ 'mail' ] ) ? $graph_user[ 'mail' ] : '';
                $usr->upn = $is_aad_guest && !empty( $usr->email ) 
                    ? $usr->email 
                    : ( isset( $graph_user[ 'userPrincipalName' ] ) 
                        ? $graph_user[ 'userPrincipalName' ] 
                        : '' );
                $usr->name = !empty( $usr->full_name )
                    ? $usr->full_name
                    : $usr->upn;
                
				return $usr;	
            }
            
            private static function get_property_or_default( 
                $id_token, 
                $prop, 
                $default = '', 
                $tolower = false, 
                $log_message = '' ) {
                    if( isset( $id_token->$prop )  && !empty( $id_token->$prop) ) {
                        return $tolower
                            ? strtolower( trim( $id_token->$prop ) )
                            : trim( $id_token->$prop );
                    }
                    Logger::write_log( 'ERROR', $log_message );
                    return '';
            }
		}
	}

?>
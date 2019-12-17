<?php

    // Prevent public access to this script
    defined( 'ABSPATH' ) or die();

    use \Wpo\Util\Helpers;

    global $wp_roles;

    $plugin_version = preg_replace_callback(
        '/_([a-z])/', 
        function( $c ) { 
            return strtoupper($c[1]); 
        }, 
        strtolower( str_replace( '-', '_', $GLOBALS[ 'WPO365_SLUG' ] ) ) );

    $props = array( 
        'siteUrl'           => get_site_url(), 
        'adminUrl'          => get_site_url( null, '/wp-admin' ),
        'nonce'             => Helpers::get_nonce(),
        'pluginVersion'     => $plugin_version, // e.g. wpo365Login, wpo365LoginUnlimited, wpo365LoginPremium
        'availableRoles'    => json_encode( $wp_roles->roles ),
    );
    
    ?>
    
        <!-- Main -->
        <div>
            <script src="<?php echo $GLOBALS[ 'WPO365_PLUGIN_URL' ] ?>apps/dist/wizard.js?cb=<?php echo $GLOBALS[ $GLOBALS[ 'WPO365_VERSION_KEY' ] ] ?>" 
                data-nonce="<?php echo wp_create_nonce( 'wpo365_fx_nonce' ) ?>"
                data-wpajaxadminurl="<?php echo admin_url() . 'admin-ajax.php' ?>"
                data-props="<?php echo htmlspecialchars( json_encode( $props ) ) ?>">
            </script>
            <!-- react root element will be added here -->
        </div>

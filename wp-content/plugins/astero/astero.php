<?php
/*
 * Plugin Name: Astero WordPress Weather Plugin
 * Plugin URI: http://archtheme.com/astero
 * Description: Weather Widget Plugin.
 * Author: ArchTheme
 * Author URI: http://archtheme.com/
 * Version: 1.4.3
 */

// Define plugin path
if ( !defined('ASTERO_PATH') ) {
       define('ASTERO_PATH', plugin_dir_path(__FILE__) ); 
}

// Define plugin url
if ( !defined('ASTERO_URL') ) {
       define('ASTERO_URL', plugins_url( '/', __FILE__) ); 
}

// Define plugin name
if ( !defined('ASTERO_NAME') ) {
        define('ASTERO_NAME', "Astero Weather Plugin");
}

// Define plugin version
if ( !defined('ASTERO_VERSION') ) {
        define ("ASTERO_VERSION", "1.4.3");
}

// Define plugin slug
if ( !defined('ASTERO_SLUG') ) {
      define ("ASTERO_SLUG", 'astero');  
}

// Define plugin options name
if ( !defined('ASTERO_OPTIONS') ) {
      define ("ASTERO_OPTIONS", 'astero_options');  
}

/* ==========================================================================
   Internalisation and Translation
   ========================================================================== */
add_action('plugins_loaded', 'astero_load_language');

if (!function_exists('astero_load_language'))
{
	function astero_load_language()
	{
		load_plugin_textdomain( ASTERO_SLUG, false, dirname( plugin_basename( __FILE__ ) ) . '/languages');
	}
}

/* ==========================================================================
   Activation and Uninstall Hooks
   ========================================================================== */
if( !function_exists('_astero_activate') )
{
        function _astero_activate() {
                
                // If previous versions exist, regenerate custom.css
                if( get_option( 'astero_weather_version' ) ) {
                        astero_update_static_css();
                }
                
                // Save plugin version in db
                add_option( 'astero_weather_version', ASTERO_VERSION );
        }
}

if( !function_exists('astero_network_propagate') )
{
        function astero_network_propagate($function, $networkwide) {
                
                // Multisite activation
                if (function_exists('is_multisite') && is_multisite()) {
                        // check if it is a network activation - if so, run the activation function for each blog id
                        if ( $networkwide ) {
                                global $wpdb;
                                
                                $old_blog = $wpdb->blogid;
                                // Get all blog ids
                                $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
                                
                                foreach ($blogids as $blog_id) {
                                        switch_to_blog($blog_id);
                                        call_user_func($function, $networkwide);
                                }
                                
                                switch_to_blog($old_blog);
                                return;
                        }   
                } 
                
                // Single site activation
                call_user_func($function, $networkwide);
        }
}

if( !function_exists('astero_activate') )
{
        function astero_activate( $networkwide ) {
                
                // Network propagate
                astero_network_propagate('_astero_activate', $networkwide);
        }
}
register_activation_hook(__FILE__, 'astero_activate');

/* ==========================================================================
   Includes
   ========================================================================== */
include_once( ASTERO_PATH . 'admin/astero-admin.php'); //admin
include_once( ASTERO_PATH . 'public/astero-public.php'); //public

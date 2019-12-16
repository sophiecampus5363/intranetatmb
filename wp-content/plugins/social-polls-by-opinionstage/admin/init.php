<?php
// block direct access to plugin PHP files:
defined( 'ABSPATH' ) or die();

require( plugin_dir_path( __FILE__ ).'opinionstage-login-callback.php' );
require( plugin_dir_path( __FILE__ ).'opinionstage-disconnect.php' );
require( plugin_dir_path( __FILE__ ).'opinionstage-content-login-callback.php' );
require( plugin_dir_path( __FILE__ ).'menu-page.php' );
require( plugin_dir_path( __FILE__ ).'admin-page-loader.php' );
require( plugin_dir_path( __FILE__ ).'menu-target.php' );
require( plugin_dir_path( __FILE__ ).'message-handler.php' );
	
?>
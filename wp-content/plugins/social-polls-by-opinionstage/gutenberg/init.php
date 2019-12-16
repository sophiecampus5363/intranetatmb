<?php
// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
 // Adding a block opinion-stage for below elements 
add_filter( 'block_categories', 'osplugin_guten_blockCategories', 10, 2 );	
function osplugin_guten_blockCategories( $categories, $post ) {
	return array_merge(
		$categories,
		array(
			array(
				'slug' => 'opinion-stage',
				'title' => __( 'Interactive Content by OpinionStage', 'opinion-stage' ),
			),
		)
	);
}


/**
 * BLOCK: Poll.
 */
require( plugin_dir_path( __FILE__ ).'poll/src/init.php' );
/**
 * BLOCK: Trivia.
 */
require( plugin_dir_path( __FILE__ ).'trivia/src/init.php' );
/**
 * BLOCK: Personality.
 */
require( plugin_dir_path( __FILE__ ).'personality/src/init.php' );
/**
 * BLOCK: Survey.
 */
require( plugin_dir_path( __FILE__ ).'survey/src/init.php' );
/**
 * BLOCK: Slideshow.
 */
require( plugin_dir_path( __FILE__ ).'slideshow/src/init.php' );
/**
 * BLOCK: Form.
 */
require( plugin_dir_path( __FILE__ ).'form/src/init.php' );


function oswp_gutenberg_enqueue_scripts() {
  // Fetching options for opinionstage connection
		$os_options =(array) get_option(OPINIONSTAGE_OPTIONS_KEY);
		$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
		$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']; 

		// get admin url for opinionstage plugin
		$adminUrlForOs = opinionstage_callback_url_gutenberg_connect();

		// opinionstage plugin version
		$OswpPluginVersion = OPINIONSTAGE_WIDGET_VERSION;

		// Fetch Url For Ajax Call Opinion Stage
		$FetchUrlOS = OPINIONSTAGE_SERVER_BASE.'/api/wp/v1/my/widgets';

		// Url For Creating New Content OR Template On Opinion Stage
		$getUrlFormAction = OPINIONSTAGE_SERVER_BASE.'/integrations/wordpress/new';

		// Opninionstge logo image link
		$logoImagelinkOs = plugin_dir_url( __FILE__ ) . 'image/os-logo.png';

		// Opinionstage view item count
		$OsOptions = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);


		// Data to pass to gutenberg editor
	    $dataToPass = array(
	        'isOsConnected'         => (isset($os_options['uid']) && $os_options['uid'] != '') ? true : false,
	        'onCreateButtonClickOs' => OPINIONSTAGE_SERVER_BASE.'/api/wp/redirects/widgets/new',
	        'callbackUrlOs'         => $adminUrlForOs,
	        'OswpPluginVersion'     => $OswpPluginVersion,
	        'OswpClientToken'       => opinionstage_user_access_token(),
	        'OswpFetchDataUrl'      => $FetchUrlOS,
	        'getActionUrlOS'        => $getUrlFormAction,
	        'getLogoImageLink'		=> $logoImagelinkOs,
	        'getOsOption'			=> $OsOptions,
	    );
    	wp_localize_script( 'opinionStage_poll_oswp_block_js_set', 'osGutenData', $dataToPass );
}
add_action( 'enqueue_block_editor_assets', 'oswp_gutenberg_enqueue_scripts' );
?>
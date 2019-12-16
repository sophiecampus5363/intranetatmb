<?php

// block direct access to plugin PHP files:
defined( 'ABSPATH' ) or die();

/**
 * Utility function to create a link with the correct host and all the required information.
 */
function opinionstage_link($caption, $path, $css_class = '', $query_data = array()) {
	$query_data['utm_source'] = OPINIONSTAGE_UTM_SOURCE;
	$query_data['utm_campaign'] = OPINIONSTAGE_UTM_CAMPAIGN;
	$query_data['utm_medium'] = OPINIONSTAGE_UTM_MEDIUM;
	$query_data['o'] = OPINIONSTAGE_WIDGET_API_KEY;

	$link = OPINIONSTAGE_SERVER_BASE.'/'.$path.'?'.http_build_query($query_data);

	return "<a href='{$link}' target='_blank' class='{$css_class}'>{$caption}</a>";
}
function opinionstage_template_link($caption, $path, $css_class = '') {
	$query_data['utm_source'] = OPINIONSTAGE_UTM_SOURCE;
	$query_data['utm_campaign'] = OPINIONSTAGE_UTM_CAMPAIGN;
	$query_data['utm_medium'] = OPINIONSTAGE_UTM_MEDIUM;
	$query_data['o'] = OPINIONSTAGE_WIDGET_API_KEY;

	$link = OPINIONSTAGE_SERVER_BASE.'/'.$path.'&'.http_build_query($query_data);
	return "<a href='{$link}' target='_blank' class='{$css_class}'>{$caption}</a>";
}


function opinionstage_register_javascript_asset( $name, $relative_path, $deps=array(), $in_footer=true ) {
	$registered = wp_register_script(
		opinionstage_asset_name($name),
		plugins_url( opinionstage_asset_path().'/js/'.$relative_path, plugin_dir_path(__FILE__) ),
		$deps,
		OPINIONSTAGE_WIDGET_VERSION,
		$in_footer
	);

	if ( !$registered ) {
		error_log( "[opinionstage plugin] ERROR registering javascript asset '$name'" );
	}
}

function opinionstage_register_css_asset($name, $relative_path) {
	wp_register_style(
		opinionstage_asset_name($name),
		plugins_url( opinionstage_asset_path().'/css/'.$relative_path, plugin_dir_path(__FILE__) ),
		null,
		OPINIONSTAGE_WIDGET_VERSION
	);
}

function opinionstage_enqueue_js_asset($name) {
	wp_enqueue_script( opinionstage_asset_name($name) );
}

function opinionstage_enqueue_css_asset($name) {
	wp_enqueue_style( opinionstage_asset_name($name) );
}

function opinionstage_asset_name($name) {
	return 'opinionstage-'.$name;
}

function opinionstage_asset_path() {
	return is_admin() ? 'admin' : 'public';
}

/**
 * Generates a link for editing the flyout placement on Opinion Stage site
 */
function opinionstage_flyout_edit_url($tab) {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	return OPINIONSTAGE_SERVER_BASE.'/containers/'.$os_options['fly_id'].'/edit?selected_tab='.$tab;
}


/**
 * Generates a link for editing the article placement on Opinion Stage site
 */
function opinionstage_article_placement_edit_url($tab) {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	return OPINIONSTAGE_SERVER_BASE.'/containers/'.$os_options['article_placement_id'].'/edit?selected_tab='.$tab;
}
/**
 * Generates a link for editing the sidebar placement on Opinion Stage site
 */
function opinionstage_sidebar_placement_edit_url($tab) {
	$os_options = (array) get_option(OPINIONSTAGE_OPTIONS_KEY);
	return OPINIONSTAGE_SERVER_BASE.'/containers/'.$os_options['sidebar_placement_id'].'/edit?selected_tab='.$tab;
}

function opinionstage_create_poll_link($css_class, $title='CREATE NEW') {
	return opinionstage_link($title, 'api/wp/redirects/widgets/new', $css_class, array('w_type' => 'poll'));

}

function opinionstage_template_poll_link($css_class, $title='USE A TEMPLATE') {
	return opinionstage_template_link($title, 'dashboard/content/templates?types%5B%5D=poll', $css_class);	// %5B%5D --> []
}

function opinionstage_template_survey_link($css_class, $title='USE A TEMPLATE') {
	return opinionstage_template_link($title, 'dashboard/content/templates?types%5B%5D=survey', $css_class);	// %5B%5D --> []
}

function opinionstage_template_trivia_link($css_class, $title='USE A TEMPLATE') {
	return opinionstage_template_link($title, 'dashboard/content/templates?types%5B%5D=trivia_quiz', $css_class);	// %5B%5D --> []
}

function opinionstage_template_personality_quiz_link($css_class, $title='USE A TEMPLATE') {
	return opinionstage_template_link($title, 'dashboard/content/templates?types%5B%5D=personality_quiz', $css_class);	// %5B%5D --> []
}

function opinionstage_template_slideshow_link($css_class, $title='USE A TEMPLATE') {
	return opinionstage_template_link($title, 'dashboard/content/templates?types%5B%5D=slideshow', $css_class);	// %5B%5D --> []
}

function opinionstage_template_form_link($css_class, $title='USE A TEMPLATE') {
	return opinionstage_template_link($title, 'dashboard/content/templates?types%5B%5D=form', $css_class);	// %5B%5D --> []
}

function opinionstage_template_list_link($css_class, $title='USE A TEMPLATE') {
	return opinionstage_template_link($title, 'dashboard/content/templates?types%5B%5D=list', $css_class);	// %5B%5D --> []
}

function opinionstage_template_story_link($css_class, $title='USE A TEMPLATE') {
	return opinionstage_template_link($title, 'dashboard/content/templates?types%5B%5D=story', $css_class);	// %5B%5D --> []
}

function opinionstage_create_poll_set_link($css_class, $title='CREATE NEW') {
	return opinionstage_link($title, 'sets/new', $css_class);
}

function opinionstage_create_widget_link($w_type, $css_class, $title='CREATE NEW') {
	return opinionstage_link($title, 'api/wp/redirects/widgets/new', $css_class, array('w_type' => $w_type));
}

function opinionstage_create_slideshow_link( $css_class, $title='CREATE NEW' ) {
	return opinionstage_link($title, 'api/wp/redirects/widgets/new', $css_class, array('w_type' => 'slideshow'));
}
/**
 * Generates a to the callback page used to connect the plugin to the Opinion Stage account
 */
function opinionstage_callback_url() {
	return get_admin_url('', '', 'admin') . 'admin.php?page='.OPINIONSTAGE_LOGIN_CALLBACK_SLUG;
}
/**
 * Generates a to the callback page used to connect the plugin to the Opinion Stage account via gutenberg editor
 */
function opinionstage_callback_url_gutenberg_connect() {
	return get_admin_url('', '', 'admin') . 'admin.php?page='.OPINIONSTAGE_GETTING_STARTED_SLUG;
}
/**
 * Generates a to the callback page used to connect the plugin to the Opinion Stage account on content page
 */
function opinionstage_content_login_callback_url() {
	$current_url = '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	return get_admin_url(null, 'admin.php?page='.OPINIONSTAGE_CONTENT_LOGIN_CALLBACK_SLUG ). '&return_path=' . urlencode(opinionstage_add_modal_opening_to_url_params($current_url));
}
/**
 * Adds special param for modal opening on page load
 */
function opinionstage_add_modal_opening_to_url_params($url) {
	if (strpos($url, '?') !== false) {
		return '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '&modal_is_open=true';
	} else {
		return '//' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '?modal_is_open=true';
	}
}
/**
 * Take the received data and parse it
 *
 * Returns the newly updated widgets parameters.
*/
function opinionstage_parse_client_data($raw_data) {
	$os_options = array('uid' => $raw_data['uid'],
						   'email' => $raw_data['email'],
						   'fly_id' => $raw_data['fly_id'],
						   'article_placement_id' => $raw_data['article_placement_id'],
						   'sidebar_placement_id' => $raw_data['sidebar_placement_id'],
						   'version' => OPINIONSTAGE_WIDGET_VERSION,
						   'fly_out_active' => 'false',
						   'article_placement_active' => 'false',
						   'sidebar_placement_active' => 'false',
						   'token' => $raw_data['token']);
	$valid_ids = preg_match("/^[0-9]+$/", $raw_data['fly_id']) && preg_match("/^[0-9]+$/", $raw_data['article_placement_id']) &&  preg_match("/^[0-9]+$/", $raw_data['sidebar_placement_id']);
	if ($valid_ids) {
		update_option(OPINIONSTAGE_OPTIONS_KEY, $os_options);
	}
}
function opinionstage_custom_content_popup_callback_url(){
	$protocol = ((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
	$url = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	$adminUrlForOs = admin_url( 'admin.php?page="'.OPINIONSTAGE_CONTENT_LOGIN_CALLBACK_SLUG.'"&return_path=', $protocol );
	return $adminUrlForOs.urlencode($url);
}
?>

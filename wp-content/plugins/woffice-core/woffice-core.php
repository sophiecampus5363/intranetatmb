<?php
/**
 * Plugin Name: Woffice Core
 * Plugin URI:  https://woffice.io
 * Description: Woffice extensions and settings
 * Version:     2.8.5
 * Author:      ALKALAB
 * Author URI:  https://alkalab.com
 * License:     GPL2
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: woffice-core
 * Domain Path: /languages
 */

require_once 'woffice-functions.php';

/**
 * Class Woffice_Core
 *
 * @since 2.8.2
 */
class Woffice_Core
{
	/**
	 * Woffice_Core constructor.
	 */
	public function __construct()
	{
		define('WOFFICE_CORE_ENABLED', true);

		add_action('fw_extensions_locations', array($this, 'loadExtensions'));
		add_action('admin_enqueue_scripts', array($this, 'removeDpProEventGoogleMap'), 99);
		add_action('wp_enqueue_scripts', array($this, 'avoidEventonMapsConflict'), 99);
		add_action('after_setup_theme', array($this, 'removeAdminBar'));
		add_action('fw_init', array($this, 'fwInit'));
		add_action( 'widgets_init', array($this, 'removePluginWidgets') );
	}

	/**
	 * Remove the extra plugin widgets
	 */
	public function removePluginWidgets()
	{
		if (class_exists('multiverso_mv_category_files')) {
			unregister_widget('multiverso_mv_category_files');
			unregister_widget('multiverso_login_register');
			unregister_widget('multiverso_mv_personal_recent_files');
			unregister_widget('multiverso_mv_recent_files');
			unregister_widget('multiverso_search');
			unregister_widget('multiverso_mv_registered_recent_files');
		}

		if (class_exists('EventON')) {
			unregister_widget('EvcalWidget');
			unregister_widget('EvcalWidget_SC');
			unregister_widget('EvcalWidget_three');
			unregister_widget('EvcalWidget_four');
		}

		if (class_exists('bbPress')) {
			unregister_widget('BBP_Login_Widget');
		}
	}

	/**
	 * Setting our own / customer key for the Google Map API
	 */
	public function replaceGmapsScript()
	{
		$handle = 'google-maps-api-v3';

		if (!wp_script_is($handle) || !defined( 'FW')) {
			return;
		}

		wp_dequeue_script($handle);
		wp_deregister_script($handle);

		/* GET THE API KEY */
		$key_option = woffice_get_settings_option('gmap_api_key');
		if (!empty($key_option)){
			$key = $key_option;
		}
		else {
			$key = "AIzaSyAyXqXI9qYLIWaD9gLErobDccodaCgHiGs";
		}

		wp_enqueue_script(
			$handle,
			'https://maps.googleapis.com/maps/api/js?'. http_build_query( array(
				'v'         => '3.15',
				'libraries' => 'places',
				'language'  => substr(get_locale(),0,2),
				'key'       => $key,
			) ),
			array(),
			fw()->manifest->get_version(),
			true
		);
	}

	/**
	 * Fixing a page builder conflict issue with Unyson
	 *
	 * @link https://github.com/ThemeFuse/Unyson-PageBuilder-Extension/commit/a780e1789e6ff454e3382ac71dd98c78b7844037
	 */
	public function fwInit()
	{
		if (function_exists('fw') && fw()->extensions->get( 'page-builder' ) ) {
			if ( version_compare( fw_ext( 'page-builder' )->manifest->get_version(), '1.5.6', '>=' ) ) {
				add_action( 'admin_enqueue_scripts', array($this, 'replaceGmapsScript'), 20 );
			} else {
				add_action( 'admin_print_scripts', array($this, 'replaceGmapsScript'), 20 );
			}
		}
	}

	/**
	 * Remove the admin bar for any user if he isn't an administrator
	 */
	public function removeAdminBar()
	{
		/**
		 * Custom filter to allow the admin bar in the frontend for a certain role
		 *
		 * @param string - the role name or any valid Capability
		 */
		$role = apply_filters('woffice_admin_bar_capability', 'administrator');

		if (!current_user_can($role) && !is_admin()) {
			show_admin_bar(false);
		}
	}

	/**
	 * Deactivate EventON Google Map API calls
	 */
	public function avoidEventonMapsConflict()
	{
		if (wp_script_is('google-maps-api-v3')) {
			wp_dequeue_script('evcal_gmaps');
			wp_deregister_script('evcal_gmaps');
		}
	}

	/**
	 * Temporary patch regarding
	 * WordPress Pro Event Calendar and Unyson Map conflict
	 * We can't stop the plugin to load the API and the MAPS API loaded
	 * does not have all the parameters requested by the Unyson map option type
	 * "Cannot read property 'Autocomplete' of undefined"
	 * @since 2.1.5
	 */
	public function removeDpProEventGoogleMap()
	{
		wp_deregister_script('gmaps');
	}

	/**
	 * Load the Woffice extensions
	 *
	 * @param array $locations
	 *
	 * @return array
	 */
	public function loadExtensions($locations)
	{
		$locations[dirname(__FILE__) .'/extensions'] = dirname(__FILE__) .'extensions';

		return $locations;
	}
}

new Woffice_Core();
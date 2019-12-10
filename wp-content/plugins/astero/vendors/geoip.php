<?php
require_once ASTERO_PATH . 'vendors/autoload.php';
use MaxMind\Db\Reader;

if( !function_exists('astero_get_geoip') ) {
	function astero_get_geoip() {
		$reader = new Reader( ASTERO_PATH . "vendors/GeoLite2-City.mmdb" );

		$record = $reader->get( astero_get_client_ip() );
		$reader->close();

		return array( 'lat' => $record['location']['latitude'], 'lon' => $record['location']['longitude'], 'city' => $record['city']['names']['en'] . ',' . $record['country']['iso_code'] );
	}
}
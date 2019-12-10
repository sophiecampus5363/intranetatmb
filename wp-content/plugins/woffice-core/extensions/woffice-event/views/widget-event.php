<?php if ( ! defined( 'ABSPATH' ) ) die( 'Direct access forbidden.' );

global $bp;

echo $before_widget;
echo $title;

if ($bp && $bp->displayed_user) {
	echo do_shortcode("[woffice_calendar widget='true' visibility='personal' id='{$bp->displayed_user->id}']");
}

echo $after_widget;

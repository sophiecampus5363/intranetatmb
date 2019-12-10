<?php
/*  Copyright 2014  Baptiste Placé

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License, version 2, as
    published by the Free Software Foundation.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
/**
 * Plugin Name: iCalendrier
 * Plugin URI: http://icalendrier.fr/widget/wordpress-plugin
 * Description: Un simple calendrier qui affiche des infos du jour, comme le numéro de semaine, la date, la fête du jour et la phase de lune.
 * Version: 1.6
 * Author: Baptiste Placé
 * Author URI: http://icalendrier.fr/
 * License: GNU General Public License, version 2
 */

defined('ABSPATH') or die("No script kiddies please!");

// Include lib
require_once(dirname(__FILE__) . '/lib/Icalendrier.php');

// Add localized strings
load_plugin_textdomain('icalendrier', false, basename(dirname(__FILE__)) . '/languages');

// Enqueue CSS
function icalendrier_wp_head($posts)
{
    wp_enqueue_style('icalendrier', plugins_url('/css/icalendrier.css', __FILE__));
}

add_action('wp_enqueue_scripts', 'icalendrier_wp_head');


// Shortcode
add_shortcode('icalendrier', 'icalendrier_shortcode');
function icalendrier_shortcode($atts)
{
    $calType  = isset($atts['type']) ? $atts['type'] : 'comp175';
    $language = isset($atts['language']) ? $atts['language'] : 'en';
    $timezone = isset($atts['timezone']) ? $atts['timezone'] : 0;
    $showLink = ((isset($atts['showLink']) AND $atts['showLink'] == 'on') OR ! isset($atts['showLink'])) ? 1 : 0;
    $bgColor  = isset($atts['bgColor']) ? $atts['bgColor'] : false;

    $iCalendrier = new Icalendrier($language, $timezone);

    if ('comp175' === $calType) {
        echo $iCalendrier->iCalendrierComp($showLink, $bgColor);
    } elseif ('wide300' === $calType) {
        echo $iCalendrier->iCalendrierWide($showLink, $bgColor);
    }
}


/**
 * Class ICalendrier
 */
class ICalendrierWidget extends WP_Widget
{

    /**
     * ICalendrierWidget constructor.
     */
    public function __construct()
    {
        parent::WP_Widget('icalendrier_widget', 'iCalendrier Widget', 'Simple daily calendar');
    }

    /**
     * @param $args
     * @param $instance
     */
    public function widget($args, $instance)
    {
        extract($args);

        $calType     = isset($instance['type']) ? $instance['type'] : 'comp175';
        $language    = isset($instance['language']) ? $instance['language'] : 'en';
        $timezone    = isset($instance['timezone']) ? $instance['timezone'] : 0;
        $widgetTitle = isset($instance['widgetTitle']) ? $instance['widgetTitle'] : false;
        $showLink    = ((isset($instance['showLink']) AND $instance['showLink'] == 'on') OR ! isset($instance['showLink'])) ? 1 : 0;
        $bgColor     = isset($instance['bgColor']) ? $instance['bgColor'] : false;

        echo $before_widget;

        if ($widgetTitle != "") {
            echo $before_title . $widgetTitle . $after_title;
        }

        $iCalendrier = new Icalendrier($language, $timezone);

        if ('comp175' === $calType) {
            echo $iCalendrier->iCalendrierComp($showLink, $bgColor);
        } elseif ('wide300' === $calType) {
            echo $iCalendrier->iCalendrierWide($showLink, $bgColor);
        }

        echo $after_widget;
    }

    /**
     * @param $new_instance
     * @param $old_instance
     *
     * @return mixed
     */
    public function update($new_instance, $old_instance)
    {
        $instance                = $old_instance;
        $instance['type']        = strip_tags($new_instance['type']);
        $instance['language']    = strip_tags($new_instance['language']);
        $instance['timezone']    = strip_tags($new_instance['timezone']);
        $instance['widgetTitle'] = strip_tags($new_instance['widgetTitle']);
        $instance['bgColor']     = strip_tags($new_instance['bgColor']);
        $instance['showLink']    = ((isset($instance['showLink']) AND $instance['showLink'] == 'on') OR ! isset($instance['showLink'])) ? 1 : 0;

        return $instance;
    }

    /**
     * @param $instance
     */
    public function form($instance)
    {
        $type        = isset($instance['type']) ? esc_attr($instance['type']) : "comp175";
        $language    = isset($instance['language']) ? esc_attr($instance['language']) : "en";
        $timezone    = isset($instance['timezone']) ? esc_attr($instance['timezone']) : 5;
        $widgetTitle = isset($instance['widgetTitle']) ? esc_attr($instance['widgetTitle']) : "";
        $showLink    = ((isset($instance['showLink']) AND $instance['showLink'] == 'on') OR ! isset($instance['showLink'])) ? 1 : 0;
        $bgColor     = isset($instance['bgColor']) ? esc_attr($instance['bgColor']) : "";
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('type'); ?>"><?php echo _e('MOD_ICAL_CALTYPE_LABEL', 'icalendrier'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('type'); ?>" name="<?php echo $this->get_field_name('type'); ?>">
                <option value="comp175"<?php if ($type == 'comp175') {
                    echo " selected=\"selected\"";
                } ?>><?php echo _e('MOD_ICAL_CALTYPE_COMP175', 'icalendrier'); ?></option>
                <option value="wide300"<?php if ($type == 'wide300') {
                    echo " selected=\"selected\"";
                } ?>><?php echo _e('MOD_ICAL_CALTYPE_COMP300', 'icalendrier'); ?></option>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('language'); ?>"><?php echo _e('MOD_ICAL_LANGUAGE_LABEL', 'icalendrier'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('language'); ?>" name="<?php echo $this->get_field_name('language'); ?>">
                <option value="de"<?php if ($language == 'de') {
                    echo " selected=\"selected\"";
                } ?>>Deutsch
                </option>
                <option value="en"<?php if ($language == 'en') {
                    echo " selected=\"selected\"";
                } ?>>English
                </option>
                <option value="es"<?php if ($language == 'es') {
                    echo " selected=\"selected\"";
                } ?>>Español
                </option>
                <option value="fr"<?php if ($language == 'fr') {
                    echo " selected=\"selected\"";
                } ?>>Français
                </option>
                <option value="it"<?php if ($language == 'it') {
                    echo " selected=\"selected\"";
                } ?>>Italiano
                </option>
                <option value="pt"<?php if ($language == 'pt') {
                    echo " selected=\"selected\"";
                } ?>>Português
                </option>
                <option value="pt-BR"<?php if ($language == 'pt-BR') {
                    echo " selected=\"selected\"";
                } ?>>Português (Brasil)
                </option>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('timezone'); ?>"><?php echo _e('MOD_ICAL_TIMEZONE_LABEL', 'icalendrier'); ?></label>
            <select class="widefat" id="<?php echo $this->get_field_id('timezone'); ?>" name="<?php echo $this->get_field_name('timezone'); ?>">
                <option value="0"<?php if ($timezone === 0) {
                    echo " selected=\"selected\"";
                } ?>><?php echo _e('MOD_ICAL_TIMEZONE_AUTO', 'icalendrier'); ?></option>
                <option value="Europe/Paris"<?php if ($timezone == 'Europe/Paris') {
                    echo " selected=\"selected\"";
                } ?>>Europe/Paris
                </option>
                <option value="Europe/Berlin"<?php if ($timezone == 'Europe/Berlin') {
                    echo " selected=\"selected\"";
                } ?>>Europe/Berlin
                </option>
                <option value="Europe/Moscow"<?php if ($timezone == 'Europe/Moscow') {
                    echo " selected=\"selected\"";
                } ?>>Europe/Moscow
                </option>
                <option value="America/Montreal"<?php if ($timezone == 'America/Montreal') {
                    echo " selected=\"selected\"";
                } ?>>America/Montreal
                </option>
                <option value="America/Guyana"<?php if ($timezone == 'America/Guyana') {
                    echo " selected=\"selected\"";
                } ?>>America/Guyana
                </option>
                <option value="UTC"<?php if ($timezone == 'UTC') {
                    echo " selected=\"selected\"";
                } ?>>UTC
                </option>
            </select>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('showLink'); ?>"><?php echo _e('MOD_ICAL_SHOWLINK_LABEL', 'icalendrier'); ?></label> &nbsp;
            <input id="<?php echo $this->get_field_id('showLink'); ?>" name="<?php echo $this->get_field_name('showLink'); ?>" type="checkbox" <?php if ($showLink) {
                echo ' checked="checked"';
            } ?> />
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('widgetTitle'); ?>"><?php echo _e('MOD_ICAL_WIDGET_TITLE_LABEL', 'icalendrier'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('widgetTitle'); ?>" name="<?php echo $this->get_field_name('widgetTitle'); ?>" type="text" value="<?php echo $widgetTitle; ?>"/>
        </p>

        <p>
            <label for="<?php echo $this->get_field_id('bgColor'); ?>"><?php echo _e('MOD_ICAL_BGCOLOR_LABEL', 'icalendrier'); ?></label><br/>
            <input class="widefat" id="<?php echo $this->get_field_id('bgColor'); ?>" name="<?php echo $this->get_field_name('bgColor'); ?>" type="text" value="<?php echo $bgColor; ?>"/>
        </p>

        <?php
    }
}

add_action( 'widgets_init', function(){
    register_widget( 'ICalendrierWidget' );
});
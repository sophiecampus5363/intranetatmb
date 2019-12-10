<?php
/* ==========================================================================
   Actions
   ========================================================================== */
add_action('admin_menu', 'astero_admin_menu');
add_action( 'admin_enqueue_scripts', 'astero_add_custom_script', 10, 1 );

if ( !function_exists('astero_admin_menu') ) {
        function astero_admin_menu() {
                add_options_page('Astero Weather Settings', 'Astero Weather', 'manage_options','astero-settings', 'astero_settings');
                add_action( 'admin_init', 'register_astero_settings' );
     }
}

if( !function_exists('register_astero_settings') ) {
        function register_astero_settings() {
                //register our settings
               register_setting( ASTERO_OPTIONS . '_group', ASTERO_OPTIONS);
        }
}

if ( !function_exists('astero_settings') ) {
        function astero_settings() {
                if ( !current_user_can('manage_options') ) {
                        wp_die('You do not have sufficient permissions to access this page.');
                }
                
                $astero_options = get_option(ASTERO_OPTIONS);
                
                include_once(ASTERO_PATH . '/admin/partials/astero-admin-settings-display.php');
        }
}

if ( !function_exists('astero_add_custom_script') ) {
        function astero_add_custom_script($hook) {
                global $post;  
  
                if ( $hook == 'post-new.php' || $hook == 'post.php' ) {  
                        if ( 'astero' === $post->post_type ) {       
                                wp_enqueue_script('astero_admin_js', ASTERO_URL . 'admin/js/admin.js', '', '', true); 
                                wp_enqueue_script( 'wp-color-picker' ); 
                                wp_enqueue_script( 'jquery-ui-core' );
                                wp_enqueue_script( 'jquery-ui-tabs' );
                                wp_enqueue_style('astero_meta_css', ASTERO_URL . 'admin/css/meta.css');
                                wp_enqueue_style( 'wp-color-picker' );
                                wp_enqueue_style('google-font', "https://fonts.googleapis.com/css?family=Amaranth:italic' rel='stylesheet' type='text/css'>");
                                wp_localize_script('astero_admin_js', 'cmb_vars', array(
                                                'mediaTitle' => __('Insert Image', ASTERO_SLUG),
                                                'mediaButton' => __('Add image', ASTERO_SLUG),
                                        )
                                );
                        }  
                } 
        }
}

/* ==========================================================================
   Filters
   ========================================================================== */
add_filter( 'plugin_action_links_astero/astero.php', 'astero_link_action_on_plugin' );
add_filter('upload_mimes','astero_add_custom_mime_types');

if ( !function_exists('astero_link_action_on_plugin')) {
        function astero_link_action_on_plugin( $links ) {
                $links[] = '<a href="'. get_admin_url(null, 'options-general.php?page=astero-settings') .'">' . __('Settings', ASTERO_SLUG) . '</a>';
                return $links;
        }
}

if ( !function_exists('astero_add_custom_mime_types') ) {
        function astero_add_custom_mime_types( $mimes ) {
                return array_merge($mimes,array (
			'webm' => 'video/webm',
		));
        }
}


/* ==========================================================================
   Astero Post Type
   ========================================================================== */
add_action('init', 'astero_register'); //Add custom post type
add_action('do_meta_boxes', 'astero_remove_thumbnail_box');
add_filter('manage_edit-astero_columns', 'astero_edit_columns' ); //Display custom columns in room admin view
add_action('manage_astero_posts_custom_column', 'astero_custom_columns', 10, 2 );

if ( !function_exists('astero_register') )
{
        function astero_register() {
                $labels = array(
                        'name'                  => __( 'Astero Weather', ASTERO_SLUG ),
                        'singular_name'         => __( 'Weather', ASTERO_SLUG ),
                        'add_new'               => __( 'Add New Weather', ASTERO_SLUG ),
                        'add_new_item'          => __( 'Add New Weather', ASTERO_SLUG ),
                        'edit_item'             => __( 'Edit Weather', ASTERO_SLUG ),
                        'new_item'              => __( 'New Weather', ASTERO_SLUG ),
                        'view_item'             => __( 'View Weather', ASTERO_SLUG ),
                        'search_items'          => __( 'Search Weather', ASTERO_SLUG ),
                        'not_found'             => __( 'No weather found', ASTERO_SLUG ),
                        'not_found_in_trash'    => __( 'No weather found in trash', ASTERO_SLUG )
                );
                
                $args = array(
                        'labels'                 => $labels,
                        'public'                => false,
                        'show_ui'               => true,
                        'capability_type'       => 'post',
                        'hierarchical'          => false,
                        'supports'              => array('title','thumbnail'),
                        'menu_icon'             => 'dashicons-cloud',
                        'rewrite'               => array('slug' => 'astero'),
                        'has_archive'           => false,
                        'exclude_from_search'   => true,
                        'publicly_queryable'    => false,
                        'show_in_nav_menus'     => false,
                        'menu_position'         => 100,
                        'show_in_menu'          => true,
                        'show_in_admin_bar'     => true,
                );
                
                register_post_type('astero', $args);
        }
}

if (!function_exists('astero_remove_thumbnail_box'))
{
        function astero_remove_thumbnail_box() {
            remove_meta_box('postimagediv', 'astero', 'side');
        }
}

if (!function_exists('astero_edit_columns'))
{
        function astero_edit_columns($columns){
                $columns = array(
                        "cb" => "<input type=\"checkbox\" />",
                        "title" => _x('Title', ASTERO_SLUG),
                        "location" => _x('Location',ASTERO_SLUG),
                        "shortcode" => _x('Shortcode',ASTERO_SLUG),
                        "date" => _x('Date', ASTERO_SLUG),
                );  
        
                return $columns;
        }
}

if (!function_exists('astero_custom_columns'))
{
        function astero_custom_columns( $columns, $post_id ){
           
                switch ( $columns )
                {
                        case "shortcode":
                                echo "[astero id='$post_id']";
                                break;
                        case "location":
                                $meta = get_post_meta( $post_id, '_astero_meta', true);
                                
                                if ( isset( $meta['location'] ) && $meta['location'] == 'city' && isset( $meta['city'] ) && $meta['city'] != '' ) {
                                        echo $meta['city'];
                                } else {
                                        _e('Geolocation', ASTERO_SLUG);
                                }
                                break;
                }
        }
}

/* ==========================================================================
   Add Astero Weather Meta
   ========================================================================== */
add_action("admin_init", "astero_add_meta");  

if (!function_exists('astero_add_meta'))
{
        function astero_add_meta(){  
                add_meta_box("astero_meta", "Astero Weather Options", "astero_meta_options", "astero", "normal", "high");
        }
}

if (!function_exists('astero_meta_options'))
        {
        function astero_meta_options(){ //Create media upload meta box
                global $post; // refers to the post or parent being displayed
                 if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return $post->ID;
                
                $custom = get_post_meta( $post->ID, '_astero_meta', true);
                
                // List all custom meta keys
                $meta_keys = array(
                        // 'service' => 'owm',
                        'location' => 'geolocation',
                        'lat' => '',
                        'lon' => '',
                        'lat_manual' => '',
                        'lon_manual' => '',
                        'city' => '',
                        // 'heading' => '',
                        'units' => 'metric',
                        // 'lang' => 'en',
                        'fc_lang' => 'en',
                        'border' => '',//array(width,color,style),
                        'search' => '',
                        'border_style' => '',
                        'border_size' => '',
                        'border_color' => '',
                        'display_units' => '0',
                        'layout' => 'simple',
                        'round' => '1', // true|false
                        'font_family' => '',
                        'font_variant' => '',
                        'font_subset' => '',
                        'font_color' => '',
                        'font_backup' => '',
                        'base_font_size' => '',
                        'temp_font_size' => '',
                        'style' => 'color',
                        'background_color' => '',
                        'sun' => 'default',
                        'night' => 'default',
                        'clouds' => 'default',
                        'rain' => 'default',
                        'fog' => 'default',
                        'thunderstorm' => 'default',
                        'snow' => 'default',
                        'video' => 'html5', //yt/html5
                        'webm' => '',
                        'mp4' => '',
                        'ogg' => '',
                        'placeholder' => '',
                        'yt_id' => '',
                        'aspect_ratio' => '',
                        'custom_ratio1' => '',
                        'custom_ratio2' => '',
                );
 
                foreach ( $meta_keys as $meta_key => $default ) {
                        $$meta_key = isset( $custom[$meta_key] ) && $custom[$meta_key] != '' ? esc_attr( $custom[$meta_key] ) : $default; 
                }
                
                // Get list of Google Fonts
                $fonts = json_decode(file_get_contents( ASTERO_PATH . '/admin/json/google-fonts.json'), true);

                $items = $fonts['items'];
                $i = 0;

                // List of weather conditions
                $conditions = array(
                        'sun' => __('Clear Sky', ASTERO_SLUG), 
                        'night' => __('Clear Night', ASTERO_SLUG),    
                        'clouds' => __('Cloudy', ASTERO_SLUG),   
                        'rain' => __('Rain', ASTERO_SLUG),   
                        'thunderstorm' => __('Thunderstorm', ASTERO_SLUG),   
                        'fog' => __('Foggy', ASTERO_SLUG),   
                        'snow' => __('Snow', ASTERO_SLUG),    
                );
                
                $languages = array(
                	'ar' => __('Arabic', ASTERO_SLUG),
                	'az' => __('Azerbaijani', ASTERO_SLUG),
                	'be' => __('Belarusian', ASTERO_SLUG),
                	'bg' => __('Bulgarian', ASTERO_SLUG),
                	'bs' => __('Bosnian', ASTERO_SLUG),
                	'ca' => __('Catalan', ASTERO_SLUG),
                	'cs' => __('Czech', ASTERO_SLUG),
                	'da' => __('Danish', ASTERO_SLUG),
                	'de' => __('German', ASTERO_SLUG),
                	'el' => __('Greek', ASTERO_SLUG),
                        'en' => __('English', ASTERO_SLUG),
                        'es' => __('Spanish', ASTERO_SLUG),
                        'et' => __('Estonian', ASTERO_SLUG),
                	'fi' => __('Finnish', ASTERO_SLUG),
                        'fr' => __('French', ASTERO_SLUG),
                        'hr' => __('Croatian', ASTERO_SLUG),
                        'hu' => __('Hungarian', ASTERO_SLUG),
                        'id' => __('Indonesian', ASTERO_SLUG),
                	'is' => __('Icelandic', ASTERO_SLUG),
                        'it' => __('Italian', ASTERO_SLUG),
                        'ka' => __('Georgian', ASTERO_SLUG),
                        'ko' => __('Korean', ASTERO_SLUG),
                        'kw' => __('Cornish', ASTERO_SLUG),
                        'nb' => __('Norwegian Bokmål', ASTERO_SLUG),
                        'nl' => __('Dutch', ASTERO_SLUG),
                        'pl' => __('Polish', ASTERO_SLUG),
                        'pt' => __('Portuguese', ASTERO_SLUG),
                        'ro' => __('Romanian', ASTERO_SLUG),
                        'ru' => __('Russian', ASTERO_SLUG),
                        'sk' => __('Slovak', ASTERO_SLUG),
                        'sl' => __('Slovenian', ASTERO_SLUG),
                        'sr' => __('Serbian', ASTERO_SLUG),
                        'sv' => __('Swedish', ASTERO_SLUG),
                        'tet' => __('Tetum', ASTERO_SLUG),
                        'tr' => __('Turkish', ASTERO_SLUG),
                        'uk' => __('Ukrainian', ASTERO_SLUG),
                        'x-pig-latin' => __('Igpay Atinlay', ASTERO_SLUG),
                        'zh' => __('Simplified Chinese', ASTERO_SLUG),
                        'zh-tw' => __('Traditional Chinese', ASTERO_SLUG),
                        //'self' => __('Use Own Translation', ASTERO_SLUG),
                );
                
                // List of html5 vidoe formats
                $videos = array('webm', 'mp4', 'ogg');
                
                include_once(ASTERO_PATH . '/admin/partials/astero-admin-metabox-display.php');
        }
}

/* ==========================================================================
   Save Astero Weather meta
   ========================================================================== */
add_action('save_post', 'astero_save_meta'); 

if (!function_exists('astero_save_meta'))
{
        function astero_save_meta( $post_id ){          
        
                // Bail if we're doing an auto save  
                if( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) return $post_id;
           
                // if our nonce isn't there, or we can't verify it, bail 
                if( !isset( $_POST['save_astero_meta_nonce_field'] ) || !wp_verify_nonce( $_POST['save_astero_meta_nonce_field'], 'save_astero_meta_' . $post_id ) ) return $post_id; 
        
                if (!current_user_can('edit_post', $post_id)) return $post_id;

                //list of the meta keys
                $meta_keys = array(
                        'location', //array(geolocation/city,cityname),
                        'lat',
                        'lon',
                        'lat_manual',
                        'lon_manual',
                        'city',
                        'heading',
                        'units', //metric|imperial,
                        'fc_lang',
                        'search',
                        'border',//array(width,color,style)
                        'border_style',
                        'border_size',
                        'border_color',
                        'layout',
                        'display_units',
                        'round', // true|false
                        'font_family',
                        'font_variant',
                        'font_subset',
                        'font_color',
                        'font_backup',
                        'base_font_size',
                        'temp_font_size',
                        'style',
                        'background_color', //video/img/color,
                        'sun',
                        'night',
                        'clouds',
                        'rain',
                        'fog',
                        'thunderstorm',
                        'snow',
                        'video', //yt/html5
                        'webm',
                        'mp4',
                        'ogg',
                        'placeholder',
                        'yt_id',
                        'aspect_ratio',
                        'custom_ratio1',
                        'custom_ratio2'
                );
                
                // Array of new values
                $saved_meta = array();
                $posts = $_POST;
                
                // Get lat and lon
        //         $posts['lat'] = '';
        //         $posts['lon'] = '';
        //         if( $posts['location'] == 'city' ) {

        //         	$google_api = isset( $options['google_map_api'] ) ? 'key=' . $options['google_map_api'] . '&' : '';
        //                 $url = 'https://maps.googleapis.com/maps/api/geocode/json?' . $google_api . 'address=' . urlencode($posts['city']);
                        
        //                 // Open connection
        //                 $ch = curl_init();
                        
        //                 // Set the url, number of GET vars, GET data
        //                 curl_setopt($ch, CURLOPT_URL, $url);
        //                 curl_setopt($ch, CURLOPT_POST, false);
        //                 curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        //                 curl_setopt($ch, CURLOPT_RETURNTRANSFER, true );
        //                 curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                        
        //                 // Execute request
        //                 $result = curl_exec($ch);
                        
        //                 // Close connection
        //                 curl_close($ch);
        
        // //$result = file_get_contents($url);
        
        //                 // get the result and parse to JSON
        //                 $result_arr = json_decode($result, true);
                        
        //                 if( $result_arr['status'] == 'OK' && isset( $result_arr['results'][0]['geometry']['location']['lat'] )
        //                     && isset( $result_arr['results'][0]['geometry']['location']['lng'] ) ) {
        //                         $posts['lat'] = $result_arr['results'][0]['geometry']['location']['lat'];
        //                         $posts['lon'] = $result_arr['results'][0]['geometry']['location']['lng'];
        //                 }
        //         }
        
                foreach ( $meta_keys as $meta_key ) {
                        if ( isset( $posts[$meta_key]  )) {
                                $saved_meta[ $meta_key ] = sanitize_text_field( $posts[$meta_key] );
                        }
                }
                update_post_meta( $post_id, '_astero_meta', $saved_meta );
                
                
                // Generate custom css. If different from old one update static custom.css
                $css = '';
                
                // border style
                if( isset( $posts['border']) && $posts['border'] == 1 ) {
                        $css .= $posts['border_style'] != '' ? 'border-style: ' . $posts['border_style']  . ';' : '';
                        $css .= $posts['border_size'] != '' ? 'border-width: ' . $posts['border_size'] . 'px;' : '';
                        $css .= $posts['border_color'] != '' ? 'border-color: ' . $posts['border_color']  . ';' : '';
                }
                
                // font style
                $css .= $posts['font_family'] != '' ? 'font-family: ' . $posts['font_family'] . ', ' . $posts['font_backup'] . ';' : '';
                $css .= $posts['font_color'] != '' ? 'color: ' . $posts['font_color']  . ';' : '';
                if ( isset( $posts['font_variant'] ) && ( $posts['font_variant'] != '' || $posts['font_variant'] != 'regular' ) ) {
                        if ( strpos($posts['font_variant'],'italic' ) !== false) {
                                $css .= 'font-style: italic;';
                                $saved_meta['font_variant'] = str_replace('italic', '', $posts['font_variant']);
                        }
                        if ( is_numeric( $posts['font_variant'] ) ) {
                                $css .= 'font-weight: ' . $posts['font_variant'] . ';';
                        }
                }
                $css .= $posts['base_font_size'] != '' ? 'font-size: ' . $posts['base_font_size'] . 'px;' : '';
                if( $css != '' ) {
                        $css = '#astero' . $post_id . ' {' . $css . '}';
                }
                
                // link color
                $css .= $posts['font_color'] != '' ? '#astero' . $post_id . ' a, #astero' . $post_id . ' a:hover { color: ' . $posts['font_color']  . ';}' : '';

                // custom background
                $css .= isset( $posts['background_color'] ) && $posts['style'] == 'color' ? '#astero' . $post_id . ' .astero-background{background-color: ' . $posts['background_color'] . ';}' : '';
                
                // temp font size
                $css .= $posts['temp_font_size'] != '' ? '#astero' . $post_id . ' .astero-small .astero-temp, #astero' . $post_id . ' .astero-large .astero-temp {font-size: ' . $posts['temp_font_size']  . 'em;}' : '';
                
                // custom images
                $conditions = array('sun', 'night', 'clouds', 'rain', 'fog', 'thunderstorm', 'snow');
                foreach( $conditions as $c ){
                        if( $posts['style'] == 'image' && $posts[$c] != 'default' && $posts[$c] != '' ) {
                                $img = wp_get_attachment_image_src( $posts[$c], 'full' );
                                $css .= '#astero' . $post_id . '.astero-img .astero-background.astero-i-' . $c . '{background-image: url(' . $img[0] . ');}';
                        }
                }
                
                // search form
                if ( isset( $posts['search'] ) && $posts['search'] == 1 && $posts['font_color'] != '' ) {
                        $font_color = $posts['font_color'];
                        $css .= '#astero' . $post_id . ' .astero-form input, #astero' . $post_id . ' .astero-form select{border-bottom-color: ' . $font_color . ';}';
                        $css .= '#astero' . $post_id . ' .astero-form input, #astero' . $post_id . ' .astero-form select{color: ' . astero_shadeColor( $font_color, 15 ) . ';}';
                        $css .= '#astero' . $post_id . ' .astero-form input::-webkit-input-placeholder{color: ' . $font_color . ';}';
                        $css .= '#astero' . $post_id . ' .astero-form input:-moz-placeholder{color: ' . $font_color . ';}';
                        $css .= '#astero' . $post_id . ' .astero-form input::-moz-placeholder{color: ' . $font_color . ';}';
                        $css .= '#astero' . $post_id . ' .astero-form input:-ms-input-placeholder{color: ' . $font_color . ';}';
                }
                
                // grab old css
                $old_css = get_post_meta( $post_id, '_astero_css', true);
                
                update_post_meta( $post_id, '_astero_css', $css );

                if( $old_css != $css ) {
                        astero_update_static_css();
                }
        }
}

if( !function_exists('astero_update_static_css') ) {
        function astero_update_static_css() {
                
                global $wpdb, $wp_filesystem;
                
                $r = $wpdb->get_col( $wpdb->prepare( "
                        SELECT pm.meta_value FROM {$wpdb->postmeta} pm
                        LEFT JOIN {$wpdb->posts} p ON p.ID = pm.post_id
                        WHERE pm.meta_key = '%s' 
                        AND p.post_status = '%s' 
                        AND p.post_type = '%s'
                ", '_astero_css', 'publish', 'astero' ) );
                
                $css = implode('', $r);
                
                $blog_id = '';
                if( is_multisite() ) {
                        $blog_id = get_current_blog_id();
                }
                
                $filename = ASTERO_PATH . 'public/css/custom' . $blog_id . '.css';
                
                if( file_exists( $filename ) ) {
                        file_put_contents($filename, $css, LOCK_EX);
                } else {
                        $file = fopen($filename, "w");
                        
                        if( $file == false ) {
                                die("unable to create file");
                        }
                        
                        fwrite($file, $css);
                        fclose($file);
                }
        }
}

if( !function_exists('astero_shadeColor') ) {
        function astero_shadeColor($color, $percent) {
                $num = base_convert(substr($color, 1), 16, 10);
                $amt = round(2.55 * $percent);
                $r = ($num >> 16) + $amt;
                $b = ($num >> 8 & 0x00ff) + $amt;
                $g = ($num & 0x0000ff) + $amt;
                
                return '#'.substr(base_convert(0x1000000 + ($r<255?$r<1?0:$r:255)*0x10000 + ($b<255?$b<1?0:$b:255)*0x100 + ($g<255?$g<1?0:$g:255), 10, 16), 1);
        }
}
?>
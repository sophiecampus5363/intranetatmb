<?php
/* ==========================================================================
   Register styles and scripts
   ========================================================================== */
if( !function_exists('astero_register_scripts') ) {
        function astero_register_scripts() {
                
                $blog_id = '';
                if( is_multisite() ) {
                        $blog_id = get_current_blog_id();
                }

                $options = get_option(ASTERO_OPTIONS);
                $fc_api = isset( $options['forecast_api'] ) ? $options['forecast_api'] : '';
                $google_api = isset( $options['google_map_api'] ) ? '?key=' . $options['google_map_api'] : '';
                
                wp_register_style('astero_css', ASTERO_URL . 'public/css/style.css');
                wp_register_style('astero_custom_css', ASTERO_URL . 'public/css/custom' . $blog_id . '.css');
                wp_register_script('astero_fc_js', ASTERO_URL . 'public/js/astero-fc.min.js', array('jquery'), '', true);
                wp_register_script('astero_google_map', 'https://maps.googleapis.com/maps/api/js' . $google_api);
      
                wp_localize_script('astero_fc_js', 'astero_fc_vars', array(
                        'fc_api' => $fc_api,
                        'na' => __('N/A', ASTERO_SLUG),
                        "n" => __('N', ASTERO_SLUG),
                        "nne" => __("NNE", ASTERO_SLUG),
                        "ne" => __("NE", ASTERO_SLUG),
                        "ene" => __("ENE", ASTERO_SLUG),
                        "e" => __("E", ASTERO_SLUG),
                        "ese" => __("ESE", ASTERO_SLUG),
                        "se" => __("SE", ASTERO_SLUG),
                        "sse" => __("SSE", ASTERO_SLUG),
                        "s" => __("S", ASTERO_SLUG),
                        "ssw" => __("SSW", ASTERO_SLUG),
                        "sw" => __("SW", ASTERO_SLUG),
                        "wsw" => __("WSW", ASTERO_SLUG),
                        "w" => __("W", ASTERO_SLUG),
                        "wnw" => __("WNW", ASTERO_SLUG),
                        "nw" => __("NW", ASTERO_SLUG),
                        "nnw" => __("NNW", ASTERO_SLUG),
                        "am" => __("am", ASTERO_SLUG),
                        "pm" => __("pm", ASTERO_SLUG),
                        'ajaxurl' => admin_url( 'admin-ajax.php' ),
                        )
                );
        }
 
}
add_action('init', 'astero_register_scripts');

if( !function_exists('astero_enqueue_scripts') ) {
	function astero_enqueue_scripts() {
		wp_enqueue_style('astero_css');
                wp_enqueue_style('astero_custom_css');
	}
}
add_action( 'wp_enqueue_scripts', 'astero_enqueue_scripts' );  

/* ==========================================================================
   Ajax Functions
   ========================================================================== */
if( !function_exists('astero_fc_geoip') ) {
        function astero_fc_geoip() {

                $ip = astero_get_client_ip();

                if( $ip == 'unknown' ) {
                        $results = array('error' => 'no_ip');
                } else {
                        try{
                            require_once ASTERO_PATH . 'vendors/geoip.php';
                            $place = astero_get_geoip();
                            $results = array( 'success' => $place );
                        } catch ( Exception $e ){
                                $results = array( 'error' => 'not_found' );
                        }
                }
                
                if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                        echo json_encode( $results );
                }
                else {
                        header("Location: " . $_SERVER["HTTP_REFERER"]);
                }
             
                die();
        }
}
add_action("wp_ajax_astero_fc_geoip", "astero_fc_geoip");
add_action("wp_ajax_nopriv_astero_fc_geoip", "astero_fc_geoip");

if( !function_exists('astero_get_client_ip') ) {
        function astero_get_client_ip() {
                $ipaddress = '';
                if (isset($_SERVER['HTTP_CLIENT_IP'])) {
                        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
                } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
                } elseif(isset($_SERVER['HTTP_X_FORWARDED'])) {
                        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
                } elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])) {
                        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
                } elseif(isset($_SERVER['HTTP_FORWARDED'])) {
                        $ipaddress = $_SERVER['HTTP_FORWARDED'];
                } elseif(isset($_SERVER['REMOTE_ADDR'])) {
                        $ipaddress = $_SERVER['REMOTE_ADDR'];
                } else {
                        $ipaddress = 'unknown';
                }
                $ipaddress = explode( ',', $ipaddress);
                return $ipaddress[0];
        }
}


/* ==========================================================================
   Astero Weather Shortcode
   ========================================================================== */
if (!function_exists('astero_display')) {
        
        function astero_display( $attr ) {
                extract(shortcode_atts(array(
                        'id'           => '',
                        'style'        => '',
                ), $attr));
                
                $custom = get_post_meta( $id, '_astero_meta', true);
                
                if( !$custom ) {
                        return;
                }
                
                // get custom settings
        	switch( $custom['location'] ) {
        		case 'city':
        			$lat = isset( $custom['lat_manual'] ) ? $custom['lat_manual'] : '';
        			$lon = isset( $custom['lon_manual'] ) ? $custom['lon_manual'] : '';
        			$location = '"city": "' . esc_html( $custom['city'] ) . '", "lat": "' . $lat . '", "lon": "' . $lon . '"';
        			break;
        		case 'ip':
        			$location = '"location": "ip"';
        			break;
        		default:
        			$location = '"lat":"","lon":""';

        	}

                $units = $custom['units'] == 'metric' ? 'si' : 'us';
                $lang = isset( $custom['fc_lang'] ) && $custom['fc_lang'] != 'en' ? ', "lang":"' . esc_html( $custom['fc_lang'] ) . '"' : '';
                $plugin_name = 'astero_fc';
                $service = ' astero-forecast';
                $credit = '<div class="astero-credit">' . __('Powered by <a href="https://darksky.net/poweredby/" target="_blank">Dark Sky</a>', ASTERO_SLUG) . '</div>';
                
                
                // $title = isset( $custom['heading'] ) && $custom['heading'] != '' ? ', "heading":"' . esc_html( $custom['heading'] ) . '"' : '';
                $custom['aspect_ratio'] = $custom['aspect_ratio'] == 'custom' ? (int) $custom['custom_ratio1'] / (int) $custom['custom_ratio2'] : $custom['aspect_ratio'];
                $ratio = $custom['style'] == 'video' && $custom['video'] == 'yt' && $custom['aspect_ratio'] != '1.77777778' ? ', "iframe_ratio": "' . $custom['aspect_ratio'] . '"' : '';
                if( isset( $custom['display_units'] ) && $custom['display_units'] == '1' ) {
                        $temp_unit = $custom['units'] == 'imperial' ? '<span class="astero-unit">' . __('&deg;F', ASTERO_SLUG) . '</span>': '<span class="astero-unit">' . __('&deg;C', ASTERO_SLUG) . '</span>';
                        $unit_c = __('&deg;C', ASTERO_SLUG);
                        $unit_f = __('&deg;F', ASTERO_SLUG);
                } else {
                        $temp_unit = '<span class="astero-unit">' . __('&deg;', ASTERO_SLUG) . '</span>';
                        $unit_c = $unit_f = __('&deg;', ASTERO_SLUG);
                }
                $weather = '{' . $location . ', "units": "' . $units . '", "unit_c": "' . $unit_c . '", "unit_f": "' . $unit_f . '"' . $lang . $ratio . '}';
                
                // get custom classes
                $radius = isset( $custom['round'] ) && $custom['round'] == '1' ? ' radius' : '';
                $img = $custom['style'] == 'image' ? ' astero-img' : '';
                
                // generate video background
                $background = '';
                if( $custom['style'] == 'video' ) {
                        if( $custom['video'] == 'html5' ) {
                                $fallback = wp_get_attachment_image_src( $custom['placeholder'], 'full' );
                                $poster = $fallback ? ' poster="' . $fallback[0] . '"' : '';
                                $video_types = array('mp4', 'webm', 'ogg');
                                
                                $background = '<video loop muted autoplay' . $poster . '>';
                                foreach( $video_types as $v ) {
                                        $background .= $custom[ $v ] != '' && wp_get_attachment_url( $custom[ $v ] ) ? '<source src="' . wp_get_attachment_url( $custom[ $v ] ) . '" type="video/' . $v . '">' : '';
                                }
                                
                                $background .= $fallback ? '<img src="' . $fallback[0] . '" alt="' . __('The video tag is not supported for your browser.', ASTERO_SLUG) . '" />' : '';
                                $background .= '</video>';
                                
                        } elseif( $custom['video'] == 'yt') {
                                if (isset($_SERVER['HTTPS']) && ($_SERVER['HTTPS'] == ‘on’ || $_SERVER['HTTPS'] == 1) ||
                                        isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == ‘https’) {
                                        $protocol = 'https:';
                                } else {
                                        $protocol = 'http:';
                                }
                                //$yt_class = isset( $custom['service'] ) && $custom['service'] == 'fc' ? 'astero-fc-yt' : ''
                                $background = '<div class="astero-yt" id="astero_yt' . $style . $id . '" data-videoid="' . esc_html( $custom['yt_id'] ) . '"></div>';
                        }
                }
                
                // get layout
                $layout = isset( $custom['layout'] ) && $custom['layout'] == 'full' ? 'full' : 'simple';
                
                // generate html
                ob_start();                      // start capturing output
                include( ASTERO_PATH . '/public/partials/astero-public-' . $layout . '-display.php');   // execute the file
                $html = ob_get_contents();    // get the contents from the buffer
                ob_end_clean();
                              
                // enqueue styles and scripts
                wp_enqueue_script('astero_fc_js');

                $options = get_option(ASTERO_OPTIONS);
                if( (!isset( $options['skip_map'] ) || $options['skip_map'] == '0') &&
                	( ( isset( $custom['search'] ) && $custom['search'] == '1' ) || ( isset( $custom['location'] ) && $custom['location'] == 'geolocation' ) ) ) {
                        wp_enqueue_script('astero_google_map');
                }
                
                if( $custom['font_family'] != '' ) {
                        wp_enqueue_style('astero_google_font' . $id, 'https://fonts.googleapis.com/css?family=' . urlencode( esc_html( $custom['font_family'] ) ) . ':' . esc_html( $custom['font_variant'] ) . '&subset=' . esc_html( $custom['font_subset'] ) );
                }

                return $html;
        }
}
add_shortcode("astero", "astero_display");

/* ==========================================================================
   Astero Weather Widget
   ========================================================================== */
class astero_widget extends WP_Widget { 
	
	// Widget Settings
	function __construct() {
		parent::__construct(
			'astero_widget', // Base ID
			__('Astero WordPress Weather Widget', ASTERO_SLUG), // Name
			array( 'description' => __( 'Displays weather forecast in the sidebar', ASTERO_SLUG ), ) // Args
		);
	}
	
	// Widget Output
	function widget($args, $instance) {
                
                extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );

		echo $before_widget;
		if ( ! empty( $title ) ) echo $before_title . $title . $after_title;
                
                if( get_post_status( $instance['astero'] ) == 'publish' && get_post_type( $instance['astero'] ) == 'astero' ) {
                       echo do_shortcode('[astero id="' . $instance['astero'] . '" style="_widget"]'); 
                }
                
		echo $after_widget;
	}
	
	// Update
	function update( $new_instance, $old_instance ) {  
		$instance = $old_instance; 
                
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
                $instance['astero'] = (int) $new_instance['astero'];

		return $instance;
	}
	
	// Backend Form
	function form($instance) {
		
		$defaults = array('title' => '', 'astero' => '',);
		$instance = wp_parse_args((array) $instance, $defaults);
                
                $args= array(
			'post_type' => 'astero',
			'posts_per_page' => -1,
                        'post_status' => 'publish',
		);
                $posts = new WP_Query($args);
                ?>
		<p>
			<label for="<?php echo $this->get_field_id('title'); ?>">Title:</label>
			<input type="text" class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" value="<?php echo $instance['title']; ?>" />
		</p>
                <p>
			<label for="<?php echo $this->get_field_id('astero'); ?>"><?php _e('Astero Weather:',ASTERO_SLUG); ?></label>
                        <select id="<?php echo $this->get_field_id('astero'); ?>" name="<?php echo $this->get_field_name('astero'); ?>" class="widefat">  
                                <option><?php _e('Choose weather',ASTERO_SLUG); ?></option>
                                <?php while ( $posts->have_posts() ) : $posts->the_post(); ?>
                                <option value="<?php the_ID(); ?>" <?php selected( $instance['astero'], get_the_ID()); ?>><?php the_title(); ?></option>
                                <?php endwhile; ?>
                                <?php wp_reset_postdata(); ?>
                        </select>
		</p>
                <p>
                        <a href="<?php echo get_option("siteurl"); ?>/wp-admin/post-new.php?post_type=astero" target="_blank"><?php _e('Add new weather', ASTERO_SLUG); ?></a>
                </p>
		
        	<?php 
	}
}

// Add Widget
function astero_widget_init() {
	register_widget('astero_widget');
}
add_action('widgets_init', 'astero_widget_init');
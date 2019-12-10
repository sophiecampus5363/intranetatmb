<div class="cmb-container">
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Location', ASTERO_SLUG); ?></label>
                        <p class="description<?php if( $location != 'geolocation') echo ' js-hide'; ?>" data-check-field="location" data-check-value="location-geolocation"><?php _e('Requires Google Map API. Please enter under Settings > Astero Weather.', ASTERO_SLUG); ?></p>
                </div>
                <div class="small-9 columns end">
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input id="location" type="radio" class="select-change" name="location" value="geolocation" <?php checked($location, 'geolocation'); ?>></div>
                                <div class="small-11 columns"><?php _e('Html5 Geolocation with IP fallback (HTTPS Sites Only)', ASTERO_SLUG); ?></div>
                        </div>
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input id="location" type="radio" class="select-change" name="location" value="ip" <?php checked($location, 'ip'); ?>></div>
                                <div class="small-11 columns"><?php _e('IP Detection Only', ASTERO_SLUG); ?></div>
                        </div>
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input id="location" type="radio" class="select-change" name="location" value="city" <?php checked($location, 'city'); ?>></div>
                                <div class="small-11 columns">
                                        <?php _e('Default Location', ASTERO_SLUG); ?>
                                        <div class="cmb-subcontainer<?php if( $location != 'city' ) echo ' js-hide'; ?>" data-check-field="location" data-check-value="location-city">
                                                <h3 class="hndle"><?php _e('City and Optional Country', ASTERO_SLUG); ?></h3>
                                                <div class="cmb-subcontainer-content">
                                                        <input type="text" class="regular-text" name="city" value="<?php echo $city; ?>" />
                                                        <input type="hidden" name="lat" value="<?php echo $lat; ?>" />
                                                        <input type="hidden" name="lon" value="<?php echo $lon; ?>" />
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
        </div>
        <div class="row<?php if( $location != 'city') echo ' js-hide'; ?>" data-check-field="location" data-check-value="location-city">
                <div class="small-3 columns">
                        <label><?php _e('Latitude*', ASTERO_SLUG); ?></label>
                        <p class="description"><?php _e('Required. Address to Latitude and Longitude: <a href="https://www.latlong.net/" target="_blank">https://www.latlong.net/</a>', ASTERO_SLUG); ?></p>
                </div>
                <div class="small-9 columns end">
                	<input type="text" class="regular-text" name="lat_manual" value="<?php echo $lat_manual; ?>" />
                	<p class="description"><?php _e('In decimal places. E.g. 40.730610', ASTERO_SLUG); ?></p>
                </div>
        </div>
        <div class="row<?php if($location != 'city') echo ' js-hide'; ?>" data-check-field="location" data-check-value="location-city">
                <div class="small-3 columns">
                        <label><?php _e('Longitude*', ASTERO_SLUG); ?></label>
                        <p class="description"><?php _e('Required. Address to Latitude and Longitude: <a href="https://www.latlong.net/" target="_blank">https://www.latlong.net/</a>', ASTERO_SLUG); ?></p>
                </div>
                <div class="small-9 columns end">
                	<input type="text" class="regular-text" name="lon_manual" value="<?php echo $lon_manual; ?>" />
                	<p class="description"><?php _e('In decimal places. E.g. -73.935242', ASTERO_SLUG); ?></p>
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Units',ASTERO_SLUG); ?></label>
                        <p class="description"><?php _e('Metric units: &degC | m/s<br />Imperial units: &degF | MPH', ASTERO_SLUG); ?></p>
                </div>
                <div class="small-9 columns end">
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input type="radio" name="units" value="metric" <?php checked($units, 'metric'); ?> /></div>
                                <div class="small-11 columns"><?php _e('Metric', ASTERO_SLUG); ?></div>
                        </div>
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input type="radio" name="units" value="imperial" <?php checked($units, 'imperial'); ?> /></div>
                                <div class="small-11 columns"><?php _e('Imperial', ASTERO_SLUG); ?></div>
                        </div>
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Temperature Unit Style',ASTERO_SLUG); ?></label>
                </div>
                <div class="small-9 columns end">
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input type="radio" name="display_units" value="0" <?php checked($display_units, '0'); ?> /></div>
                                <div class="small-11 columns"><?php _e('&deg;', ASTERO_SLUG); ?></div>
                        </div>
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input type="radio" name="display_units" value="1" <?php checked($display_units, '1'); ?> /></div>
                                <div class="small-11 columns"><?php _e('&deg;C | &deg;F', ASTERO_SLUG); ?></div>
                        </div>
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Layout',ASTERO_SLUG); ?></label>
                </div>
                <div class="small-9 columns end">
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input type="radio" name="layout" value="simple" <?php checked($layout, 'simple'); ?> /></div>
                                <div class="small-11 columns"><?php _e('Simple Layout + Fullscreen Overlay', ASTERO_SLUG); ?></div>
                        </div>
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input type="radio" name="layout" value="full" <?php checked($layout, 'full'); ?> /></div>
                                <div class="small-11 columns"><?php _e('Full Forecast Layout', ASTERO_SLUG); ?></div>
                        </div>
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Language',ASTERO_SLUG); ?></label>
                </div>
                <div class="small-9 columns end">
                        <select name="fc_lang" class="css-select">
                                <?php foreach ( $languages as $code => $l): ?>
                                <option value="<?php echo $code; ?>" <?php selected($fc_lang, $code); ?>><?php echo $l; ?></option>
                                <?php endforeach; ?>
                        </select>
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Search',ASTERO_SLUG); ?></label>
                	<p class="description"><?php _e('Requires Google Map API. Please enter under Settings > Astero Weather.', ASTERO_SLUG); ?></p>
                </div>
                <div class="small-9 columns end">
                        <input id="search" type="checkbox" name="search" value="1" <?php checked( $search, '1' ); ?>>
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Border',ASTERO_SLUG); ?></label>
                </div>
                <div class="small-9 columns">
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input type="checkbox" class="select-change" id="border" name="border" value="1" <?php checked($border, '1'); ?> /></div>
                                <div class="small-11 columns">
                                        <div class="cmb-top cmb-subcontainer<?php if( $border != '1' ) echo ' js-hide'; ?>" data-check-field="border" data-check-value="border-1">
                                                <h3 class="hndle"><?php _e('Select Border Style', ASTERO_SLUG); ?></h3>
                                                <div class="cmb-subcontainer-content">
                                                        <input id="border_size" type="text" class="small-text input-prefix" name="border_size" value="<?php echo $border_size; ?>" /><span class="input-suffix"><?php _e('px', ASTERO_SLUG); ?></span>
                                                        <select id="border_style" name="border_style" class="css-select">
                                                                <option value=""><?php _e('Border style', ASTERO_SLUG); ?></option>
                                                                <option value="dotted" <?php selected($border_style, 'dotted'); ?>><?php _e('Dotted', ASTERO_SLUG); ?></option>
                                                                <option value="dashed" <?php selected($border_style, 'dashed'); ?>><?php _e('Dashed', ASTERO_SLUG); ?></option>
                                                                <option value="solid" <?php selected($border_style, 'solid'); ?>><?php _e('Solid', ASTERO_SLUG); ?></option>
                                                                <option value="double" <?php selected($border_style, 'double'); ?>><?php _e('Double', ASTERO_SLUG); ?></option>
                                                                <option value="groove" <?php selected($border_style, 'groove'); ?>><?php _e('Groove', ASTERO_SLUG); ?></option>
                                                                <option value="ridge" <?php selected($border_style, 'ridge'); ?>><?php _e('Ridge', ASTERO_SLUG); ?></option>
                                                                <option value="inset" <?php selected($border_style, 'inset'); ?>><?php _e('Inset', ASTERO_SLUG); ?></option>
                                                                <option value="outset" <?php selected($border_style, 'outset'); ?>><?php _e('Outset', ASTERO_SLUG); ?></option>
                                                        </select>
                                                        <input type="text" id="border_color" class="colorpicker" name="border_color" value="<?php echo $border_color; ?>" />
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Rounded Corners',ASTERO_SLUG); ?></label>
                </div>
                <div class="small-9 columns end">
                        <input id="roundhidden" type="hidden" value="0" name="round" />
                        <input id="round" type="checkbox" name="round" value="1" <?php checked( $round, '1' ); ?>>
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Font',ASTERO_SLUG); ?></label>
                </div>
                <div class="small-9 columns end">
                        <select id="font_family" name="font_family" class="css-select">
                                <option value=""><?php _e('Font Family', ASTERO_SLUG); ?></option>
                                <?php
                                $variants_group = '';
                                $subsets_group = '';
                                foreach ($items as $item) {
                                        $i++;
                                        $str = $item['family'];
                                        $variants = '';
                                        $subsets = '';
                                        $backup = '';
                                        if ( isset( $item['variants']) && is_array( $item['variants'] ) ) {
                                                $variants = ' data-variants="' . implode(',', $item['variants']) . '"';
                                                
                                                if( $str == $font_family ) {
                                                        foreach( $item['variants'] as $var ) {
                                                                $variants_group .= '<option value="' . $var . '" ' . selected($font_variant, $var) . '>' . $var . '</option>';
                                                        }
                                                }
                                        }
                                        if ( isset( $item['subsets']) && is_array( $item['subsets'] ) ) {
                                                $subsets = ' data-subsets="' . implode(',', $item['subsets']) . '"';
                                                
                                                if( $str == $font_family ) {
                                                        foreach( $item['subsets'] as $sub ) {
                                                                $subsets_group .= '<option value="' . $sub . '" ' . selected($font_subset, $sub) . '>' . $sub . '</option>';
                                                        }
                                                }
                                        }
                                        if ( isset( $item['category'] ) ) {
                                                $cat = $item['category'];
                                                
                                                switch( $cat) {
                                                        case 'sans-serif':
                                                                $backup = ' data-backup="sans-serif"';
                                                                break;
                                                        case 'serif':
                                                                $backup = ' data-backup="serif"';
                                                                break;
                                                        case 'display':
                                                        case 'handwriting':
                                                                $backup = ' data-backup="cursive"';
                                                                break;
                                                }
                                        }
                                       ?>
                                     
                                       <option value="<?php echo $str; ?>"<?php echo $variants . $subsets . $backup; ?> <?php selected($font_family, $str); ?>><?php  echo $str; ?></option>
                                <?php } ?>
                        </select>
                        <select id="font_variant" name="font_variant" class="css-select<?php if( $font_variant == '' ) echo ' js-hide'; ?>">
                                <?php echo $variants_group; ?>
                        </select>
                        <select id="font_subset" name="font_subset" class="css-select<?php if( $font_subset == '' ) echo ' js-hide'; ?>">
                                <?php echo $subsets_group; ?>
                        </select>
                        <input type="hidden" name="font_backup" id="font_backup" value="<?php echo $font_backup; ?>" />
                        <input type="text" id="font_color" class="colorpicker" name="font_color" value="<?php echo $font_color; ?>" />
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Base Font Size',ASTERO_SLUG); ?></label>
                        <p class="description"><?php _e('Adjust this value to change the overall font size.', ASTERO_SLUG); ?></p>
                </div>
                <div class="small-9 columns end">
                        <input id="base_font_size" type="text" class="small-text input-prefix" name="base_font_size" value="<?php echo $base_font_size; ?>" /><span class="input-suffix"><?php _e('px', ASTERO_SLUG); ?></span>
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Temperature Font Size',ASTERO_SLUG); ?></label>
                        <p class="description"><?php _e('Adjust this value to change the temperature font size only.', ASTERO_SLUG); ?></p>
                </div>
                <div class="small-9 columns end">
                        <input id="temp_font_size" type="text" class="small-text input-prefix" name="temp_font_size" value="<?php echo $temp_font_size; ?>" /><span class="input-suffix"><?php _e('em', ASTERO_SLUG); ?></span>
                </div>
        </div>
        <div class="row">
                <div class="small-3 columns">
                        <label><?php _e('Background Style',ASTERO_SLUG); ?></label>
                </div>
                <div class="small-9 columns end">
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input id="style" type="radio" class="select-change" name="style" value="color" <?php checked($style, 'color'); ?>></div>
                                <div class="small-11 columns">
                                        <?php _e('Color Background', ASTERO_SLUG); ?>
                                        <div class="cmb-subcontainer<?php if( $style != 'color' ) echo ' js-hide'; ?>" data-check-field="style" data-check-value="style-color">
                                                <h3 class="hndle"><?php _e('Color', ASTERO_SLUG); ?></h3>
                                                <div class="cmb-subcontainer-content">
                                                        <input type="text" class="colorpicker" id="background-color" name="background_color" value="<?php echo $background_color; ?>" />
                                                </div>
                                        </div>
                                </div>
                        </div>
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input id="style" type="radio" class="select-change" name="style" value="image" <?php checked($style, 'image'); ?>></div>
                                <div class="small-11 columns">
                                        <?php _e('Image Background', ASTERO_SLUG); ?>
                                        <div class="cmb-subcontainer<?php if( $style != 'image' ) echo ' js-hide'; ?>" data-check-field="style" data-check-value="style-image">
                                                <h3 class="hndle"><?php _e('Weather Images', ASTERO_SLUG); ?></h3>
                                                <div class="cmb-subcontainer-content">
                                                        <div id="tabs" class="clearfix">
                                                                <ul>
                                                                <?php foreach ( $conditions as $k => $label): ?>
                                                                        <li><a href="#tabs-<?php echo $k; ?>"><?php echo $label; ?></a></li>
                                                                <?php endforeach; ?>
                                                                </ul>
                                                                <?php foreach ( $conditions as $k => $label): ?>
                                                                <div id="tabs-<?php echo $k; ?>">
                                                                        <div class="row">
                                                                                <div class="small-6 columns select-radio">
                                                                                        <div class="row">
                                                                                                <div class="small-2 columns">
                                                                                                        <input id="<?php echo $k; ?>_default" type="radio" name="<?php echo $k; ?>" value="default" <?php checked($$k, 'default'); ?> />
                                                                                                </div>
                                                                                                <div class="small-10 columns">
                                                                                                        <?php _e('Default', ASTERO_SLUG); ?>
                                                                                                        <p><img src="<?php echo ASTERO_URL . 'admin/imgs/' . $k . '.jpg'; ?>" alt="<?php echo $k; ?>" /></p>
                                                                                                </div>
                                                                                        </div>
                                                                                </div>
                                                                                <div class="small-6 columns select-radio">
                                                                                        <div class="row">
                                                                                                <div class="small-2 columns">
                                                                                                        <input id="<?php echo $k; ?>_custom" type="radio" name="<?php echo $k; ?>" <?php if( $$k != 'default' ) echo 'value="' . $$k . '" checked="checked"'; ?> />
                                                                                                </div>
                                                                                                <div class="small-10 columns">
                                                                                                        <?php _e('Custom Image', ASTERO_SLUG); ?>
                                                                                                        <ul class="image-preview">
                                                                                                                <?php if ($$k != 'default'): ?>
                                                                                                                        <li id="<?php echo $k . '_upload' . $$k; ?>">
                                                                                                                                <?php echo wp_get_attachment_link( $$k, 'thumbnail', true ); ?>
                                                                                                                                <a href="javascript:;" id="delete-<?php echo $k . '_upload' . $$k; ?>" class="cmb-button small-button delete-media" data-input-name="<?php echo $k;?>_custom">&times;</a>
                                                                                                                        </li>
                                                                                                                <?php endif; ?>
                                                                                                        </ul>
                                                                                                        <div class="uploader clear">
                                                                                                                <a id="<?php echo $k; ?>_upload" href="javascript:;" class="cmb-button default-button upload_image_button<?php if ($$k != 'default' && $$k != '') echo ' js-hide'; ?>" data-lib="image" data-input-name="<?php echo $k;?>_custom"><?php _e('Upload', ASTERO_SLUG); ?></a>
                                                                                                        </div>
                                                                                                </div>
                                                                                        </div>
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                                <?php endforeach; ?>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                        <div class="row cmb-row">
                                <div class="small-1 columns"><input id="style" type="radio" class="select-change" name="style" value="video" <?php checked($style, 'video'); ?>></div>
                                <div class="small-11 columns">
                                        <?php _e('Video Background', ASTERO_SLUG); ?>
                                        <div class="cmb-subcontainer<?php if( $style != 'video' ) echo ' js-hide'; ?>" data-check-field="style" data-check-value="style-video">
                                                <h3 class="hndle"><?php _e('Video Background', ASTERO_SLUG); ?></h3>
                                                <div class="cmb-subcontainer-content">
                                                        <div class="row">
                                                                <div class="small-3 columns">
                                                                        <label><?php _e('Type', ASTERO_SLUG); ?></label>
                                                                </div>
                                                                <div class="small-9 columns end">
                                                                        <div class="row cmb-row">
                                                                                <div class="small-1 columns"><input id="video" type="radio" class="select-change" name="video" value="html5" <?php checked($video, 'html5'); ?>></div>
                                                                                <div class="small-11 columns"><?php _e('Html5 Video', ASTERO_SLUG); ?></div>
                                                                        </div>
                                                                        <div class="row cmb-row">
                                                                                <div class="small-1 columns"><input id="video" type="radio" class="select-change" name="video" value="yt" <?php checked($video, 'yt'); ?>></div>
                                                                                <div class="small-11 columns">
                                                                                        <?php _e('YouTube Embed', ASTERO_SLUG); ?>
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                        <div class="<?php if( $video != 'html5' ) echo 'js-hide'; ?>" data-check-field="video" data-check-value="video-html5">
                                                                <div class="row">
                                                                        <div class="small-3 columns">
                                                                                <label><?php _e('Source', ASTERO_SLUG); ?></label>
                                                                                <p class="description">
                                                                                        <?php _e('.webm and .mp4 formats are recommended for cross browser support.',ASTERO_SLUG); ?><br />
                                                                                </p>
                                                                        </div>
                                                                        <div class="small-9 columns end">
                                                                                <?php foreach( $videos as $v ): ?>
                                                                                <div class="row cmb-row">
                                                                                        <div class="small-2 columns"><?php _e('.' . $v, ASTERO_SLUG); ?></div>
                                                                                        <div class="small-9 end columns">
                                                                                                <div class="uploader clear">
                                                                                                        <a id="<?php echo $v; ?>_upload" href="javascript:;" class="cmb-button default-button upload_image_button<?php if( $$v != '' ) echo ' js-hide'; ?>" data-lib="video/<?php echo $v; ?>" data-input-name="<?php echo $v; ?>"><?php _e('Upload', ASTERO_SLUG); ?></a>
                                                                                                        <?php if( $$v != '' ) echo '<div id="' . $v . '_upload' . $$v . '"><span class="description">' . wp_get_attachment_url( $$v ) . '</span><a href="javascript:;" id="delete-' . $v . '_upload' . $$v . '" class="cmb-button small-button delete-media" data-input-name="' . $v . '">&times;</a></div>'; ?>
                                                                                                        <input type="hidden" id="<?php echo $v; ?>" name="<?php echo $v; ?>" value="<?php echo $$v; ?>" />
                                                                                                </div>
                                                                                        </div>
                                                                                </div>
                                                                                <?php endforeach; ?>
                                                                        </div>
                                                                </div>
                                                                <div class="row">
                                                                        <div class="small-3 columns">
                                                                                <label><?php _e('Placeholder Image', ASTERO_SLUG); ?></label>
                                                                                <p class="description">
                                                                                        <?php _e('Fallback image for unsupported browsers.',ASTERO_SLUG); ?><br />
                                                                                </p>
                                                                        </div>
                                                                        <div class="small-9 columns end">
                                                                                <ul class="image-preview">
                                                                                        <?php if ($placeholder != ''): ?>
                                                                                                <li id="media<?php echo $placeholder; ?>">
                                                                                                        <?php echo wp_get_attachment_link( $placeholder, 'thumbnail', true ); ?>
                                                                                                        <a href="javascript:;" id="delete-media<?php echo $placeholder; ?>" class="cmb-button small-button delete-media" data-input-name="placeholder">&times;</a>
                                                                                                </li>
                                                                                        <?php endif; ?>
                                                                                </ul>
                                                                                <input type="hidden" id="placeholder" name="placeholder" value="<?php echo $placeholder; ?>" />
                                                                                <div class="uploader clear">
                                                                                        <a id="placeholder_upload" href="javascript:;" class="cmb-button default-button upload_image_button<?php if( $placeholder != '') echo ' js-hide'; ?>" data-lib="image" data-input-name="placeholder"><?php _e('Upload', ASTERO_SLUG); ?></a>
                                                                                </div>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                        <div class="<?php if( $video != 'yt' ) echo 'js-hide'; ?>" data-check-field="video" data-check-value="video-yt">
                                                                <div class="row">
                                                                        <div class="small-3 columns">
                                                                                <label><?php _e('YouTube Video ID', ASTERO_SLUG); ?></label>
                                                                        </div>
                                                                        <div class="small-9 columns">
                                                                                <input type="text" class="regular-text" id="yt_id" name="yt_id" value="<?php echo $yt_id; ?>" />
                                                                                <p class="description">
                                                                                        <?php _e('E.g. For http://www.youtube.com/watch?v=<strong>pfqNykQgaOa</strong>&key=value, enter <strong>pfqNykQgaOa</strong>',ASTERO_SLUG); ?><br />
                                                                                </p>
                                                                        </div>
                                                                </div>
                                                                <div class="row">
                                                                        <div class="small-3 columns">
                                                                                <label><?php _e('Aspect Ratio', ASTERO_SLUG); ?></label>
                                                                        </div>
                                                                        <div class="small-9 columns end">
                                                                                <select id="aspect_ratio" name="aspect_ratio" class="select-change" class="css-select">
                                                                                        <option value="1.77777778" <?php selected($aspect_ratio, '1.77777778'); ?>><?php _e('16:9', ASTERO_SLUG); ?></option>
                                                                                        <option value="1.33333333" <?php selected($aspect_ratio, '1.33333333'); ?>><?php _e('4:3', ASTERO_SLUG); ?></option>
                                                                                        <option value="custom" <?php selected($aspect_ratio, 'custom'); ?>><?php _e('Custom Ratio', ASTERO_SLUG); ?></option>
                                                                                </select>
                                                                                <p class="<?php if( $aspect_ratio != 'custom' ) echo 'js-hide'; ?>" data-check-field="aspect_ratio" data-check-value="aspect_ratio-custom">
                                                                                        <input type="text" name="custom_ratio1" id="custom_ratio1" class="small-text" value="<?php echo $custom_ratio1; ?>" /> : <input type="text" name="custom_ratio2" id="custom_ratio2" class="small-text" value="<?php echo $custom_ratio2; ?>" />
                                                                                </p>
                                                                        </div>
                                                                </div>
                                                        </div>
                                                </div>
                                        </div>
                                </div>
                        </div>
                </div>
        </div>
        <?php wp_nonce_field('save_astero_meta_' . $post->ID,'save_astero_meta_nonce_field'); ?>
</div>
<div id="astero<?php echo $id; ?>" class="astero<?php echo $service . $radius . $img; ?>" data-<?php echo $plugin_name; ?>='<?php echo $weather; ?>'>
        <div class="astero-background">
                <?php echo $background; ?>
        </div>
        <div class="astero-large astero-full">
                <div class="astero-large-container">
                        <div class="astero-content">
                                <div class="astero-inner">
                                        <div class="astero-location">
                                                ______
                                        </div>
                                        <div class="astero-date small-font-size">
                                                <?php echo date_i18n( 'F jS Y, l' ); ?>
                                        </div>
                                        <div class="astero-temp margin10"><span class="astero-temperature"></span><?php echo $temp_unit; ?></div>
                                        <div class="astero-icon margin30">
                                                <span class="astero-code asterofont"></span>&nbsp;&nbsp;&nbsp;<span class="astero-condition">___</span>
                                        </div>
                                        <ul class="astero-details astero-small-blck-grid-2 astero-row">
                                                <li>
                                                        <div class="astero-row astero-hi-lo-temp">
                                                                <div class="astero-small-4"><span class="asterofont astero-details-icon asterofont-thermometer"></span></div>
                                                                <div class="astero-small-8"><?php _e('TEMPERATURE', ASTERO_SLUG); ?><br /><span class="astero-lo-temp"></span><?php echo $temp_unit; ?> | <span class="astero-hi-temp"></span><?php echo $temp_unit; ?></div>    
                                                        </div>
                                                </li>
                                                <li>
                                                        <div class="astero-row astero-humidity">
                                                                <div class="astero-small-4"><span class="asterofont asterofont-droplets astero-details-icon"></span></div>
                                                                <div class="astero-small-8"><?php _e('HUMIDITY', ASTERO_SLUG); ?><br /><span class="astero-humidity-text"></span>%</div>  
                                                        </div>
                                                </li>
                                                <li>
                                                        <div class="astero-row astero-wind">
                                                                <div class="astero-small-4"><span class="asterofont astero-details-icon asterofont-windy"></span></div>
                                                                <div class="astero-small-8"><?php _e('WIND', ASTERO_SLUG); ?><br /><span class="astero-wind-text"></span><?php echo $custom['units'] == 'imperial' ? 'MPH' : 'm/s'; ?></div>  <!-- imperial: miles per hour metric: meters per second -->
                                                        </div>
                                                </li>
                                                <li>
                                                        <div class="astero-row astero-cloudiness">
                                                                <div class="astero-small-4"><span class="asterofont astero-details-icon asterofont-heavy-cloud"></span></div>
                                                                <div class="astero-small-8"><?php _e('CLOUDINESS', ASTERO_SLUG); ?><br /><span class="astero-cloud-text"></span>%</div>  
                                                        </div>
                                                </li>
                                                <li class="astero-eq-medium-hide astero-eq-xsmall-hide">
                                                        <div class="astero-row astero-sunrise">
                                                                <div class="astero-small-4"><span class="asterofont asterofont-sunrise astero-details-icon"></span></div>
                                                                <div class="astero-small-8"><?php _e('SUNRISE', ASTERO_SLUG); ?><br /><span class="astero-sunrise-text"></span></div>  
                                                        </div>
                                                </li>
                                                <li class="astero-eq-medium-hide astero-eq-xsmall-hide">
                                                        <div class="astero-row astero-sunset">
                                                                <div class="astero-small-4"><span class="asterofont asterofont-sunset astero-details-icon"></span></div>
                                                                <div class="astero-small-8"><?php _e('SUNSET', ASTERO_SLUG); ?><br /><span class="astero-sunset-text"></span></div>  
                                                        </div>
                                                </li>
                                        </ul>
                                        <div class="astero-fc astero-eq-small-hide astero-eq-xsmall-hide">
                                                <ul class="astero-small-blck-grid-1">
                                                        <?php for ( $i = 0; $i < 6; $i++): ?>
                                                        <li<?php if ( $i == 4 || $i == 5 ) echo ' class="astero-eq-medium-hide"'; ?>>
                                                                <div class="astero-row">
                                                                        <div class="astero-eq-small-3 astero-eq-medium-12"><div class="astero-fc-date"><?php echo strtoupper( date_i18n( 'D j', strtotime("+" . $i + 1 . " day") ) ); ?></div></div>
                                                                        <div class="astero-eq-small-2 astero-eq-medium-12"><div class="astero-fc-icon astero-fc-icon<?php echo $i; ?> asterofont"></div></div>
                                                                        <div class="astero-eq-small-3 astero-eq-medium-12"><div class="astero-fc-temp margin10"><span class="astero-fc-lo-temp<?php echo $i; ?>"></span><?php echo $temp_unit; ?> | <span class="astero-fc-hi-temp<?php echo $i; ?>"></span><?php echo $temp_unit; ?></div></div>
                                                                        <div class="astero-eq-small-4 astero-eq-medium-12 margin20 astero-fc-condition astero-fc-condition<?php echo $i; ?>"></div>
                                                                        <div class="astero-eq-small-hide astero-eq-medium-12 margin10"><div class="small-font-size"><span class="asterofont asterofont-heavy-cloud"></span> <?php _e('Cloudiness', ASTERO_SLUG); ?></div><span class="astero-fc-cloud<?php echo $i; ?>"></span>%</div>
                                                                        <div class="astero-eq-small-hide astero-eq-medium-12"><div class="small-font-size"><span class="asterofont asterofont-droplet"></span> <?php _e('Humidity', ASTERO_SLUG); ?></div><span class="astero-fc-humidity<?php echo $i; ?>"></span>%</div>
                                                                </div>
                                                        </li>
                                                        <?php endfor; ?>
                                                </ul>
                                        </div>
                                        <?php if( isset( $custom['search'] ) && $custom['search'] == '1' ): ?>
                                        <div class="astero-search">
                                                <a href="javascript:;"><span class="astero-searchicon asterofont-search"></span></a>
                                        </div>
                                        <?php endif; ?>
                                        <?php echo $credit; ?>
                                </div>
                        </div>
                </div>
        </div>
        <?php if( isset( $custom['search'] ) && $custom['search'] == '1' ): ?>
        <?php
        $input = '<input type="text" name="location" placeholder="' . __('a city or zip code', ASTERO_SLUG) . '" autocomplete="off" />';
        $select = '<select name="units"><option value="metric">' . __('&deg;C', ASTERO_SLUG) . '</option><option value="imperial">' . __('&deg;F', ASTERO_SLUG) . '</option></select>';
        ?>
        <div class="astero-form center">
                <form method="post" action="javascript:;">
                        <p class="margin30"><?php printf( __( 'I want to find the weather for %1$s in %2$s .', ASTERO_SLUG ), $input, $select ); ?></p>
                        <button><span class="asterofont asterofont-search astero-searchicon"></span></button>
                </form>
                <div class="astero-closeform"><a href="javascript:;">&times;</a></div>
        </div>
        <?php endif; ?>
</div>
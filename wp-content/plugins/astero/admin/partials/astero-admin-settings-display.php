<div class="wrap">
        <h2><?php _e('Astero Weather Plugin Settings'); ?></h2>
        <form method="post" action="options.php">
                <?php settings_fields( ASTERO_OPTIONS . '_group' ); ?>
                <table class="form-table">
                        <tr valign="top">
                                <th scope="row"><?php _e('Dark Sky (Forecast.io) API Key', ASTERO_SLUG); ?></th>
                                <td><input type="text" id="api" class="regular-text" name="<?php echo ASTERO_OPTIONS; ?>[forecast_api]" value="<?php echo isset( $astero_options['forecast_api'] ) ? $astero_options['forecast_api']: ''; ?>" /><br />
                                    <span class="description"><?php _e('Sign up for a free or paid Dark Sky (Forecast.io) key <a href="https://darksky.net/dev/register" target="_blank">here</a> and enter the API key above.', ASTERO_SLUG); ?></span></td>
                        </tr>
                        <?php $skip_map = isset( $astero_options['skip_map'] ) ? $astero_options['skip_map'] : 0; ?>
                        <tr valign="top"<?php if( $skip_map == '1') echo ' class="hidden"'; ?> data-check-field="skip_map" data-check-value="skip_map-0">
                                <th scope="row"><?php _e('Google Map API Key', ASTERO_SLUG); ?></th>
                                <td><input type="text" id="api" class="regular-text" name="<?php echo ASTERO_OPTIONS; ?>[google_map_api]" value="<?php echo isset( $astero_options['google_map_api'] ) ? $astero_options['google_map_api']: ''; ?>" /><br />
                                    <span class="description"><?php _e('Required for weather search on the frontend. Please sign up for a Google Map API Key <a href="https://developers.google.com/maps/documentation/javascript/get-api-key#key" target="_blank">here</a> and enter the API key above.', ASTERO_SLUG); ?></span></td>
                        </tr>
                        <tr valign="top">
                                <th scope="row"><?php _e('Skip Load Google Map', ASTERO_SLUG); ?></th>
                                <td><input type="checkbox" id="skip_map" name="<?php echo ASTERO_OPTIONS; ?>[skip_map]" class="select-change" value="1" <?php checked( $skip_map, '1' ); ?>" /><br />
                                    <span class="description"><?php _e('If Google Map is already loaded from your theme or other plugins, check this option to skip loading Google Map.', ASTERO_SLUG); ?></span></td>
                        </tr>
                </table>
                <?php submit_button(); ?>
        </form>
</div>
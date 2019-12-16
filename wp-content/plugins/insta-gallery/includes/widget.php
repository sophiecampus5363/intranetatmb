<?php
if (!defined('ABSPATH'))
  exit;

class QLIGG_Widget extends WP_Widget {

  public function __construct() {
    parent::__construct('QLIGG_Widget', QLIGG_PLUGIN_NAME, array(
        'classname' => 'instagal-widget',
        'description' => esc_html__('Displays your Instagram gallery', 'insta-gallery')
    ));
  }

  public function widget($args, $instance) {
    $title = empty($instance['title']) ? '' : apply_filters('widget_title', $instance['title']);
    $instagal_id = empty($instance['instagal_id']) ? '' : $instance['instagal_id'];

    echo $args['before_widget'];

    if (!empty($title)) {
      echo $args['before_title'] . wp_kses_post($title) . $args['after_title'];
    }

    if (!empty($instagal_id)) {
      echo do_shortcode('[insta-gallery id="' . $instagal_id . '"]');
    }

    echo $args['after_widget'];
  }

  public function form($instance) {
    $instance = wp_parse_args((array) $instance, array(
        'title' => '',
        'instagal_id' => 0,
    ));

    $title = $instance['title'];
    $instagal_id = $instance['instagal_id'];
    $instagram_feeds = get_option('insta_gallery_items');
    ?>
    <p>
      <label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php esc_html_e('Title', 'insta-gallery'); ?>: <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></label>
    </p>
    <?php if (!empty($instagram_feeds) && is_array($instagram_feeds)): ?>
      <p>
        <label for="<?php echo esc_attr($this->get_field_id('instagal_id')); ?>"><?php esc_html_e('Select your Instagram gallery', 'insta-gallery'); ?>: </label> <select
          id="<?php echo esc_attr($this->get_field_id('instagal_id')); ?>" name="<?php echo esc_attr($this->get_field_name('instagal_id')); ?>" class="widefat">
            <?php
            foreach ($instagram_feeds as $id => $instagram_feed) {

              if (isset($instagram_feed['insta_source'])) {
                if ($instagram_feed['insta_source'] == 'username') {
                  $profile_info = qligg_get_user_profile($instagram_feed['insta_username']);
                } else {
                  $profile_info = qligg_get_tag_profile($instagram_feed['insta_tag']);
                }
              }

              $label = sprintf('%s: %s', sprintf(esc_html__('Feed %s', 'insta-gallery'), $id), $profile_info['name']);
              ?>		
            <option value="<?php echo esc_html($id); ?>" <?php selected($id, $instagal_id) ?>><?php echo esc_html($label); ?></option>
          <?php } ?>
        </select>
      </p>
    <?php else: ?>
      <p style="color: #e23565;">
        <?php esc_html_e('Please add new gallery in plugin admin panel, then come back and select your gallery to here.', 'insta-gallery'); ?>
      </p>
    <?php endif; ?> 
    <p style="text-align: center;" >
      <a target="_blank" href="<?php echo admin_url('admin.php?page=qligg_feeds'); ?>"><?php esc_html_e('Add New Gallery', 'insta-gallery'); ?></a> 
    </p>
    <?php
  }

  public function update($new_instance, $old_instance) {
    $instance = $old_instance;
    $instance['title'] = strip_tags($new_instance['title']);
    $instance['instagal_id'] = trim(strip_tags($new_instance['instagal_id']));
    return $instance;
  }

}

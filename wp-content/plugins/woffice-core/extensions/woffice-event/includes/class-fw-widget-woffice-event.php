<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

class Widget_Woffice_Event extends WP_Widget {

	/**
     * Woffice Event widget registration
	 *
	 * @since 2.8.3
     *
	 * @internal
	 */
	function __construct() {
		$this->events = fw()->extensions->get( 'woffice-event' );
		if ( is_null( $this->events ) ) {
			return;
		}
		
		$widget_ops = array( 'description' => 'Woffice Calendar to display the events' );
		parent::__construct( false, __( '(Woffice) Calendar Events', 'woffice' ), $widget_ops );
	}

	/**
     * Render the Event widget
     *
	 * @param array $args
	 * @param array $instance
     *
     * @return mixed
	 */
	function widget( $args, $instance ) {
        // We will render this widget when user not logged in
		if (!is_user_logged_in()) {
			return false;
		}

		global $bp;

        // This is to avoid multiple instance of calendar on Dashboard.
		if (is_front_page()) {
			$sidebars = wp_get_sidebars_widgets();
			$sidebar_name = null;

			foreach ((array) $sidebars as $sidebar_id => $sidebar) {
				if ( in_array( $args['widget_id'], (array) $sidebar, true ) ) {
					$sidebar_name = $sidebar_id;
				}
			}

			if ($sidebar_name === 'content' && preg_grep('/^woffice_event-/', $sidebars['dashboard'])) {
				return false;
			}
		}

        /*
         * Do not render this widget on Project single page, BuddyPress Group Calendar page
         * and BuddyPress Profile Calendar page
         */
		if (is_singular('project')
            || (bp_is_groups_component() && $bp->current_action === 'group-calendar')
            || $bp->current_action === 'calendar' ) {
			return false;
		}

		$data = array(
			'before_widget' => $args['before_widget'],
			'after_widget'  => $args['after_widget'],
			'before_title'  => str_replace( 'class="', 'class="widget_woffice_calendar ', $args['before_title']),
			'after_title'   => $args['after_title'],
			'title'         => str_replace( 'class="', 'class="widget_woffice_events ',
					$args['before_title'] ) . esc_html($instance['title']) . $args['after_title'],
		);

		echo fw_render_view($this->events->locate_view_path( 'widget-event' ), $data );
		
	}

	/**
	 * Render widget form
	 *
	 * @param array $instance
	 *
	 * @return string|void
	 */
	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '' ));
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>">
                <?php _e( 'Title', 'woffice' ); ?>
            </label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			       value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"
			       id="<?php esc_attr( $this->get_field_id( 'title' ) ); ?>"/>
		</p>
		<?php
	}

	/**
	 * Update widget settings
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	function update( $new_instance, $old_instance ) {
		return $new_instance;
	}
}

function fw_ext_woffice_event_register_widget() {
	register_widget( 'Widget_Woffice_Event' );
}
add_action( 'widgets_init', 'fw_ext_woffice_event_register_widget' );


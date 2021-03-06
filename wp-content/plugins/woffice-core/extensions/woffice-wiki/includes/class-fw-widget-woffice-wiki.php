<?php 

class Widget_Woffice_Wiki extends WP_Widget {

	/**
	 * @internal
	 */
	function __construct() {
		$this->wiki = fw()->extensions->get( 'woffice-wiki' );
		if ( is_null( $this->wiki ) ) {
			return;
		}
		
		$widget_ops = array( 'description' => 'Woffice widget to display the last wiki posts.' );
		parent::__construct( false, __( '(Woffice) Wiki Articles', 'woffice' ), $widget_ops );
	}
	/**
	 * @param array $args
	 * @param array $instance
	 */
	function widget( $args, $instance ) {
	
	
		$data = array(
			'before_widget' => $args['before_widget'],
			'after_widget'  => $args['after_widget'],
			'show'     		=> esc_attr($instance['show']),
			'before_title'  => str_replace( 'class="', 'class="widget_wiki ', $args['before_title']),
			'after_title'   => $args['after_title'],
			'title'         => str_replace( 'class="', 'class="widget_wiki ',
				 $args['before_title'] ) . esc_html($instance['title']) . $args['after_title'],
		);

        if(array_key_exists('category', $instance))
            $data['category'] = $instance['category'];

		echo fw_render_view($this->wiki->locate_view_path( 'widget' ), $data );
		
	}

	function update( $new_instance, $old_instance ) {
		return $new_instance;
	}

	function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '',  'show' => '4') );
		?>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'title' ) ); ?>"><?php _e( 'Title', 'woffice' ); ?> </label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'title' ) ); ?>"
			       value="<?php echo esc_attr( $instance['title'] ); ?>" class="widefat"
			       id="<?php esc_attr( $this->get_field_id( 'title' ) ); ?>"/>
		</p>
		<p>
			<label for="<?php echo esc_attr( $this->get_field_id( 'show' ) ); ?>"><?php _e( 'Number', 'woffice' ); ?> </label>
			<input type="text" name="<?php echo esc_attr( $this->get_field_name( 'show' ) ); ?>"
			       value="<?php echo esc_attr( $instance['show'] ); ?>" class="widefat"
			       id="<?php esc_attr( $this->get_field_id( 'show' ) ); ?>"/>
		</p>
        <p>
            <label for="<?php echo esc_attr( $this->get_field_id('category') ); ?>"><?php _e('Category:','woffice'); ?></label>
            <?php
            // GET PROJECTS CATEGORY
            $projects_terms = get_terms('wiki-category', array('hide_empty' => false)); ?>
            <select class="widefat" name="<?php echo esc_attr( $this->get_field_name('category') ); ?>" id="<?php echo esc_attr( $this->get_field_id('category') ); ?>">
                <option value="all"><?php _e('All','woffice'); ?></option>
                <?php if ($projects_terms) :
                    foreach ( $projects_terms as $term ) { ?>
                        <option value="<?php echo esc_attr( $term->slug ); ?>" <?php selected($term->slug, (isset($instance['category']) ? $instance['category'] : null)); ?>><?php echo esc_html( $term->name ); ?></option>
                    <?php }
                endif; ?>
            </select>
        </p>
	<?php
	}
}

function fw_ext_woffice_wiki_register_widget() {
	register_widget( 'Widget_Woffice_Wiki' );
}
add_action( 'widgets_init', 'fw_ext_woffice_wiki_register_widget' );


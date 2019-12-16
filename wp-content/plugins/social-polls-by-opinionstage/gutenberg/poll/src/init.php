<?php
/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package OSWP
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function opinionStage_poll_oswp_block_assets_set() {
	wp_enqueue_style(
		'opinionStage_poll_oswp_style_css_set',
		plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), 
		array() 
	);
} 

// add_action( 'enqueue_block_assets', 'opinionStage_poll_oswp_block_assets_set' );


function opinionStage_poll_oswp_editor_assets_set() {
	// Scripts.
	wp_enqueue_script(
			'opinionStage_poll_oswp_block_js_set', // Handle.
			plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element','wp-components','wp-editor' ), 		
			true
		);

	// Styles.
	wp_enqueue_style('Bootstrap',plugins_url( '/css/bootstrap.min.css', dirname( __FILE__  )));

	wp_enqueue_style(
			'opinionStage_poll_oswp_block_editor_css_set', // Handle.
			plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
			array( 'wp-edit-blocks' ) 
		);
} 

add_action( 'enqueue_block_editor_assets', 'opinionStage_poll_oswp_editor_assets_set' );

// Adding in  tool bar 
add_action( 'admin_footer', 'print_admin_js_template' );
function print_admin_js_template() {
	?>
	<script id="opinion-stage-gutenberg-button-switch-mode-check" type="text/html">
		<div id="opinion-stage-switch-mode-check">
			<button id="opinion-stage-mode-button" type="button" class="button button-primary button-large">
				<span class="opinion-stage-switch-mode-connected">Connect To Opinion Stage</span>
			</button>
		</div>
	</script>
	<?php
}
?>
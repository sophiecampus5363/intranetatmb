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
	function opinionStage_form_oswp_block_assets_set() {
		wp_enqueue_style(
			'opinionStage_form_oswp_style_css_set',
			plugins_url( 'dist/blocks.style.build.css', dirname( __FILE__ ) ), 
			array() 
		);
	} 
	// add_action( 'enqueue_block_assets', 'opinionStage_form_oswp_block_assets_set' );

	function opinionStage_form_oswp_editor_assets_set() {
		// Scripts.
		wp_enqueue_script(
			'opinionStage_form_oswp_block_js_set', // Handle.
			plugins_url( '/dist/blocks.build.js', dirname( __FILE__ ) ),
			array( 'wp-blocks', 'wp-i18n', 'wp-element','wp-components','wp-editor' ), 	
			true
		);

		
		// Styles.
		wp_enqueue_style('Bootstrap',plugins_url( '/css/bootstrap.min.css', dirname( __FILE__  )));
		wp_enqueue_style(
			'opinionStage_form_oswp_block_editor_css_set', // Handle.
			plugins_url( 'dist/blocks.editor.build.css', dirname( __FILE__ ) ), // Block editor CSS.
			array( 'wp-edit-blocks' ) 
		);
	} 
	add_action( 'enqueue_block_editor_assets', 'opinionStage_form_oswp_editor_assets_set' );
	?>
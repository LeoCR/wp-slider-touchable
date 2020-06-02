<?php
/**
 * Plugin Name: Slider Touchable
 * Plugin URI:  https://github.com/LeoCR/wp-slider-touchable
 * Description: Responsive Slider
 * Version:     1.0.0
 * Author:      Leonardo Aranibar 
 * Author URI:  https://twitter.com/LeoAranibarCR
 * License:     GPL-2.0+
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
require_once(plugin_dir_path( __FILE__ ). '/includes/class-slider-touchable-display.php' );
require_once(plugin_dir_path( __FILE__ ). '/includes/class-slider-touchable-editor.php' );
require_once(plugin_dir_path( __FILE__ ). '/includes/class-slider-touchable-admin.php' );

function slider_touchable_start() {
	if( is_admin() ) {
		$post_editor = new SliderTouchable_Editor();
		$post_notice = new SliderTouchable_Admin( $post_editor );
	} 
	else {
		$post_notice = new SliderTouchable_Display();
	}
	$post_notice->initialize();
}
slider_touchable_start();
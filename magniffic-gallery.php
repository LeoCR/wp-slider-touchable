<?php
/**
 * Plugin Name: Magniffic Gallery
 * Plugin URI:  https://github.com/LeoCR/wp-magniffic-gallery
 * Description: Display a Gallery 
 * Version:     1.0.0
 * Author:      Leonardo Aranibar 
 * Author URI:  https://twitter.com/LeoAranibarCR
 * License:     GPL-2.0+
 */
if ( ! defined( 'ABSPATH' ) ) {
	die;
}
require_once(plugin_dir_path( __FILE__ ). '/includes/class-magniffic-gallery-display.php' );
require_once(plugin_dir_path( __FILE__ ). '/includes/class-magniffic-gallery-editor.php' );
require_once(plugin_dir_path( __FILE__ ). '/includes/class-magniffic-gallery-admin.php' );

function magniffic_gallery_start() {
	if( is_admin() ) {
		$post_editor = new MagnifficGallery_Editor();
		$post_notice = new MagnifficGallery_Admin( $post_editor );
	} 
	else {
		$post_notice = new MagnifficGallery_Display();
	}
	$post_notice->initialize();
}
magniffic_gallery_start();
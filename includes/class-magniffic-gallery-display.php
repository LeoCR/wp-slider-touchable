<?php
class MagnifficGallery_Display {
	public function initialize() {
		add_filter( 'the_content', array( $this, 'display_notice' ) );
		add_shortcode( 'magniffic_gallery', array( $this, 'magniffic_gallery_func') );		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
	}
	public function display_notice( $content ) {
		$notice = get_post_meta( get_the_ID(), 'magniffic_gallery', true );
		if ( '' != $notice ) {
			$notice_html = '<div id="magniffic_gallery">';
				$notice_html .= $notice;
			$notice_html .= '</div>';
			$content = $notice_html . $content;
		}
		return $content;
	}
	public function enqueue_styles_and_scripts() {
		wp_enqueue_style( 'simplelightbox_css', plugin_dir_url( __FILE__ ) . '../assets/simplelightbox/dist/simplelightbox.min.css' );
		wp_enqueue_script( 'simplelightbox_js', plugin_dir_url( __FILE__ ) . '../assets/simplelightbox/dist/simple-lightbox.min.js', array('jquery'), '1.0',true);
	}
	/**
	* This function set a shortcode for watching our magnifficGallery on the Front-End
	* @link https://codex.wordpress.org/Shortcode_API
	* @package wp_magniffic_gallery
	* @since wp_magniffic_gallery 1.0.0
	* @param  array $atts   => attributtes of the shortcode 
	* @return string        => html and javascript source code
	**/
	public function magniffic_gallery_func( $atts ) {
		$mgnigLighboxGalleryJson=$mgnigLighboxGalleryTouchable=$slidesImagesJs="";
		global $wpdb;
		$mgnigLighboxGalleryTouchable.="<div class='main-slider_container' id='main-slider_container'>";
		$attributes = shortcode_atts( array(
			'id' => 1 
		), $atts );  
		$sqlMagnifficGalleryImagesArgs = "SELECT * FROM $wpdb->posts WHERE post_type='magniffic_gallery' AND ID=".$attributes['id']." AND post_status='publish'";  	
		$sqlMagnifficGallery=$wpdb->get_results($sqlMagnifficGalleryImagesArgs) ;
		foreach($sqlMagnifficGallery  as $image) { // each column in your row will be accessible like this 
			if(get_post_meta($image->ID, "magniffic_gallery_object", true )!==''){
				$slidesImagesJs="var slideImagesJson=".get_post_meta($image->ID, "magniffic_gallery_object", true ).";"; 
				$mgnigLighboxGalleryJson= json_decode( get_post_meta($image->ID, "magniffic_gallery_object", true )) ;
			}  
		} 
		if( $slidesImagesJs===''){ 
			$mgnigLighboxGalleryTouchable=" ";//do nothing
		}
		else{
			$mgnigLighboxGalleryTouchable .="  
				<div id='wp_magniffic_gallery-container'>
					<div id='content_wp_magniffic_gallery' class='wp_magniffic_gallery-content'>
						<ul id='wp_magniffic_gallery-".$attributes['id'] ."' class='wp_magniffic_gallery-list-of-images'>";
						for ($i=0; $i < count($mgnigLighboxGalleryJson); $i++) {
							$mgnigLighboxGalleryTouchable .="<li> <a href='".$mgnigLighboxGalleryJson[$i]->url."'  data-lightbox='image-1' data-title='Mycaption'>";
							if($mgnigLighboxGalleryJson[$i]->caption!==null){
								$mgnigLighboxGalleryTouchable .="<img src='".$mgnigLighboxGalleryJson[$i]->url."' title='".$mgnigLighboxGalleryJson[$i]->caption."'/></a></li>";
							}
							else{
								$mgnigLighboxGalleryTouchable .="<img src='".$mgnigLighboxGalleryJson[$i]->url."'/></a></li>";
							}
						}
						$mgnigLighboxGalleryTouchable .=" </ul>
					</div> 
				</div> 
				<script id='wp_magnifficGallery_js' data-defer='defer'> 
				jQuery(function(){
					var gallery = jQuery('.wp_magniffic_gallery-list-of-images a').simpleLightbox();
			
					gallery.on('show.simplelightbox', function(){
						console.log('Requested for showing');
					})
					.on('shown.simplelightbox', function(){
						console.log('Shown');
					})
					.on('close.simplelightbox', function(){
						console.log('Requested for closing');
					})
					.on('closed.simplelightbox', function(){
						console.log('Closed');
					})
					.on('change.simplelightbox', function(){
						console.log('Requested for change');
					})
					.on('next.simplelightbox', function(){
						console.log('Requested for next');
					})
					.on('prev.simplelightbox', function(){
						console.log('Requested for prev');
					})
					.on('nextImageLoaded.simplelightbox', function(){
						console.log('Next image loaded');
					})
					.on('prevImageLoaded.simplelightbox', function(){
						console.log('Prev image loaded');
					})
					.on('changed.simplelightbox', function(){
						console.log('Image changed');
					})
					.on('nextDone.simplelightbox', function(){
						console.log('Image changed to next');
					})
					.on('prevDone.simplelightbox', function(){
						console.log('Image changed to prev');
					})
					.on('error.simplelightbox', function(e){
						console.log('No image found, go to the next/prev');
						console.log(e);
					});
				});
				</script> 
				</div>
			";
		} 
		return $mgnigLighboxGalleryTouchable; 
	}
}

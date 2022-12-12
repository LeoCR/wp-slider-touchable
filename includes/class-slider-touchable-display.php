<?php
class SliderTouchable_Display {
	public function initialize() {
		add_filter( 'the_content', array( $this, 'display_notice' ) );
		add_shortcode( 'slider_touchable', array( $this, 'slider_touchable_func') );
		add_action('wp_footer', array( $this, 'footer_scripts'));
	}
	public function display_notice( $content ) {
		$notice = get_post_meta( get_the_ID(), 'slider_touchable', true );
		if ( '' != $notice ) {
			$notice_html = '<div id="slider_touchable">';
				$notice_html .= $notice;
			$notice_html .= '</div>';
			$content = $notice_html . $content;
		}
		return $content;
	}
	public function footer_scripts()
{
	global $post;
	global $wpdb;
	
}

	/**
	* This function set a shortcode for watching our magnifficGallery on the Front-End
	* @link https://codex.wordpress.org/Shortcode_API
	* @package wp_slider_touchable
	* @since wp_slider_touchable 1.0.0
	* @param  array $atts   => attributtes of the shortcode 
	* @return string        => html and javascript source code
	**/
	public function slider_touchable_func( $atts ) {
		
		$sliderTouchableJson=$sliderTouchable=$slidesImagesJs=$sliderTouchableJsonSettings="";
		global $wpdb;
		
		$attributes = shortcode_atts( array(
			'id' => 1 
		), $atts );  
		$sliderTouchableJS="";
		$sqlSliderTouchableImagesArgs = "SELECT * FROM $wpdb->posts WHERE post_type='slider_touchable' AND ID=".$attributes['id']." AND post_status='publish'";  	
		$sqlSliderTouchable=$wpdb->get_results($sqlSliderTouchableImagesArgs) ;
		$sliderTouchable.="<div class='swiper-container swiper-slider-container' id='slider-touchable-".$sqlSliderTouchable[0]->ID."' style='float:left;'><div class='swiper-wrapper'>";
		
		
		foreach($sqlSliderTouchable  as $image) { // each column in your row will be accessible like this 
			if(get_post_meta($image->ID, "slider_touchable_object", true )!==''){
				$slidesImagesJs="var slideImagesJson=".get_post_meta($image->ID, "slider_touchable_object", true ).";"; 
				$sliderTouchableJson= json_decode( get_post_meta($image->ID, "slider_touchable_object", true )) ;
			}  
			if(get_post_meta($image->ID,"slider_touchable_object_settings",true)!==''){
				$sliderTouchableJsonSettings=json_decode( get_post_meta($image->ID,"slider_touchable_object_settings",true));
			}
		} 
		$sliderTouchableJS.='<script data-defer="defer" id="swiper" src="'.get_stylesheet_directory_uri() . '/assets/js/vendors/swiper.min.js"></script>';
		$sliderTouchableJS.='<script data-defer="defer" id="swiper-slider">';
		$sliderTouchableJS.='const jsonSettings='.get_post_meta($image->ID,"slider_touchable_object_settings",true).';';
		$sliderTouchableJS.='let hasAutoPlay=jsonSettings.settings.hasAutoplay;';
		$sliderTouchableJS.='if (typeof Swiper !== undefined) {';
		$sliderTouchableJS.='if (hasAutoPlay) {}';
		$sliderTouchableJS.='var swiperSlider = new Swiper("#slider-touchable-'.$sqlSliderTouchable[0]->ID.'", {';
		$sliderTouchableJS.='loop: true,effect: "cube",grabCursor: true,';
		$sliderTouchableJS.='cubeEffect: {';
		$sliderTouchableJS.='shadow: true,slideShadows: true,shadowOffset: 20,';
		$sliderTouchableJS.='shadowScale: 0.94,},';
		$sliderTouchableJS.='navigation: {';
		$sliderTouchableJS.='nextEl: ".swiper-button-next",';
		$sliderTouchableJS.='prevEl: ".swiper-button-prev",';
		$sliderTouchableJS.='clickable: true,';
		$sliderTouchableJS.='},});}</script>';
		$sliderTouchableCSS="<style type='text/css' id='slider-touachable-css'>";
		if( $slidesImagesJs===''){ 
			$sliderTouchable=" ";//do nothing
		}
		else{
						try {
							for ($i=0; $i < count($sliderTouchableJson); $i++) {
								$imageUrl=esc_url($sliderTouchableJson[$i]->url);
								$sliderTouchable .="<div class='swiper-slide slide-".$i ."' data-img='".$imageUrl."' style='background:url(" .$imageUrl .") no-repeat center center;background-position:fixed;background-size:cover;'>";
								
								if(isset($sliderTouchableJson[$i]->captionBackground)&&$sliderTouchableJson[$i]->captionBackground!==''){
									$sliderTouchableCSS.=".slide-".$i." .caption{background:'".$sliderTouchableJson[$i]->captionBackground."';}";
									$sliderTouchable .="<aside style='background:".$sliderTouchableJson[$i]->captionBackground.";z-index: 7; position: absolute; width: 100%; max-width: 500px; top: 15%; left: 45%; padding: 10px; height: 100%; opacity: 1; max-height: 270px; visibility: inherit;' class='aside_caption'>";
								}
								else{
									$sliderTouchable .="<aside style='background:blue;z-index: 7; position: absolute; width: 100%; max-width: 500px; top: 15%; left: 45%; padding: 10px; height: 100%; opacity: 1; max-height: 270px; visibility: inherit;' class='aside_caption'>";
								}
								if(isset($sliderTouchableJson[$i]->slideBackground) && $sliderTouchableJson[$i]->slideBackground!==''){
									$sliderTouchableCSS.=".slide-".$i."{background:'".$sliderTouchableJson[$i]->slideBackground."';}";
								}
								else{
									$sliderTouchableCSS.=".slide-".$i."{background:url('".$imageUrl."') no-repeat center center;}";
									
									
								}
								if(isset($sliderTouchableJson[$i]->caption)){
									$sliderTouchable.="<div style='position:absolute;z-index:6;padding:20px 30px 10px 25px' class='content_caption'>";
									if(isset($sliderTouchableJson[$i]->title)&& $sliderTouchableJson[$i]->title!==''){
										$sliderTouchable .="<h1 style='visibility: inherit; opacity: 1; transform: matrix(1, 0, 0, 1, 0, 0);'>".$sliderTouchableJson[$i]->title ."</h1>";
									}
									if($sliderTouchableJson[$i]->caption!==''){
										$sliderTouchable.="<p style='visibility: inherit; opacity: 1; transform: matrix(1, 0, 0, 1, 0, 0);'>".$sliderTouchableJson[$i]->caption."</p>";
									} 
									if(isset($sliderTouchableJson[$i]->readMoreTxt) && $sliderTouchableJson[$i]->readMoreTxt!==''){
										if(isset($sliderTouchableJson[$i]->readMoreUrl) && $sliderTouchableJson[$i]->readMoreUrl!==''){
											$sliderTouchable.="<a href='".$sliderTouchableJson[$i]->readMoreUrl."' class='btn btn_slider'  style='z-index: 10; visibility: inherit; opacity: 1; transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);'>".$sliderTouchableJson[$i]->readMoreTxt."</a>";
										}
										else{
											$sliderTouchable.="<a href='#' class='btn btn_slider' style='z-index: 10; visibility: inherit; opacity: 1; transform: matrix3d(1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1, 0, 0, 0, 0, 1);'>".$sliderTouchableJson[$i]->readMoreTxt."</a>";
										}
									}
									$sliderTouchable .="</div></aside>";
									
								}
								$sliderTouchable .="</div>";
							}
						} catch (Exception $e) {
							$sliderTouchable .=$e->getMessage();
						}
						$sliderTouchable .="
				</div>
				<div class='swiper-button-next'></div>
				<div class='swiper-button-prev'></div>
			</div>
			";
			$sliderTouchable.=$sliderTouchableJS;
			$sliderTouchableCSS.="</style>";
			$sliderTouchable .=$sliderTouchableCSS;
		} 
		return $sliderTouchable; 
	}
}

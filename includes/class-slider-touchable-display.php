<?php
class SliderTouchable_Display {
	public function initialize() {
		add_filter( 'the_content', array( $this, 'display_notice' ) );
		add_shortcode( 'slider_touchable', array( $this, 'slider_touchable_func') );		
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles_and_scripts' ) );
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
	public function enqueue_styles_and_scripts() {
		//wp_enqueue_style( 'swiper_css', plugin_dir_url( __FILE__ ) . '../assets/css/dev/swiper.css' );
		wp_register_script( 'swiper',plugin_dir_url( __FILE__ ) . '../assets/js/swiper.min.js',array(), '5.2.0',false );
		wp_register_script( 'swiper_slider',plugin_dir_url( __FILE__ ) . '../assets/js/slides.min.js',array('swiper'), '5.2.0',true );
		
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
		$sqlSliderTouchableImagesArgs = "SELECT * FROM $wpdb->posts WHERE post_type='slider_touchable' AND ID=".$attributes['id']." AND post_status='publish'";  	
		$sqlSliderTouchable=$wpdb->get_results($sqlSliderTouchableImagesArgs) ;
		$sliderTouchable.="<div class='swiper-container swiper-slider-container' id='slider-touchable-".$attributes['id']."'><div class='swiper-wrapper'>";
		$sliderTouchableCSS="<style type='text/css' id='slider-touachable-css'>";
		
		foreach($sqlSliderTouchable  as $image) { // each column in your row will be accessible like this 
			if(get_post_meta($image->ID, "slider_touchable_object", true )!==''){
				$slidesImagesJs="var slideImagesJson=".get_post_meta($image->ID, "slider_touchable_object", true ).";"; 
				$sliderTouchableJson= json_decode( get_post_meta($image->ID, "slider_touchable_object", true )) ;
			}  
			if(get_post_meta($image->ID,"slider_touchable_object_settings",true)!==''){
				$sliderTouchableJsonSettings=json_decode( get_post_meta($image->ID,"slider_touchable_object_settings",true));
			}
		} 
		if( $slidesImagesJs===''){ 
			$sliderTouchable=" ";//do nothing
		}
		else{
						try {
							for ($i=0; $i < count($sliderTouchableJson); $i++) {
								$imageUrl=esc_url($sliderTouchableJson[$i]->url);
								$sliderTouchable .="<div class='swiper-slide slide-".$i ."' data-img='".$imageUrl."'>";
								
								if(isset($sliderTouchableJson[$i]->captionBackground)&&$sliderTouchableJson[$i]->captionBackground!==''){
									$sliderTouchableCSS.=".slide-".$i." .caption{background:'".$sliderTouchableJson[$i]->captionBackground."';}";
								}
								if(isset($sliderTouchableJson[$i]->slideBackground) && $sliderTouchableJson[$i]->slideBackground!==''){
									$sliderTouchableCSS.=".slide-".$i."{background:'".$sliderTouchableJson[$i]->slideBackground."';}";
								}
								else{
									$sliderTouchableCSS.=".slide-".$i."{background:url('".$imageUrl."') no-repeat center center;}";
								}
								if(isset($sliderTouchableJson[$i]->caption)){
									$sliderTouchable .="<div class='caption'>";
									if(isset($sliderTouchableJson[$i]->title)&& $sliderTouchableJson[$i]->title!==''){
										$sliderTouchable .="<h1>".$sliderTouchableJson[$i]->title ."</h1>";
									}
									if($sliderTouchableJson[$i]->caption!==''){
										$sliderTouchable.="<p>".$sliderTouchableJson[$i]->caption."</p>";
									} 
									if(isset($sliderTouchableJson[$i]->readMoreTxt) && $sliderTouchableJson[$i]->readMoreTxt!==''){
										if(isset($sliderTouchableJson[$i]->readMoreUrl) && $sliderTouchableJson[$i]->readMoreUrl!==''){
											$sliderTouchable.="<a href='".$sliderTouchableJson[$i]->readMoreUrl."' class='btn hvr-rectangle-out'>".$sliderTouchableJson[$i]->readMoreTxt."</a>";
										}
										else{
											$sliderTouchable.="<a href='#' class='btn hvr-rectangle-out'>".$sliderTouchableJson[$i]->readMoreTxt."</a>";
										}
									}
									$sliderTouchable .="</div>";
									
								}
								$sliderTouchable .="</div>";
							}
						} catch (Exception $e) {
							$sliderTouchable .=$e->getMessage();
						}
						$sliderIdSelector="#slider-touchable-".$attributes['id'];
						$sliderData = array(
							'slider_id' => $sliderIdSelector,
							'settings'=>$sliderTouchableJsonSettings
						);
						wp_localize_script( 'swiper_slider', 'slider_object', $sliderData );
						wp_enqueue_script( 'swiper_slider' );
						$sliderTouchable .="  
				</div>
				<div class='swiper-button-next'></div>
				<div class='swiper-button-prev'></div>
			</div>
			";
			$sliderTouchableCSS.="</style>";
			$sliderTouchable .=$sliderTouchableCSS;
		} 
		return $sliderTouchable; 
	}
}

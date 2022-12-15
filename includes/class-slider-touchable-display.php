<?php
class SliderTouchable_Display {
	public function initialize() {
		add_filter( 'the_content', array( $this, 'display_notice' ) );
		add_shortcode( 'slider_touchable', array( $this, 'slider_touchable_func') );
		add_action('wp_head',array($this,'header_scripts'));
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
	public function header_scripts(){
		 echo '<style type="text/css" media="all" id="swiper-slider">'.wp_remote_fopen(plugin_dir_url( __FILE__ ). '../assets/css/dev/swiper.css').'</style>';
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
		$sliderTouchableJS.='<script id="swiper-lib-js" data-defer="defer" src="'.plugin_dir_url( __FILE__ ). '../assets/js/swiper.min.js'.'"></script>';
		$sliderTouchableJS.='<script data-defer="defer" id="swiper-slider">';
		$sliderTouchableJS.='const jsonSettings='.get_post_meta($image->ID,"slider_touchable_object_settings",true).';';
		 
		$sliderTouchableJsonSettings=json_decode(get_post_meta($image->ID,"slider_touchable_object_settings",true));
		$sliderTouchableJS.='let hasAutoPlay=jsonSettings.settings.hasAutoplay;';
		$sliderTouchableJS.='if (typeof Swiper !== undefined) {';
		$sliderTouchableJS.='if (hasAutoPlay) {}';
		$sliderTouchableJS.='var swiperSlider = new Swiper("#slider-touchable-'.$sqlSliderTouchable[0]->ID.'", {';
		$sliderTouchableJS.='loop: true,effect: "cube",grabCursor: true,';
		if($sliderTouchableJsonSettings->settings->hasAutoplay){
			$sliderTouchableJS.='autoplay: {
				delay: '.$sliderTouchableJsonSettings->settings->autoplay->duration.',
				disableOnInteraction: false,
			  },';
		}
		
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
				<div class='swiper-button-prev btn-prev'>
                    <svg style='fill-rule:evenodd;clip-rule:evenodd;stroke-linecap:square;stroke-miterlimit:1.5;max-width:70px' width='100%' height='100%' viewBox='0 0 272 276' version='1.1' xmlns='http://www.w3.org/2000/svg'>
                        <g transform='matrix(1,0,0,1,-13.8912,-10.9071)'>
                            <g transform='matrix(4.1707,0,0,4.1707,-5072.11,-1174.15)'>
                                <g class='gray_elipse' transform='matrix(0.397005,0,0,0.376374,1251.86,-298.781)'>
                                    <ellipse cx='0.259' cy='1638.91' rx='81.401' ry='84.792' style='fill:url(#bg_btn_linear_gray);stroke:rgb(108,108,108);stroke-width:0.53px'></ellipse>
                                </g>
                            </g>
                            <g transform='matrix(4.1707,0,0,4.1707,-5072.11,-1174.15)'>
                                <g class='bg_normal' transform='matrix(0.38389,0,0,0.367676,1252.03,-286.839)'>
                                    <ellipse cx='0.259' cy='1638.91' rx='81.401' ry='84.792' style='fill:#ffffff;stroke:#ffffff;stroke-width:1.28px'></ellipse>
                                </g>
                            </g>
                            <g transform='matrix(4.1707,0,0,4.1707,-5072.11,-1174.15)'>
                                <g transform='matrix(0.63116,1.52847e-16,-1.52847e-16,0.61693,1214.93,-682.097)'>
                                    <path d='M73.637,1649.99L26.725,1618.63L78.779,1584.29L61.028,1617.77L73.637,1649.99Z' style='fill:rgb(75,75,75);stroke:rgb(76,76,76);stroke-width:0.13px;stroke-linejoin:bevel'></path>
                                </g>
                            </g>
                            <g transform='matrix(4.1707,0,0,4.1707,-5072.11,-1174.15)'>
                                <g transform='matrix(0.63116,1.52847e-16,-1.52847e-16,0.61693,1215.32,-685.153)'>
                                    <path d='M73.29,1655.09L28.139,1623.03L79.931,1587.67L59.202,1622.46L73.29,1655.09Z' style='stroke:black;stroke-width:0.13px;stroke-linejoin:bevel'></path>
                                </g>
                            </g>
                        </g>
                        <defs>
                            <linearGradient id='bg_btn_linear_gray' x1='0' y1='0' x2='1' y2='0' gradientUnits='userSpaceOnUse' gradientTransform='matrix(162.802,0,0,169.583,-81.1418,1638.91)'>
                                <stop offset='0' style='stop-color:black;stop-opacity:1'></stop>
                                <stop offset='1' style='stop-color:rgb(160,160,160);stop-opacity:1'></stop>
                            </linearGradient>
                            <radialGradient id='bg_btn_radial_orange' cx='0' cy='0' r='1' gradientUnits='userSpaceOnUse' gradientTransform='matrix(75.3738,0,0,78.6976,5.53429,1631.77)'>
                                <stop offset='0' style='stop-color:rgb(255,141,53);stop-opacity:1'></stop>
                                <stop offset='1' style='stop-color:rgb(255,161,88);stop-opacity:1'></stop>
                            </radialGradient>
                        </defs>
                    </svg>
                </div>
                <div class='swiper-button-next btn-next'>
                    <svg width='100%' height='100%' viewBox='0 0 272 276' version='1.1' xmlns='http://www.w3.org/2000/svg' style='fill-rule:evenodd;clip-rule:evenodd;stroke-linecap:square;stroke-miterlimit:1.5;max-width:70px'>
                        <g transform='matrix(1,0,0,1,-13.8912,-10.9071)'>
                            <g transform='matrix(4.1707,0,0,4.1707,-5072.11,-1174.15)'>
                                <g class='gray_elipse' transform='matrix(0.397005,0,0,0.376374,1251.86,-298.781)'>
                                    <ellipse cx='0.259' cy='1638.91' rx='81.401' ry='84.792' style='fill:url(#bg_btn_linear_gray);stroke:rgb(108,108,108);stroke-width:0.53px'></ellipse>
                                </g>
                            </g>
                            <g transform='matrix(4.1707,0,0,4.1707,-5072.11,-1174.15)'>
                                <g class='bg_normal' transform='matrix(0.38389,0,0,0.367676,1252.03,-286.839)'>
                                    <ellipse cx='0.259' cy='1638.91' rx='81.401' ry='84.792' style='fill:#ffffff;stroke:#ffffff;stroke-width:1.28px'> 
                                    </ellipse>
                                </g>
                            </g>
                            <g transform='matrix(4.1707,0,0,4.1707,-5072.11,-1174.15)'>
                                <g id='gray_arrow' transform='matrix(-0.63116,-2.30142e-16,2.28399e-16,-0.61693,1287.43,1312.35)'>
                                    <path d='M73.637,1649.99L26.725,1618.63L78.779,1584.29L61.028,1617.77L73.637,1649.99Z' style='fill:rgb(75,75,75);stroke:rgb(76,76,76);stroke-width:0.13px;stroke-linejoin:bevel'></path>
                                </g>
                            </g>
                            <g transform='matrix(4.1707,0,0,4.1707,-5072.11,-1174.15)'>
                                <g id='black_arrow' transform='matrix(-0.63116,-2.30142e-16,2.28399e-16,-0.61693,1287.04,1315.4)'>
                                    <path d='M73.29,1655.09L28.139,1623.03L79.931,1587.67L59.202,1622.46L73.29,1655.09Z' style='stroke:black;stroke-width:0.13px;stroke-linejoin:bevel'></path>
                                </g>
                            </g>
                        </g>
                        <defs>
                            <linearGradient id='bg_btn_linear_gray' x1='0' y1='0' x2='1' y2='0' gradientUnits='userSpaceOnUse' gradientTransform='matrix(162.802,0,0,169.583,-81.1418,1638.91)'>
                                <stop offset='0' style='stop-color:black;stop-opacity:1'></stop>
                                <stop offset='1' style='stop-color:rgb(160,160,160);stop-opacity:1'></stop>
                            </linearGradient>
                            <radialGradient id='bg_btn_radial_orange' cx='0' cy='0' r='1' gradientUnits='userSpaceOnUse' gradientTransform='matrix(75.3738,0,0,78.6976,5.53429,1631.77)'>
                                <stop offset='0' style='stop-color:rgb(255,141,53);stop-opacity:1'></stop>
                                <stop offset='1' style='stop-color:rgb(255,161,88);stop-opacity:1'></stop>
                            </radialGradient>
                        </defs>
                    </svg>
                </div>
			</div>";
			$sliderTouchable.=$sliderTouchableJS;
			$sliderTouchableCSS.="</style>";
			$sliderTouchable .=$sliderTouchableCSS;
		} 
		return $sliderTouchable; 
	}
}

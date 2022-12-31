<?php
class SliderTouchable_Admin
{
	public function __construct($editor)
	{
		$editor->initialize();
	}
	public function initialize()
	{
		/**
		 * @description  init magniffic gallery
		 */
		add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
		add_action('save_post', array($this, "wp_save_slider_touchable_meta_box"), 10, 3);
		add_action('add_meta_boxes', array($this, "add_wp_slider_touchable_meta_box"));
		add_action('init', array($this, 'wp_slider_touchable_init'));
		register_activation_hook(__FILE__, array($this, 'wp_slider_touchable_flush_rewrite'));
		add_filter('manage_edit-slider_touchable_columns', array($this, 'slider_touchable_shortcodes_columns'));
		add_filter('manage_slider_touchable_posts_custom_column', array($this, 'slider_touchable_shortcodes_posts_columns'));
		add_filter('admin_footer_text', array($this, 'remove_footer_admin'));
	}
	/**
	 * This function Adds a meta box for put our custom fields of our slider_touchable .
	 * @link https://developer.wordpress.org/reference/functions/add_meta_box/
	 **/
	public function custom_meta_box_wp_slider_touchable()
	{
		wp_nonce_field(basename(__FILE__), "slider_touchable-nonce");
		global $post;
		$sliderTouchableID = $post->ID;
		$sliderSettings = get_post_meta($sliderTouchableID, 'slider_touchable_object_settings', true);
		$sliderTouchablePostName = $post->post_name;

		$sliderTouchableSQLArgs = array(
			'post_type' => 'slider_touchable',
			'p' => $sliderTouchableID,
			'pagename' => $sliderTouchablePostName,
			'post_status' => 'publish',
			'posts_per_page' => -1,
		);
?>
		<script>
			var tempJsonSettings;
			jQuery(document).ready(function() {
				<?php
				$querySlider = new WP_Query($sliderTouchableSQLArgs);
				try {
					if ($querySlider->have_posts()) {
						while ($querySlider->have_posts()) {
							$querySlider->the_post();
							$tempPostId = get_the_ID();
							if (get_post_meta($tempPostId, "slider_touchable_object_settings", true) !== '') {
								echo 'tempJsonSettings=' . $sliderSettings . ';';
							} else {
								echo 'tempJsonSettings={
								settings:{
									hasAutoplay:false
								}
							};';
							}
						}
						wp_reset_postdata();
					} else {
						echo 'tempJsonSettings={
						settings:{
							hasAutoplay:false
						}
					};';
					}
				} catch (Exception $exc) {
					echo 'console.log("' . $exc . '");';
				}
				?>
				if (tempJsonSettings.settings.hasAutoplay) {
					tempJsonSettings.settings.autoplay = {
						duration: parseInt(tempJsonSettings.settings.autoplay.duration)
					};
					jQuery('#autoplay-settings').css('display', 'block');
					jQuery('#autoplay_checkbox')[0].checked = true;
					jQuery('#slider_duration').val(parseInt(tempJsonSettings.settings.autoplay.duration));
				} else {
					jQuery('#autoplay-settings').css('display', 'none');
					jQuery('#autoplay_checkbox')[0].checked = false;
					tempJsonSettings.settings.hasAutoplay = false;
					tempJsonSettings.settings.autoplay = null;
				}
				jQuery('textarea#slider_touchable_object_settings').val(JSON.stringify(tempJsonSettings));
			});
		</script>
		<div class="attachments-browser hide-sidebar sidebar-for-errors wp_slider_touchable_container row">
			<div class="media-toolbar wp-filter">
				<div class="media-toolbar-secondary">
					<div class="view-switch media-grid-view-switch">
						<a href="#" class="view-list">
							<span class="screen-reader-text">List View</span>
						</a>
						<a href="#" class="view-grid current">
							<span class="screen-reader-text">Grid View</span>
						</a>
					</div>
					<input type="button" value="Upload Images" id="btn_add_more_pictures_slider_touchable" class="btn_add_more_pictures_slider_touchable btn btn-danger" />
				</div>
			</div>
			<div class="well well-sm col-lg-11">
				<p>For using this shortcode copy and paste the next code line inside a page,post or Widget:</p>
				<strong>
					<?php
					echo '[slider_touchable id="' . $sliderTouchableID . '"]';
					?>
				</strong>
			</div>
			<div class="col-lg-11 slider-ui-container">
				<ul class="nav nav-tabs">
					<li class="active" data-tab="slides">
						<a href="#">Images</a>
					</li>

					<li data-tab="settings">
						<a href="#">Settings</a>
					</li>

				</ul>
				<div class="tabs-content">
					<div class="tab-pane active tab-content current active" id="slides">
						<div class="col-lg-11 slider-ui-subcontainer">
							<div id="filter" style="margin-left: 30px">
								<div class="block__list block__list_words img_slider_touch">
									<ul id="sortable_slides_imgs">
										<?php
										/**
										 * The variable $sliderTouchableImagesJsonObject save the images inside a JSON Object
										 **/
										$sliderTouchableImagesJsonObject = '';
										$sliderTouchableImagesArgs = array(
											'post_type' => 'slider_touchable',
											'p' => $sliderTouchableID,
											'pagename' => $sliderTouchablePostName,
											'post_status' => 'publish',
											'posts_per_page' => -1,
										);
										/**
										 * The variable $query_images get all the images with the same id of the current slider_touchable post
										 * @see $sliderTouchableID
										 **/
										$query_images = new WP_Query($sliderTouchableImagesArgs);
										foreach ($query_images->posts as $image) {
											/**
											 * We just have one unique slider_touchable_object or an
											 * empty object
											 **/
											if (get_post_meta($image->ID, "slider_touchable_object", true) !== '') {
												$sliderTouchableImagesJsonObject .= get_post_meta($image->ID, "slider_touchable_object", true);
											}
										}
										?>
										<script type="text/javascript" id="slider_touchableObject" defer="defer">
											<?php
											$tempJson = "";
											if ($this->isJson($sliderTouchableImagesJsonObject)) {
												$tempJson = 'var sliderTouchableImagesJsonObject =' . $sliderTouchableImagesJsonObject . ';';
											} else {
												$tempJson = 'var sliderTouchableImagesJsonObject =[];';
											}
											echo $tempJson;
											?>
												(function() {
													jQuery(document).ready(function($) {
														var $imagesMagnifficGallery = '';
														for (var counter = 0; counter < sliderTouchableImagesJsonObject.length; counter++) {
															$imagesMagnifficGallery += '<li class="ui-state-default col-sm-2" data-order="' + parseInt(counter + 1) + '" data-id="' + sliderTouchableImagesJsonObject[counter].id + '" ' +
																'" data-url="' + sliderTouchableImagesJsonObject[counter].url + '" data-caption="'+sliderTouchableImagesJsonObject[counter]?.caption+'" '+
																' data-caption-background="'+sliderTouchableImagesJsonObject[counter]?.captionBackground+'" '+
																' data-slide-background="'+sliderTouchableImagesJsonObject[counter]?.slideBackground+'" '+
																' data-title-caption="'+sliderTouchableImagesJsonObject[counter]?.title+'" '+
																' data-txt-btn-read-more-caption="'+sliderTouchableImagesJsonObject[counter]?.readMoreTxt+'" '+
																' data-url-btn-read-more-caption="'+sliderTouchableImagesJsonObject[counter]?.readMoreUrl+'">'+
																'<span class="btn-icon-remove"></span>' +
																'<span class="btn-icon-move"></span>' +
																'<span class="btn-icon-edit"></span>' +
																'<p><img src="' + sliderTouchableImagesJsonObject[counter].url + '" ' +
																' class="image_slider_touchable" />' +
																'</p> ' +
																'</li>';
														}
														$('ul#sortable_slides_imgs').html($imagesMagnifficGallery);
														$('textarea#slider_touchable_object').text(JSON.stringify(sliderTouchableImagesJsonObject));
														$("#sortable_slides_imgs").sortable({
															stop: function(event, ui) {
																const imagesSlideUpdatedData = [];
																jQuery("ul#sortable_slides_imgs li.ui-state-default.col-sm-2").each(function(o) {
																	const currentPosition=parseInt(o + 1) 
																	jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').attr("data-order", parseInt(o + 1));
																	const objUpdated = {
																		
																		order: parseInt(o + 1),
																		id: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("id"),
																		url: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("url"),
																		caption: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("caption"),
																		slideBackground: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("slide-background"),
																		captionBackground: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("caption-background"),
																		title: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("title-caption"),
																		readMoreTxt: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("txt-btn-read-more-caption"),
																		readMoreUrl: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("url-btn-read-more-caption")

																	};
																	imagesSlideUpdatedData.push(objUpdated);
																});
																$('textarea#slider_touchable_object').val(JSON.stringify(imagesSlideUpdatedData));
																
															}
														});
														
													});
												})()
										</script>
									</ul>
								</div>
							</div>
						</div>
						<?php
						?>
						<textarea id="slider_touchable_object" name="slider_touchable_object" class="slider_touchable_object" style="width:100%;height:400px;"></textarea>
					</div>
				</div>

				<div class="tab-pane tab-content " id="settings">
					<h1>General Settings</h1>
					<div id="autoplay" class="form-check">
						<label for="autoplay" class="form-check-label">Autoplay</label>
						<input type="checkbox" name="autoplay_checkbox" id="autoplay_checkbox" class="form-check-input" />
					</div>
					<script data-defer="defer">
						jQuery(document).ready(function() {
							jQuery('#slide_bg_color_picker').wpColorPicker();
							jQuery('#caption_bg_color_picker').wpColorPicker();
							jQuery("#slider_touchable_object_settings").val(JSON.stringify(tempJsonSettings));
							jQuery('#autoplay_checkbox').on('change', function() {
								if (jQuery('#autoplay_checkbox').is(':checked')) {
									tempJsonSettings.settings.hasAutoplay = true;
									tempJsonSettings.settings.autoplay = {
										duration: jQuery("#slider_duration").val()
									};
									jQuery('#autoplay-settings').css('display', 'block');
								} else {
									jQuery('#autoplay-settings').css('display', 'none');
									tempJsonSettings.settings.hasAutoplay = false;
									tempJsonSettings.settings.autoplay = null;
								}
								jQuery("#slider_touchable_object_settings").val(JSON.stringify(tempJsonSettings));

							});
							jQuery("#slider_duration").on('change', function() {
								tempJsonSettings.settings.autoplay = {
									duration: jQuery(this).val()
								};
								jQuery("#slider_touchable_object_settings").val(JSON.stringify(tempJsonSettings));
							});
						});
					</script>
					<div id="autoplay-settings" style="display:none;">
						<label for="slider_duration">Duraci&oacute;n</label>
						<input type="text" name="slider_duration" id="slider_duration" />
						<span>Mili Seconds</span>
					</div>
					<textarea id="slider_touchable_object_settings" name="slider_touchable_object_settings" class="slider_touchable_object_settings"></textarea>
				</div>

			</div>
		</div>
		</div>
		<div class="modal" tabindex="-1" role="dialog" id="modal_caption">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
					<div class="modal-header">
						<h2 class="modal-title" style="max-width:120px;float:left;">Caption</h2>
						<button type="button" class="btn-close close" data-dismiss="modal" aria-label="Close">
							<span aria-hidden="true">&times;</span>
						</button>
					</div>
					<div class="modal-body">
						<div class="form-group">
							<label for="txt_title">Title Caption:</label>
							<input type="text" name="txt_title" id="txt_title" class="form-control" />
							<div id="caption-bg-container">
								<label for="caption_bg_color_picker">Caption Background:</label>
								<input type="text" class="caption_bg_color_picker" id="caption_bg_color_picker" class="form-control" />
							</div>
							<div id="slide-bg-container">
								<label for="slide_bg_color_picker">Slide Background:</label>
								<input type="text" class="slide_bg_color_picker" id="slide_bg_color_picker" class="form-control" />
							</div>
							<label for="txt_caption">Content Caption here:</label>

							<textarea name="txt_caption" id="txt_caption" cols="30" rows="10"></textarea>
							<label for="txt_read_more">Button Text:</label>
							<input type="text" name="txt_read_more" id="txt_read_more" class="form-control" value="Leer M&aacute;s" />
							<label for="txt_read_more_url">Button URL:</label>
							<input type="text" name="txt_read_more_url" id="txt_read_more_url" class="form-control" value="#" />
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-primary" id="btn-save">Save changes</button>
						<button type="button" class="btn btn-secondary btn-close" data-dismiss="modal">Close</button>
					</div>
				</div>
			</div>
		</div>
		<script data-defer="defer">
			let jsonPosition = 0;
			let tempJson = {};
			let jsonKeys = [];
			let imagesDataSavedOnLoad = [];
			let mediaUploader; //initialize mediaUploader
			jQuery(function() {
				jQuery(document).ready(function($) {
					$('ul#sortable_slides_imgs li.ui-state-default.col-sm-2').each(function(index) {
						const objSliderImages = {
							order: $(this).data('order'),
							id: $(this).data('id'),
							url: $(this).data('url'),
							caption: $(this).data("caption"),
							captionBackground: $(this).data("caption-background"),
							slideBackground: $(this).data("slide-background"),
							title: $(this).data("title-caption"),
							readMoreTxt: $(this).data("txt-btn-read-more-caption"),
							readMoreUrl: $(this).data("url-btn-read-more-caption")
						};
						imagesDataSavedOnLoad.push(objSliderImages);
					});

					/**
					 * Function for manage the tabs on the slider_touchable post_type
					 **/
					$('ul.nav.nav-tabs li').click(function() {
						var tab_id = $(this).attr('data-tab');
						$('ul.nav.nav-tabs li').removeClass('active current');
						$(this).addClass('active current');
						$('.tab-content').removeClass('active current');
						$('#' + tab_id).addClass('active current');
					});

					/**
					 * Function for add more images into slider without delete the old images saved
					 **/
					$('#btn_add_more_pictures_slider_touchable').on('click', function(e) {
						e.preventDefault();
						if (mediaUploader) {
							mediaUploader.open();
							return;
						}
						mediaUploader = wp.media.frames.file_frame = wp.media({
							title: 'Adding Pictures',
							button: {
								text: 'Adding your Pictures'
							},
							library: {
								order: 'id',
								orderby: 'title',
								type: 'image',
								uploadedTo: null
							},
							multiple: true
						});
						mediaUploader.on('select', function() {
							attachment = mediaUploader.state().get('selection').toJSON();
							const actualValue = $('textarea#slider_touchable_object').val() !== '' ? JSON.parse($('textarea#slider_touchable_object').val()) : []
							var imagesData = imagesDataSavedOnLoad,
								htmlImagesInserted = '';
							
								for (let index = 0; index < attachment.length; index++) {
									const obj = {
										order: actualValue.length===0?1:actualValue.length+1,
										id: attachment[index].id,
										url: attachment[index].url
									};
									htmlImagesInserted += '<li class="ui-state-default col-sm-2" ' +
										'data-order="' + parseInt(obj.order ) + '" data-id="' + obj.id + '" data-url="' + obj.url + '">' +
										'<span class="btn-icon-remove"></span>' +
										'<span class="btn-icon-move"></span>' +
										'<span class="btn-icon-edit" onclick="editCaption(this);"></span>' +
										'<p><img src="' + obj.url + '" ' +
										' class="image_slider_touchable" />' +
										'</p> ' +
										'</li>';

									actualValue.push(obj);
								}
							imagesDataSavedOnLoad = imagesData;
							$('ul#sortable_slides_imgs').append(htmlImagesInserted);
							$('textarea#slider_touchable_object').text(JSON.stringify(actualValue));
						});
						mediaUploader.open();
					});
					/**
					 * Function for open bootstrap modal on the slider_touchable post_type
					 **/
					const actualObjectValue=$('textarea#slider_touchable_object').val()
					tempJson = !actualObjectValue ?[]:JSON.parse(actualObjectValue);
					setTimeout(() => {
						let selectorPosition = 1;
						for (let k = 0; k < tempJson.length; k++) {
							for (let key in tempJson[k]) {
								if (key == 'captionBackground') {
									jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + selectorPosition + ')').attr('data-caption-background', tempJson[k][key]);
								} 
								else if (key == 'id') {
									jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + selectorPosition + ')').attr('data-id', tempJson[k][key]);
								} 
								else if (key == 'url') {
									jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + selectorPosition + ')').attr('data-url', tempJson[k][key]);
								} 
								else if (key == 'slideBackground') {
									jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + selectorPosition + ')').attr('data-slide-background', tempJson[k][key]);
								} else if (key == 'readMoreTxt') {
									jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + selectorPosition + ')').attr('data-txt-btn-read-more', tempJson[k][key]);
								} else if (key == 'readMoreUrl') {
									jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + selectorPosition + ')').attr('data-url-btn-read-more', tempJson[k][key]);
								} else if (key == 'title') {
									jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + selectorPosition + ')').attr('data-title-caption', tempJson[k][key]);
								} 
								else if (key == 'caption') {
									jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + selectorPosition + ')').attr('data-caption', tempJson[k][key]);
								} 
								else {
									jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + selectorPosition + ')').attr('data-' + key, tempJson[k][key]);
								}
							}
							selectorPosition++;
						}
					}, 1700);
					$('.btn-icon-edit').on('click', function() {
						jsonPosition = parseInt($(this).parent('.ui-state-default').data('order'))-1;

						if (tempJson[jsonPosition]?.caption !== null) {
							$('#txt_caption').val(tempJson[jsonPosition].caption);
						} else {
							$('#txt_caption').empty();
						}
						if (tempJson[jsonPosition]?.readMoreTxt !== null) {
							$('#txt_read_more').val(tempJson[jsonPosition].readMoreTxt);
						} else {
							$('#txt_read_more').empty();
						}
						if (tempJson[jsonPosition]?.readMoreUrl !== null) {
							$('#txt_read_more_url').val(tempJson[jsonPosition].readMoreUrl);
						} else {
							$('#txt_read_more_url').empty();
						}
						if (tempJson[jsonPosition]?.title !== null) {
							$('#txt_title').val(tempJson[jsonPosition].title);
						} else {
							$('#txt_title').empty();
						}
						if (tempJson[jsonPosition]?.captionBackground !== null) {
							$('#caption_bg_color_picker').val(tempJson[jsonPosition].captionBackground);
							$('#caption-bg-container .wp-color-result').css('background', tempJson[jsonPosition].captionBackground);
						} else {
							$('#caption_bg_color_picker').empty();
						}
						if (tempJson[jsonPosition]?.slideBackground !== null) {
							$("#slide_bg_color_picker").val(tempJson[jsonPosition].slideBackground);
							$("#slide-bg-container .wp-color-result").css('background', tempJson[jsonPosition].slideBackground);
						} else {
							$("#slide_bg_color_picker").empty();
						}
						$('#modal_caption').toggleClass('opened');
					});
					$("#caption-bg-container .wp-color-result").on('click', function() {
						if (tempJson[jsonPosition].captionBackground !== null) {
							$("#caption_bg_color_picker").val(tempJson[jsonPosition].captionBackground);
						}
					});
					$("#slide-bg-container .wp-color-result").on('click', function() {
						if (tempJson[jsonPosition].slideBackground !== null) {
							$("#slide_bg_color_picker").val(tempJson[jsonPosition].slideBackground);
						}
					});
					$('#btn-save').on('click', function() {
						const tempCaption = $('#txt_caption').val();
						const tempTitle = $('#txt_title').val();
						const tempCaptionBackground = $('#caption_bg_color_picker').val();
						const tempSlideBackground = $("#slide_bg_color_picker").val();
						const tempReadMoreTxt = $("#txt_read_more").val();
						const tempReadMoreUrl = $("#txt_read_more_url").val();
						if (tempCaption !== '') {
							jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + parseInt(jsonPosition + 1) + ')').attr('data-caption', tempCaption);
							Object.assign(tempJson[jsonPosition], {
								caption: tempCaption
							});
						}
						if (tempTitle !== '') {
							jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + parseInt(jsonPosition + 1) + ')').attr('data-title-caption', tempTitle);
							Object.assign(tempJson[jsonPosition], {
								title: tempTitle
							});
						}
						if (tempCaptionBackground !== '') {
							jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + parseInt(jsonPosition + 1) + ')').attr('data-caption-background', tempCaptionBackground);
							Object.assign(tempJson[jsonPosition], {
								captionBackground: tempCaptionBackground
							});
						}
						if (tempSlideBackground !== '') {
							jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + parseInt(jsonPosition + 1) + ')').attr('data-slide-background', tempSlideBackground);
							Object.assign(tempJson[jsonPosition], {
								slideBackground: tempSlideBackground
							});
						}
						if (tempReadMoreTxt !== '') {
							jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + parseInt(jsonPosition + 1) + ')').attr('data-txt-btn-read-more', tempReadMoreTxt);
							Object.assign(tempJson[jsonPosition], {
								readMoreTxt: tempReadMoreTxt
							});
						}
						if (tempReadMoreUrl !== '') {
							jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + parseInt(jsonPosition + 1) + ')').attr('data-url-btn-read-more', tempReadMoreUrl);
							Object.assign(tempJson[jsonPosition], {
								readMoreUrl: tempReadMoreUrl
							});
						}
						$('textarea#slider_touchable_object').val(JSON.stringify(tempJson));
						$('#modal_caption').toggleClass('opened');
					})
					$('.btn-close').on('click', function() {
						$('#modal_caption').toggleClass('opened');
					});
					/**
					 * Function for delete images of the slider
					 **/
					$('.btn-icon-remove').on('click', function() {
						$(this).parent('li.col-sm-2').remove();
						$("#sortable_slides_imgs").sortable("refreshPositions");
						const imagesSlideUpdatedData = [];
						jQuery("ul#sortable_slides_imgs li.ui-state-default.col-sm-2").each(function(o) {
							const currentPosition=parseInt(o + 1)
							jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' +currentPosition  + ')').data("order", parseInt(o + 1));
							jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition+ ')').attr("data-order", parseInt(o + 1));
							const objUpdated = {
								order: parseInt(o + 1),
								id: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').attr("data-id"),
								url: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').attr("data-url"),
								caption: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("caption"),
								captionBackground: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("caption-background"),
								slideBackground: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("slide-background"),
								title: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("title-caption"),
								readMoreTxt: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("txt-btn-read-more-caption"),
								readMoreUrl: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child(' + currentPosition + ')').data("url-btn-read-more-caption")
							};
							imagesSlideUpdatedData.push(objUpdated);
						});
						$('textarea#slider_touchable_object').val(JSON.stringify(imagesSlideUpdatedData));
						imagesDataSavedOnLoad = imagesSlideUpdatedData;
					});
				});
			});

			function editCaption(e) {
				try {
					
					tempJson = JSON.parse(jQuery('textarea#slider_touchable_object').val());
					jsonPosition = parseInt(jQuery(e.parentNode).data('order'))-1;
					if (tempJson[jsonPosition]?.caption !== null) {
						jQuery('#txt_caption').val(tempJson[jsonPosition].caption)
					} else {
						jQuery('#txt_caption').empty();
					}
					if (tempJson[jsonPosition]?.readMoreTxt !== null) {
						jQuery('#txt_read_more').val(tempJson[jsonPosition].readMoreTxt);
					} else {
						jQuery('#txt_read_more').empty();
					}
					if (tempJson[jsonPosition]?.readMoreUrl !== null) {
						jQuery('#txt_read_more_url').val(tempJson[jsonPosition].readMoreUrl);
					} else {
						jQuery('#txt_read_more_url').empty();
					}
					if (tempJson[jsonPosition]?.title !== null) {
						jQuery('#txt_title').val(tempJson[jsonPosition].title)
					} else {
						jQuery('#txt_title').empty();
					}
					if (tempJson[jsonPosition]?.captionBackground !== null) {
						jQuery('#caption_bg_color_picker').val(tempJson[jsonPosition].captionBackground);
						jQuery('#caption-bg-container .wp-color-result').css('background', tempJson[jsonPosition].captionBackground);
					} else {
						jQuery('#caption_bg_color_picker').empty();
					}
					if (tempJson[jsonPosition]?.slideBackground !== null) {
						jQuery('#slide_bg_color_picker').val(tempJson[jsonPosition].slideBackground);
						jQuery('#slide-bg-container .wp-color-result').css('background', tempJson[jsonPosition].slideBackground);
					} else {
						jQuery('#slide_bg_color_picker').empty();
					}
					jQuery('#modal_caption').toggleClass('opened');
				} catch (error) {
					console.log('An error occurs');
					console.error(error);
				}
			}
		</script>
<?php
	}
	public function isJson($string)
	{
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	/**
	 * This functions save the data inside our slider_touchable post_type
	 * @link https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
	 * @see https://developer.wordpress.org/reference/hooks/save_post/
	 **/
	public function wp_save_slider_touchable_meta_box($post_id, $post, $update)
	{
		if (
			!isset($_POST["slider_touchable-nonce"]) ||
			!wp_verify_nonce($_POST["slider_touchable-nonce"], basename(__FILE__))
		) {
			return $post_id;
		}
		if (!current_user_can("edit_post", $post_id)) {
			return $post_id;
		}
		if (defined("DOING_AUTOSAVE") && DOING_AUTOSAVE) {
			return $post_id;
		}
		$slug = "slider_touchable";
		if ($slug != $post->post_type) {
			return $post_id;
		}
		$meta_box_captions_galleries_html_value = "";
		if (isset($_POST["slider_touchable_object"])) {
			$meta_box_captions_galleries_html_value = $_POST["slider_touchable_object"];
		}
		update_post_meta($post_id, "slider_touchable_object", $meta_box_captions_galleries_html_value);
		if (isset($_POST["slider_touchable_object_settings"])) {
			$meta_box_captions_galleries_html_value = $_POST["slider_touchable_object_settings"];
		}
		update_post_meta($post_id, "slider_touchable_object_settings", $meta_box_captions_galleries_html_value);
	}
	/**
	 * This function Fires after all built-in meta boxes have been added
	 * @see https://developer.wordpress.org/reference/hooks/add_meta_boxes/
	 **/
	public function add_wp_slider_touchable_meta_box()
	{
		add_meta_box("wp_slider_touchable", "Slider Touchable", array($this, "custom_meta_box_wp_slider_touchable"), "slider_touchable", "normal", "high", null);
	}
	/**
	 * @package wp_slider_touchable
	 * @since 1.0.0 
	 * @version 1.0.0
	 * This function return the total of images saved in the media library 
	 * @return      integer 			=> The total of images saved 
	 * @param       string           => The post_type 
	 **/
	public function countImagesSavedOnDatabase($postType = 'attachment')
	{
		global $wpdb;
		$sqlCountPosts = "SELECT COUNT(id) AS totalImages FROM $wpdb->posts WHERE post_type='" . $postType . "' AND post_status='inherit' AND post_mime_type LIKE '%image%'";
		$sqlImagesSaved = intval($wpdb->get_var($sqlCountPosts));
		return $sqlImagesSaved;
	}
	/**
	 * This function register a post type called wp-slider-touchable
	 * @package wp_slider_touchable
	 * @since wp_slider_touchable 1.0.0
	 **/
	public function wp_slider_touchable_init()
	{
		/**
		 * Adds or overwrites a taxonomy. It takes in a name, an object name that it affects, and an array of parameters. It does not return anything.
		 * @see  https://codex.wordpress.org/Function_Reference/register_taxonomy
		 **/
		$labelsTaxnomy = array(
			'name'               => __('Groups', 'wp_wp_slider_touchable'),
			'singular_name'      => __('Group', 'wp_slider_touchable'),
			'menu_name'          => __('Groups', 'wp_slider_touchable'),
			'all_items'          => __('All Groups', 'wp_slider_touchable'),
			'edit_item'          => __('Edit Group', 'wp_slider_touchable'),
			'view_item'          => __('View Group', 'wp_slider_touchable'),
			'update_item'        => __('Update Group', 'wp_slider_touchable'),
			'add_new_item'       => __('Add New Group', 'wp_slider_touchable'),
			'new_item_name'      => __('New Group Name', 'wp_slider_touchable'),
			'parent_item'        => __('Parent Group', 'wp_slider_touchable'),
			'parent_item_colon'  => __('Parent Group:', 'wp_slider_touchable'),
			'search_items'       => __('Search Tags', 'wp_slider_touchable'),
			'popular_items'      => __('Popular Groups', 'wp_slider_touchable'),
			'separate_items_with_commas' => __('Separate Groups with commas', 'wp_slider_touchable'),
			'add_or_remove_items' => __('Add or Remove Groups', 'wp_slider_touchable'),
			'choose_from_most_used' => __('Choose from the most used Groups', 'wp_slider_touchable'),
			'not_found' => __('No Groups Found.', 'wp_slider_touchable')
		);
		$mgnigLighboxGalleryTaxonomyArgs = array(
			'label' => __('Sliders', 'wp_slider_touchable'),
			'labels' => $labelsTaxnomy,
			'public' => false,
			'publicly_queryable' => false,
			'show_ui' => false,
			'show_in_menu' => false,
			'show_in_nav_menus' => false,
			'show_in_rest' => false,
			'show_in_quick_edit' => false,
			'show_admin_column' => false,
			'description' => __('Group or Category for manage Sliders from a shortcode', 'wp_slider_touchable'),
			'hierarchical' => false,
			'query_var' => 'group_slider_touchable',
			'rewrite' => array('slug' => 'group_slider_touchable')
		);
		register_taxonomy('group_slider_touchable', 'slider_touchable', $mgnigLighboxGalleryTaxonomyArgs);
		$labelsSliderPostType = array(
			'name'               => __('Sliders', 'wp_slider_touchable'),
			'singular_name'      => __('Slider', 'wp_slider_touchable'),
			'add_new'            => __('Add New', 'wp_slider_touchable'),
			'add_new_item'       => __('Add New Slider', 'wp_slider_touchable'),
			'edit_item'          => __('Edit Slider', 'wp_slider_touchable'),
			'new_item'           => __('New Slider', 'wp_slider_touchable'),
			'view_item'          => __('View Slider', 'wp_slider_touchable'),
			'search_items'          => __('Search Sliders', 'wp_slider_touchable'),
			'not_found'          => __('No Sliders found.', 'wp_slider_touchable'),
			'not_found_in_trash' => __('No Sliders found in Trash.', 'wp_slider_touchable'),
			'all_items'          => __('All Sliders', 'wp_slider_touchable'),
			'archives' => __('Post Sliders', 'wp_slider_touchable'),
			'insert_into_item'   => __('Your image was inserted', 'wp_slider_touchable'),
			'uploaded_to_this_item' => __('Uploaded to this Slider', 'wp_slider_touchable'),
			'menu_name'          => __('Slider', 'wp_slider_touchable'),
			'filter_items_list' => __('Sliders', 'wp_slider_touchable'),
			'items_list_navigation' => __('Sliders', 'wp_slider_touchable'),
			'items_list' => __('Galleries', 'wp_slider_touchable'),
			'name_admin_bar'     => __('Slider', 'wp_slider_touchable')
		);
		$argsSliderPostType = array(
			'labels'             => $labelsSliderPostType,
			'description'        => __('Slider .', 'wp_slider_touchable'),
			'exclude_from_search' => true,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_nav_menus' => false,
			'menu_position'      => 50,
			'menu_icon'          => 'dashicons-images-alt2',
			'map_meta_cap' => true,
			'query_var'          => true,
			'rewrite'            => array('slug' => 'slider_touchable'),
			'capability_type'    => 'post',
			'hierarchical'       => true,
			'register_meta_box_cb' => array($this, 'wp_slider_touchable_meta_box'),
			'taxonomies' => array('group_slider_touchable'),
			'show_in_rest'       => true,
			'supports'           => array('title'/*, 'thumbnail' ,'custom-fields',  'editor', 'author','excerpt'*/),
			'can_export' => true
		);
		register_post_type('slider_touchable', $argsSliderPostType);
	}
	/**
	 * This function register a new post_type noted that this post_type only works 
	 * inside wp_slider_touchable theme for manage the images inside the Wordpress Admin Panel
	 **/
	public function wp_slider_touchable_flush_rewrite()
	{
		$this->wp_slider_touchable_init();
		/**
		 * First, we "add" the custom post type via the above written function 
		 * if the content exists don't get added to the DB,
		 * this is only done during theme activation hook 
		 * @see https://codex.wordpress.org/Function_Reference/register_post_type
		 **/
		flush_rewrite_rules();
	}
	public function wp_slider_touchable_meta_box()
	{
	}
	/**
	 * This function showing the Column Title for the Shortcode on the Wordpress Admin Panel
	 * when the user clicking the sub menu All Sliders of the post_type slider_touchable
	 * @see wp_slider_touchable_init()
	 * @return String     =>   the Title for the shortcode of the slider_touchable
	 **/
	public function slider_touchable_shortcodes_columns($columns)
	{
		$columns['slider_touchable_shortcode'] = 'Shortcode';
		return $columns;
	}
	
	/**
	 * This function showing the shortcode on the Wordpress Admin Panel
	 * when the user clicking the sub menu All Sliders of the post_type slider_touchable
	 * @see wp_slider_touchable_init()
	 * @return String     =>   the shortcode of the slider_touchable
	 **/
	public function slider_touchable_shortcodes_posts_columns($columns)
	{
		global $post;
		switch ( $columns ) {
			case 'slider_touchable_shortcode':
				echo '[slider_touchable id="' . $post->ID . '"]';
				break;
		}
	}
	public function enqueue_scripts()
	{
		wp_enqueue_media();
		wp_enqueue_style('slider_touchable-admin-css', plugins_url('wp-slider-touchable/assets/css/dev/admin/slider_touchable_main_admin.css'), array(), '1.0.0');
		wp_enqueue_script('wp-color-picker');
		wp_enqueue_style('wp-color-picker');
	}
	public function remove_footer_admin()
	{
		echo ' ';
	}
}

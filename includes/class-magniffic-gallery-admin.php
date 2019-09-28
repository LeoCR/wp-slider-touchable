<?php
class MagnifficGallery_Admin {
	public function __construct( $editor ) {
		$editor->initialize();
	}
	public function initialize() {
		/**
		 * @description  init magniffic gallery
		 */
		add_action('admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_filter('attachment_fields_to_edit', array($this,'wp_magniffic_gallery_caption_field'), 10, 2 );
		add_filter('attachment_fields_to_save', array($this,'wp_magniffic_gallery_caption_field_save'), 10, 2 );
		add_action('save_post',array($this, "wp_save_magniffic_gallery_meta_box"), 10, 3);
		add_action('add_meta_boxes', array($this,"add_wp_magniffic_gallery_meta_box"));
		add_action('init', array($this,'wp_magniffic_gallery_init') );
		register_activation_hook( __FILE__,array($this,'wp_magniffic_gallery_flush_rewrite') ); 
		add_filter('manage_edit-magniffic_gallery_columns',array($this,'magniffic_gallery_shortcodes_columns') );
		add_filter('manage_magniffic_gallery_posts_custom_column',array($this,'magniffic_gallery_shortcodes_posts_columns' ));
		add_filter('admin_footer_text', array($this,'remove_footer_admin'));
	}
	/**
	 * Add the magniffic_gallery Caption to media uploader
	 * @param $form_fields array, fields to include in attachment form
	 * @param $post object, attachment record in database
	 * @return $form_fields, modified form fields
	 */	
	public function wp_magniffic_gallery_caption_field( $form_fields, $post ) {     
		$form_fields['magniffic_gallery_caption_content'] = array(
			'label' => 'Gallery Caption', 
			'input' => 'textarea',
			'value' => esc_html(get_post_meta( $post->ID, 'magniffic_gallery_caption', true )),
			'helps' => 'If provided, caption description text on the slider will be displayed',
		); 
		return $form_fields;
	}
	/**
	 * Save values of the Slider Caption Text in media uploader
	 * @param $post array, the post data for database
	 * @param $attachment array, attachment fields from $_POST form
	 * @return $post array, modified post data
	 */
	public function wp_magniffic_gallery_caption_field_save( $post, $attachment ) {
		if( isset( $attachment['magniffic_gallery_caption_content'] ) ){
			update_post_meta( $post['ID'], 'magniffic_gallery_caption', $attachment['magniffic_gallery_caption_content'] );
		}  
		return $post;
	}
	/**
	* This function Adds a meta box for put our custom fields of our magniffic_gallery .
	* @link https://developer.wordpress.org/reference/functions/add_meta_box/
	**/
	public function custom_meta_box_wp_magniffic_gallery(){ 
		wp_nonce_field(basename(__FILE__), "magniffic_gallery-nonce"); 
		global $post; 
		$hasImages=$this->countImagesSavedOnDatabase();//check if we have images saved on the database 
		$magnifficLighboxGalleryID= $post->ID;
		$magnifficLighboxGalleryPostName= $post->post_name;
		?> 
		<div class="attachments-browser hide-sidebar sidebar-for-errors wp_magniffic_gallery_container row">
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
						<input type="button" value="Upload Images" id="btn_select_pictures_magniffic_gallery" class="btn_upload_magniffic_gallery btn btn-danger"/> 
						<?php 
						if($hasImages==0){
							?>
								<input type="button" value="Upload Images" id="btn_upload_magniffic_gallery" class="btn_upload_magniffic_gallery btn btn-danger"/> 
								<input type="button" value="Add Images" id="btn_add_more_pictures_magniffic_gallery" class="btn_add_more_pictures_magniffic_gallery btn btn-primary"/>
						<?php 
						} 
						else{
								?>
								<input type="button" value="Select Images" id="btn_add_more_pictures_magniffic_gallery" class="btn_add_more_pictures_magniffic_gallery btn btn-primary"/>
								<?php
						}
						?>  
						<!--<button type="button" class="button media-button button-primary button-large  delete-selected-button  "  >Delete Selected</button>-->
					</div> 
			</div>
			<div class="well well-sm col-lg-11">
				<p>For using this shortcode copy and paste the next code line inside a page,post or Widget:</p> 
				<strong>
					<?php 
					echo '[magniffic_gallery id="'.$magnifficLighboxGalleryID.'"]'; 
					?> 
				</strong>
			</div>
			<div class="col-lg-11 slider-ui-container">  
					<ul class="nav nav-tabs">
						<li class="active" data-tab="slides">
						<a href="#">Images</a>
						</li> 
						<!-- 
						<li data-tab="settings">
							<a href="#">Settings</a>
						</li> 
						-->
					</ul> 
					<div class="tabs-content">
						<div class="tab-pane active tab-content current active" id="slides"> 
							<div class="col-lg-11 slider-ui-subcontainer"> 
									<div id="filter" style="margin-left: 30px">  
										<div class="block__list block__list_words img_slider_touch">
											<ul id="sortable_slides_imgs"> 
												<?php 
												/**
												* The variable $magniffic_galleryImagesJsonObject save the images inside a JSON Object
												**/
												$magniffic_galleryImagesJsonObject=''; 
												$magniffic_galleryImagesArgs = array(
													'post_type'=> 'magniffic_gallery',
													'p'=>$magnifficLighboxGalleryID,
													'pagename'=>$magnifficLighboxGalleryPostName,
													'post_status'=> 'publish',
													'posts_per_page'=> -1,
												); 
												/**
												* The variable $query_images get all the images with the same id of the current magniffic_gallery post
												* @see $magnifficLighboxGalleryID
												**/
												$query_images = new WP_Query( $magniffic_galleryImagesArgs );  
												foreach ( $query_images->posts as $image ) { 
													/**
													* We just have one unique magniffic_gallery_object or an
													* empty object
													**/
													if(get_post_meta($image->ID, "magniffic_gallery_object", true )!==''){
														$magniffic_galleryImagesJsonObject.=get_post_meta($image->ID, "magniffic_gallery_object", true ); 
													}
												} 
												?>
												<script type="text/javascript" id="magniffic_galleryObject">
													<?php
													$tempJson="";
													if($this->isJson($magniffic_galleryImagesJsonObject)){
														$tempJson='var magniffic_galleryImagesJsonObject ='.$magniffic_galleryImagesJsonObject.';';
													}
													else{
														$tempJson='var magniffic_galleryImagesJsonObject =[];';
													}
													echo $tempJson;
													?>
													(function(){  
														jQuery(document).ready(function($){
															var $imagesMagnifficGallery='';
															for (var counter = 0; counter < magniffic_galleryImagesJsonObject.length; counter++) {
																$imagesMagnifficGallery+='<li class="ui-state-default col-sm-2" data-order="'+parseInt(counter+1) +'" data-id="'+magniffic_galleryImagesJsonObject[counter].id+'" '+
																'" data-url="'+magniffic_galleryImagesJsonObject[counter].url+'" > '+
																'<span class="btn-icon-remove"></span>'+
																'<span class="btn-icon-move"></span>'+
																'<span class="btn-icon-edit"></span>'+
																'<p><img src="'+magniffic_galleryImagesJsonObject[counter].url+'" '+
																' class="image_slider_touchable" />'+
																'</p> '+
																'</li>';  
															}
															$('ul#sortable_slides_imgs').html($imagesMagnifficGallery);
															$('textarea#magniffic_gallery_object').text(JSON.stringify(magniffic_galleryImagesJsonObject)); 
																jQuery( "#sortable_slides_imgs" ).sortable({ 
																	update: function(event, ui) { 
																		var imagesSlideUpdatedData = [];
																		jQuery("ul#sortable_slides_imgs li.ui-state-default.col-sm-2").each(function(o){
																				jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child('+parseInt(o+1)+')').data("order",parseInt(o+1)); 
																				jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child('+parseInt(o+1)+')').attr("data-order",parseInt(o+1)); 
																				var objUpdated = { 
																						order:parseInt(o+1),
																						id: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child('+parseInt(o+1)+')').attr("data-id"),
																						url: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child('+parseInt(o+1)+')').attr("data-url")
																					};
																					imagesSlideUpdatedData.push(objUpdated); 
																		});
																		$('textarea#magniffic_gallery_object').val(JSON.stringify(imagesSlideUpdatedData));
																		console.log(JSON.stringify(imagesSlideUpdatedData));
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
								<textarea id="magniffic_gallery_object" name="magniffic_gallery_object" class="magniffic_gallery_object"></textarea>
							</div>
						</div>
						<!--
						<div class="tab-pane tab-content " id="settings">
							<h1>General Settings</h1>
							<textarea id="magniffic_gallery_object_settings" name="magniffic_gallery_object_settings" class="magniffic_gallery_object_settings"></textarea>
						</div> 
						-->
					</div>
			</div> 
		</div>  
		<div class="modal" tabindex="-1" role="dialog" id="modal_caption">
			<div class="modal-dialog" role="document">
				<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title">Caption</h5>
					<button type="button" class="btn-close close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
					</button>
				</div>
				<div class="modal-body">
					<p>Insert the Caption here:</p>
					<textarea name="txt_caption" id="txt_caption" cols="30" rows="10"></textarea>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-primary" id="btn-save">Save changes</button>
					<button type="button" class="btn btn-secondary btn-close" data-dismiss="modal">Close</button>
				</div>
				</div>
			</div>
		</div>
		<script>
				var jsonPosition;
				var tempJson;
				jQuery( function() { 
						jQuery(document).ready(function($){
							var imagesDataSavedOnLoad = [];
							$('ul#sortable_slides_imgs li.ui-state-default.col-sm-2').each(function(index){
								var objSliderImages = { 
									order:$(this).data('order'),
									id: $(this).data('id'),
									url: $(this).data('url')
								};
								imagesDataSavedOnLoad.push(objSliderImages); 
							}); 
							var mediaUploader;//initialize mediaUploader 
							/**
							* Function for manage the tabs on the magniffic_gallery post_type
							**/
							$('ul.nav.nav-tabs li').click(function(){
								var tab_id = $(this).attr('data-tab'); 
								$('ul.nav.nav-tabs li').removeClass('active current');  
								$(this).addClass('active current');
								$('.tab-content').removeClass('active current');
								$('#'+tab_id).addClass('active current');   
							});  
							/**
							* Function for select images on the magniffic_gallery post_type
							**/
							$('#btn_upload_magniffic_gallery').on('click',function(e){
									e.preventDefault();
									if(mediaUploader){
										mediaUploader.open();
										return;
									}  
									mediaUploader= wp.media.frames.file_frame=wp.media({
											title:'Alls your Pictures',
											button:{
												text:'Upload your Pictures'
											}, 
											multiple:true, 
											library: {
												order: 'id', 
												orderby: 'title', 
												type: 'image', 
												uploadedTo: 1
											}
									});
									mediaUploader.on('select',function(){
										attachment=mediaUploader.state().get('selection').toJSON(); 
										var imagesData = [],htmlImagesInserted='';
										for (var index = 0; index < attachment.length; index++) {
											var obj = { 
												order:index,
												id: attachment[index].id,
												url: attachment[index].url
											};
											imagesData.push(obj); 
											htmlImagesInserted+='<li class="ui-state-default col-sm-2" '+
											'data-order="'+parseInt(index+1)+'" >'+
											'<span class="btn-icon-remove"></span>'+
											'<span class="btn-icon-move"></span>'+
											'<span class="btn-icon-edit" onclick="editCaption(this);"></span>'+
											'<p><img src="'+attachment[index].url+'" '+
											' class="image_slider_touchable" />'+
											'</p> '+
											'</li>'; 
										}
										$('ul#sortable_slides_imgs').html(htmlImagesInserted);//console.log(attachment);
										$('textarea#magniffic_gallery_object').text(JSON.stringify(imagesData));
										imagesDataSavedOnLoad=imagesData;
									});
									mediaUploader.open();
							});
							/**
							* Function for add more images into slider without delete the old images saved
							**/
							$('#btn_add_more_pictures_magniffic_gallery').on('click',function(e){
								e.preventDefault();
									if(mediaUploader){
										mediaUploader.open();
										return;
									}
									mediaUploader= wp.media.frames.file_frame=wp.media({
											title:'Adding Pictures',
											button:{
												text:'Adding your Pictures'
											},
											library: {
												order: 'id', 
												orderby: 'title', 
												type: 'image', 
												uploadedTo: null
											},
											multiple:true
									}); 
									mediaUploader.on('select',function(){ 
										attachment=mediaUploader.state().get('selection').toJSON(); 
										var imagesData = imagesDataSavedOnLoad,
										htmlImagesInserted='';
										for (var index = 0; index < attachment.length; index++) {
											var obj = { 
												order:index,
												id: attachment[index].id,
												url: attachment[index].url
											};
											imagesData.push(obj); 
											htmlImagesInserted+='<li class="ui-state-default col-sm-2" '+
											'data-order="'+parseInt(index+1)+'" >'+
											'<span class="btn-icon-remove"></span>'+
											'<span class="btn-icon-move"></span>'+
											'<span class="btn-icon-edit" onclick="editCaption(this);"></span>'+
											'<p><img src="'+attachment[index].url+'" '+
											' class="image_slider_touchable" />'+
											'</p> '+
											'</li>'; 
										}//console.log(imagesData);console.log(attachment);
										imagesDataSavedOnLoad=imagesData;
										$('ul#sortable_slides_imgs').append(htmlImagesInserted);
										$('textarea#magniffic_gallery_object').text(JSON.stringify(imagesData));
									});
									mediaUploader.open(); 
							});
							/**
							* Function for select the images for the slider
							**/
							$('#btn_select_pictures_magniffic_gallery').on('click',function(e){
									e.preventDefault();
									if(mediaUploader){
										mediaUploader.open();
										return;
									}
									mediaUploader= wp.media.frames.file_frame=wp.media({
											title:'Select Pictures',
											button:{
												text:'Select your Pictures'
											},
											library: {
												order: 'id', 
												orderby: 'title', 
												type: 'image', 
												uploadedTo: null
											},
											multiple:true
									}); 
									mediaUploader.on('select',function(){ 
										attachment=mediaUploader.state().get('selection').toJSON(); 
										var imagesData = [],htmlImagesInserted='';
										for (var index = 0; index < attachment.length; index++) {
											var obj = { 
												order:index,
												id: attachment[index].id,
												url: attachment[index].url
											};
											imagesData.push(obj); 
											htmlImagesInserted+='<li class="ui-state-default col-sm-2" '+
											'data-order="'+parseInt(index+1)+'" >'+
											'<span class="btn-icon-remove"></span>'+
											'<span class="btn-icon-move"></span>'+
											'<span class="btn-icon-edit" onclick="editCaption(this);"></span>'+
											'<p><img src="'+attachment[index].url+'" '+
											' class="image_slider_touchable" />'+
											'</p> '+
											'</li>'; 
										}//console.log(imagesData);console.log(attachment);
										imagesDataSavedOnLoad=imagesData;
										$('ul#sortable_slides_imgs').html(htmlImagesInserted);
										$('textarea#magniffic_gallery_object').text(JSON.stringify(imagesData));
									});
									mediaUploader.open();
							});  
							/**
							* Function for open bootstrap modal on the magniffic_gallery post_type
							**/
							try {
								tempJson=JSON.parse($('textarea#magniffic_gallery_object').val());
							} catch (error) {
								console.log('An error occurs');
								console.error(error);
								
							}
							$('.btn-icon-edit').on('click',function(){
								
								jsonPosition=parseInt($(this).parent('.ui-state-default').data('order'))-1;
								if(tempJson[jsonPosition].caption!==null){//console.log('tempJson[jsonPosition].caption!==null');
									$('#txt_caption').val(tempJson[jsonPosition].caption)
								}
								else{
									$('#txt_caption').empty();//console.log('tempJson[jsonPosition].caption===null');
								}
								//console.log(jsonPosition);console.log(tempJson[jsonPosition]);Object.assign(target, source);
								$('#modal_caption').toggleClass('opened');
							});
							$('#btn-save').on('click',function(){
								var tempCaption=$('#txt_caption').val();
								Object.assign(tempJson[jsonPosition], {caption:tempCaption});
								//console.log('tempJson');console.log(tempJson);
								$('textarea#magniffic_gallery_object').val(JSON.stringify(tempJson));
								$('#modal_caption').toggleClass('opened');
							})
							$('.btn-close').on('click',function(){
								$('#modal_caption').toggleClass('opened');
							});
							/**
							* Function for delete images of the slider
							**/
							$('.btn-icon-remove').on('click',function(){
									$(this).parent('li.col-sm-2').remove();
									$( "#sortable_slides_imgs" ).sortable( "refreshPositions" );
									var imagesSlideUpdatedData = [];
									jQuery("ul#sortable_slides_imgs li.ui-state-default.col-sm-2").each(function(o){
											jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child('+parseInt(o+1)+')').data("order",parseInt(o+1)); 
											jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child('+parseInt(o+1)+')').attr("data-order",parseInt(o+1)); 
												var objUpdated = { 
													order:parseInt(o+1),
													id: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child('+parseInt(o+1)+')').attr("data-id"),
													url: jQuery('ul#sortable_slides_imgs li.ui-state-default.col-sm-2:nth-child('+parseInt(o+1)+')').attr("data-url")
												};
												imagesSlideUpdatedData.push(objUpdated); 
									});
									$('textarea#magniffic_gallery_object').val(JSON.stringify(imagesSlideUpdatedData));
									imagesDataSavedOnLoad=imagesSlideUpdatedData;
									console.log(JSON.stringify(imagesSlideUpdatedData));
							});
							/**
							* Function for close bootstrap modal on the magniffic_gallery post_type
							**/
							$('.btn-font-close , .btn-close-modal , .btn-success-modal').on('click',function(){
									$('#qiuty_modal_admin_panel').css('display','none');
									console.log('Close Modal');
							}); 
						});
				});
				function editCaption(e){
					console.log('editCaption');
					console.log(e);
					try {
						tempJson=JSON.parse($('textarea#magniffic_gallery_object').val());
						jsonPosition=parseInt($(e.parentNode).data('order'))-1;
						if(tempJson[jsonPosition].caption!==null){//console.log('tempJson[jsonPosition].caption!==null');
							$('#txt_caption').val(tempJson[jsonPosition].caption)
						}
						else{
							$('#txt_caption').empty();//console.log('tempJson[jsonPosition].caption===null');
						}
						//console.log(jsonPosition);console.log(tempJson[jsonPosition]);Object.assign(target, source);
						$('#modal_caption').toggleClass('opened');
					} catch (error) {
						console.log('An error occurs');
						console.error(error);
						
					}
					console.log('editCaption');
					
				}
		</script>
		<?php 
	}
	public function isJson($string) {
		json_decode($string);
		return (json_last_error() == JSON_ERROR_NONE);
	}
	/**
	* This functions save the data inside our magniffic_gallery post_type
	* @link https://codex.wordpress.org/Plugin_API/Action_Reference/save_post
	* @see https://developer.wordpress.org/reference/hooks/save_post/
	**/
	public function wp_save_magniffic_gallery_meta_box($post_id, $post, $update){
		if (!isset($_POST["magniffic_gallery-nonce"]) || 
			!wp_verify_nonce($_POST["magniffic_gallery-nonce"], basename(__FILE__))){
			return $post_id;
		}
		if(!current_user_can("edit_post", $post_id)){
			return $post_id;
		}
		if(defined("DOING_AUTOSAVE") && DOING_AUTOSAVE){
			return $post_id;
		}
		$slug = "magniffic_gallery";
		if($slug != $post->post_type){
			return $post_id;
		}
		$meta_box_captions_galleries_html_value = "";
		if(isset($_POST["magniffic_gallery_object"])) {
			$meta_box_captions_galleries_html_value = $_POST["magniffic_gallery_object"];
		}   
		update_post_meta($post_id, "magniffic_gallery_object", $meta_box_captions_galleries_html_value);
		if(isset($_POST["magniffic_gallery_object_settings"])) {
			$meta_box_captions_galleries_html_value = $_POST["magniffic_gallery_object_settings"];
		}   
		update_post_meta($post_id, "magniffic_gallery_object_settings", $meta_box_captions_galleries_html_value);
	} 
	/**
	* This function Fires after all built-in meta boxes have been added
	* @see https://developer.wordpress.org/reference/hooks/add_meta_boxes/
	**/
	public function add_wp_magniffic_gallery_meta_box(){
		add_meta_box("wp_magniffic_gallery", "Qiuity Slider", array($this,"custom_meta_box_wp_magniffic_gallery"), "magniffic_gallery", "normal", "high", null);
	} 
	/**
	* @package wp_magniffic_gallery
	* @since 1.0.0 
	* @version 1.0.0
	* This function return the total of images saved in the media library 
	* @return      integer 			=> The total of images saved 
	* @param       string           => The post_type 
	**/
	public function countImagesSavedOnDatabase($postType='attachment' ){
		global $wpdb; 
		$sqlCountPosts="SELECT COUNT(id) AS totalImages FROM $wpdb->posts WHERE post_type='".$postType."' AND post_status='inherit' AND post_mime_type LIKE '%image%'";   
		//$sql_sections = $wpdb->get_results("SELECT wp_terms.name FROM wp_term_taxonomy LEFT JOIN wp_terms ON wp_term_taxonomy.term_id=wp_terms.term_id  WHERE wp_term_taxonomy.taxonomy='Section' AND wp_term_taxonomy.COUNT != 0 ORDER BY wp_terms.name ASC    ");
		$sqlImagesSaved=intval($wpdb->get_var($sqlCountPosts)); 
		return $sqlImagesSaved; 
	}
	/**
	* This function register a post type called qiuty_wp-magniffic-gallery
	* @package wp_magniffic_gallery
	* @since wp_magniffic_gallery 1.0.0
	**/
	public function wp_magniffic_gallery_init() {
		/**
		* Adds or overwrites a taxonomy. It takes in a name, an object name that it affects, and an array of parameters. It does not return anything.
		* @see  https://codex.wordpress.org/Function_Reference/register_taxonomy
		**/
		$labelsTaxnomy=array(
				'name'               => __( 'Groups', 'wp_wp_magniffic_gallery' ),
				'singular_name'      => __( 'Group','wp_magniffic_gallery' ),
				'menu_name'          => __( 'Groups', 'wp_magniffic_gallery' ), 
				'all_items'          => __( 'All Groups','wp_magniffic_gallery' ),
				'edit_item'          => __( 'Edit Group', 'wp_magniffic_gallery' ),
				'view_item'          => __( 'View Group', 'wp_magniffic_gallery' ),
				'update_item'        => __( 'Update Group', 'wp_magniffic_gallery' ),
				'add_new_item'       => __( 'Add New Group', 'wp_magniffic_gallery' ),
				'new_item_name'      => __( 'New Group Name', 'wp_magniffic_gallery' ),
				'parent_item'        => __( 'Parent Group', 'wp_magniffic_gallery' ),
				'parent_item_colon'  => __( 'Parent Group:', 'wp_magniffic_gallery' ),
				'search_items'       => __( 'Search Tags', 'wp_magniffic_gallery'),
				'popular_items'      =>__( 'Popular Groups', 'wp_magniffic_gallery'),
				'separate_items_with_commas'=>__( 'Separate Groups with commas', 'wp_magniffic_gallery'),
				'add_or_remove_items'=>__( 'Add or Remove Groups', 'wp_magniffic_gallery'),
				'choose_from_most_used'=>__('Choose from the most used Groups','wp_magniffic_gallery'),
				'not_found'=>__('No Groups Found.','wp_magniffic_gallery')
		);
		$mgnigLighboxGalleryTaxonomyArgs=array(
				'label'=>__( 'Galleries', 'wp_magniffic_gallery' ),
				'labels'=>$labelsTaxnomy,
				'public'=>false,
				'publicly_queryable'=>false,
				'show_ui'=>false,
				'show_in_menu'=>false,
				'show_in_nav_menus'=>false,
				'show_in_rest'=>false,
				'show_in_quick_edit'=>false,
				'show_admin_column'=>false,
				'description'=>__('Group or Category for manage Sliders from a shortcode','wp_magniffic_gallery'),
				'hierarchical'=>false,
				'query_var'=>'group_magniffic_gallery',
				'rewrite'=>array('slug'=>'group_magniffic_gallery') 
		);
		register_taxonomy( 'group_magniffic_gallery', 'magniffic_gallery', $mgnigLighboxGalleryTaxonomyArgs );
		$labelsSliderPostType = array(
			'name'               => __( 'Galleries', 'wp_magniffic_gallery' ),
			'singular_name'      => __( 'Gallery','wp_magniffic_gallery' ),
			'add_new'            => __( 'Add New', 'wp_magniffic_gallery' ),
			'add_new_item'       => __( 'Add New Gallery', 'wp_magniffic_gallery' ),
			'edit_item'          => __( 'Edit Gallery', 'wp_magniffic_gallery' ),
			'new_item'           => __( 'New Gallery', 'wp_magniffic_gallery' ),
			'view_item'          => __( 'View Gallery', 'wp_magniffic_gallery' ),
			//'view_items'          => __( 'View Galleries', 'wp_magniffic_gallery' ),
			'search_items'          => __( 'Search Galleries', 'wp_magniffic_gallery' ),
			'not_found'          => __( 'No Galleries found.', 'wp_magniffic_gallery' ),
			'not_found_in_trash' => __( 'No Galleries found in Trash.', 'wp_magniffic_gallery' ), 
			'all_items'          => __( 'All Galleries', 'wp_magniffic_gallery' ),
			'archives' => __( 'Post Galleries', 'wp_magniffic_gallery' ),
			//'attributes'=> __( 'Post Galleries', 'wp_magniffic_gallery' ),
			'insert_into_item'   => __( 'Your image was inserted', 'wp_magniffic_gallery' ),
			'uploaded_to_this_item'=> __( 'Uploaded to this Gallery', 'wp_magniffic_gallery' ), 
			'menu_name'          => __( 'Gallery', 'wp_magniffic_gallery' ), 
			'filter_items_list' => __( 'Galleries', 'wp_magniffic_gallery' ), 
			'items_list_navigation' => __( 'Galleries', 'wp_magniffic_gallery' ), 
			'items_list'=> __( 'Galleries', 'wp_magniffic_gallery' ),  
			'name_admin_bar'     => __( 'Gallery', 'wp_magniffic_gallery' ) 
		);
		$argsSliderPostType = array(
			'labels'             => $labelsSliderPostType,
			'description'        => __( 'Gallery .', 'wp_magniffic_gallery' ),
			'exclude_from_search'=> true,
			'public'             => false,
			'publicly_queryable' => false,
			'show_ui'            => true,
			'show_in_nav_menus'=>false,
			//'show_in_menu'       => true,
			//'show_in_admin_bar'       => true,
			'menu_position'      => 50,
			'menu_icon'          =>'dashicons-format-image',
			'map_meta_cap'=>true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => 'magniffic_gallery' ),
			'capability_type'    => 'post',
			//'has_archive'        => true,
			'hierarchical'       => true,
			'register_meta_box_cb'=>array($this,'wp_magniffic_gallery_meta_box'),
			'taxonomies'=>array('group_magniffic_gallery'),
			'show_in_rest'       =>true,
			'supports'           => array( 'title'/*, 'thumbnail' ,'custom-fields',  'editor', 'author','excerpt'*/ ),
			'can_export'=>true
		);
		register_post_type( 'magniffic_gallery', $argsSliderPostType );
	}
	/**
	* This function register a new post_type noted that this post_type only works 
	* inside wp_magniffic_gallery theme for manage the images inside the Wordpress Admin Panel
	**/
	public function wp_magniffic_gallery_flush_rewrite() {
		wp_magniffic_gallery_init();
		/**
		* First, we "add" the custom post type via the above written function 
		* if the content exists don't get added to the DB,
		* this is only done during theme activation hook 
		* @see https://codex.wordpress.org/Function_Reference/register_post_type
		**/  
		flush_rewrite_rules();
	} 
	public function wp_magniffic_gallery_meta_box(){ 
	}
	/**
	* This function showing the Column Title for the Shortcode on the Wordpress Admin Panel
	* when the user clicking the sub menu All Sliders of the post_type magniffic_gallery
	* @see wp_magniffic_gallery_init()
	* @return String     =>   the Title for the shortcode of the magniffic_gallery
	**/ 
	public function magniffic_gallery_shortcodes_columns( $columns){
		$columns['magniffic_gallery_shortcode'] = 'Shortcode';
		return $columns;
	}
	/**
	* This function showing the shortcode on the Wordpress Admin Panel
	* when the user clicking the sub menu All Sliders of the post_type magniffic_gallery
	* @see wp_magniffic_gallery_init()
	* @return String     =>   the shortcode of the magniffic_gallery
	**/ 
	public function magniffic_gallery_shortcodes_posts_columns(){
		global $post;
		echo '[magniffic_gallery id="'.$post->ID.'"]'; 
	}	
	public function enqueue_scripts() {
		wp_enqueue_media();
		wp_enqueue_style('magniffic_gallery-admin-css', plugins_url( 'magniffic-gallery/assets/css/dev/admin/magniffic_gallery_main_admin.css'), array(), '1.0.0');
		//wp_enqueue_script('magniffic_gallery-admin-js', plugins_url( 'magniffic-gallery/assets/js/dev/admin/qiuity-admin-dashboard.min.js'),array('jquery'),'1.0.0') ;
	}	
	public function remove_footer_admin () {
		echo ' ';
	}		
}


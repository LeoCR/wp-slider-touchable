(function( $ ) {
	$(function() {
		var $navHoverSelector="ul.nav_menu_header_top.menu-header-top > li:hover > a:hover"+
		" , ul.nav_menu_header_top.menu-header-top > li:hover > ul.sub-menu li > a:hover "+
		" , header#header_top ul.nav_menu_header_top > li > a:hover, "+
		" ul.nav_menu_header_top.menu-header-top li ul.sub-menu li a:hover",
		$navSelector="ul.nav_menu_header_top.menu-header-top li > a ,"+
        " ul.nav_menu_header_top.menu-header-top > li > ul.sub-menu li > a , "+ 
        " ul.nav_menu_header_top.menu-header-top > li > ul.sub-menu li > li > ul> a , "+
        " ul.nav_menu_header_top.menu-header-top > li > ul.sub-menu li > li > ul > li > a ",
        $newCuteCSS='';

		wp.customize( 'blogname', function( value ) {
			value.bind( function( to ) {
				$('h1.qiuty-furniture-title-name a.qiuty_website-name-link').text( to );
			});
		});
		wp.customize( 'blogdescription', function( value ) {
			value.bind( function( to ) {
				$('h2.qiuty-furniture-subtitle-description a.qiuty_website-description-link').text( to );
			});
		});
		
		wp.customize( 'head_nav_hov_col', function( value ) {
			value.bind( function( to ) {  
				$newCuteCSS=$('#sld_touch_footer').text();
				$newCuteCSS+=$navHoverSelector+"{color:"+to+"!important; }";
				$('#sld_touch_footer').text($newCuteCSS);
				console.log(to);
			});
		});
		
		wp.customize( 'head_nav_color', function( value ) {
			value.bind( function( to ) {
				$newCuteCSS=$('#sld_touch_footer').text();
				$newCuteCSS+=$navSelector+"{color:"+to+"!important; }";
				$('#sld_touch_footer').text($newCuteCSS);
				console.log(to);
			});
		});
		wp.customize( 'sld_touch_display_header_logo', function( value ) {
			value.bind( function( to ) {
				false === to ? $('#header').hide() : $('#header').show();
			});
		});
/*
		wp.customize( 'qiuty-furniture_footer_message', function( value ) {
			value.bind( function( to ) {
				$('#footer').text( to );
			});
		});
		
		wp.customize( 'qiuty-furniture_display_footer_title', function( value ) {
			value.bind( function( to ) {
				'never' === to ? $('#footer-title').hide() : $('#footer-title').show();
			});
		});
		
		wp.customize( 'qiuty-furniture_background_image', function( value ) {
			value.bind( function( to ) {
				$('body').css( 'background-image', 'url(' + to + ')' );
			});
		});
		
		wp.customize( 'qiuty-furniture_demo_file', function( value ) {
			value.bind( function( to ) {
				console.log( '' === to );
				'' === to ? $('#sample-file').hide() : $('#sample-file').show();		
			});
		});*/
	
	});
}( jQuery ));
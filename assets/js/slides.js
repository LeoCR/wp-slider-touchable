(() => {
    if(slider_object.settings.settings.hasAutoplay){
        var tempAutoPlayDelay=slider_object.settings.settings.autoplay.duration;
        var swiperSliderHome = new Swiper(slider_object.slider_id, {
            spaceBetween: 30,
            centeredSlides: true,
            loop: true,
            autoplay:{
                delay:tempAutoPlayDelay
            },
            navigation: {
                nextEl: slider_object.slider_id+' .swiper-button-next',
                prevEl: slider_object.slider_id+' .swiper-button-prev',
            },
        });
    }
    else{
        var swiperSliderHome = new Swiper(slider_object.slider_id, {
            spaceBetween: 30,
            centeredSlides: true,
            loop: true,
            navigation: {
                nextEl: slider_object.slider_id+' .swiper-button-next',
                prevEl: slider_object.slider_id+' .swiper-button-prev',
            },
        });
    }
})();
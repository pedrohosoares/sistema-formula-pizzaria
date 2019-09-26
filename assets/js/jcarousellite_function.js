$(function(){
   var noConf = jQuery.noConflict();
   noConf("#banners").jCarouselLite({
	    mouseWheel: true,
        visible: 1,
	    auto:2000,
		speed:2000
    });
});
$(function(){
   var noConf = jQuery.noConflict();
   noConf("#banners-promo").jCarouselLite({
	    mouseWheel: true,
        visible: 10,
	    auto:2000,
		speed:2000
    });
});
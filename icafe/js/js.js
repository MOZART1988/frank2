$(document).ready(function(){
// bx_slider slaidnavigation
  $('.bxslider2').bxSlider({
	mode: "fade",
	speed: 1000,
	auto: true,
	pause: 3000,
	pager: true
  });	
//
			$('#products').slides({
				preload: true,
				effect: 'slide, fade',
				crossfade: true,
				slideSpeed: 200,
				fadeSpeed: 500,
				generateNextPrev: false,
				generatePagination: false
			});
//
});

$(function () {
		$(window).scroll(function () {
			if ($(this).scrollTop() > 100) {
				$('#upBoutton').fadeIn();
			} else {
				$('#upBoutton').fadeOut();
			}
		});
	});
    
function topFunction() {
    $('body,html').animate({
				scrollTop: 0
			}, 800);
			return false;
}
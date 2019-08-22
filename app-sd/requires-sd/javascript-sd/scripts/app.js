$(function(){
	'user strict';

	// OVER HEADER
	$('.over-header[href*=#]:not([href=#])').click(function() {
		if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
			var target = $(this.hash);
			target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
			if (target.length) {
				$('html,body').animate({
					scrollTop: target.offset().top
				}, 1000);
				return false;
			}
		}
	});

	// WINDOW SCROOL EFFECT
	$(window).scroll(function(){
	    if ($(this).scrollTop() > 0) {
	    	$('.over-header').fadeOut(1000);

	    } else {
	       $('.over-header').fadeIn(1000);
	    }
	});

	// STOP DEFAULT DROPDOWN ACTION
	$(document).on('click', '.yamm .dropdown-menu', function(e) {
		e.stopPropagation();
	});

	// $('.s-li-first').click(function(){
	// 	console.log('F Clicks');
	// 	// $(this).siblings('ul').clone().appendTo('.data-show');
	// });

	// $('.s-li-first').dblclick(function(){
	// 	console.log('D Clicks');
	// });

	var count=0;
	$(".s-li-first").click(function() { 
	    count++;
	    if(count==2){
			count=0;
			console.log('2nd Clicks');
			$('.data-show').last('ul').remove();
	    }else{
			console.log('1st Clicks');
			$(this).siblings('ul').clone().appendTo('.data-show');
	    }
	});


});
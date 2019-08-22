$( document ).ready(function() {
	// $('a.page-scroll').bind('click', function (event) {
	//     var $anchor = $(this);
	//     $('html, body').stop().animate({
	//         scrollTop: ($($anchor.attr('href')).offset().top - 65)
	//     }, 300);
	//     event.preventDefault();
	// });
 //    // Highlight the top nav as scrolling occurs
 //    $('body').scrollspy({
 //        target: '.navbar-fixed-top',
 //        offset: 60
 //    });

     // For Sub menu 
    $(document).on("click","#dashboardMenu",function(){
        $("#mySidenav").css("left","0px");
        $("#mySidenav").addClass("open");
    });
    $(document).on("click","#mySidenav .closebtn",function(){
        $("#mySidenav").css("left","-250px");
        $("#mySidenav").removeClass("open");
    });


    // Offset for Main Navigation
    if ($("body").hasClass("homepage_class") == true){
        $('.navbar-default').affix({
            offset: {
                top: 60
            }
        })
    }
    if ($("body").hasClass("customer_home") == true){
        $('.navbar-default').affix({
            offset: {
                top: 60
            }
        })
        $(".newsletter-bg svg g").attr("fill", "#86c777");
    }
    if ($("body").hasClass("freelancer_home") == true){
        $('.navbar-default').affix({
            offset: {
                top: 60
            }
        })
    }

    $('#relatedService').owlCarousel({
        
        margin:30,
        nav:true,
        dots:false,
        navText: ["<span><i class='fa fa-angle-left'></i></span>","<span><i class='fa fa-angle-right'></i></span>"],
        responsive:{
            0:{
                items:1
            },
            480:{
                items:2,
                margin:15
            },
            560:{
                items:2,
                margin:30
            },
            690:{
                items:3,
                margin:15
            },
            768:{
                 margin:30
            },
            1200:{
                items:4
            }
        }
    })

    var resize_post_wrap = function () {
        if($(window).width() >= 641){
            $('.howitwork-items').each(function () {
                var maxheight = 0;
                $(".howitwork-item").height('100%');
                $(".howitwork-item").children().each(function () {
                    maxheight = ($(this).height() > maxheight ? $(this).height() : maxheight);
                });
                $(".howitwork-item").height(maxheight);
            });
        }
        else{
            $('.howitwork-items').each(function () {
                $(".howitwork-item").height('auto');
            });
        }
        if($(window).width() >= 992){
            $('.saved-fl-wrap').each(function () {
                var maxheight = 0;
                $(".pbox").height('100%');
                $(".pbox").each(function () {
                    maxheight = ($(this).height() > maxheight ? $(this).height() : maxheight);
                });
                $(".pbox").height(maxheight);
            });
        } 
        else{
            $('.saved-fl-wrap').each(function () {
                $(".pbox").height('auto');
            });
        }

        if($(window).width() >= 768){
            $('.job-list-wrap').each(function () {
                var maxheight = 0;
                $(".fjob-box").height('100%');
                $(".fjob-box").each(function () {
                    maxheight = ($(this).height() > maxheight ? $(this).height() : maxheight);
                });
                $(".fjob-box").height(maxheight);
            });
        }
        else {
            $('.job-list-wrap').each(function () {
                $(".fjob-box").height('auto');
            });
        }
        if($(window).height() <= 450){
            if($("#mySidenav").hasClass("open")){
                $(".sidenav .closebtn").css("right","-35px");
            }
            else{
                $(".sidenav .closebtn").css("right","0px");
            }
            $("#dashboardMenu").click(function(){
                $(".sidenav .closebtn").css("right","-35px");
            });
            $("#mySidenav .closebtn").click(function(){
                $(".sidenav .closebtn").css("right","0px");
            });
        }
        else{
            $(".sidenav .closebtn").css("right","10px");
            $("#dashboardMenu").click(function(){
                $(".sidenav .closebtn").css("right","10px");
            });
            $("#mySidenav .closebtn").click(function(){
                $(".sidenav .closebtn").css("right","10px");
            });
        }
    };

    $(window).resize(function () {
        resize_post_wrap();
    });

    $(window).on('load', function () {
        resize_post_wrap();
    });
});
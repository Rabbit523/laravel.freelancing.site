$(document).on("click",".ddd",function () {
    var $button = $(this);
    var oldValue = $button.closest('.sp-quantity').find("input.quntity-input").val();
    if ($button.text() == "+") {
        var newVal = parseFloat(oldValue) + 1;
        //console.log(newVal);
    } else {
        if (oldValue > 0) {
            var newVal = parseFloat(oldValue) - 1;
        } else {
            newVal = 0;
        }
    }
    $button.closest('.sp-quantity').find("input.quntity-input").val(newVal);
});


$(document).ready(function(){

    $(".cancel-noti").click(function(){
        $(this).closest("li.noti-item").remove();
    });

    // responsive filter
    $(".filter-menu button").click(function(){
      $(".left-sidebarmenu-responsive").slideToggle();
      $(".seperator").slideToggle();
    });

    $(".filter-menu button").click(function(){
      $("#sidebarMenu").slideToggle();
      $(".seperator").slideToggle();
    });

    // freelancer profile

    // mobile screen account settings
    if($(window).width() <= 767){
      $(".mobile-bars").click(function(){
        $(".account_setting ul.nav").slideToggle();
      });
    }
    else{
        return false;
    }
    
    $(".account_setting ul.nav li a").click(function(){
        $(".account_setting ul.nav").hide();
    });

    // $(".filter-button").click(function() {
    //     var value = $(this).attr('data-filter');

    //     if(value == "all") {
    //         $('.filter').show('1000');
    //         $(this).addClass('active');
    //     }
    //     else {
    //         $(".filter").not('.'+value).hide('2000');
    //         $('.filter').filter('.'+value).show('2000');

    //         $('.pro-filter .filter-button').removeClass('active');
    //         $(this).addClass('active');
    //     }
    // });

});
